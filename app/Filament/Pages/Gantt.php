<?php

namespace App\Filament\Pages;

use App\Support\PrazzuWorkPlanningData;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class Gantt extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string | UnitEnum | null $navigationGroup = 'Trabalho';
    protected static ?string $navigationLabel = 'Gantt Enterprise';
    protected static ?string $title = 'Gantt Enterprise';
    protected static ?int $navigationSort = 6;
    protected string $view = 'filament.pages.gantt-enterprise';

    public ?string $search = null;
    public string $statusFilter = 'abertos';
    public ?int $empresaFilter = null;
    public ?int $responsavelFilter = null;

    public ?int $dependencyItemId = null;
    public ?int $dependencyDependsOnId = null;
    public string $dependencyType = 'finish_to_start';
    public ?string $dependencyNotes = null;

    public ?int $windowItemId = null;
    public ?string $windowStart = null;
    public ?string $windowEnd = null;

    protected function getViewData(): array
    {
        return PrazzuWorkPlanningData::gantt($this->filters());
    }

    public function createDependency(): void
    {
        if (! $this->dependencyItemId || ! $this->dependencyDependsOnId) {
            Notification::make()->title('Escolha a tarefa bloqueada e a tarefa predecessora.')->warning()->send();
            return;
        }

        PrazzuWorkPlanningData::createDependency((int) $this->dependencyItemId, (int) $this->dependencyDependsOnId, $this->dependencyType, $this->dependencyNotes);
        $this->dependencyItemId = null;
        $this->dependencyDependsOnId = null;
        $this->dependencyNotes = null;

        Notification::make()->title('Dependência criada. O bloqueio foi recalculado.')->success()->send();
    }

    public function removeDependency(int $id): void
    {
        PrazzuWorkPlanningData::deleteDependency($id);
        Notification::make()->title('Dependência removida.')->success()->send();
    }

    public function moveTask(int $id, int $days): void
    {
        PrazzuWorkPlanningData::moveTask($id, $days);
        Notification::make()->title('Tarefa movida. Dependentes foram ajustados quando necessário.')->success()->send();
    }

    public function updateWindow(): void
    {
        if (! $this->windowItemId || ! $this->windowStart || ! $this->windowEnd) {
            Notification::make()->title('Escolha a tarefa, início e fim.')->warning()->send();
            return;
        }

        PrazzuWorkPlanningData::setTaskWindow((int) $this->windowItemId, $this->windowStart, $this->windowEnd);
        $this->windowItemId = null;
        $this->windowStart = null;
        $this->windowEnd = null;

        Notification::make()->title('Período da tarefa atualizado no cronograma.')->success()->send();
    }

    public function saveBaseline(): void
    {
        $count = PrazzuWorkPlanningData::saveBaseline();
        Notification::make()->title("Linha de base salva para {$count} itens.")->success()->send();
    }

    public function syncBlocked(): void
    {
        PrazzuWorkPlanningData::syncBlockedFlags();
        Notification::make()->title('Bloqueios por dependência recalculados.')->success()->send();
    }

    public function toggleMilestone(int $id): void
    {
        PrazzuWorkPlanningData::toggleMilestone($id);
        Notification::make()->title('Marco atualizado.')->success()->send();
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'empresa_id' => $this->empresaFilter,
            'responsavel_id' => $this->responsavelFilter,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
