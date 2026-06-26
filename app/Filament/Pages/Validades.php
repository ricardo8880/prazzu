<?php

namespace App\Filament\Pages;


use App\Support\CachedSchema;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class Validades extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar';
    protected static string | UnitEnum | null $navigationGroup = 'Documentos';
    protected static ?string $navigationLabel = 'Validades';
    protected static ?string $title = 'Validades Documentais';
    protected static ?int $navigationSort = 40;
    protected string $view = 'filament.pages.validades';

    public function getHeading(): string
    {
        return 'Validades documentais';
    }

    public function getSubheading(): ?string
    {
        return 'Visão especializada dos vencimentos documentais. A fonte oficial continua sendo Documentos.';
    }


    protected function getViewData(): array
    {
        return ['resumo' => $this->resumo(), 'validades' => $this->validades()];
    }

    private function resumo(): array
    {
        if (! CachedSchema::hasTable('item_controles') || ! $this->hasColumn('data_vencimento')) {
            return ['total' => 0, 'vencidos' => 0, 'seteDias' => 0, 'trintaDias' => 0, 'semData' => 0, 'concluidos' => 0];
        }

        $query = $this->baseQuery();

        return [
            'total' => (clone $query)->whereNotNull('data_vencimento')->count(),
            'vencidos' => (clone $query)->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString())->count(),
            'seteDias' => (clone $query)->whereBetween('data_vencimento', [now()->toDateString(), now()->addDays(7)->toDateString()])->count(),
            'trintaDias' => (clone $query)->whereBetween('data_vencimento', [now()->toDateString(), now()->addDays(30)->toDateString()])->count(),
            'semData' => (clone $query)->whereNull('data_vencimento')->count(),
            'concluidos' => $this->hasColumn('status') ? (clone $query)->whereIn('status', $this->statusFinalizados())->count() : 0,
        ];
    }

    private function validades(): array
    {
        if (! CachedSchema::hasTable('item_controles') || ! $this->hasColumn('data_vencimento')) {
            return [];
        }

        return $this->baseQuery()
            ->select($this->selectColumns())
            ->with($this->withRelations())
            ->whereNotNull('data_vencimento')
            ->orderBy('data_vencimento')
            ->orderByDesc($this->hasColumn('updated_at') ? 'updated_at' : 'id')
            ->limit(24)
            ->get()
            ->map(function (ItemControle $item): array {
                $empresa = $item->relationLoaded('empresa') ? $item->empresa : null;

                return [
                    'id' => $item->id,
                    'titulo' => $this->value($item, 'titulo') ?: 'Documento sem título',
                    'descricao' => $this->value($item, 'descricao'),
                    'tipo' => $this->value($item, 'tipo'),
                    'status' => $this->value($item, 'status'),
                    'prioridade' => $this->value($item, 'prioridade'),
                    'data_vencimento' => $this->value($item, 'data_vencimento'),
                    'data_conclusao' => $this->value($item, 'data_conclusao'),
                    'ultimo_lembrete_enviado_em' => $this->value($item, 'ultimo_lembrete_enviado_em'),
                    'qtd_lembretes_enviados' => $this->value($item, 'qtd_lembretes_enviados') ?? 0,
                    'nome_fantasia' => $empresa?->nome_fantasia,
                    'razao_social' => $empresa?->razao_social,
                    'edit_url' => ItemControleResource::getUrl('edit', ['record' => $item->id]),
                ];
            })
            ->all();
    }

    private function baseQuery(): Builder
    {
        return ItemControle::query()->visibleForUser(auth()->user());
    }

    /** @return array<int, string> */
    private function selectColumns(): array
    {
        $columns = ['id'];

        foreach ([
            'titulo', 'descricao', 'tipo', 'status', 'prioridade', 'data_vencimento', 'data_conclusao',
            'ultimo_lembrete_enviado_em', 'qtd_lembretes_enviados', 'empresa_id', 'created_at', 'updated_at',
        ] as $column) {
            if ($this->hasColumn($column)) {
                $columns[] = $column;
            }
        }

        return array_values(array_unique($columns));
    }

    /** @return array<int, string> */
    private function withRelations(): array
    {
        return CachedSchema::hasTable('empresas') && $this->hasColumn('empresa_id')
            ? ['empresa:id,razao_social,nome_fantasia']
            : [];
    }

    private function value(ItemControle $item, string $column): mixed
    {
        return $this->hasColumn($column) ? $item->getAttribute($column) : null;
    }

    private function hasColumn(string $column): bool
    {
        return CachedSchema::hasColumn('item_controles', $column);
    }

    /** @return array<int, string> */
    private function statusFinalizados(): array
    {
        return ['concluido', 'concluído', 'finalizado', 'cancelado', 'aprovado'];
    }
}
