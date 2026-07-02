<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\ItemControle;
use App\Models\ItemControleChecklist;
use App\Models\ItemControleTimeline;
use App\Models\PrazzuDependency;
use App\Models\PrazzuDocumentVersion;
use App\Models\PrazzuTemplate;
use App\Models\Responsavel;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PrazzuTemplateApplicationService
{
    public function apply(PrazzuTemplate $template, array $options): array
    {
        $empresaId = (int) ($options['empresa_id'] ?? 0);
        $empresa = Empresa::query()->find($empresaId);

        if (! $empresa || ! $empresa->isAtivo()) {
            throw ValidationException::withMessages([
                'empresa_id' => 'Empresa inativa ou não vinculada. Não é possível aplicar o template.',
            ]);
        }

        $responsavelId = $this->resolveResponsavelId($empresaId, $options['responsavel_id'] ?? null);
        $startDate = Carbon::parse($options['data_inicio'] ?? now());
        $userId = filled($options['user_id'] ?? null) ? (int) $options['user_id'] : null;
        $createDependencies = (bool) ($options['create_dependencies'] ?? true);
        $createDocuments = (bool) ($options['create_documents'] ?? true);
        $recurrence = $options['recurrence'] ?? $template->payloadValue('recurrence');
        $processInstanceId = (string) Str::uuid();
        $tasks = $this->normalizeTasks($template);

        return DB::transaction(function () use ($template, $tasks, $empresaId, $responsavelId, $startDate, $userId, $createDependencies, $createDocuments, $recurrence, $processInstanceId): array {
            $createdItems = [];
            $createdChecklists = 0;
            $createdDocuments = 0;
            $createdDependencies = 0;

            foreach ($tasks as $position => $task) {
                $item = $this->createItemControle(
                    template: $template,
                    task: $task,
                    position: $position,
                    empresaId: $empresaId,
                    responsavelId: $responsavelId,
                    startDate: $startDate,
                    userId: $userId,
                    recurrence: $recurrence,
                    processInstanceId: $processInstanceId,
                );

                $createdItems[$position] = $item;
                $this->registerOperationalTimeline($item, $template, $task, $userId, $processInstanceId);
                $createdChecklists += $this->createChecklist($item, Arr::get($task, 'checklist', []));

                if ($createDocuments) {
                    $createdDocuments += $this->createDocumentPlaceholders($item, $template, $task, $userId);
                }
            }

            if ($createDependencies) {
                $createdDependencies = $this->createDependencies($createdItems, $tasks);
            }

            return [
                'process_instance_id' => $processInstanceId,
                'items_created' => count($createdItems),
                'checklists_created' => $createdChecklists,
                'documents_created' => $createdDocuments,
                'dependencies_created' => $createdDependencies,
                'recurrence' => $recurrence,
            ];
        });
    }

    private function normalizeTasks(PrazzuTemplate $template): array
    {
        $tasks = $template->payloadValue('tasks', []);

        if (! empty($tasks)) {
            return array_values($tasks);
        }

        return [[
            'title' => $template->name,
            'description' => $template->description,
            'type' => $template->module ?: 'tarefa',
            'priority' => 'media',
            'days_after_start' => 0,
            'estimated_minutes' => null,
            'checklist' => [],
        ]];
    }

    private function resolveResponsavelId(int $empresaId, mixed $responsavelId): int
    {
        if (filled($responsavelId)) {
            $responsavel = Responsavel::query()
                ->whereKey((int) $responsavelId)
                ->where('empresa_id', $empresaId)
                ->first();

            if ($responsavel) {
                return (int) $responsavel->id;
            }
        }

        $fallback = Responsavel::query()
            ->where('empresa_id', $empresaId)
            ->orderByRaw("CASE WHEN cargo LIKE '%Administrador%' THEN 0 WHEN cargo LIKE '%Gestor%' THEN 1 ELSE 2 END")
            ->orderBy('id')
            ->value('id');

        if (! $fallback) {
            throw ValidationException::withMessages([
                'responsavel_id' => 'A empresa selecionada não possui responsável operacional cadastrado.',
            ]);
        }

        return (int) $fallback;
    }

    private function createItemControle(PrazzuTemplate $template, array $task, int $position, int $empresaId, int $responsavelId, Carbon $startDate, ?int $userId, mixed $recurrence, string $processInstanceId): ItemControle
    {
        $dueDate = (clone $startDate)->addDays((int) Arr::get($task, 'days_after_start', $position));
        $slaHours = Arr::get($task, 'sla_hours');
        $estimatedMinutes = Arr::get($task, 'estimated_minutes');
        $approvalRequired = (bool) Arr::get($task, 'approval_required', false);
        $priority = (string) Arr::get($task, 'priority', 'media');
        $slaStart = now();
        $slaLimit = is_numeric($slaHours) ? (clone $slaStart)->addHours((int) $slaHours) : null;

        return ItemControle::query()->create([
            'empresa_id' => $empresaId,
            'responsavel_id' => $responsavelId,
            'titulo' => Str::limit((string) Arr::get($task, 'title', $template->name), 255, ''),
            'descricao' => Arr::get($task, 'description', $template->description),
            'tipo' => Arr::get($task, 'type', $template->module ?: 'tarefa'),
            'status' => Arr::get($task, 'status', 'pendente'),
            'prioridade' => $priority,
            'urgencia' => $this->priorityToUrgencia($priority),
            'data_vencimento' => $dueDate->toDateString(),
            'sla_horas' => is_numeric($slaHours) ? (int) $slaHours : null,
            'sla_inicio_em' => $slaLimit ? $slaStart : null,
            'sla_limite_em' => $slaLimit,
            'sla_prazo_alvo_em' => $slaLimit,
            'sla_status' => $slaLimit ? 'em_andamento' : null,
            'status_operacional_at' => now(),
            'estimated_minutes' => is_numeric($estimatedMinutes) ? (int) $estimatedMinutes : null,
            'template_id' => $template->id,
            'approval_required' => $approvalRequired,
            'approval_status' => $approvalRequired ? 'pendente' : null,
            'document_status' => $this->hasDocuments($template, $task) ? 'pendente' : null,
            'custom_payload' => [
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'module' => $template->module,
                    'family' => $template->payloadValue('family'),
                    'area' => $template->payloadValue('area'),
                    'official' => (bool) $template->payloadValue('official', false),
                ],
                'template_process' => [
                    'instance_id' => $processInstanceId,
                    'task_index' => $position,
                    'task_key' => Arr::get($task, 'key', Str::slug((string) Arr::get($task, 'title', 'tarefa-' . $position))),
                    'created_from_template_by' => $userId,
                    'applied_at' => now()->toDateTimeString(),
                ],
                'custom_fields' => $template->payloadValue('custom_fields', []),
                'views' => $template->payloadValue('views', []),
                'automations' => $template->payloadValue('automations', []),
                'recurrence' => $recurrence ?: Arr::get($task, 'recurrence'),
                'proofing' => Arr::get($task, 'proofing', $template->payloadValue('proofing', [])),
                'docs' => $this->documentsForPayload($template, $task),
                'mind_map' => $template->payloadValue('mind_map', []),
            ],
        ]);
    }

    private function registerOperationalTimeline(ItemControle $item, PrazzuTemplate $template, array $task, ?int $userId, string $processInstanceId): void
    {
        ItemControleTimeline::query()->create([
            'item_controle_id' => $item->id,
            'empresa_id' => $item->empresa_id,
            'user_id' => $userId,
            'tipo' => 'criacao',
            'titulo' => 'Tarefa criada a partir de template',
            'descricao' => 'Criada automaticamente pelo template "' . $template->name . '".',
            'dados' => [
                'template_id' => $template->id,
                'template_name' => $template->name,
                'process_instance_id' => $processInstanceId,
                'task_title' => Arr::get($task, 'title'),
                'task_key' => Arr::get($task, 'key'),
            ],
        ]);
    }

    private function createChecklist(ItemControle $item, array $checklist): int
    {
        $created = 0;

        foreach ($checklist as $order => $checklistItem) {
            $title = is_array($checklistItem)
                ? Arr::get($checklistItem, 'titulo', Arr::get($checklistItem, 'title'))
                : $checklistItem;

            if (blank($title)) {
                continue;
            }

            ItemControleChecklist::query()->create([
                'item_controle_id' => $item->id,
                'titulo' => Str::limit((string) $title, 255, ''),
                'concluido' => false,
                'ordem' => $order + 1,
            ]);

            $created++;
        }

        return $created;
    }

    private function createDocumentPlaceholders(ItemControle $item, PrazzuTemplate $template, array $task, ?int $userId): int
    {
        $documents = $this->documentsForPayload($template, $task);
        $created = 0;

        foreach ($documents as $document) {
            $title = Arr::get($document, 'title');

            if (blank($title)) {
                continue;
            }

            PrazzuDocumentVersion::query()->create([
                'item_controle_id' => $item->id,
                'document_type' => Str::limit(Str::slug((string) $title, '_'), 100, ''),
                'version_number' => 1,
                'file_path' => null,
                'uploaded_by' => $userId,
                'status' => 'pendente',
                'notes' => Arr::get($document, 'content', 'Documento previsto pelo template e aguardando arquivo/versão.'),
            ]);

            $created++;
        }

        return $created;
    }

    private function createDependencies(array $createdItems, array $tasks): int
    {
        $created = 0;

        foreach ($createdItems as $position => $item) {
            $dependencies = Arr::get($tasks[$position] ?? [], 'depends_on');

            if ($dependencies === null && $position > 0) {
                $dependencies = [$position - 1];
            }

            foreach (Arr::wrap($dependencies) as $dependency) {
                $dependsOn = is_numeric($dependency)
                    ? ($createdItems[(int) $dependency] ?? null)
                    : $this->findCreatedItemByTitle($createdItems, (string) $dependency);

                if (! $dependsOn || (int) $dependsOn->id === (int) $item->id) {
                    continue;
                }

                PrazzuDependency::query()->firstOrCreate([
                    'item_controle_id' => $item->id,
                    'depends_on_item_controle_id' => $dependsOn->id,
                ], [
                    'type' => 'finish_to_start',
                    'notes' => 'Dependência criada automaticamente pela aplicação do template.',
                    'blocked_until_resolved' => true,
                ]);

                $item->forceFill([
                    'bloqueado_por_dependencia' => true,
                    'blocked_by_dependency' => true,
                    'bloqueado' => true,
                ])->save();

                $created++;
            }
        }

        return $created;
    }

    private function findCreatedItemByTitle(array $createdItems, string $title): ?ItemControle
    {
        foreach ($createdItems as $item) {
            if (Str::lower($item->titulo) === Str::lower($title)) {
                return $item;
            }
        }

        return null;
    }

    private function documentsForPayload(PrazzuTemplate $template, array $task): array
    {
        return array_values(array_filter(array_merge(
            $template->payloadValue('docs', []),
            Arr::get($task, 'docs', [])
        )));
    }

    private function hasDocuments(PrazzuTemplate $template, array $task): bool
    {
        return ! empty($this->documentsForPayload($template, $task));
    }

    private function priorityToUrgencia(string $priority): string
    {
        return match ($priority) {
            'urgente' => 'critica',
            'alta' => 'alta',
            'baixa' => 'baixa',
            default => 'media',
        };
    }
}
