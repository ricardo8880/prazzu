<?php
namespace App\Filament\Resources\FluxosOperacionais\Pages;
use App\Filament\Resources\FluxosOperacionais\FluxoOperacionalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
class EditFluxoOperacional extends EditRecord { protected static string $resource = FluxoOperacionalResource::class; protected function getHeaderActions(): array { return [DeleteAction::make()]; } }
