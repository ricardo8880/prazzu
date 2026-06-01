<?php

namespace App\Filament\Resources\ItemControles\Widgets;

use App\Models\ItemControle;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;

class ItemControleAnexosResumoWidget extends Widget
{
    public ?ItemControle $record = null;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.resources.item-controles.widgets.item-controle-anexos-resumo-widget';

    public function getViewData(): array
    {
        $item = $this->record;
        $arquivoPrincipal = $item?->arquivo;
        $principalNome = $arquivoPrincipal ? basename((string) $arquivoPrincipal) : null;
        $principalUrl = $arquivoPrincipal ? Storage::disk('public')->url((string) $arquivoPrincipal) : null;
        $principalPreview = $principalNome ? $this->isPreviewable($principalNome) : false;
        $anexosCount = $item ? (int) $item->anexos()->count() : 0;

        return [
            'temPrincipal' => filled($arquivoPrincipal),
            'principalNome' => $principalNome,
            'principalUrl' => $principalUrl,
            'principalPreview' => $principalPreview,
            'anexosCount' => $anexosCount,
            'totalArquivos' => ($arquivoPrincipal ? 1 : 0) + $anexosCount,
        ];
    }

    private function isPreviewable(string $nome): bool
    {
        return preg_match('/\.(pdf|jpg|jpeg|png|webp)$/i', $nome) === 1;
    }
}
