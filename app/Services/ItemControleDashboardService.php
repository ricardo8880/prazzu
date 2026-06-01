<?php

namespace App\Services;

use App\Models\ItemControle;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ItemControleDashboardService
{
    public static function baseQuery(?User $user): Builder
    {
        return ItemControle::query()->visibleForUser($user);
    }

    protected static function cacheKey(string $sufixo, ?User $user): string
    {
        return sprintf(
            'item_controles_dashboard_%s_user_%s',
            $sufixo,
            $user?->id ?? 'guest'
        );
    }

    public static function getResumo(?User $user): array
    {
        return Cache::remember(
            self::cacheKey('resumo', $user),
            now()->addSeconds(30),
            function () use ($user): array {
                $query = self::baseQuery($user);

                $hoje = now()->toDateString();
                $amanha = now()->addDay()->toDateString();
                $mais7 = now()->addDays(7)->toDateString();

                $total = (clone $query)->count();

                $concluidos = (clone $query)
                    ->where('status', 'concluido')
                    ->count();

                $cancelados = (clone $query)
                    ->where('status', 'cancelado')
                    ->count();

                $vencidos = (clone $query)
                    ->whereNotIn('status', ['concluido', 'cancelado'])
                    ->whereDate('data_vencimento', '<', $hoje)
                    ->count();

                $venceHoje = (clone $query)
                    ->whereNotIn('status', ['concluido', 'cancelado'])
                    ->whereDate('data_vencimento', $hoje)
                    ->count();

                $proximos7 = (clone $query)
                    ->whereNotIn('status', ['concluido', 'cancelado'])
                    ->whereBetween('data_vencimento', [$amanha, $mais7])
                    ->count();

                $emAbertoNoPrazo = (clone $query)
                    ->whereNotIn('status', ['concluido', 'cancelado'])
                    ->where(function (Builder $builder) use ($mais7): void {
                        $builder
                            ->whereNull('data_vencimento')
                            ->orWhereDate('data_vencimento', '>', $mais7);
                    })
                    ->count();

                $emAberto = $vencidos + $venceHoje + $proximos7 + $emAbertoNoPrazo;

                return [
                    'total' => $total,
                    'em_aberto' => $emAberto,
                    'concluidos' => $concluidos,
                    'cancelados' => $cancelados,
                    'vencidos' => $vencidos,
                    'vence_hoje' => $venceHoje,
                    'proximos_7' => $proximos7,
                    'em_aberto_no_prazo' => $emAbertoNoPrazo,
                ];
            }
        );
    }

    public static function getStatusDistribuicao(?User $user): array
    {
        return Cache::remember(
            self::cacheKey('status_distribuicao', $user),
            now()->addSeconds(30),
            function () use ($user): array {
                $resumo = self::getResumo($user);

                return [
                    'labels' => [
                        'Concluídos',
                        'Cancelados',
                        'Vencidos',
                        'Vence hoje',
                        'Próximos 7 dias',
                        'Em aberto no prazo',
                    ],
                    'data' => [
                        $resumo['concluidos'],
                        $resumo['cancelados'],
                        $resumo['vencidos'],
                        $resumo['vence_hoje'],
                        $resumo['proximos_7'],
                        $resumo['em_aberto_no_prazo'],
                    ],
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.88)',
                        'rgba(107, 114, 128, 0.88)',
                        'rgba(239, 68, 68, 0.88)',
                        'rgba(245, 158, 11, 0.88)',
                        'rgba(59, 130, 246, 0.88)',
                        'rgba(168, 85, 247, 0.88)',
                    ],
                    'borderColor' => [
                        'rgba(16, 185, 129, 1)',
                        'rgba(107, 114, 128, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(168, 85, 247, 1)',
                    ],
                ];
            }
        );
    }

    public static function getTipoDistribuicao(?User $user): array
    {
        return Cache::remember(
            self::cacheKey('tipo_distribuicao', $user),
            now()->addSeconds(30),
            function () use ($user): array {
                $query = self::baseQuery($user);

                $contrato = (clone $query)->where('tipo', 'contrato')->count();
                $documento = (clone $query)->where('tipo', 'documento')->count();
                $licenca = (clone $query)->where('tipo', 'licenca')->count();
                $acordo = (clone $query)->where('tipo', 'acordo')->count();

                return [
                    'labels' => [
                        'Contrato',
                        'Documento',
                        'Licença',
                        'Acordo',
                    ],
                    'data' => [
                        $contrato,
                        $documento,
                        $licenca,
                        $acordo,
                    ],
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.88)',
                        'rgba(107, 114, 128, 0.88)',
                        'rgba(245, 158, 11, 0.88)',
                        'rgba(16, 185, 129, 0.88)',
                    ],
                    'borderColor' => [
                        'rgba(59, 130, 246, 1)',
                        'rgba(107, 114, 128, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(16, 185, 129, 1)',
                    ],
                ];
            }
        );
    }

    public static function getIndicadoresGerenciais(?User $user): array
    {
        return Cache::remember(
            self::cacheKey('indicadores_gerenciais', $user),
            now()->addSeconds(30),
            function () use ($user): array {
                $query = self::baseQuery($user);

                $concluidos = (clone $query)
                    ->where('status', 'concluido')
                    ->whereNotNull('data_conclusao')
                    ->get();

                $slaMedio = $concluidos->count() > 0
                    ? round(
                        $concluidos->avg(function ($item) {
                            if (! $item->created_at || ! $item->data_conclusao) {
                                return 0;
                            }

                            return $item->created_at->copy()->startOfDay()
                                ->diffInDays($item->data_conclusao->copy()->startOfDay());
                        }),
                        1
                    )
                    : 0;

                $vencidosAbertos = (clone $query)
                    ->whereNotIn('status', ['concluido', 'cancelado'])
                    ->whereDate('data_vencimento', '<', now()->toDateString())
                    ->get();

                $atrasoMedio = $vencidosAbertos->count() > 0
                    ? round(
                        $vencidosAbertos->avg(function ($item) {
                            if (! $item->data_vencimento) {
                                return 0;
                            }

                            return $item->data_vencimento->copy()->startOfDay()
                                ->diffInDays(now()->copy()->startOfDay());
                        }),
                        1
                    )
                    : 0;

                $empresasAtivas = (clone $query)
                    ->whereNotNull('empresa_id')
                    ->distinct()
                    ->count('empresa_id');

                $responsaveisAtivos = (clone $query)
                    ->whereNotNull('responsavel_id')
                    ->distinct()
                    ->count('responsavel_id');

                return [
                    'sla_medio' => $slaMedio,
                    'atraso_medio' => $atrasoMedio,
                    'empresas_ativas' => $empresasAtivas,
                    'responsaveis_ativos' => $responsaveisAtivos,
                ];
            }
        );
    }
}
