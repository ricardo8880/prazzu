<?php

namespace App\Filament\Resources\ItemControles\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Filament\Resources\ItemControles\Widgets\ItemControleAnexosWidget;
use App\Filament\Resources\ItemControles\Widgets\ItemControleAssinaturasWidget;
use App\Filament\Resources\ItemControles\Widgets\ItemControleComentariosWidget;
use App\Filament\Resources\ItemControles\Widgets\ItemControleHistoricoWidget;
use App\Filament\Resources\ItemControles\Widgets\ItemControleVersionamentoWidget;
use App\Models\ItemControle;
use App\Models\ItemControleAnexo;
use App\Models\ItemControleComentario;
use App\Services\ItemControleFluxoService;
use App\Models\Responsavel;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditItemControle extends EditRecord
{
    protected static string $resource = ItemControleResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $user = Filament::auth()->user();
        $item = $this->getCurrentItem();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if (! $item->canBeAccessedBy($user)) {
            abort(403, 'Você não tem permissão para acessar este item.');
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Filament::auth()->user();
        $record = $this->getCurrentItem();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if (! $record->canBeModifiedBy($user)) {
            abort(403, 'Você não tem permissão para editar este item.');
        }

        if ($user->isSuperAdmin()) {
            $responsavel = Responsavel::query()->find($data['responsavel_id'] ?? null);

            if (! $responsavel) {
                abort(403, 'Responsável inválido.');
            }

            $data['empresa_id'] = $responsavel->empresa_id;

            return $data;
        }

        if (! $user->hasEmpresaVinculada()) {
            abort(403, 'Seu usuário não possui empresa vinculada.');
        }

        $data['empresa_id'] = $user->empresa_id;

        if ($user->isAdminEmpresa()) {
            $responsavelId = isset($data['responsavel_id']) ? (int) $data['responsavel_id'] : null;

            if (! ItemControleResource::canUserAssignResponsavel($user, $responsavelId)) {
                abort(403, 'Você só pode vincular o item a responsáveis da sua empresa.');
            }

            return $data;
        }

        if ($user->isGestor()) {
            $responsavelId = isset($data['responsavel_id']) ? (int) $data['responsavel_id'] : null;

            if (! ItemControleResource::canUserAssignResponsavel($user, $responsavelId)) {
                abort(403, 'Você só pode vincular o item a responsáveis da sua equipe.');
            }

            return $data;
        }

        $responsavelId = ItemControleResource::getDefaultResponsavelIdForUser($user);

        if (! $responsavelId) {
            abort(403, 'Seu usuário não possui responsável vinculado.');
        }

        $data['responsavel_id'] = $responsavelId;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('concluir')
                ->label('Concluir item')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Concluir item')
                ->modalDescription('Deseja marcar este item como concluído?')
                ->visible(function (): bool {
                    $user = Filament::auth()->user();
                    $record = $this->getCurrentItem();

                    if (! $user) {
                        return false;
                    }

                    if ($record->isConcluido()) {
                        return false;
                    }

                    return $record->canBeModifiedBy($user);
                })
                ->action(function (): void {
                    $record = $this->getCurrentItem();
                    $user = Filament::auth()->user();

                    if (! $user) {
                        abort(403, 'Usuário não autenticado.');
                    }

                    if (! $record->canBeModifiedBy($user)) {
                        abort(403, 'Você não tem permissão para concluir este item.');
                    }

                    $statusAnterior = $record->status;
                    $dataConclusaoAnterior = $record->data_conclusao;
                    $agora = now();

                    app(ItemControleFluxoService::class)->atualizarStatus($record, 'concluido', $user);

                    activity('item_controle')
                        ->performedOn($record)
                        ->causedBy($user)
                        ->withProperties([
                            'old' => [
                                'status' => $statusAnterior,
                                'data_conclusao' => $dataConclusaoAnterior,
                            ],
                            'attributes' => [
                                'status' => 'concluido',
                                'data_conclusao' => $agora->format('Y-m-d'),
                            ],
                        ])
                        ->event('status_manual')
                        ->log('Item marcado manualmente como concluído');

                    $this->fillForm();
                    $this->dispatchItemUpdated($record->id);

                    Notification::make()
                        ->title('Item marcado como concluído com sucesso.')
                        ->success()
                        ->send();
                }),

            Action::make('adicionarComentario')
                ->label('Adicionar comentário')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->form([
                    Textarea::make('comentario')
                        ->label('Comentário')
                        ->required()
                        ->rows(5)
                        ->maxLength(5000),
                ])
                ->action(function (array $data): void {
                    $record = $this->getCurrentItem();
                    $user = Filament::auth()->user();

                    if (! $user) {
                        abort(403, 'Usuário não autenticado.');
                    }

                    if (! $record->canBeModifiedBy($user)) {
                        abort(403, 'Você não tem permissão para comentar neste item.');
                    }

                    $comentario = trim((string) ($data['comentario'] ?? ''));

                    app(ItemControleFluxoService::class)->adicionarComentario($record, $comentario, $user);

                    activity('item_controle')
                        ->performedOn($record)
                        ->causedBy($user)
                        ->withProperties([
                            'attributes' => [
                                'comentario' => $comentario,
                            ],
                        ])
                        ->event('comentario')
                        ->log('Comentário registrado no item');

                    $this->dispatchItemUpdated($record->id);

                    Notification::make()
                        ->title('Comentário adicionado com sucesso.')
                        ->success()
                        ->send();
                }),

            Action::make('adicionarAnexos')
                ->label('Adicionar anexos')
                ->icon('heroicon-o-paper-clip')
                ->color('warning')
                ->form([
                    FileUpload::make('arquivos')
                        ->label('Arquivos')
                        ->multiple()
                        ->required()
                        ->disk('public')
                        ->directory('comprovantes-prazos/complementares')
                        ->downloadable()
                        ->openable()
                        ->previewable(true),

                    Textarea::make('observacao')
                        ->label('Observação dos anexos')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $record = $this->getCurrentItem();
                    $user = Filament::auth()->user();

                    if (! $user) {
                        abort(403, 'Usuário não autenticado.');
                    }

                    if (! $record->canBeModifiedBy($user)) {
                        abort(403, 'Você não tem permissão para adicionar anexos neste item.');
                    }

                    $arquivos = array_values((array) ($data['arquivos'] ?? []));
                    $observacao = filled($data['observacao'] ?? null)
                        ? trim((string) $data['observacao'])
                        : null;

                    foreach ($arquivos as $arquivoPath) {
                        $arquivoPath = (string) $arquivoPath;
                        $nomeOriginal = basename($arquivoPath);

                        $mimeType = null;
                        $tamanho = null;

                        try {
                            if (Storage::disk('public')->exists($arquivoPath)) {
                                $mimeType = Storage::disk('public')->mimeType($arquivoPath);
                                $tamanho = Storage::disk('public')->size($arquivoPath);
                            }
                        } catch (\Throwable) {
                            $mimeType = null;
                            $tamanho = null;
                        }

                        app(ItemControleFluxoService::class)->adicionarAnexo($record, $arquivoPath, $observacao, $user);

                        activity('item_controle')
                            ->performedOn($record)
                            ->causedBy($user)
                            ->withProperties([
                                'attributes' => [
                                    'arquivo' => $nomeOriginal,
                                    'observacao' => $observacao,
                                ],
                            ])
                            ->event('anexo_adicionado')
                            ->log('Anexo complementar adicionado ao item');
                    }

                    $this->dispatchItemUpdated($record->id);

                    Notification::make()
                        ->title('Anexo(s) complementar(es) adicionado(s) com sucesso.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function afterSave(): void
    {
        $this->dispatchItemUpdated($this->getCurrentItem()->id);
    }

    public function getFooterWidgets(): array
    {
        return [
            ItemControleAssinaturasWidget::class,
            ItemControleVersionamentoWidget::class,
            ItemControleComentariosWidget::class,
            ItemControleAnexosWidget::class,
            ItemControleHistoricoWidget::class,
        ];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }

    public function getWidgetData(): array
    {
        return [
            'record' => $this->record,
        ];
    }

    protected function getCurrentItem(): ItemControle
    {
        /** @var ItemControle $record */
        $record = $this->record;

        return $record;
    }

    protected function dispatchItemUpdated(int $itemId): void
    {
        $this->dispatch('item-controle-updated', id: $itemId);
    }
}
