<?php

namespace App\Filament\Resources\ItemControles\Widgets;

use App\Models\ItemControle;
use App\Models\ItemControleAnexo;
use App\Support\ItemControleAnexoUploader;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ItemControleAnexosWidget extends TableWidget
{
    public ?ItemControle $record = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Anexos Complementares';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25])
            ->striped()
            ->emptyStateIcon('heroicon-o-paper-clip')
            ->emptyStateHeading('Nenhum anexo complementar encontrado')
            ->emptyStateDescription('Use o botão “Upload rápido” para adicionar evidências, versões auxiliares ou arquivos de apoio sem substituir o anexo principal.')
            ->headerActions([
                Action::make('uploadRapido')
                    ->label('Upload rápido')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('warning')
                    ->visible(fn (): bool => $this->usuarioPodeModificar())
                    ->modalHeading('Adicionar anexos complementares')
                    ->modalDescription('Os arquivos enviados aqui ficam como anexos extras e não substituem o anexo principal do item.')
                    ->schema($this->getUploadSchema())
                    ->action(fn (array $data) => $this->adicionarAnexos($data)),
            ])
            ->columns([
                IconColumn::make('preview')
                    ->label('Preview')
                    ->boolean()
                    ->getStateUsing(fn (ItemControleAnexo $record): bool => $record->isPreviewable())
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn (ItemControleAnexo $record): string => $record->isPreviewable() ? 'Preview disponível' : 'Arquivo sem preview direto'),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Enviado por')
                    ->placeholder('Sistema')
                    ->sortable(),

                TextColumn::make('nome_original')
                    ->label('Arquivo complementar')
                    ->getStateUsing(fn (ItemControleAnexo $record): string => $record->getNomeExibicao())
                    ->url(fn (ItemControleAnexo $record): ?string => $record->getUrl(), shouldOpenInNewTab: true)
                    ->color('primary')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('mime_type')
                    ->label('Tipo')
                    ->badge()
                    ->placeholder('-')
                    ->formatStateUsing(fn (?string $state): string => $this->formatarTipoArquivo($state))
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('tamanho_formatado')
                    ->label('Tamanho')
                    ->getStateUsing(fn (ItemControleAnexo $record): string => $record->getTamanhoFormatado()),

                TextColumn::make('observacao')
                    ->label('Observação')
                    ->placeholder('Sem observação')
                    ->wrap(),
            ])
            ->recordActions([
                Action::make('abrir')
                    ->label('Abrir')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ItemControleAnexo $record): ?string => $record->getUrl(), shouldOpenInNewTab: true),

                DeleteAction::make()
                    ->label('Remover')
                    ->icon('heroicon-o-trash')
                    ->visible(fn (): bool => $this->usuarioPodeModificar())
                    ->requiresConfirmation()
                    ->modalHeading('Remover anexo complementar')
                    ->modalDescription('O registro será removido da lista. O arquivo físico será mantido no storage para evitar perda acidental de evidências antigas.'),
            ]);
    }

    /**
     * @return array<int, mixed>
     */
    protected function getUploadSchema(): array
    {
        return [
            FileUpload::make('arquivos')
                ->label('Arquivos complementares')
                ->helperText('PDF, Word, Excel, CSV, TXT ou imagem. Tamanho máximo: 10 MB por arquivo.')
                ->multiple()
                ->required()
                ->disk('public')
                ->directory('comprovantes-prazos/complementares')
                ->acceptedFileTypes(ItemControleAnexoUploader::ALLOWED_MIME_TYPES)
                ->maxSize(ItemControleAnexoUploader::MAX_SIZE_KB)
                ->downloadable()
                ->openable()
                ->previewable(true),

            Textarea::make('observacao')
                ->label('Observação')
                ->helperText('Opcional. Ajuda a equipe a entender o contexto do anexo.')
                ->rows(3)
                ->maxLength(1000),
        ];
    }

    protected function adicionarAnexos(array $data): void
    {
        $item = $this->record;
        $user = Filament::auth()->user();

        if (! $item || ! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if (! $item->canBeModifiedBy($user)) {
            abort(403, 'Você não tem permissão para adicionar anexos neste item.');
        }

        ItemControleAnexoUploader::storeComplementares(
            item: $item,
            user: $user,
            arquivos: array_values((array) ($data['arquivos'] ?? [])),
            observacao: filled($data['observacao'] ?? null) ? trim((string) $data['observacao']) : null,
        );

        Notification::make()
            ->title('Anexo(s) enviado(s) com sucesso.')
            ->body('Os arquivos já estão disponíveis na lista de anexos complementares.')
            ->success()
            ->send();
    }

    protected function usuarioPodeModificar(): bool
    {
        $user = Filament::auth()->user();

        return $user && $this->record && $this->record->canBeModifiedBy($user);
    }

    protected function getTableQuery(): Builder
    {
        $itemId = $this->record?->id;

        if (! $itemId) {
            return ItemControleAnexo::query()->whereRaw('1 = 0');
        }

        return ItemControleAnexo::query()
            ->with('user')
            ->where('item_controle_id', $itemId);
    }

    private function formatarTipoArquivo(?string $mimeType): string
    {
        return match ($mimeType) {
            'application/pdf' => 'PDF',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel',
            'text/csv' => 'CSV',
            'text/plain' => 'TXT',
            'image/jpeg', 'image/png', 'image/webp' => 'Imagem',
            default => filled($mimeType) ? (string) $mimeType : '-',
        };
    }
}
