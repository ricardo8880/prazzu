<?php

namespace App\Filament\Resources\ItemControles\Widgets;


use App\Support\CachedSchema;
use App\Support\DocumentStorage;
use App\Models\ItemControle;
use App\Models\PrazzuDocumentVersion;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ItemControleVersionamentoWidget extends Widget
{
    public ?ItemControle $record = null;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.resources.item-controles.widgets.item-controle-versionamento-widget';

    protected $listeners = [
        'item-controle-updated' => '$refresh',
    ];

    public function getViewData(): array
    {
        $item = $this->record?->fresh(['documentVersions.uploader', 'documentVersions.approver']);

        return [
            'item' => $item,
            'temTabelaVersionamento' => CachedSchema::hasTable('prazzu_document_versions'),
            'versaoAtual' => $this->versaoAtual($item),
            'versoes' => $this->versoes($item),
            'comparacao' => $this->comparacao($item),
            'podeGerenciar' => $this->podeGerenciar(),
        ];
    }

    public function restaurarVersao(int $versionId): void
    {
        $item = $this->record;
        $user = Filament::auth()->user();

        if (! $item || ! $user || ! $item->canBeModifiedBy($user)) {
            abort(403, 'Você não tem permissão para restaurar versões deste item.');
        }

        if (! CachedSchema::hasTable('prazzu_document_versions')) {
            Notification::make()->title('Tabela de versionamento não encontrada.')->danger()->send();
            return;
        }

        $versao = PrazzuDocumentVersion::query()
            ->where('item_controle_id', $item->id)
            ->whereKey($versionId)
            ->first();

        if (! $versao || blank($versao->file_path)) {
            Notification::make()->title('Versão sem arquivo para restaurar.')->warning()->send();
            return;
        }

        $arquivoAnterior = $item->arquivo;

        activity()->disableLogging();

        $item->forceFill([
            'arquivo' => $versao->file_path,
        ])->save();

        activity()->enableLogging();

        activity('item_controle')
            ->performedOn($item)
            ->causedBy($user)
            ->withProperties([
                'old' => ['arquivo' => $arquivoAnterior],
                'attributes' => [
                    'arquivo' => $versao->file_path,
                    'version_number' => $versao->version_number,
                    'motivo' => 'Restauração de versão documental',
                ],
            ])
            ->event('versao_restaurada')
            ->log('Versão documental restaurada');

        $this->dispatch('item-controle-updated', id: $item->id);

        Notification::make()
            ->title('Versão restaurada')
            ->body('O arquivo principal do item foi atualizado para a versão selecionada.')
            ->success()
            ->send();
    }

    private function versaoAtual(?ItemControle $item): ?array
    {
        if (! $item) {
            return null;
        }

        $atual = $item->documentVersions->first();

        if ($atual) {
            return $this->formatarVersao($atual, true);
        }

        if (blank($item->arquivo)) {
            return null;
        }

        return [
            'id' => null,
            'numero' => 'Principal',
            'tipo' => 'Arquivo principal',
            'arquivo' => basename((string) $item->arquivo),
            'url' => DocumentStorage::publicUrl($item->arquivo),
            'status' => 'atual',
            'status_label' => 'Atual',
            'status_tom' => 'success',
            'criado_por' => 'Registro principal',
            'aprovado_por' => null,
            'criado_em' => optional($item->updated_at)->format('d/m/Y H:i') ?: '-',
            'motivo' => 'Arquivo principal cadastrado no item.',
            'is_atual' => true,
        ];
    }

    private function versoes(?ItemControle $item): array
    {
        if (! $item || ! CachedSchema::hasTable('prazzu_document_versions')) {
            return [];
        }

        return $item->documentVersions
            ->map(fn (PrazzuDocumentVersion $version) => $this->formatarVersao($version, false))
            ->values()
            ->all();
    }

    private function comparacao(?ItemControle $item): ?array
    {
        $versoes = $this->versoes($item);

        if (count($versoes) < 2) {
            return null;
        }

        return [
            'atual' => $versoes[0],
            'anterior' => $versoes[1],
            'mudancas' => array_values(array_filter([
                $versoes[0]['arquivo'] !== $versoes[1]['arquivo'] ? 'Arquivo diferente' : null,
                $versoes[0]['status'] !== $versoes[1]['status'] ? 'Status alterado' : null,
                $versoes[0]['motivo'] !== $versoes[1]['motivo'] ? 'Motivo/observação diferente' : null,
            ])),
        ];
    }

    private function formatarVersao(PrazzuDocumentVersion $version, bool $isAtual): array
    {
        $status = (string) ($version->status ?: 'pendente');
        $arquivo = (string) ($version->file_path ?: '');

        return [
            'id' => $version->id,
            'numero' => 'v' . ($version->version_number ?: 1),
            'tipo' => Str::headline((string) ($version->document_type ?: 'documento')),
            'arquivo' => $arquivo !== '' ? basename($arquivo) : 'Sem arquivo vinculado',
            'url' => $arquivo !== '' ? DocumentStorage::publicUrl($arquivo) : null,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'status_tom' => $this->statusTone($status),
            'criado_por' => $version->uploader?->name ?: 'Sistema',
            'aprovado_por' => $version->approver?->name,
            'criado_em' => optional($version->created_at)->format('d/m/Y H:i') ?: '-',
            'motivo' => filled($version->notes) ? $version->notes : 'Sem motivo informado.',
            'is_atual' => $isAtual,
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'aprovado', 'aprovada', 'ativo', 'atual' => 'Atual/aprovado',
            'pendente' => 'Pendente',
            'reprovado', 'reprovada' => 'Reprovado',
            'substituido', 'substituído' => 'Substituído',
            default => Str::headline($status),
        };
    }

    private function statusTone(string $status): string
    {
        return match ($status) {
            'aprovado', 'aprovada', 'ativo', 'atual' => 'success',
            'pendente' => 'warning',
            'reprovado', 'reprovada' => 'danger',
            default => 'info',
        };
    }

    private function podeGerenciar(): bool
    {
        $user = Filament::auth()->user();
        $item = $this->record;

        return $user && $item && $item->canBeModifiedBy($user);
    }
}
