<?php

namespace App\Filament\Resources\AuditoriaDetalhada\Pages;

use App\Filament\Resources\AuditoriaDetalhada\AuditoriaDetalhadaResource;
use App\Models\AuditoriaDetalhada;
use App\Models\Empresa;
use App\Models\User;
use App\Support\AuditoriaFormatter;
use App\Services\AuditoriaAccessService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class VisualizarAuditoriaDetalhada extends Page
{
    protected static string $resource = AuditoriaDetalhadaResource::class;

    protected string $view = 'filament.resources.auditoria-detalhada.pages.visualizar-auditoria-detalhada';

    protected static ?string $title = 'Investigação de Auditoria';

    public function getTitle(): string
    {
        return 'Investigação de Auditoria';
    }

    public function getHeading(): string
    {
        return 'Investigação de Auditoria';
    }

    public function getSubheading(): ?string
    {
        return 'Veja rapidamente quem fez, quando fez, onde mexeu e o que mudou antes/depois.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gerenciar')
                ->label('Tabela completa')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(AuditoriaDetalhadaResource::getUrl('gerenciar')),
        ];
    }

    public function filtros(): array
    {
        return [
            'periodo' => request('periodo', '30'),
            'evento' => request('evento'),
            'user_id' => request('user_id'),
            'empresa_id' => request('empresa_id'),
            'modulo' => request('modulo'),
            'suspeito' => request()->boolean('suspeito'),
            'busca' => trim((string) request('busca', '')),
        ];
    }

    public function exportUrl(): string
    {
        return route('auditoria-detalhada.exportar', array_filter($this->filtros(), fn ($value) => $value !== null && $value !== '' && $value !== false));
    }

    public function canExportAuditoria(): bool
    {
        return app(AuditoriaAccessService::class)->canExport(Filament::auth()->user());
    }

    public function queryFiltrada(): Builder
    {
        $filtros = $this->filtros();

        $query = AuditoriaDetalhadaResource::getEloquentQuery();

        if (! empty($filtros['periodo'])) {
            match ((string) $filtros['periodo']) {
                'hoje' => $query->whereDate('created_at', now()->toDateString()),
                '7' => $query->where('created_at', '>=', now()->subDays(7)),
                '30' => $query->where('created_at', '>=', now()->subDays(30)),
                default => null,
            };
        }

        if (! empty($filtros['evento'])) {
            $query->where('evento', $filtros['evento']);
        }

        if (! empty($filtros['user_id'])) {
            $query->where('user_id', (int) $filtros['user_id']);
        }

        if (! empty($filtros['empresa_id'])) {
            $query->where('empresa_id', (int) $filtros['empresa_id']);
        }

        if (! empty($filtros['modulo'])) {
            $query->where('auditable_type', $filtros['modulo']);
        }

        if (! empty($filtros['suspeito'])) {
            $query = $this->aplicarFiltroSuspeito($query);
        }

        if (! empty($filtros['busca'])) {
            $busca = '%' . str_replace(['%', '_'], ['\%', '\_'], $filtros['busca']) . '%';

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

    public function metricas(): array
    {
        $user = Filament::auth()->user();
        $empresaId = $user?->empresa_id;
        $cacheKey = 'auditoria_detalhada_metricas_v2_' . md5(json_encode($this->filtros())) . '_' . ($user?->id ?? 'guest') . '_' . ($empresaId ?? 'all');

        return Cache::remember($cacheKey, now()->addMinutes(2), function (): array {
            $query = $this->queryFiltrada();

            return [
                'total' => (clone $query)->count(),
                'hoje' => (clone $query)->whereDate('created_at', now()->toDateString())->count(),
                'alteracoes' => (clone $query)->where('evento', 'updated')->count(),
                'exclusoes' => (clone $query)->where('evento', 'deleted')->count(),
                'suspeitas' => $this->aplicarFiltroSuspeito((clone $query))->count(),
            ];
        });
    }

    public function eventos(): Collection
    {
        $total = $this->queryFiltrada()->count();

        return $this->queryFiltrada()
            ->selectRaw('evento, COUNT(*) as total')
            ->groupBy('evento')
            ->orderByDesc('total')
            ->get()
            ->map(function ($linha) use ($total): array {
                $valor = (int) $linha->total;

                return [
                    'label' => $this->eventoLabel($linha->evento),
                    'valor' => $valor,
                    'percentual' => $total > 0 ? max(4, round(($valor / $total) * 100, 2)) : 0,
                    'classe' => $this->eventoClasse($linha->evento),
                ];
            });
    }

    public function usuariosMaisAtivos(): Collection
    {
        return $this->queryFiltrada()
            ->with(['user:id,name'])
            ->selectRaw('user_id, COUNT(*) as total, MAX(created_at) as ultima_acao')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($linha): array => [
                'nome' => $linha->user?->name ?? 'Sistema',
                'total' => (int) $linha->total,
                'ultima' => $linha->ultima_acao ? \Carbon\Carbon::parse($linha->ultima_acao)->format('d/m/Y H:i') : '-',
            ]);
    }

    public function modulosAuditados(): Collection
    {
        return $this->queryFiltrada()
            ->selectRaw('auditable_type, COUNT(*) as total')
            ->groupBy('auditable_type')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($linha): array => [
                'nome' => AuditoriaFormatter::modulo((string) $linha->auditable_type),
                'tipo' => (string) $linha->auditable_type,
                'total' => (int) $linha->total,
            ]);
    }

    public function empresasAuditadas(): Collection
    {
        return $this->queryFiltrada()
            ->with(['empresa:id,razao_social,nome_fantasia'])
            ->selectRaw('empresa_id, COUNT(*) as total')
            ->groupBy('empresa_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($linha): array => [
                'nome' => $linha->empresa?->razao_social ?: $linha->empresa?->nome_fantasia ?: 'Sem empresa',
                'total' => (int) $linha->total,
            ]);
    }

    public function acoesSuspeitas(): Collection
    {
        return $this->aplicarFiltroSuspeito($this->queryFiltrada())
            ->with(['empresa:id,razao_social,nome_fantasia', 'user:id,name'])
            ->latest('created_at')
            ->limit(8)
            ->get();
    }

    public function registrosRecentes(): Collection
    {
        return $this->queryFiltrada()
            ->with(['empresa:id,razao_social,nome_fantasia', 'user:id,name'])
            ->latest('created_at')
            ->limit(16)
            ->get();
    }

    public function usuariosFiltro(): Collection
    {
        $ids = AuditoriaDetalhadaResource::getEloquentQuery()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        return User::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function empresasFiltro(): Collection
    {
        $ids = AuditoriaDetalhadaResource::getEloquentQuery()
            ->whereNotNull('empresa_id')
            ->distinct()
            ->pluck('empresa_id');

        return Empresa::query()
            ->whereIn('id', $ids)
            ->orderBy('razao_social')
            ->get(['id', 'razao_social', 'nome_fantasia']);
    }

    public function modulosFiltro(): Collection
    {
        return AuditoriaDetalhadaResource::getEloquentQuery()
            ->whereNotNull('auditable_type')
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->map(fn ($tipo): array => [
                'value' => $tipo,
                'label' => AuditoriaFormatter::modulo((string) $tipo),
            ]);
    }


    public function filtrosAtivos(): array
    {
        $filtros = $this->filtros();
        $ativos = [];

        if (! empty($filtros['periodo'])) {
            $ativos[] = 'Período: ' . match ((string) $filtros['periodo']) {
                'hoje' => 'Hoje',
                '7' => 'Últimos 7 dias',
                '30' => 'Últimos 30 dias',
                default => 'Todo período',
            };
        }

        if (! empty($filtros['evento'])) {
            $ativos[] = 'Evento: ' . $this->eventoLabel($filtros['evento']);
        }

        if (! empty($filtros['modulo'])) {
            $ativos[] = 'Módulo: ' . $this->moduloLabel($filtros['modulo']);
        }

        if (! empty($filtros['suspeito'])) {
            $ativos[] = 'Somente ações sensíveis';
        }

        if (! empty($filtros['busca'])) {
            $ativos[] = 'Busca: ' . $filtros['busca'];
        }

        return $ativos;
    }

    public function resumoAcao($registro): string
    {
        $usuario = $registro->user?->name ?? 'Sistema';
        $evento = mb_strtolower($this->eventoLabel($registro->evento));
        $campo = $this->campoLabel($registro->campo);
        $registroLabel = $this->registroLabel($registro);

        return trim("{$usuario} executou {$evento} em {$campo} de {$registroLabel}");
    }

    public function dataHumana($data): string
    {
        if (! $data) {
            return '-';
        }

        return $data->format('d/m/Y H:i:s');
    }

    public function nomeEmpresa($registro): string
    {
        return $registro->empresa?->razao_social ?: $registro->empresa?->nome_fantasia ?: 'Sem empresa';
    }

    public function iniciaisUsuario($registro): string
    {
        $nome = trim((string) ($registro->user?->name ?? 'Sistema'));
        $partes = preg_split('/\s+/', $nome) ?: [];
        $iniciais = collect($partes)->filter()->take(2)->map(fn ($parte) => mb_substr($parte, 0, 1))->implode('');

        return mb_strtoupper($iniciais ?: 'S');
    }

    public function eventoLabel(?string $evento): string
    {
        return AuditoriaFormatter::evento($evento);
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

    public function suspeitoClasse($registro): string
    {
        return $this->isSuspeito($registro) ? 'ad-badge--danger' : 'ad-badge--gray';
    }

    public function suspeitoLabel($registro): string
    {
        return $this->isSuspeito($registro) ? 'Atenção' : 'Normal';
    }

    public function valorCurto(?string $valor): string
    {
        return AuditoriaFormatter::valor($valor, null, 110);
    }

    public function moduloLabel(?string $auditableType): string
    {
        return AuditoriaFormatter::modulo($auditableType);
    }

    public function registroLabel($registro): string
    {
        return AuditoriaFormatter::registroCurto($registro->auditable_type, $registro->auditable_id);
    }

    public function campoLabel(?string $campo): string
    {
        return AuditoriaFormatter::campo($campo);
    }

    public function valorRegistro(?string $valor, ?string $campo = null): string
    {
        return AuditoriaFormatter::valor($valor, $campo, 110);
    }

    private function aplicarFiltroSuspeito(Builder $query): Builder
    {
        return $query->where(function (Builder $subQuery): void {
            $subQuery
                ->where('evento', 'deleted')
                ->orWhere('campo', 'like', '%password%')
                ->orWhere('campo', 'like', '%senha%')
                ->orWhere('campo', 'like', '%role%')
                ->orWhere('campo', 'like', '%permiss%')
                ->orWhere('campo', 'like', '%status%');
        });
    }

    private function isSuspeito($registro): bool
    {
        $campo = mb_strtolower((string) $registro->campo);

        return AuditoriaFormatter::isSuspeito($registro);
    }
}
