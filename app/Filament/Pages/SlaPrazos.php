<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use UnitEnum;

class SlaPrazos extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static string | UnitEnum | null $navigationGroup = 'Trabalho';

    protected static ?string $navigationLabel = 'SLA e Prazos';

    protected static ?string $title = 'SLA e Prazos';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.sla-prazos';

    public ?int $itemSelecionadoId = null;

    public bool $modalAberto = false;

    protected function baseQuery(): Builder
    {
        return ItemControle::query()
            ->with([
                'empresa:id,razao_social',
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
            'com_sla' => (clone $query)->whereNotNull('sla_limite_em')->count(),
            'em_andamento' => (clone $query)->where('sla_status', 'em_andamento')->count(),
            'vencidos' => (clone $query)->where(function (Builder $query): void {
                $query->where('sla_status', 'vencido')
                    ->orWhere(function (Builder $query): void {
                        $query->whereNotNull('sla_limite_em')
                            ->whereNull('sla_concluido_em')
                            ->where('sla_limite_em', '<', now());
                    });
            })->count(),
            'concluidos' => (clone $query)->whereNotNull('sla_concluido_em')->count(),
        ];
    }

    public function getItensCriticos(): array
    {
        return $this->baseQuery()
            ->whereNotNull('sla_limite_em')
            ->whereNull('sla_concluido_em')
            ->orderBy('sla_limite_em')
            ->limit(12)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarItemSla($item))
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
        $limite = $item->sla_limite_em;
        $vencido = $limite && $limite->isPast() && blank($item->sla_concluido_em);

        return [
            'id' => $item->id,
            'titulo' => $item->titulo,
            'titulo_curto' => Str::limit((string) $item->titulo, 80),
            'status' => $vencido ? 'Vencido' : 'Em andamento',
            'sla' => $item->sla_horas ? $item->sla_horas . 'h' : '-',
            'limite' => $limite?->format('d/m/Y H:i') ?? '-',
            'empresa' => $item->empresa?->razao_social ?? '-',
            'responsavel' => $item->responsavel?->nome ?? '-',
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
        ];
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
