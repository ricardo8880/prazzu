<?php

namespace App\Filament\Resources\RelatoriosPersonalizados\Pages;

use App\Filament\Resources\RelatoriosPersonalizados\RelatorioPersonalizadoResource;
use App\Services\RelatorioPersonalizadoService;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateRelatorioPersonalizado extends CreateRecord
{
    protected static string $resource = RelatorioPersonalizadoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Filament::auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->colunas()->exists()) {
            return;
        }

        $camposPadrao = [
            'id',
            'titulo',
            'tipo',
            'status',
            'prioridade',
            'responsavel.nome',
            'data_vencimento',
        ];

        foreach ($camposPadrao as $ordem => $campo) {
            $this->record->colunas()->create([
                'campo' => $campo,
                'rotulo' => RelatorioPersonalizadoService::CAMPOS_ITEM_CONTROLE[$campo] ?? $campo,
                'tipo' => app(RelatorioPersonalizadoService::class)->tipoPadraoCampo($campo),
                'ordem' => $ordem + 1,
                'ativo' => true,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return RelatorioPersonalizadoResource::getUrl('index');
    }
}
