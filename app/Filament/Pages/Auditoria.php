<?php

namespace App\Filament\Pages;

use App\Exports\AuditoriaExport;
use App\Support\ComplianceModuleData;
use App\Services\AuditoriaAccessService;
use App\Filament\Resources\AuditoriaDetalhada\AuditoriaDetalhadaResource;
use App\Support\AuditoriaFormatter;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class Auditoria extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static string | UnitEnum | null $navigationGroup = 'Relatórios e Auditoria';

    protected static ?string $navigationLabel = 'Auditoria e Rastreabilidade';

    protected static ?string $title = 'Auditoria e Rastreabilidade';

    protected static ?int $navigationSort = 4;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected string $view = 'filament.pages.compliance-auditoria';

    public static function canAccess(): bool
    {
        return app(AuditoriaAccessService::class)->canView(auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }


    public function getSubNavigation(): array
    {
        return [
            NavigationItem::make('Resumo')
                ->icon('heroicon-o-chart-bar')
                ->url(static::getUrl(['aba' => 'resumo']))
                ->isActiveWhen(fn (): bool => request('aba', 'resumo') === 'resumo')
                ->sort(1),

            NavigationItem::make('Timeline')
                ->icon('heroicon-o-clock')
                ->url(static::getUrl(['aba' => 'timeline']))
                ->isActiveWhen(fn (): bool => request('aba', 'resumo') === 'timeline')
                ->sort(2),

            NavigationItem::make('Aprovações')
                ->icon('heroicon-o-check-circle')
                ->url(static::getUrl(['aba' => 'aprovacoes']))
                ->isActiveWhen(fn (): bool => request('aba', 'resumo') === 'aprovacoes')
                ->sort(3),

            NavigationItem::make('Investigação')
                ->icon('heroicon-o-magnifying-glass')
                ->url(static::getUrl(['aba' => 'investigacao']))
                ->isActiveWhen(fn (): bool => request('aba', 'resumo') === 'investigacao')
                ->sort(4),
        ];
    }

    public function exportAuditoriaCsv(): StreamedResponse
    {
        abort_unless(app(AuditoriaAccessService::class)->canExport(auth()->user()), 403);

        $filters = $this->resolveAuditFilters();
        $rows = ComplianceModuleData::auditoriaExportRows(auth()->user(), $filters);
        $headings = ComplianceModuleData::auditoriaExportHeadings();
        $filename = $this->exportFilename('csv');

        return response()->streamDownload(function () use ($rows, $headings): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headings, ';');

            foreach ($rows as $row) {
                fputcsv($output, array_values($row), ';');
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportAuditoriaExcel(): BinaryFileResponse
    {
        abort_unless(app(AuditoriaAccessService::class)->canExport(auth()->user()), 403);

        $filters = $this->resolveAuditFilters();
        $rows = ComplianceModuleData::auditoriaExportRows(auth()->user(), $filters);
        $headings = ComplianceModuleData::auditoriaExportHeadings();

        return Excel::download(
            new AuditoriaExport($rows, $headings),
            $this->exportFilename('xlsx')
        );
    }

    protected function getViewData(): array
    {
        $filters = $this->resolveAuditFilters();

        return [
            'data' => ComplianceModuleData::auditoria($filters),
            'filters' => $filters,
            'auditoriaDetalhadaUrl' => '#auditoria-detalhada',
            'auditoriaDetalhadaManageUrl' => class_exists(AuditoriaDetalhadaResource::class)
                ? AuditoriaDetalhadaResource::getUrl('gerenciar')
                : null,
            'detalheMetricas' => $this->metricasDetalhadas(),
            'detalheRecentes' => $this->registrosDetalhados(),
            'detalheSuspeitas' => $this->acoesSensiveis(),
            'detalheUsuarios' => $this->usuariosMaisAtivos(),
            'detalheModulos' => $this->modulosAuditados(),
            'detalheEmpresas' => $this->empresasAuditadas(),
            'detalheEventos' => $this->eventosPorTipo(),
        ];
    }


    public function queryDetalhada(): Builder
    {
        $filters = $this->resolveAuditFilters();
        $query = AuditoriaDetalhadaResource::getEloquentQuery();

        if (($filters['dateFilter'] ?? '30') !== 'todos') {
            match ((string) ($filters['dateFilter'] ?? '30')) {
                'hoje' => $query->whereDate('created_at', now()->toDateString()),
                '7' => $query->where('created_at', '>=', now()->subDays(7)),
                '30' => $query->where('created_at', '>=', now()->subDays(30)),
                '90' => $query->where('created_at', '>=', now()->subDays(90)),
                '180' => $query->where('created_at', '>=', now()->subDays(180)),
                '365' => $query->where('created_at', '>=', now()->subDays(365)),
                'custom' => $this->applyCustomDateRange($query, $filters),
                default => null,
            };
        }

        if (! empty($filters['fromDate'])) {
            $query->whereDate('created_at', '>=', $filters['fromDate']);
        }

        if (! empty($filters['toDate'])) {
            $query->whereDate('created_at', '<=', $filters['toDate']);
        }

        if (($filters['userFilter'] ?? 'todos') !== 'todos') {
            $query->where('user_id', (int) $filters['userFilter']);
        }

        if (($filters['companyFilter'] ?? 'todas') !== 'todas') {
            $query->where('empresa_id', (int) $filters['companyFilter']);
        }

        if (($filters['actionFilter'] ?? 'todas') !== 'todas') {
            $query->where('evento', $filters['actionFilter']);
        }

        if (! empty($filters['auditableType'])) {
            $query->where('auditable_type', $filters['auditableType']);
        }

        if (! empty($filters['auditableId'])) {
            $query->where('auditable_id', $filters['auditableId']);
        }

        if (! empty($filters['searchFilter'])) {
            $busca = '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['searchFilter']) . '%';
            $query->where(function (Builder $subQuery) use ($busca): void {
                $subQuery
                    ->where('evento', 'like', $busca)
                    ->orWhere('campo', 'like', $busca)
                    ->orWhere('valor_anterior', 'like', $busca)
                    ->orWhere('valor_novo', 'like', $busca)
                    ->orWhere('ip', 'like', $busca)
                    ->orWhere('auditable_type', 'like', $busca)
                    ->orWhereHas('user', fn (Builder $userQuery): Builder => $userQuery->where('name', 'like', $busca))
                    ->orWhereHas('empresa', fn (Builder $empresaQuery): Builder => $empresaQuery
                        ->where('razao_social', 'like', $busca)
                        ->orWhere('nome_fantasia', 'like', $busca));
            });
        }

        return $query;
    }

    public function metricasDetalhadas(): array
    {
        $user = auth()->user();
        $cacheKey = 'auditoria_unificada_metricas_' . md5(json_encode($this->resolveAuditFilters())) . '_' . ($user?->id ?? 'guest');

        return Cache::remember($cacheKey, now()->addMinutes(2), function (): array {
            $query = $this->queryDetalhada();

            return [
                'total' => (clone $query)->count(),
                'hoje' => (clone $query)->whereDate('created_at', now()->toDateString())->count(),
                'alteracoes' => (clone $query)->where('evento', 'updated')->count(),
                'exclusoes' => (clone $query)->where('evento', 'deleted')->count(),
                'sensiveis' => $this->aplicarFiltroSensivel((clone $query))->count(),
            ];
        });
    }

    public function registrosDetalhados()
    {
        return $this->queryDetalhada()
            ->with(['empresa:id,razao_social,nome_fantasia', 'user:id,name'])
            ->latest('created_at')
            ->limit(18)
            ->get();
    }

    public function acoesSensiveis()
    {
        return $this->aplicarFiltroSensivel($this->queryDetalhada())
            ->with(['empresa:id,razao_social,nome_fantasia', 'user:id,name'])
            ->latest('created_at')
            ->limit(6)
            ->get();
    }

    public function eventosPorTipo()
    {
        $query = $this->queryDetalhada();
        $total = (clone $query)->count();

        return $query->selectRaw('evento, COUNT(*) as total')
            ->groupBy('evento')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($linha): array => [
                'label' => AuditoriaFormatter::evento($linha->evento),
                'valor' => (int) $linha->total,
                'percentual' => $total > 0 ? max(4, round(((int) $linha->total / $total) * 100, 2)) : 0,
                'classe' => $this->eventoClasse($linha->evento),
            ]);
    }

    public function usuariosMaisAtivos()
    {
        return $this->queryDetalhada()
            ->with(['user:id,name'])
            ->selectRaw('user_id, COUNT(*) as total, MAX(created_at) as ultima_acao')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($linha): array => [
                'nome' => $linha->user?->name ?? 'Sistema',
                'total' => (int) $linha->total,
                'ultima' => $linha->ultima_acao ? \Carbon\Carbon::parse($linha->ultima_acao)->format('d/m/Y H:i') : '-',
            ]);
    }

    public function modulosAuditados()
    {
        return $this->queryDetalhada()
            ->selectRaw('auditable_type, COUNT(*) as total')
            ->groupBy('auditable_type')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($linha): array => [
                'nome' => AuditoriaFormatter::modulo((string) $linha->auditable_type),
                'total' => (int) $linha->total,
            ]);
    }

    public function empresasAuditadas()
    {
        return $this->queryDetalhada()
            ->with(['empresa:id,razao_social,nome_fantasia'])
            ->selectRaw('empresa_id, COUNT(*) as total')
            ->groupBy('empresa_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($linha): array => [
                'nome' => $linha->empresa?->razao_social ?: $linha->empresa?->nome_fantasia ?: 'Sem empresa',
                'total' => (int) $linha->total,
            ]);
    }

    public function resumoAcao($registro): string
    {
        $usuario = $registro->user?->name ?? 'Sistema';
        $evento = mb_strtolower(AuditoriaFormatter::evento($registro->evento));
        $campo = AuditoriaFormatter::campo($registro->campo);
        $item = AuditoriaFormatter::registroCurto($registro->auditable_type, $registro->auditable_id);

        return trim("{$usuario} executou {$evento} em {$campo} de {$item}");
    }

    public function iniciaisUsuario($registro): string
    {
        $nome = trim((string) ($registro->user?->name ?? 'Sistema'));
        $partes = preg_split('/\s+/', $nome) ?: [];
        $iniciais = collect($partes)->filter()->take(2)->map(fn ($parte) => mb_substr($parte, 0, 1))->implode('');

        return mb_strtoupper($iniciais ?: 'S');
    }

    public function nomeEmpresa($registro): string
    {
        return $registro->empresa?->razao_social ?: $registro->empresa?->nome_fantasia ?: 'Sem empresa';
    }

    public function eventoClasse(?string $evento): string
    {
        return match ($evento) {
            'created' => 'ad-badge--success',
            'updated' => 'ad-badge--warning',
            'deleted' => 'ad-badge--danger',
            default => 'ad-badge--gray',
        };
    }

    public function sensivelClasse($registro): string
    {
        return AuditoriaFormatter::isSuspeito($registro) ? 'ad-badge--danger' : 'ad-badge--gray';
    }

    public function sensivelLabel($registro): string
    {
        return AuditoriaFormatter::isSuspeito($registro) ? 'Revisar' : 'Normal';
    }

    public function dataHumana($data): string
    {
        return $data ? $data->format('d/m/Y H:i:s') : '-';
    }

    public function valorRegistro(?string $valor, ?string $campo = null): string
    {
        return AuditoriaFormatter::valor($valor, $campo, 110);
    }

    private function applyCustomDateRange(Builder $query, array $filters): void
    {
        if (! empty($filters['fromDate'])) {
            $query->whereDate('created_at', '>=', $filters['fromDate']);
        }

        if (! empty($filters['toDate'])) {
            $query->whereDate('created_at', '<=', $filters['toDate']);
        }
    }

    private function aplicarFiltroSensivel(Builder $query): Builder
    {
        return $query->where(function (Builder $subQuery): void {
            $subQuery
                ->where('evento', 'deleted')
                ->orWhere('campo', 'like', '%password%')
                ->orWhere('campo', 'like', '%senha%')
                ->orWhere('campo', 'like', '%role%')
                ->orWhere('campo', 'like', '%permiss%')
                ->orWhere('campo', 'like', '%status%')
                ->orWhere('evento', 'like', '%failed%')
                ->orWhere('evento', 'like', '%export%');
        });
    }

    public function canExportAuditoria(): bool
    {
        return app(AuditoriaAccessService::class)->canExport(auth()->user());
    }

    private function resolveAuditFilters(): array
    {
        $empresaId = app(AuditoriaAccessService::class)->normalizeEmpresaFilter(auth()->user(), request()->query('companyFilter', 'todas'));

        return [
            'dateFilter' => request()->query('dateFilter', '30'),
            'fromDate' => request()->query('fromDate'),
            'toDate' => request()->query('toDate'),
            'userFilter' => request()->query('userFilter', 'todos'),
            'companyFilter' => $empresaId ?: 'todas',
            'actionFilter' => request()->query('actionFilter', 'todas'),
            'searchFilter' => trim((string) request()->query('searchFilter', '')),
            'auditableType' => trim((string) request()->query('auditableType', '')),
            'auditableId' => trim((string) request()->query('auditableId', '')),
        ];
    }

    private function exportFilename(string $extension): string
    {
        return 'auditoria-' . now()->format('Y-m-d-His') . '-' . Str::random(6) . '.' . $extension;
    }
}
