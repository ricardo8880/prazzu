<?php

namespace App\Filament\Resources\RelatoriosPersonalizados\Pages;

use App\Filament\Resources\RelatoriosPersonalizados\RelatorioPersonalizadoResource;
use App\Models\RelatorioPersonalizado;
use App\Services\PlanoService;
use App\Services\RelatorioPersonalizadoService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class VisualizarRelatoriosPersonalizados extends Page
{
    protected static string $resource = RelatorioPersonalizadoResource::class;

    protected string $view = 'filament.resources.relatorios-personalizados.pages.visualizar-relatorios-personalizados';

    protected static ?string $title = 'Visualizar Relatórios';

    protected static ?string $navigationLabel = 'Visualizar Relatórios';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 3;

    public static function canAccess(array $parameters = []): bool
    {
        return PlanoService::usuarioPossuiFeature(
            Filament::auth()->user(),
            PlanoService::FEATURE_RELATORIOS_PERSONALIZADOS
        );
    }

    public function getTitle(): string
    {
        return 'Visualizar Relatórios';
    }

    public function getHeading(): string
    {
        return 'Relatórios Personalizados';
    }

    public function getSubheading(): ?string
    {
        return 'Painel visual dos relatórios ativos configurados para a empresa do usuário.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('novoRelatorio')
                ->label('Novo relatório')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->visible(fn (): bool => RelatorioPersonalizadoResource::canCreate())
                ->url(RelatorioPersonalizadoResource::getUrl('create')),

            Action::make('gerenciar')
                ->label('Gerenciar relatórios')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(RelatorioPersonalizadoResource::getUrl('gerenciar')),
        ];
    }

    public function relatoriosPorEmpresa(): Collection
    {
        return RelatorioPersonalizado::query()
            ->with([
                'empresa:id,razao_social',
                'colunas',
                'filtros',
            ])
            ->withCount([
                'colunas',
                'filtros',
            ])
            ->visibleForUser(Filament::auth()->user())
            ->where('ativo', true)
            ->orderBy('empresa_id')
            ->orderBy('nome')
            ->get()
            ->groupBy(fn (RelatorioPersonalizado $relatorio): string => $relatorio->empresa?->razao_social ?: 'Sem empresa');
    }

    public function metricas(RelatorioPersonalizado $relatorio): array
    {
        $cacheKey = 'relatorio_personalizado_metricas_' . $relatorio->id . '_' . md5((string) $relatorio->updated_at);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($relatorio): array {
            $service = app(RelatorioPersonalizadoService::class);

            return [
                'total' => $service->total($relatorio),
                'pendentes' => $service->pendentes($relatorio),
                'vencidos' => $service->vencidos($relatorio),
                'concluidos' => $service->concluidos($relatorio),
            ];
        });
    }

    public function dadosGrafico(RelatorioPersonalizado $relatorio): Collection
    {
        $cacheKey = 'relatorio_personalizado_grafico_' . $relatorio->id . '_' . md5((string) $relatorio->updated_at);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($relatorio): Collection {
            return app(RelatorioPersonalizadoService::class)->agrupadoPor($relatorio, 'status');
        });
    }

    public function maiorValorGrafico(RelatorioPersonalizado $relatorio): int|float
    {
        $maior = $this->dadosGrafico($relatorio)->max('valor');

        return is_numeric($maior) && (float) $maior > 0 ? (float) $maior : 1;
    }

    public function dadosTabela(RelatorioPersonalizado $relatorio): Collection
    {
        $cacheKey = 'relatorio_personalizado_tabela_' . $relatorio->id . '_' . md5((string) $relatorio->updated_at);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($relatorio): Collection {
            return app(RelatorioPersonalizadoService::class)->registrosPreview($relatorio, 8);
        });
    }

    public function labelFonte(RelatorioPersonalizado $relatorio): string
    {
        return match ($relatorio->fonte) {
            'item_controles' => 'Itens de controle',
            default => ucfirst(str_replace('_', ' ', (string) $relatorio->fonte)),
        };
    }

    public function labelFormato(RelatorioPersonalizado $relatorio): string
    {
        return match ($relatorio->formato_padrao) {
            'pdf' => 'PDF',
            'excel' => 'Excel',
            'csv' => 'CSV',
            default => 'Tela',
        };
    }

    public function classeRelatorio(RelatorioPersonalizado $relatorio): string
    {
        $metricas = $this->metricas($relatorio);

        if (($metricas['vencidos'] ?? 0) > 0) {
            return 'rp-report--danger';
        }

        if (($metricas['pendentes'] ?? 0) > 0) {
            return 'rp-report--warning';
        }

        return 'rp-report--default';
    }
}
