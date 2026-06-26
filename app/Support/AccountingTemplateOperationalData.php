<?php

namespace App\Support;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Filament\Resources\PrazzuTemplates\PrazzuTemplateResource;
use Filament\Facades\Filament;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AccountingTemplateOperationalData
{
    private const DONE_STATUSES = ['concluido', 'concluído', 'finalizado', 'finalizada', 'cancelado'];

    public function summary(): array
    {
        if (! $this->hasTable('item_controles')) {
            return $this->emptySummary();
        }

        $base = $this->baseItemsQuery();

        return [
            'templates_active' => $this->activeTemplateCount(),
            'processes_open' => $this->openProcessCount(),
            'tasks_open' => (clone $base)->whereNotIn('item_controles.status', self::DONE_STATUSES)->count(),
            'tasks_late' => (clone $base)
                ->whereNotIn('item_controles.status', self::DONE_STATUSES)
                ->whereNotNull('item_controles.data_vencimento')
                ->whereDate('item_controles.data_vencimento', '<', now()->toDateString())
                ->count(),
            'blocked' => $this->blockedCount(),
            'pending_documents' => $this->pendingDocumentsCount(),
            'pending_approvals' => $this->pendingApprovalsCount(),
            'by_template' => $this->byTemplate(),
            'origin_url' => $this->safeUrl(ItemControleResource::class),
            'templates_url' => $this->safeUrl(PrazzuTemplateResource::class),
        ];
    }

    public function executiveDecisionCard(): array
    {
        $summary = $this->summary();
        $risk = (int) $summary['tasks_late'] + (int) $summary['blocked'] + (int) $summary['pending_documents'] + (int) $summary['pending_approvals'];

        return [
            'key' => 'templates_contabeis',
            'label' => 'Templates contábeis em execução',
            'value' => number_format((int) $summary['processes_open'], 0, ',', '.'),
            'raw' => (int) $summary['processes_open'],
            'hint' => sprintf(
                '%s tarefa(s) abertas • %s atrasada(s) • %s bloqueio(s)',
                number_format((int) $summary['tasks_open'], 0, ',', '.'),
                number_format((int) $summary['tasks_late'], 0, ',', '.'),
                number_format((int) $summary['blocked'], 0, ',', '.')
            ),
            'icon' => '📚',
            'action_label' => 'Abrir execução',
            'source_label' => 'Central Operacional',
            'priority' => 5,
            'tone' => $risk > 0 ? ($summary['tasks_late'] > 0 ? 'danger' : 'warning') : 'success',
            'url' => $summary['origin_url'],
        ];
    }

    public function riskRows(int $limit = 5): array
    {
        if (! $this->hasTable('item_controles')) {
            return [];
        }

        return $this->baseItemsQuery()
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->where(function (Builder $query): void {
                $query->whereDate('item_controles.data_vencimento', '<=', now()->addDays(3)->toDateString());

                foreach (['bloqueado', 'bloqueado_por_dependencia', 'blocked_by_dependency'] as $column) {
                    if ($this->hasColumn('item_controles', $column)) {
                        $query->orWhere('item_controles.' . $column, 1);
                    }
                }

                if ($this->hasColumn('item_controles', 'document_status')) {
                    $query->orWhereIn('item_controles.document_status', ['pendente', 'aguardando', 'aguardando_cliente', 'em_revisao']);
                }

                if ($this->hasColumn('item_controles', 'approval_status')) {
                    $query->orWhereIn('item_controles.approval_status', ['pendente', 'aguardando', 'em_aprovacao']);
                }
            })
            ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.data_vencimento')
            ->limit($limit)
            ->get()
            ->map(fn (object $item): array => [
                'title' => $item->titulo ?? 'Tarefa de template',
                'status' => $this->statusLabel($item),
                'meta' => trim(($item->empresa_nome ?? 'Empresa') . ' • ' . ($item->template_nome ?? 'Template contábil')),
                'description' => 'Tarefa gerada por template contábil. Acompanhe pela Central Operacional, Checklist, Documentos, SLA, Kanban, Timeline e Gantt sem duplicar gestão.',
                'tone' => $this->rowTone($item),
                'url' => $this->safeUrl(ItemControleResource::class),
                'action_label' => 'Abrir tarefa',
                'deadline' => ! empty($item->data_vencimento) ? date('d/m', strtotime((string) $item->data_vencimento)) : null,
            ])
            ->all();
    }

    private function byTemplate(): array
    {
        if (! $this->hasTable('prazzu_templates')) {
            return [];
        }

        return $this->baseItemsQuery()
            ->select('prazzu_templates.name as template', DB::raw('COUNT(*) as total'))
            ->leftJoin('prazzu_templates', 'prazzu_templates.id', '=', 'item_controles.template_id')
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->groupBy('prazzu_templates.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn (object $row): array => [
                'template' => $row->template ?: 'Template não identificado',
                'total' => (int) $row->total,
            ])
            ->all();
    }

    private function baseItemsQuery(): Builder
    {
        $query = DB::table('item_controles')
            ->whereNotNull('item_controles.template_id');

        if ($this->hasTable('prazzu_templates')) {
            $query->leftJoin('prazzu_templates as tpl_summary', 'tpl_summary.id', '=', 'item_controles.template_id')
                ->where(function (Builder $query): void {
                    $query->whereIn('tpl_summary.module', ['contabil', 'rh', 'societario'])
                        ->orWhereIn('item_controles.tipo', ['contabil', 'fiscal', 'dp', 'societario']);
                });
        }

        if ($this->hasTable('empresas')) {
            $query->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id');
        }

        $query->select('item_controles.*');

        if ($this->hasTable('empresas')) {
            $query->addSelect(DB::raw('COALESCE(empresas.nome_fantasia, empresas.razao_social) as empresa_nome'));
        }

        if ($this->hasTable('prazzu_templates')) {
            $query->addSelect('tpl_summary.name as template_nome');
        }

        if ($this->empresaId()) {
            $query->where('item_controles.empresa_id', $this->empresaId());
        }

        return $query;
    }

    private function blockedCount(): int
    {
        $query = $this->baseItemsQuery()->whereNotIn('item_controles.status', self::DONE_STATUSES);

        return (int) $query->where(function (Builder $query): void {
            foreach (['bloqueado', 'bloqueado_por_dependencia', 'blocked_by_dependency'] as $column) {
                if ($this->hasColumn('item_controles', $column)) {
                    $query->orWhere('item_controles.' . $column, 1);
                }
            }
        })->count();
    }

    private function pendingDocumentsCount(): int
    {
        if (! $this->hasColumn('item_controles', 'document_status')) {
            return 0;
        }

        return (int) $this->baseItemsQuery()
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->whereIn('item_controles.document_status', ['pendente', 'aguardando', 'aguardando_cliente', 'em_revisao'])
            ->count();
    }

    private function pendingApprovalsCount(): int
    {
        if (! $this->hasColumn('item_controles', 'approval_status')) {
            return 0;
        }

        return (int) $this->baseItemsQuery()
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->whereIn('item_controles.approval_status', ['pendente', 'aguardando', 'em_aprovacao'])
            ->count();
    }

    private function activeTemplateCount(): int
    {
        if (! $this->hasTable('prazzu_templates')) {
            return 0;
        }

        return (int) DB::table('prazzu_templates')
            ->where('active', 1)
            ->where(function (Builder $query): void {
                $query->whereIn('module', ['contabil', 'rh', 'societario']);
            })
            ->count();
    }

    private function statusLabel(object $item): string
    {
        if (! empty($item->data_vencimento) && strtotime((string) $item->data_vencimento) < strtotime(now()->toDateString())) {
            return 'Atrasado';
        }

        if (($item->bloqueado ?? false) || ($item->blocked_by_dependency ?? false) || ($item->bloqueado_por_dependencia ?? false)) {
            return 'Bloqueado';
        }

        if (($item->document_status ?? null) === 'pendente') {
            return 'Documento';
        }

        if (($item->approval_status ?? null) === 'pendente') {
            return 'Aprovação';
        }

        return 'Atenção';
    }

    private function rowTone(object $item): string
    {
        return $this->statusLabel($item) === 'Atrasado' ? 'danger' : 'warning';
    }

    private function emptySummary(): array
    {
        return [
            'templates_active' => 0,
            'processes_open' => 0,
            'tasks_open' => 0,
            'tasks_late' => 0,
            'blocked' => 0,
            'pending_documents' => 0,
            'pending_approvals' => 0,
            'by_template' => [],
            'origin_url' => null,
            'templates_url' => null,
        ];
    }

    private function safeUrl(string $class): ?string
    {
        try {
            if (method_exists($class, 'canAccess') && ! $class::canAccess()) {
                return null;
            }

            return $class::getUrl();
        } catch (Throwable) {
            return null;
        }
    }

    private function empresaId(): ?int
    {
        $user = Filament::auth()->user();

        if (! $user || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            return null;
        }

        return $user->empresa_id ? (int) $user->empresa_id : null;
    }

    private function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (Throwable) {
            return false;
        }
    }
}
