<?php
namespace App\Filament\Resources\FluxosOperacionais\Pages;
use App\Filament\Resources\FluxosOperacionais\FluxoOperacionalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListFluxosOperacionais extends ListRecords { protected static string $resource = FluxoOperacionalResource::class; protected function getHeaderActions(): array { return [CreateAction::make()->label('Novo fluxo')]; } }
