<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use App\Services\PrazzuSlaEngine;
use App\Services\PrazzuSlaService;
use App\Services\ItemControleCoreService;
use App\Support\CachedSchema;
use App\Support\PrazzuAccessControl;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

class SlaPrazos extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static string | UnitEnum | null $navigationGroup = 'Pendências e Prazos';

    protected static ?string $navigationLabel = 'SLA e Prazos';

    protected static ?string $title = 'Monitor de SLA e Prazos';

    protected static ?int $navigationSort = 20;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected string $view = 'filament.pages.sla-prazos';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return PrazzuAccessControl::canAccessPage('tarefas.view');
    }

    public string $aba = 'sla-prazos';

    public ?int $itemSelecionadoId = null;

    public bool $modalAberto = false;

    public function mount(): void
    {
        $aba = request()->query('aba');

        if (is_string($aba) && collect($this->abas())->contains(fn (array $item): bool => $item['key'] === $aba)) {
            $this->aba = $aba;
        }
    }

    public function getSubNavigation(): array
    {
        return collect($this->abas())
            ->map(fn (array $item): NavigationItem => NavigationItem::make($item['label'])
                ->icon($item['icon'])
                ->url(static::getUrl(['aba' => $item['key']]))
                ->isActiveWhen(fn (): bool => $this->aba === $item['key'])
                ->sort($item['sort']))
            ->all();
    }

    /** @return array<int, array{key: string, label: string, icon: string, sort: int}> */
    protected function abas(): array
    {
        return [
            ['key' => 'sla-prazos', 'label' => 'SLA e Prazos', 'icon' => 'heroicon-o-clock', 'sort' => 1],
            ['key' => 'validades', 'label' => 'Validades', 'icon' => 'heroicon-o-calendar-days', 'sort' => 2],
        ];
    }

    protected function baseQuery(): Builder
    {
        return ItemControle::query()
            ->with([
                'empresa:id,razao_social,nome_fantasia',
                'responsavel:id,nome',
                'categoria:id,nome',
                'checklists:id,item_controle_id,titulo,concluido,ordem',
            ])
            ->visibleForUser(Filament::auth()->user());
    }

    public function abrirItem(int $itemId): void
    {
        $this->itemSelecionadoId = $itemId;
        $this->modalAberto = true;
    }

    public function fecharModal(): void
    {
        $this->itemSelecionadoId = null;
        $this->modalAberto = false;
    }

    public function getResumo(): array
    {
        $query = $this->baseQuery();

        return [
            'com_sla' => (clone $query)->where(function (Builder $query): void {
                $query->whereNotNull('sla_limite_em')->orWhereNotNull('data_vencimento');
            })->count(),
            'em_andamento' => (clone $query)->whereIn('sla_status', PrazzuSlaEngine::statusAbertos())->count(),
            'vencidos' => (clone $query)->where(function (Builder $query): void {
                $query->where('sla_status', PrazzuSlaEngine::STATUS_VENCIDO)
                    ->orWhere(function (Builder $query): void {
                        $query->whereNotNull('sla_limite_em')
                            ->whereNull('sla_concluido_em')
                            ->where('sla_limite_em', '<', now());
                    })
                    ->orWhere(function (Builder $query): void {
                        $query->whereNull('sla_limite_em')
                            ->whereNotNull('data_vencimento')
                            ->whereNull('data_conclusao')
                            ->whereDate('data_vencimento', '<', now()->toDateString());
                    });
            })->count(),
            'concluidos' => (clone $query)->where(function (Builder $query): void {
                $query->whereNotNull('sla_concluido_em')
                    ->orWhereIn('sla_status', PrazzuSlaEngine::statusFinalizados())
                    ->orWhereIn('status', $this->statusFinalizados());
            })->count(),
        ];
    }

    public function getItensCriticos(): array
    {
        return $this->baseQuery()
            ->where(function (Builder $query): void {
                $query->whereNotNull('sla_limite_em')->orWhereNotNull('data_vencimento');
            })
            ->whereNull('sla_concluido_em')
            ->whereNotIn('status', $this->statusFinalizados())
            ->orderByRaw('COALESCE(sla_limite_em, data_vencimento) ASC')
            ->limit(12)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarItemSla($item))
            ->all();
    }


    public function getResumoValidades(): array
    {
        if (! CachedSchema::hasTable('item_controles') || ! $this->hasColumn('data_vencimento')) {
            return ['total' => 0, 'vencidos' => 0, 'sete_dias' => 0, 'trinta_dias' => 0, 'sem_data' => 0, 'concluidos' => 0];
        }

        $query = $this->baseQuery();

        return [
            'total' => (clone $query)->whereNotNull('data_vencimento')->count(),
            'vencidos' => (clone $query)->whereNotIn('status', $this->statusFinalizados())->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString())->count(),
            'sete_dias' => (clone $query)->whereNotIn('status', $this->statusFinalizados())->whereBetween('data_vencimento', [now()->toDateString(), now()->copy()->addDays(7)->toDateString()])->count(),
            'trinta_dias' => (clone $query)->whereNotIn('status', $this->statusFinalizados())->whereBetween('data_vencimento', [now()->toDateString(), now()->copy()->addDays(30)->toDateString()])->count(),
            'sem_data' => (clone $query)->whereNull('data_vencimento')->count(),
            'concluidos' => $this->hasColumn('status') ? (clone $query)->whereIn('status', $this->statusFinalizados())->count() : 0,
        ];
    }

    public function getValidadesDocumentais(): array
    {
        if (! CachedSchema::hasTable('item_controles') || ! $this->hasColumn('data_vencimento')) {
            return [];
        }

        return $this->baseQuery()
            ->whereNotNull('data_vencimento')
            ->orderBy('data_vencimento')
            ->orderByDesc($this->hasColumn('updated_at') ? 'updated_at' : 'id')
            ->limit(24)
            ->get()
            ->map(function (ItemControle $item): array {
                $vencimento = $item->data_vencimento;
                $dias = $vencimento ? now()->startOfDay()->diffInDays($vencimento->copy()->startOfDay(), false) : null;
                $vencido = $dias !== null && $dias < 0;

                return [
                    'id' => $item->id,
                    'titulo' => $item->titulo ?: 'Documento sem título',
                    'descricao' => filled($item->descricao) ? (string) $item->descricao : 'Sem descrição cadastrada.',
                    'tipo' => ucfirst(str_replace('_', ' ', (string) ($item->tipo ?: '-'))),
                    'status' => ucfirst(str_replace('_', ' ', (string) ($item->status ?: '-'))),
                    'prioridade' => ucfirst(str_replace('_', ' ', (string) ($item->prioridade ?: '-'))),
                    'empresa' => $item->empresa?->nome_fantasia ?: ($item->empresa?->razao_social ?? '-'),
                    'responsavel' => $item->responsavel?->nome ?? '-',
                    'vencimento' => $vencimento?->format('d/m/Y') ?? '-',
                    'dias' => $dias,
                    'situacao' => $dias === null ? 'Sem data' : ($vencido ? 'Vencido há ' . abs((int) $dias) . ' dia(s)' : 'Faltam ' . (int) $dias . ' dia(s)'),
                    'vencido' => $vencido,
                    'lembretes' => (int) ($item->qtd_lembretes_enviados ?? 0),
                    'ultimo_lembrete' => filled($item->ultimo_lembrete_enviado_em) ? \Carbon\Carbon::parse($item->ultimo_lembrete_enviado_em)->format('d/m/Y H:i') : '-',
                    'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
                ];
            })
            ->all();
    }

    public function getItemSelecionado(): ?array
    {
        if (! $this->itemSelecionadoId) {
            return null;
        }

        $item = $this->baseQuery()->find($this->itemSelecionadoId);

        if (! $item) {
            $this->fecharModal();
            return null;
        }

        $checklists = $item->checklists;
        $totalChecklist = $checklists->count();
        $checklistsConcluidos = $checklists->where('concluido', true)->count();
        $percentualChecklist = $totalChecklist > 0 ? round(($checklistsConcluidos / $totalChecklist) * 100) : 0;
        $limite = $item->sla_limite_em;
        $vencido = $limite && $limite->isPast() && blank($item->sla_concluido_em);

        return array_merge($this->formatarItemSla($item), [
            'tipo' => ucfirst(str_replace('_', ' ', (string) $item->tipo)),
            'prioridade' => ucfirst((string) $item->prioridade),
            'categoria' => $item->categoria?->nome ?? '-',
            'descricao' => filled($item->descricao) ? (string) $item->descricao : 'Sem descrição cadastrada.',
            'observacao' => filled($item->observacao) ? (string) $item->observacao : null,
            'data_vencimento' => $item->data_vencimento?->format('d/m/Y') ?? '-',
            'criado_em' => $item->created_at?->format('d/m/Y H:i') ?? '-',
            'atualizado_em' => $item->updated_at?->format('d/m/Y H:i') ?? '-',
            'sla_status' => ucfirst(str_replace('_', ' ', (string) ($item->sla_status ?: 'pendente'))),
            'sla_concluido_em' => $item->sla_concluido_em?->format('d/m/Y H:i') ?? '-',
            'tempo_restante' => $this->formatarTempoRestante($item),
            'vencido' => $vencido,
            'checklists_total' => $totalChecklist,
            'checklists_concluidos' => $checklistsConcluidos,
            'checklists_percentual' => $percentualChecklist,
            'checklists' => $checklists->sortBy('ordem')->map(fn ($checklist): array => [
                'titulo' => $checklist->titulo,
                'concluido' => (bool) $checklist->concluido,
            ])->values()->all(),
        ]);
    }

    protected function formatarItemSla(ItemControle $item): array
    {
        $limite = $item->sla_limite_em ?: $item->data_vencimento;
        $slaService = app(PrazzuSlaService::class);
        $slaStatus = $slaService->status($item);
        $vencido = $slaStatus === PrazzuSlaEngine::STATUS_VENCIDO;

        return [
            'id' => $item->id,
            'titulo' => $item->titulo,
            'titulo_curto' => Str::limit((string) $item->titulo, 80),
            'status' => $this->formatarStatusSla($slaStatus),
            'sla' => $item->sla_horas ? $item->sla_horas . 'h' : 'Prazo por vencimento',
            'limite' => $limite?->format($item->sla_limite_em ? 'd/m/Y H:i' : 'd/m/Y') ?? '-',
            'tempo_restante' => $slaService->tempoRestante($item),
            'empresa' => $item->empresa?->razao_social ?? '-',
            'responsavel' => $item->responsavel?->nome ?? '-',
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
        ];
    }


    protected function hasColumn(string $column): bool
    {
        return CachedSchema::hasColumn('item_controles', $column);
    }

    /** @return array<int, string> */
    protected function statusFinalizados(): array
    {
        return [
            ItemControleCoreService::STATUS_CONCLUIDO,
            'concluído',
            'finalizado',
            ItemControleCoreService::STATUS_CANCELADO,
            ItemControleCoreService::STATUS_APROVADO,
            ItemControleCoreService::STATUS_ASSINADO,
        ];
    }

    protected function formatarStatusSla(string $status): string
    {
        return match ($status) {
            PrazzuSlaEngine::STATUS_EM_ANDAMENTO => 'Em andamento',
            PrazzuSlaEngine::STATUS_RISCO => 'Em risco',
            PrazzuSlaEngine::STATUS_VENCIDO => 'Vencido',
            PrazzuSlaEngine::STATUS_CONCLUIDO_NO_PRAZO => 'Concluído no prazo',
            PrazzuSlaEngine::STATUS_CONCLUIDO_ATRASADO => 'Concluído com atraso',
            default => 'Sem SLA',
        };
    }

    protected function formatarTempoRestante(ItemControle $item): string
    {
        if (! $item->sla_limite_em) {
            return 'Sem limite definido';
        }

        if ($item->sla_concluido_em) {
            return 'Concluído em ' . $item->sla_concluido_em->format('d/m/Y H:i');
        }

        if ($item->sla_limite_em->isPast()) {
            return 'Vencido há ' . $item->sla_limite_em->diffForHumans(null, true);
        }

        return 'Vence em ' . now()->diffForHumans($item->sla_limite_em, true);
    }
}
