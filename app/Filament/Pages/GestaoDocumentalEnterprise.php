<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Support\GestaoDocumentalEnterpriseData;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use UnitEnum;

class GestaoDocumentalEnterprise extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static string | UnitEnum | null $navigationGroup = 'Documentos e Modelos';
    protected static ?string $navigationLabel = 'Gestão Documental';
    protected static ?string $title = 'Gestão Documental Enterprise';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.gestao-documental-enterprise';

    protected function getViewData(): array
    {
        $filtros = [
            'busca' => request()->string('busca')->trim()->toString(),
            'empresa_id' => request()->input('empresa_id'),
            'responsavel_id' => request()->input('responsavel_id'),
            'status' => request()->input('status'),
            'situacao' => request()->input('situacao'),
            'prioridade' => request()->input('prioridade'),
            'tipo' => request()->input('tipo'),
            'ordenacao' => request()->input('ordenacao'),
        ];

        $filtros = array_filter($filtros, fn ($value): bool => filled($value));

        return [
            'data' => app(GestaoDocumentalEnterpriseData::class)->dados(Filament::auth()->user(), $filtros),
            'novoDocumentoUrl' => ItemControleResource::getUrl('create'),
            'listaDocumentosUrl' => ItemControleResource::getUrl('index'),
        ];
    }
}
