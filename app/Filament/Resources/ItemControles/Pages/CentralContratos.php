<?php

namespace App\Filament\Resources\ItemControles\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use App\Support\PrazzuAccessControl;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;

class CentralContratos extends Page
{
    protected static string $resource = ItemControleResource::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Contratos';

    protected static ?string $title = 'Contratos';

    protected static string | \UnitEnum | null $navigationGroup = 'Documentos';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.resources.item-controles.pages.central-contratos';

    public array $contratos = [];

    public function mount(): void
    {
        $user = Filament::auth()->user();

        $this->contratos = ItemControle::query()
            ->whereNotNull('contrato_numero')
            ->when(
                $user && ! $user->isSuperAdmin(),
                fn ($query) => $query->where('empresa_id', $user->empresa_id)
            )
            ->latest()
            ->get()
            ->toArray();
    }

    public static function canAccess(array $parameters = []): bool
    {
        return PrazzuAccessControl::canUseContratos();
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return static::canAccess($parameters);
    }
}
