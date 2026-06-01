<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrazzuTemplate extends Model
{
    protected $table = 'prazzu_templates';

    protected $fillable = [
        'module',
        'name',
        'description',
        'payload',
        'active',
    ];

    protected $casts = [
        'payload' => 'array',
        'active' => 'boolean',
    ];

    public function getTasksCountAttribute(): int
    {
        return count($this->payloadValue('tasks', []));
    }

    public function getAutomationsCountAttribute(): int
    {
        return count($this->payloadValue('automations', []));
    }

    public function getCustomFieldsCountAttribute(): int
    {
        return count($this->payloadValue('custom_fields', []));
    }

    public function getViewsCountAttribute(): int
    {
        return count($this->payloadValue('views', []));
    }

    public function getChecklistCountAttribute(): int
    {
        $total = 0;

        foreach ($this->payloadValue('tasks', []) as $task) {
            $total += count(Arr::get($task, 'checklist', []));
        }

        return $total;
    }

    public function getBlueprintLabelAttribute(): string
    {
        $parts = [];

        if ($this->views_count > 0) {
            $parts[] = $this->views_count . ' visão(ões)';
        }

        if ($this->custom_fields_count > 0) {
            $parts[] = $this->custom_fields_count . ' campo(s)';
        }

        if ($this->automations_count > 0) {
            $parts[] = $this->automations_count . ' automação(ões)';
        }

        if ($this->tasks_count > 0) {
            $parts[] = $this->tasks_count . ' tarefa(s)';
        }

        return $parts ? implode(' • ', $parts) : 'Template sem estrutura definida';
    }

    public function payloadValue(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->payload ?? [], $key, $default);
    }

    public function instantiateFor(int $empresaId, ?int $responsavelId = null, ?Carbon $firstDueDate = null, ?int $userId = null): int
    {
        $tasks = $this->payloadValue('tasks', []);

        if (empty($tasks)) {
            $tasks = [[
                'title' => $this->name,
                'description' => $this->description,
                'type' => $this->module ?: 'tarefa',
                'priority' => 'media',
                'days_after_start' => 0,
                'estimated_minutes' => null,
                'checklist' => [],
            ]];
        }

        return DB::transaction(function () use ($tasks, $empresaId, $responsavelId, $firstDueDate, $userId): int {
            $created = 0;
            $startDate = $firstDueDate ?: now();

            foreach ($tasks as $position => $task) {
                $dueDate = (clone $startDate)->addDays((int) Arr::get($task, 'days_after_start', $position));
                $slaHours = Arr::get($task, 'sla_hours');
                $estimatedMinutes = Arr::get($task, 'estimated_minutes');
                $approvalRequired = (bool) Arr::get($task, 'approval_required', false);

                $item = ItemControle::query()->create([
                    'empresa_id' => $empresaId,
                    'responsavel_id' => $responsavelId,
                    'titulo' => Str::limit((string) Arr::get($task, 'title', $this->name), 255, ''),
                    'descricao' => Arr::get($task, 'description', $this->description),
                    'tipo' => Arr::get($task, 'type', $this->module ?: 'tarefa'),
                    'status' => Arr::get($task, 'status', 'pendente'),
                    'prioridade' => Arr::get($task, 'priority', 'media'),
                    'data_vencimento' => $dueDate->toDateString(),
                    'sla_horas' => is_numeric($slaHours) ? (int) $slaHours : null,
                    'estimated_minutes' => is_numeric($estimatedMinutes) ? (int) $estimatedMinutes : null,
                    'template_id' => $this->id,
                    'approval_required' => $approvalRequired,
                    'approval_status' => $approvalRequired ? 'pendente' : null,
                    'custom_payload' => [
                        'template' => [
                            'id' => $this->id,
                            'name' => $this->name,
                            'module' => $this->module,
                        ],
                        'custom_fields' => $this->payloadValue('custom_fields', []),
                        'views' => $this->payloadValue('views', []),
                        'automations' => $this->payloadValue('automations', []),
                        'recurrence' => Arr::get($task, 'recurrence'),
                        'proofing' => Arr::get($task, 'proofing', $this->payloadValue('proofing', [])),
                        'docs' => $this->payloadValue('docs', []),
                        'mind_map' => $this->payloadValue('mind_map', []),
                        'created_from_template_by' => $userId,
                    ],
                ]);

                foreach (Arr::get($task, 'checklist', []) as $order => $checklistTitle) {
                    $checklistTitle = is_array($checklistTitle) ? Arr::get($checklistTitle, 'titulo', Arr::get($checklistTitle, 'title')) : $checklistTitle;

                    if (blank($checklistTitle)) {
                        continue;
                    }

                    ItemControleChecklist::query()->create([
                        'item_controle_id' => $item->id,
                        'titulo' => Str::limit((string) $checklistTitle, 255, ''),
                        'concluido' => false,
                        'ordem' => $order + 1,
                    ]);
                }

                $created++;
            }

            return $created;
        });
    }
}
