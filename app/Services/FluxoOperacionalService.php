<?php

namespace App\Services;

use App\Models\FluxoOperacional;
use App\Models\FluxoOperacionalExecucao;
use App\Models\ItemControle;
use Illuminate\Support\Facades\DB;

class FluxoOperacionalService
{
    public function aplicarFluxo(ItemControle $item, FluxoOperacional $fluxo): void
    {
        DB::transaction(function () use ($item, $fluxo): void {
            $item->forceFill(['fluxo_operacional_id' => $fluxo->id])->save();

            FluxoOperacionalExecucao::query()
                ->where('item_controle_id', $item->id)
                ->where('fluxo_operacional_id', $fluxo->id)
                ->delete();

            foreach ($fluxo->etapas()->where('ativo', true)->get() as $etapa) {
                FluxoOperacionalExecucao::query()->create([
                    'empresa_id' => $item->empresa_id,
                    'item_controle_id' => $item->id,
                    'fluxo_operacional_id' => $fluxo->id,
                    'fluxo_operacional_etapa_id' => $etapa->id,
                    'responsavel_id' => $etapa->responsavel_padrao_id ?: $item->responsavel_id,
                    'status' => 'pendente',
                    'prazo_em' => $etapa->prazo_horas ? now()->addHours((int) $etapa->prazo_horas) : null,
                ]);
            }
        });
    }
}
