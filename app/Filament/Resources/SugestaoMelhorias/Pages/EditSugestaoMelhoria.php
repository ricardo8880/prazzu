<?php

namespace App\Filament\Resources\SugestaoMelhorias\Pages;

use App\Filament\Resources\SugestaoMelhorias\SugestaoMelhoriaResource;
use App\Models\SugestaoMelhoria;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditSugestaoMelhoria extends EditRecord
{
    protected static string $resource = SugestaoMelhoriaResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        /** @var SugestaoMelhoria $sugestao */
        $sugestao = $this->record;

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->hasEmpresaVinculada() && (int) $user->empresa_id === (int) $sugestao->empresa_id) {
            return;
        }

        if ((int) $sugestao->user_id === (int) $user->id) {
            return;
        }

        abort(403, 'Você não tem permissão para acessar esta sugestão.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->isSuperAdmin()) {
            $statusAtual = (string) $this->record->status;
            $novoStatus = (string) ($data['status'] ?? $statusAtual);

            if ($novoStatus !== $statusAtual || filled($data['resposta_admin'] ?? null)) {
                $data['analisado_por'] = $user->id;
                $data['analisado_em'] = now();
            }

            return $data;
        }

        unset(
            $data['status'],
            $data['resposta_admin'],
            $data['analisado_por'],
            $data['analisado_em']
        );

        return $data;
    }
}