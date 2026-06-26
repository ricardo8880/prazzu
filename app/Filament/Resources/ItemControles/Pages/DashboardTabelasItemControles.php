<?php

namespace App\Filament\Resources\ItemControles\Pages;

use App\Filament\Pages\Pendencias;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Services\PlanoService;
use App\Filament\Resources\ItemControles\Widgets\EmpresasRankingWidget;
use App\Filament\Resources\ItemControles\Widgets\ResponsaveisRankingWidget;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class DashboardTabelasItemControles extends Page
{
    public static function canAccess(array $parameters = []): bool
    {
        return PlanoService::usuarioPossuiFeature(
            \Filament\Facades\Filament::auth()->user(),
            PlanoService::FEATURE_DASHBOARD_OPERACIONAL
        );
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return static::canAccess($parameters);
    }

    protected static string $resource = ItemControleResource::class;

    protected string $view = 'filament.resources.item-controles.pages.dashboard-tabelas-item-controles';

    protected static ?string $title = 'Dashboard - Tabelas';

    protected static ?string $navigationLabel = 'Painéis - Tabelas';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 2;


    public function getTitle(): string
    {
        return 'Dashboard - Tabelas';
    }

    public function getHeading(): string
    {
        return 'Dashboard - Tabelas';
    }

    public function getSubheading(): ?string
    {
        return 'Visão analítica em tabelas e rankings.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dashboardGraficos')
                ->label('Ir para gráficos')
                ->icon('heroicon-o-chart-pie')
                ->color('gray')
                ->url(ItemControleResource::getUrl('dashboard-graficos')),

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

            Action::make('centralNotificacoes')
                ->label('Notificações')
                ->icon('heroicon-o-bell')
                ->color('gray')
                ->url(ItemControleResource::getUrl('central-notificacoes')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmpresasRankingWidget::class,
            ResponsaveisRankingWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }
}
