<?php

namespace App\Services;

use App\Models\ItemControle;
use App\Support\CachedSchema;
use App\Support\PrazzuPerformance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PrazzuSlaService
{
    public function __construct(private readonly PrazzuSlaEngine $engine = new PrazzuSlaEngine())
    {
    }

    public function status($record): string
    {
        return $this->engine->statusForRecord($record);
    }

    public function tempoRestante($record): string
    {
        return $this->engine->remainingLabel($record);
    }

    /**
     * Recalcula o SLA de itens de controle usando uma única regra oficial.
     *
     * @return array{avaliados:int, atualizados:int, por_status:array<string,int>}
     */
    public function recalcularItensControle(?int $empresaId = null, int $limit = 1000): array
    {
        $limit = PrazzuPerformance::safeLimit($limit, 1000, 10000);
        if (! CachedSchema::hasTable('item_controles') || ! CachedSchema::hasColumn('item_controles', 'sla_status')) {
            return ['avaliados' => 0, 'atualizados' => 0, 'por_status' => []];
        }

        $select = array_values(array_filter([
            'id',
            'empresa_id',
            CachedSchema::hasColumn('item_controles', 'data_vencimento') ? 'data_vencimento' : null,
            CachedSchema::hasColumn('item_controles', 'data_conclusao') ? 'data_conclusao' : null,
            CachedSchema::hasColumn('item_controles', 'sla_limite_em') ? 'sla_limite_em' : null,
            CachedSchema::hasColumn('item_controles', 'sla_concluido_em') ? 'sla_concluido_em' : null,
            CachedSchema::hasColumn('item_controles', 'sla_status') ? 'sla_status' : null,
        ]));

        $query = ItemControle::query()
            ->select($select)
            ->when($empresaId, fn (Builder $query): Builder => $query->where('empresa_id', $empresaId))
            ->orderBy('id')
            ->limit($limit);

        $avaliados = 0;
        $atualizados = 0;
        $porStatus = [];

        $query->get()->each(function (ItemControle $item) use (&$avaliados, &$atualizados, &$porStatus): void {
            $avaliados++;
            $status = $this->engine->statusForRecord($item);
            $porStatus[$status] = ($porStatus[$status] ?? 0) + 1;

            if (($item->sla_status ?? null) === $status) {
                return;
            }

            DB::table('item_controles')
                ->where('id', $item->id)
                ->update([
                    'sla_status' => $status,
                    'updated_at' => now(),
                ]);

            $atualizados++;
        });

        ksort($porStatus);

        return [
            'avaliados' => $avaliados,
            'atualizados' => $atualizados,
            'por_status' => $porStatus,
        ];
    }
}
