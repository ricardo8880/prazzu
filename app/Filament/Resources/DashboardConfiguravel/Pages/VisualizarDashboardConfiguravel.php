<?php

namespace App\Filament\Resources\DashboardConfiguravel\Pages;

use App\Filament\Resources\DashboardConfiguravel\DashboardWidgetConfiguracaoResource;
use App\Models\DashboardWidgetConfiguracao;
use App\Services\DashboardConfiguravelService;
use App\Services\PlanoService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class VisualizarDashboardConfiguravel extends Page
{
    protected static string $resource = DashboardWidgetConfiguracaoResource::class;

    protected string $view = 'filament.resources.dashboard-configuravel.pages.visualizar-dashboard-configuravel';

    protected static ?string $title = 'Visualizar Dashboard';

    protected static ?string $navigationLabel = 'Visualizar Painéis';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Relatórios e Auditoria';

    protected static ?int $navigationSort = 4;

    public static function canAccess(array $parameters = []): bool
    {
        return true;
    }

    public function getTitle(): string
    {
        return 'Visualizar Dashboard';
    }

    public function getHeading(): string
    {
        return 'Dashboard Configurável';
    }

    public function getSubheading(): ?string
    {
        return 'Visualização dos widgets ativos configurados para a empresa do usuário.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('novoWidget')
                ->label('Novo widget')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->visible(fn (): bool => DashboardWidgetConfiguracaoResource::canCreate())
                ->url(DashboardWidgetConfiguracaoResource::getUrl('create')),

            Action::make('gerenciar')
                ->label('Gerenciar widgets')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(DashboardWidgetConfiguracaoResource::getUrl('gerenciar')),
        ];
    }

    public function widgetsPorEmpresa(): Collection
    {
        return DashboardWidgetConfiguracao::query()
            ->with(['empresa:id,razao_social'])
            ->visibleForUser(Filament::auth()->user())
            ->where('ativo', true)
            ->orderBy('empresa_id')
            ->orderBy('ordem')
            ->orderBy('titulo')
            ->get()
            ->groupBy(fn (DashboardWidgetConfiguracao $widget): string => $widget->empresa?->razao_social ?: 'Sem empresa');
    }

    public function valorWidget(DashboardWidgetConfiguracao $widget): int|float|string
    {
        return app(DashboardConfiguravelService::class)->valor($widget);
    }

    public function valorFormatado(DashboardWidgetConfiguracao $widget): string
    {
        $valor = $this->valorWidget($widget);

        if (is_numeric($valor)) {
            return number_format((float) $valor, 0, ',', '.');
        }

        return (string) $valor;
    }

    public function dadosTabela(DashboardWidgetConfiguracao $widget): Collection
    {
        return app(DashboardConfiguravelService::class)->dadosTabela($widget);
    }

    public function dadosGrafico(DashboardWidgetConfiguracao $widget): Collection
    {
        return app(DashboardConfiguravelService::class)->dadosGrafico($widget);
    }

    public function maiorValorGrafico(DashboardWidgetConfiguracao $widget): int|float
    {
        $maior = $this->dadosGrafico($widget)->max('valor');

        return is_numeric($maior) && (float) $maior > 0 ? (float) $maior : 1;
    }

    public function larguraWidget(DashboardWidgetConfiguracao $widget): string
    {
        return match ($widget->largura) {
            '1/1' => 'dc-widget--full',
            '1/2' => 'dc-widget--half',
            default => 'dc-widget--third',
        };
    }

    public function corWidget(DashboardWidgetConfiguracao $widget): string
    {
        return match ($widget->fonte) {
            'itens_vencidos', 'sla_vencido' => 'dc-widget--danger',
            'vencendo_hoje' => 'dc-widget--warning',
            'contratos_ativos' => 'dc-widget--info',
            default => 'dc-widget--default',
        };
    }

    public function classeTipoWidget(DashboardWidgetConfiguracao $widget): string
    {
        return match ($widget->tipo) {
            'tabela' => 'dc-widget--table',
            'grafico' => 'dc-widget--chart',
            default => 'dc-widget--card',
        };
    }

    public function labelFonte(DashboardWidgetConfiguracao $widget): string
    {
        return match ($widget->fonte) {
            'itens_abertos' => 'Itens abertos',
            'itens_vencidos' => 'Itens vencidos',
            'vencendo_hoje' => 'Vencendo hoje',
            'sla_vencido' => 'SLA vencido',
            'contratos_ativos' => 'Contratos ativos',
            'total_itens' => 'Total de itens',
            default => ucfirst(str_replace('_', ' ', (string) $widget->fonte)),
        };
    }

    public function labelTipo(DashboardWidgetConfiguracao $widget): string
    {
        return match ($widget->tipo) {
            'tabela' => 'Tabela',
            'grafico' => 'Gráfico',
            default => 'Card',
        };
    }
}
