<?php

namespace App\Services;

use App\Models\ItemControle;
use App\Support\WhiteLabelSettings;
use Barryvdh\DomPDF\Facade\Pdf;

class ItemControlePdfService
{
    public function gerar(ItemControle $item)
    {
        $item->load([
            'empresa',
            'responsavel',
            'categoria',
            'tags',
            'checklists.concluidoPor',
            'timelines.user',
            'comentarios.user',
            'ultimaAssinatura.empresa',
            'ultimaAssinatura.user',
            'ultimaAprovacao.solicitante',
            'ultimaAprovacao.aprovador',
            'etapasOperacionais.etapa',
        ]);

        return Pdf::loadView(
            'pdf.item-controle',
            [
                'item' => $item,
                'geradoEm' => now(),
                'whiteLabel' => WhiteLabelSettings::make(),
            ]
        )
            ->setPaper('a4', 'portrait')
            ->download('item-controle-' . $item->id . '.pdf');
    }
}
