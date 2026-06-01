<?php

namespace App\Filament\Resources\ItemControles\Pages;

use App\Filament\Pages\Pendencias;
use App\Exports\ItemControlesExport;
use App\Filament\Resources\DashboardConfiguravel\DashboardWidgetConfiguracaoResource;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Filament\Resources\ItemControles\Pages\Concerns\HasItemControleSubNavigation;
use App\Filament\Resources\ItemControles\Pages\Concerns\DiagnosesItemControlePerformance;
use App\Models\Empresa;
use App\Models\ItemControle;
use App\Models\Responsavel;
use App\Services\PlanoService;
use App\Support\WhiteLabelSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListItemControles extends ListRecords
{
    use HasItemControleSubNavigation;
    use DiagnosesItemControlePerformance;
    protected static string $resource = ItemControleResource::class;

    protected function getTableQuery(): Builder
    {
        $this->bootItemControlePerformanceDiagnostics('geral');

        return ItemControleResource::getEloquentQueryForContext('geral');
    }

    protected function getHeaderActions(): array
    {
        $user = Filament::auth()->user();
        $naoLidas = $this->getUnreadNotificationsCount();

        return [
            CreateAction::make()
                ->label('Novo item'),

            ActionGroup::make([
                Action::make('dashboardGraficos')
                    ->label('Dashboard - Graficos')
                    ->icon('heroicon-o-chart-pie')
                    ->color('info')
                    ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_DASHBOARD_OPERACIONAL))
                    ->url(ItemControleResource::getUrl('dashboard-graficos')),

                Action::make('dashboardTabelas')
                    ->label('Dashboard - Tabelas')
                    ->icon('heroicon-o-table-cells')
                    ->color('gray')
                    ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_DASHBOARD_OPERACIONAL))
                    ->url(ItemControleResource::getUrl('dashboard-tabelas')),

                Action::make('dashboardConfiguravel')
                    ->label('Dashboard Configurável')
                    ->icon('heroicon-o-squares-2x2')
                    ->color('success')
                    ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_DASHBOARDS_PERSONALIZADOS))
                    ->url(DashboardWidgetConfiguracaoResource::getUrl('visualizar')),

                Action::make('pendencias')
                    ->label('Pendências')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->url(Pendencias::getUrl()),

                Action::make('centralNotificacoes')
                    ->label($naoLidas > 0 ? "Notificacoes ({$naoLidas})" : 'Notificacoes')
                    ->icon('heroicon-o-bell')
                    ->color($naoLidas > 0 ? 'warning' : 'gray')
                    ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_NOTIFICACOES_BASICAS))
                    ->url(ItemControleResource::getUrl('central-notificacoes')),

                ActionGroup::make([
                    Action::make('exportarCsv')
                        ->label('Exportar CSV')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->form($this->getExportFormSchema())
                        ->action(fn (array $data): StreamedResponse => $this->exportarCsv($data)),

                    Action::make('exportarExcel')
                        ->label('Exportar Excel')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->form($this->getExportFormSchema())
                        ->action(fn (array $data) => $this->exportarExcel($data)),

                    Action::make('exportarPdf')
                        ->label('Exportar PDF')
                        ->icon('heroicon-o-document')
                        ->color('danger')
                        ->form($this->getExportFormSchema())
                        ->action(fn (array $data) => $this->exportarPdf($data)),
                ])
                    ->label('Exportacoes')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_EXPORTACOES)),
            ])
                ->label('Ações')
                ->icon('heroicon-o-ellipsis-horizontal-circle')
                ->color('primary')
                ->button(),
        ];
    }

    protected function getExportFormSchema(): array
    {
        return [
            Select::make('tipo_relatorio')
                ->label('Tipo de relatorio')
                ->required()
                ->default('todos')
                ->options([
                    'todos' => 'Todos os itens visiveis',
                    'vencidos' => 'Somente vencidos',
                    'concluidos' => 'Somente concluidos',
                    'pendentes' => 'Somente pendentes/em andamento',
                ]),

            DatePicker::make('data_inicial')
                ->label('Data inicial'),

            DatePicker::make('data_final')
                ->label('Data final'),

            Select::make('empresa_id')
                ->label('Empresa')
                ->searchable()
                ->native(false)
                ->visible(fn (): bool => Filament::auth()->user()?->isSuperAdmin() === true)
                ->getSearchResultsUsing(function (string $search): array {
                    return Empresa::query()
                        ->select(['id', 'razao_social', 'nome_fantasia', 'cnpj'])
                        ->where(function ($query) use ($search): void {
                            $query->where('razao_social', 'like', "%{$search}%")
                                ->orWhere('nome_fantasia', 'like', "%{$search}%")
                                ->orWhere('cnpj', 'like', "%{$search}%");
                        })
                        ->orderBy('razao_social')
                        ->limit(50)
                        ->pluck('razao_social', 'id')
                        ->toArray();
                })
                ->getOptionLabelUsing(fn ($value): ?string => blank($value) ? null : Empresa::find($value)?->razao_social),

            Select::make('responsavel_id')
                ->label('Responsavel')
                ->searchable()
                ->native(false)
                ->getSearchResultsUsing(function (string $search): array {
                    $user = Filament::auth()->user();

                    $query = Responsavel::query()
                        ->select(['id', 'nome', 'email', 'cargo', 'empresa_id'])
                        ->where(function ($query) use ($search): void {
                            $query->where('nome', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('cargo', 'like', "%{$search}%");
                        });

                    if ($user?->isSuperAdmin() !== true && $user?->hasEmpresaVinculada()) {
                        $query->where('empresa_id', $user->empresa_id);
                    }

                    return $query
                        ->orderBy('nome')
                        ->limit(50)
                        ->pluck('nome', 'id')
                        ->toArray();
                })
                ->getOptionLabelUsing(fn ($value): ?string => blank($value) ? null : Responsavel::find($value)?->nome),

            Select::make('tipo')
                ->label('Tipo')
                ->options([
                    'contrato' => 'Contrato',
                    'documento' => 'Documento',
                    'licenca' => 'Licenca',
                    'acordo' => 'Acordo',
                ]),

            Select::make('status')
                ->label('Status')
                ->options([
                    'pendente' => 'Pendente',
                    'em_andamento' => 'Em andamento',
                    'concluido' => 'Concluido',
                    'cancelado' => 'Cancelado',
                    'vencido' => 'Vencido',
                ]),
        ];
    }

    protected function getExportQuery(array $data): Builder
    {
        $user = Filament::auth()->user();

        $query = ItemControle::query()
            ->select('item_controles.*')
            ->with([
                'empresa:id,razao_social',
                'responsavel:id,nome,user_id,gestor_user_id,empresa_id',
            ])
            ->withCount([
                'comentarios',
                'anexos',
            ])
            ->visibleForUser($user)
            ->orderBy('data_vencimento')
            ->orderBy('id');

        $tipoRelatorio = $data['tipo_relatorio'] ?? 'todos';

        if ($tipoRelatorio === 'vencidos') {
            $query
                ->whereDate('data_vencimento', '<', now()->toDateString())
                ->whereNotIn('status', ['concluido', 'cancelado']);
        }

        if ($tipoRelatorio === 'concluidos') {
            $query->where('status', 'concluido');
        }

        if ($tipoRelatorio === 'pendentes') {
            $query->whereIn('status', ['pendente', 'em_andamento']);
        }

        if (filled($data['data_inicial'] ?? null)) {
            $query->whereDate('data_vencimento', '>=', $data['data_inicial']);
        }

        if (filled($data['data_final'] ?? null)) {
            $query->whereDate('data_vencimento', '<=', $data['data_final']);
        }

        if ($user?->isSuperAdmin() && filled($data['empresa_id'] ?? null)) {
            $query->where('empresa_id', $data['empresa_id']);
        }

        if (filled($data['responsavel_id'] ?? null)) {
            $query->where('responsavel_id', $data['responsavel_id']);
        }

        if (filled($data['tipo'] ?? null)) {
            $query->where('tipo', $data['tipo']);
        }

        if (filled($data['status'] ?? null)) {
            if ($data['status'] === 'vencido') {
                $query
                    ->whereDate('data_vencimento', '<', now()->toDateString())
                    ->whereNotIn('status', ['concluido', 'cancelado']);
            } else {
                $query->where('status', $data['status']);
            }
        }

        return $query;
    }

    protected function exportarCsv(array $data): StreamedResponse
    {
        $query = $this->getExportQuery($data);
        $filename = 'itens_controle_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'Titulo',
                'Tipo',
                'Status',
                'Empresa',
                'Responsavel',
                'Vencimento',
                'Conclusao',
                'Situacao do prazo',
                'Anexo principal',
                'Qtd. anexos complementares',
                'Qtd. comentarios',
                'Observacao',
            ], ';');

            $query->chunk(200, function ($items) use ($handle): void {
                foreach ($items as $item) {
                    fputcsv($handle, [
                        $item->id,
                        $item->titulo,
                        $item->tipo,
                        $item->getStatusExibicao(),
                        $item->empresa?->razao_social,
                        $item->responsavel?->nome,
                        optional($item->data_vencimento)?->format('d/m/Y'),
                        optional($item->data_conclusao)?->format('d/m/Y'),
                        $item->getSituacaoPrazo(),
                        $item->hasAnexoPrincipal() ? 'Sim' : 'Nao',
                        $item->anexos_count,
                        $item->comentarios_count,
                        $item->observacao,
                    ], ';');
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function exportarExcel(array $data)
    {
        $items = $this->getExportQuery($data)->get();
        $filename = 'itens_controle_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ItemControlesExport($items), $filename);
    }

    protected function exportarPdf(array $data)
    {
        $items = $this->getExportQuery($data)->get();
        $filename = 'itens_controle_' . now()->format('Ymd_His') . '.pdf';

        $pdf = Pdf::loadView('exports.item-controles-pdf', [
            'items' => $items,
            'whiteLabel' => WhiteLabelSettings::make(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }

    protected function getUnreadNotificationsCount(): int
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return 0;
        }

        $cacheKey = sprintf('item_controles_unread_notifications_user_%s', $user->id);

        return Cache::remember($cacheKey, now()->addSeconds(15), function () use ($user): int {
            return (int) $user->unreadNotifications()->count();
        });
    }
}