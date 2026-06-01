<?php

namespace App\Services;

use App\Models\ItemControle;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ItemControleMetricsService
{
    public static function baseQuery(?User $user): Builder
    {
        return ItemControle::query()->visibleForUser($user);
    }

    public static function getResumo(?User $user): array
    {
        $row = self::baseQuery($user)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status NOT IN ('concluido', 'cancelado') THEN 1 ELSE 0 END) as pendentes,
                SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos,
                SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
                SUM(
                    CASE
                        WHEN status NOT IN ('concluido', 'cancelado')
                             AND data_vencimento IS NOT NULL
                             AND DATE(data_vencimento) < CURDATE()
                        THEN 1
                        ELSE 0
                    END
                ) as vencidos,
                SUM(
                    CASE
                        WHEN status NOT IN ('concluido', 'cancelado')
                             AND data_vencimento IS NOT NULL
                             AND DATE(data_vencimento) = CURDATE()
                        THEN 1
                        ELSE 0
                    END
                ) as vence_hoje,
                SUM(
                    CASE
                        WHEN status NOT IN ('concluido', 'cancelado')
                             AND data_vencimento IS NOT NULL
                             AND DATE(data_vencimento) BETWEEN DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                        THEN 1
                        ELSE 0
                    END
                ) as proximos_7
            ")
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'pendentes' => (int) ($row->pendentes ?? 0),
            'concluidos' => (int) ($row->concluidos ?? 0),
            'cancelados' => (int) ($row->cancelados ?? 0),
            'vencidos' => (int) ($row->vencidos ?? 0),
            'vence_hoje' => (int) ($row->vence_hoje ?? 0),
            'proximos_7' => (int) ($row->proximos_7 ?? 0),
        ];
    }

    public static function getStatusDistribuicaoExclusiva(?User $user): array
    {
        $row = self::baseQuery($user)
            ->selectRaw("
                SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos,
                SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
                SUM(
                    CASE
                        WHEN status NOT IN ('concluido', 'cancelado')
                             AND data_vencimento IS NOT NULL
                             AND DATE(data_vencimento) < CURDATE()
                        THEN 1
                        ELSE 0
                    END
                ) as vencidos,
                SUM(
                    CASE
                        WHEN status NOT IN ('concluido', 'cancelado')
                             AND data_vencimento IS NOT NULL
                             AND DATE(data_vencimento) = CURDATE()
                        THEN 1
                        ELSE 0
                    END
                ) as vence_hoje,
                SUM(
                    CASE
                        WHEN status NOT IN ('concluido', 'cancelado')
                             AND data_vencimento IS NOT NULL
                             AND DATE(data_vencimento) BETWEEN DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                        THEN 1
                        ELSE 0
                    END
                ) as proximos_7,
                SUM(
                    CASE
                        WHEN status NOT IN ('concluido', 'cancelado')
                             AND (
                                 data_vencimento IS NULL
                                 OR DATE(data_vencimento) > DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                             )
                        THEN 1
                        ELSE 0
                    END
                ) as aberto_no_prazo
            ")
            ->first();

        return [
            'labels' => [
                'Vencidos',
                'Vence hoje',
                'Próximos 7 dias',
                'Em aberto no prazo',
                'Concluídos',
                'Cancelados',
            ],
            'data' => [
                (int) ($row->vencidos ?? 0),
                (int) ($row->vence_hoje ?? 0),
                (int) ($row->proximos_7 ?? 0),
                (int) ($row->aberto_no_prazo ?? 0),
                (int) ($row->concluidos ?? 0),
                (int) ($row->cancelados ?? 0),
            ],
            'backgroundColor' => [
                'rgba(239, 68, 68, 0.88)',
                'rgba(245, 158, 11, 0.88)',
                'rgba(59, 130, 246, 0.88)',
                'rgba(107, 114, 128, 0.88)',
                'rgba(16, 185, 129, 0.88)',
                'rgba(100, 116, 139, 0.88)',
            ],
            'borderColor' => [
                'rgba(239, 68, 68, 1)',
                'rgba(245, 158, 11, 1)',
                'rgba(59, 130, 246, 1)',
                'rgba(107, 114, 128, 1)',
                'rgba(16, 185, 129, 1)',
                'rgba(100, 116, 139, 1)',
            ],
        ];
    }

    public static function getDistribuicaoPorTipo(?User $user): array
    {
        $rows = self::baseQuery($user)
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $contrato = (int) ($rows['contrato'] ?? 0);
        $documento = (int) ($rows['documento'] ?? 0);
        $licenca = (int) ($rows['licenca'] ?? 0);
        $acordo = (int) ($rows['acordo'] ?? 0);

        return [
            'labels' => ['Contrato', 'Documento', 'Licença', 'Acordo'],
            'data' => [$contrato, $documento, $licenca, $acordo],
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
}
