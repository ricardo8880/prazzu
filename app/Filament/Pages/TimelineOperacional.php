<?php

namespace App\Filament\Pages;

use App\Models\ItemControle;
use App\Services\ItemControleFluxoService;
use App\Support\ItemControleStatus;
use App\Support\PrazzuAccessControl;
use App\Support\PrazzuWorkPlanningData;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;
use Throwable;

class TimelineOperacional extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bars-3-bottom-left';
    protected static string | UnitEnum | null $navigationGroup = 'Visualizações da Operação';
    protected static ?string $navigationLabel = 'Timeline Operacional';
    protected static ?string $title = 'Timeline Operacional';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.timeline-operacional';

    public ?string $search = null;
    public string $statusFilter = 'abertos';
    public ?int $responsavelFilter = null;
    public string $zoom = 'semana';
    public bool $hideDone = true;

    public ?int $scheduleItemId = null;
    public ?string $scheduleStart = null;
    public ?string $scheduleEnd = null;

    protected function getViewData(): array
    {
        return PrazzuWorkPlanningData::timeline([
            'search' => $this->search,
            'status' => $this->statusFilter,
            'responsavel_id' => $this->responsavelFilter,
            'hide_done' => $this->hideDone,
            'zoom' => $this->zoom,
        ]);
    }

    public function scheduleSelectedTask(): void
    {
        if (! $this->scheduleItemId || ! $this->scheduleStart) {
            Notification::make()->title('Selecione uma tarefa e informe início.')->warning()->send();
            return;
        }

        if (! $this->podeAlterarItem((int) $this->scheduleItemId)) {
            Notification::make()->title('Tarefa não encontrada ou sem permissão.')->danger()->send();
            return;
        }

        try {
            PrazzuWorkPlanningData::scheduleTask((int) $this->scheduleItemId, $this->scheduleStart, $this->scheduleEnd);
            $this->registrarTimelineOperacional((int) $this->scheduleItemId, 'agendamento', 'Tarefa agendada na timeline', 'Agendamento manual realizado pela Timeline Operacional.');
            $this->scheduleItemId = null;
            $this->scheduleStart = null;
            $this->scheduleEnd = null;

            Notification::make()
                ->title('Tarefa agendada')
                ->body('A timeline foi atualizada e o vencimento foi sincronizado quando aplicável.')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Não foi possível agendar a tarefa')
                ->body('Confira as datas informadas e tente novamente.')
                ->danger()
                ->send();
        }
    }

    public function schedulePreset(int $id, string $preset): void
    {
        if (! $this->podeAlterarItem($id)) {
            Notification::make()->title('Tarefa não encontrada ou sem permissão.')->danger()->send();
            return;
        }

        try {
            PrazzuWorkPlanningData::schedulePreset($id, $preset);
            $this->registrarTimelineOperacional($id, 'agendamento', 'Tarefa colocada na agenda', 'Agendamento rápido aplicado pela Timeline Operacional.');

            Notification::make()
                ->title('Tarefa colocada na agenda')
                ->body('A agenda foi atualizada com o atalho selecionado.')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Não foi possível agendar pelo atalho')
                ->body('Tente novamente ou use o agendamento manual.')
                ->danger()
                ->send();
        }
    }

    public function quickMove(int $id, int $days): void
    {
        if (! $this->podeAlterarItem($id)) {
            Notification::make()->title('Tarefa não encontrada ou sem permissão.')->danger()->send();
            return;
        }

        try {
            PrazzuWorkPlanningData::moveTask($id, $days);
            $this->registrarTimelineOperacional($id, 'agendamento', 'Tarefa reagendada', ($days > 0 ? 'Tarefa adiada em ' : 'Tarefa antecipada em ') . abs($days) . ' dia(s).');

            Notification::make()
                ->title('Tarefa reagendada')
                ->body(($days > 0 ? 'Adiada em ' : 'Antecipada em ') . abs($days) . ' dia(s).')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Não foi possível reagendar')
                ->body('A data não foi alterada. Tente novamente.')
                ->danger()
                ->send();
        }
    }

    public function toggleMilestone(int $id): void
    {
        if (! $this->podeAlterarItem($id)) {
            Notification::make()->title('Tarefa não encontrada ou sem permissão.')->danger()->send();
            return;
        }

        try {
            PrazzuWorkPlanningData::toggleMilestone($id);
            $this->registrarTimelineOperacional($id, 'marco', 'Marco atualizado', 'Marcação de marco alterada pela Timeline Operacional.');

            Notification::make()
                ->title('Marco atualizado')
                ->body('A marcação de marco foi salva na timeline.')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Não foi possível atualizar o marco')
                ->body('Tente novamente. A marcação não foi salva.')
                ->danger()
                ->send();
        }
    }

    public function updateStatus(int $id, string $status): void
    {
        if (! in_array($status, ItemControleStatus::KANBAN_STATUSES, true)) {
            Notification::make()->title('Status inválido.')->danger()->send();
            return;
        }

        $item = $this->buscarItemVisivel($id);

        if (! $item) {
            Notification::make()->title('Tarefa não encontrada ou sem permissão.')->danger()->send();
            return;
        }

        try {
            app(ItemControleFluxoService::class)->atualizarStatus($item, $status, Filament::auth()->user());

            Notification::make()
                ->title('Status atualizado')
                ->body('A tarefa foi atualizada na timeline e nas demais telas de trabalho.')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Não foi possível atualizar o status')
                ->body('Tente novamente. A alteração não foi concluída.')
                ->danger()
                ->send();
        }
    }

    private function buscarItemVisivel(int $id): ?ItemControle
    {
        return ItemControle::query()
            ->visibleForUser(Filament::auth()->user())
            ->find($id);
    }

    private function podeAlterarItem(int $id): bool
    {
        $item = $this->buscarItemVisivel($id);

        return $item !== null && $item->canBeModifiedBy(Filament::auth()->user());
    }

    private function registrarTimelineOperacional(int $id, string $tipo, string $titulo, string $descricao): void
    {
        $item = $this->buscarItemVisivel($id);

        if (! $item || ! $item->canBeModifiedBy(Filament::auth()->user())) {
            return;
        }

        $item->registrarTimeline($tipo, $titulo, $descricao, ['origem' => 'timeline_operacional'], Filament::auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return PrazzuAccessControl::canUseTimeline();
    }
}
