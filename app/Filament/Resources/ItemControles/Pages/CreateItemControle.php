<?php

namespace App\Filament\Resources\ItemControles\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\Responsavel;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;

class CreateItemControle extends CreateRecord
{
    protected static string $resource = ItemControleResource::class;

    protected static bool $canCreateAnother = false;

    protected array $extraBodyAttributes = [
        'class' => 'prazzu-create-item-controle-page',
    ];

    public function getTitle(): string
    {
        return 'Criar Item de Controle';
    }

    public function getHeading(): string
    {
        return 'Criar Item de Controle';
    }

    public function getSubheading(): ?string
    {
        return 'Cadastre um novo item para controlar prazos, evitar multas e garantir a execução correta.';
    }

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancelar_criacao')
                ->label('Cancelar')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),

            Action::make('salvar_item_topo')
                ->label('Salvar Item')
                ->color('primary')
                ->action(fn (): mixed => $this->create()),
        ];
    }

    public function defaultForm(Schema $schema): Schema
    {
        return parent::defaultForm($schema)
            ->columns(['default' => 1, 'xl' => 3]);
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Salvar Item');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar');
    }

    protected function beforeCreate(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        if (! $user->hasEmpresaVinculada()) {
            Notification::make()
                ->title('Seu usuário não possui empresa vinculada.')
                ->danger()
                ->send();

            $this->halt();
        }

        if ($user->isAdminEmpresa()) {
            return;
        }

        if ($user->isGestor()) {
            $temEquipe = Responsavel::query()
                ->where('empresa_id', $user->empresa_id)
                ->where('gestor_user_id', $user->id)
                ->exists();

            if (! $temEquipe) {
                Notification::make()
                    ->title('Este gestor ainda não possui responsáveis vinculados à equipe.')
                    ->danger()
                    ->send();

                $this->halt();
            }

            return;
        }

        if (! $user->responsavel) {
            Notification::make()
                ->title('Seu usuário não está vinculado a um responsável.')
                ->danger()
                ->send();

            $this->halt();
        }
    }


    protected function afterCreate(): void
    {
        $state = $this->data ?? [];
        $record = $this->record;

        $responsavelNome = '-';
        $responsavelId = $state['responsavel_id'] ?? $record?->responsavel_id ?? null;

        if ($responsavelId) {
            $responsavelNome = (string) (Responsavel::query()->whereKey($responsavelId)->value('nome') ?: '-');
        }

        $vencimento = $record?->data_vencimento ?? ($state['data_vencimento'] ?? null);
        $vencimentoFormatado = filled($vencimento)
            ? date('d/m/Y', strtotime((string) $vencimento))
            : '-';

        $risco = (string) ($state['risco_multa_visual'] ?? 'alto');
        $riscoLabel = [
            'nenhum' => 'Nenhum',
            'baixo' => 'Baixo',
            'medio' => 'Médio',
            'alto' => 'Alto',
            'critico' => 'Crítico',
        ][$risco] ?? ucfirst($risco);

        $checklistCount = 0;

        try {
            if ($record && method_exists($record, 'checklists')) {
                $checklistCount = (int) $record->checklists()->count();
            }
        } catch (\Throwable) {
            $checklistCount = 0;
        }

        if ($checklistCount === 0 && isset($state['checklists']) && is_array($state['checklists'])) {
            $checklistCount = count($state['checklists']);
        }

        $checklistTexto = $checklistCount === 1
            ? '1 etapa criada automaticamente'
            : $checklistCount . ' etapas criadas automaticamente';

        Notification::make()
            ->title('Item criado com sucesso')
            ->success()
            ->body(new HtmlString(
                '<div class="pz-create-success-notification">'
                . '<div><strong>Responsável:</strong> ' . e($responsavelNome) . '</div>'
                . '<div><strong>Vencimento:</strong> ' . e($vencimentoFormatado) . '</div>'
                . '<div><strong>Risco:</strong> ' . e($riscoLabel) . '</div>'
                . '<div><strong>Checklist:</strong> ' . e($checklistTexto) . '</div>'
                . '</div>'
            ))
            ->send();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
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
                abort(403, 'Você só pode criar itens para responsáveis da sua empresa.');
            }

            return $data;
        }

        if ($user->isGestor()) {
            $responsavelId = isset($data['responsavel_id']) ? (int) $data['responsavel_id'] : null;

            if (! ItemControleResource::canUserAssignResponsavel($user, $responsavelId)) {
                abort(403, 'Você só pode criar itens para responsáveis da sua equipe.');
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
}
