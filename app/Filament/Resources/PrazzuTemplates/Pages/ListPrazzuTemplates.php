<?php

namespace App\Filament\Resources\PrazzuTemplates\Pages;

use App\Filament\Resources\PrazzuTemplates\PrazzuTemplateResource;
use App\Models\PrazzuTemplate;
use App\Support\PrazzuAccountingTemplateCatalog;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPrazzuTemplates extends ListRecords
{
    protected static string $resource = PrazzuTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('instalarTemplatesContabeis')
                ->label('Instalar templates contábeis')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Instalar templates contábeis oficiais')
                ->modalDescription('Cria ou atualiza os templates oficiais de Fechamento Fiscal, Fechamento Contábil, Folha de Pagamento, Admissão, Demissão, Abertura de Empresa e Alteração Contratual dentro de Modelos Enterprise.')
                ->visible(fn (): bool => Filament::auth()->user()?->isAdmin() === true || Filament::auth()->user()?->isGestor() === true)
                ->action(function (): void {
                    $installed = 0;

                    foreach (PrazzuAccountingTemplateCatalog::templates() as $template) {
                        PrazzuTemplate::query()->updateOrCreate(
                            [
                                'module' => $template['module'],
                                'name' => $template['name'],
                            ],
                            [
                                'description' => $template['description'],
                                'payload' => $template['payload'],
                                'active' => true,
                            ]
                        );

                        $installed++;
                    }

                    Notification::make()
                        ->title('Templates contábeis instalados')
                        ->body($installed . ' template(s) oficial(is) criados ou atualizados em Modelos Enterprise.')
                        ->success()
                        ->send();
                }),

            CreateAction::make()
                ->label('Novo template'),
        ];
    }
}
