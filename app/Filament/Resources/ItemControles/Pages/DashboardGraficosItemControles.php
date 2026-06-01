<?php

namespace App\Filament\Resources\ItemControles\Pages;

use App\Filament\Pages\Pendencias;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Support\PrazzuAccessControl;
use App\Filament\Resources\ItemControles\Widgets\ItemControlesIndicadoresGerenciais;
use App\Filament\Resources\ItemControles\Widgets\ItemControlesOverview;
use App\Filament\Resources\ItemControles\Widgets\ItemControlesStatusChart;
use App\Filament\Resources\ItemControles\Widgets\ItemControlesTipoChart;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class DashboardGraficosItemControles extends Page
{
    public static function canAccess(array $parameters = []): bool
    {
        return PrazzuAccessControl::canUseDashboardOperacional();
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return static::canAccess($parameters);
    }

    protected static string $resource = ItemControleResource::class;

    protected string $view = 'filament.resources.item-controles.pages.dashboard-graficos-item-controles';

    protected static ?string $title = 'Dashboards';

    protected static ?string $navigationLabel = 'Dashboards';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static string | \UnitEnum | null $navigationGroup = 'Relatórios';

    protected static ?int $navigationSort = 2;


    public function getTitle(): string
    {
        return 'Dashboards';
    }

    public function getHeading(): string
    {
        return 'Dashboards';
    }

    public function getSubheading(): ?string
    {
        return 'Visão visual e estratégica dos itens de controle.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dashboardTabelas')
                ->label('Ir para tabelas')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(ItemControleResource::getUrl('dashboard-tabelas')),

            Action::make('listagem')
                ->label('Ver listagem')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(ItemControleResource::getUrl('list')),

            Action::make('pendencias')
                ->label('Pendências')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->url(Pendencias::getUrl()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ItemControlesOverview::class,
            ItemControlesIndicadoresGerenciais::class,
            ItemControlesStatusChart::class,
            ItemControlesTipoChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }
}
