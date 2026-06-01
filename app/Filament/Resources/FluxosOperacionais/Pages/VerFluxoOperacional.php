<?php

namespace App\Filament\Resources\FluxosOperacionais\Pages;

use App\Filament\Resources\FluxosOperacionais\FluxoOperacionalResource;
use App\Models\FluxoOperacional;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class VerFluxoOperacional extends Page
{
    protected static string $resource = FluxoOperacionalResource::class;

    protected string $view = 'filament.resources.fluxos-operacionais.pages.ver-fluxo-operacional';

    protected static ?string $title = 'Ver Fluxo Operacional';

    public FluxoOperacional $record;

    public function mount(FluxoOperacional $record): void
    {
        $this->record = $record->load([
            'empresa:id,razao_social',
            'etapas.responsavelPadrao:id,nome',
        ]);

        $this->record->loadCount([
            'etapas',
            'itens',
        ]);
    }


    public function getTitle(): string
    {
        return $this->record->nome;
    }

    public function getHeading(): string
    {
        return 'Fluxo Operacional';
    }

    public function getSubheading(): ?string
    {
        return 'Página visual do fluxo criado, com resumo, etapas e itens vinculados.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editar')
                ->label('Editar fluxo')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->visible(fn (): bool => FluxoOperacionalResource::canEdit($this->record))
                ->url(fn (): string => FluxoOperacionalResource::getUrl('edit', ['record' => $this->record->getKey()])),

            Action::make('voltar')
                ->label('Voltar para tabela')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->url(fn (): string => FluxoOperacionalResource::getUrl('index')),
        ];
    }

    public function etapas(): Collection
    {
        return $this->record->etapas ?? collect();
    }

    public function itensRecentes(): Collection
    {
        return $this->record->itens()
            ->with(['responsavel:id,nome'])
            ->latest('updated_at')
            ->limit(8)
            ->get();
    }

    public function totalItens(): int
    {
        return (int) ($this->record->itens_count ?? $this->record->itens()->count());
    }

    public function totalEtapasAtivas(): int
    {
        return $this->etapas()->where('ativo', true)->count();
    }

    public function itensPendentes(): int
    {
        return $this->record->itens()
            ->where('status', 'pendente')
            ->count();
    }

    public function itensConcluidos(): int
    {
        return $this->record->itens()
            ->where('status', 'concluido')
            ->count();
    }

    public function tipoItemLabel(): string
    {
        return match ($this->record->tipo_item) {
            'contrato' => 'Contrato',
            'documento' => 'Documento',
            'licenca' => 'Licença',
            'acordo' => 'Acordo',
            default => 'Todos os tipos',
        };
    }

    public function statusClasse(?string $status): string
    {
        return match ($status) {
            'concluido' => 'fo-status--success',
            'cancelado' => 'fo-status--danger',
            'em_andamento', 'andamento' => 'fo-status--info',
            default => 'fo-status--warning',
        };
    }

    public function statusLabel(?string $status): string
    {
        return ucfirst(str_replace('_', ' ', (string) ($status ?: 'pendente')));
    }

    public function usuarioEhSuperAdmin(): bool
    {
        return Filament::auth()->user()?->isSuperAdmin() === true;
    }
}
