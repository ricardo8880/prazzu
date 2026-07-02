<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControleChecklist;
use App\Support\PrazzuAccessControl;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class Checklist extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string | UnitEnum | null $navigationGroup = 'Operação';

    protected static ?string $navigationLabel = 'Checklists';

    protected static ?string $title = 'Checklists';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.checklist';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return PrazzuAccessControl::canUseChecklist();
    }

    protected function baseQuery(): Builder
    {
        return ItemControleChecklist::query()
            ->with(['itemControle.empresa:id,razao_social', 'itemControle.responsavel:id,nome'])
            ->whereHas('itemControle', fn (Builder $query): Builder => $query->visibleForUser(Filament::auth()->user()));
    }

    public function getResumo(): array
    {
        $query = $this->baseQuery();
        $total = (clone $query)->count();
        $concluidos = (clone $query)->where('concluido', true)->count();

        return [
            'total' => $total,
            'pendentes' => max($total - $concluidos, 0),
            'concluidos' => $concluidos,
            'percentual' => $total > 0 ? round(($concluidos / $total) * 100) : 0,
        ];
    }

    public function getItens(): array
    {
        return $this->baseQuery()
            ->latest('updated_at')
            ->limit(15)
            ->get()
            ->map(fn (ItemControleChecklist $checklist): array => [
                'titulo' => $checklist->titulo,
                'concluido' => (bool) $checklist->concluido,
                'item' => $checklist->itemControle?->titulo ?? '-',
                'empresa' => $checklist->itemControle?->empresa?->razao_social ?? '-',
                'responsavel' => $checklist->itemControle?->responsavel?->nome ?? '-',
                'url' => $checklist->itemControle ? ItemControleResource::getUrl('edit', ['record' => $checklist->itemControle]) : '#',
            ])
            ->all();
    }
}
