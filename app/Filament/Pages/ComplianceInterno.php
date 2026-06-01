<?php

namespace App\Filament\Pages;

use App\Support\ComplianceModuleData;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use UnitEnum;

class ComplianceInterno extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';
    protected static string | UnitEnum | null $navigationGroup = 'Governança';
    protected static ?string $navigationLabel = 'Interno';
    protected static ?string $title = 'Compliance Interno';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.pages.compliance-interno';

    protected function getViewData(): array
    {
        return ['data' => ComplianceModuleData::interno()];
    }

    public function viewInternoDetailsAction(): Action
    {
        return Action::make('viewInternoDetails')
            ->label('Ver detalhes')
            ->modalHeading(fn (Action $action): string => $this->resolveInternoDetailRow($action->getArguments())['title'] ?? 'Detalhes do registro')
            ->modalDescription(fn (Action $action): string => $this->resolveInternoDetailRow($action->getArguments())['description'] ?? 'Veja o contexto do registro e as próximas ações disponíveis.')
            ->modalWidth(Width::FiveExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(fn (Action $action) => view('filament.pages.partials.compliance-interno-detail-modal', [
                'row' => $this->resolveInternoDetailRow($action->getArguments()),
            ]));
    }

    protected function resolveInternoDetailRow(array $arguments = []): array
    {
        $type = trim((string) ($arguments['type'] ?? ''));
        $recordId = $arguments['recordId'] ?? null;
        $itemId = $arguments['itemId'] ?? null;

        $data = ComplianceModuleData::interno();
        $collections = [
            $data['approvals'] ?? [],
            $data['signatures'] ?? [],
            $data['documents'] ?? [],
            $data['requests'] ?? [],
        ];

        foreach (($data['workflow'] ?? []) as $section) {
            $collections[] = $section['items'] ?? [];
        }

        foreach ($collections as $rows) {
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $sameType = $type === '' || (string) ($row['type'] ?? '') === $type;
                $sameRecord = $recordId === null || $recordId === '' || (string) ($row['recordId'] ?? '') === (string) $recordId;
                $sameItem = $itemId === null || $itemId === '' || (string) ($row['itemId'] ?? '') === (string) $itemId;

                if ($sameType && $sameRecord && $sameItem) {
                    return $row;
                }
            }
        }

        return [
            'title' => 'Registro não encontrado',
            'kind' => 'Registro interno',
            'description' => 'Não foi possível localizar os detalhes deste registro no conjunto atual de dados da página.',
            'status' => 'Indisponível',
            'nextStep' => 'Atualize a página e tente novamente.',
            'tone' => 'warning',
            'detailCards' => [],
            'actions' => [],
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
