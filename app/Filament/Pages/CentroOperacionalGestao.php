<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use UnitEnum;

class CentroOperacionalGestao extends CentroOperacional
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static string | UnitEnum | null $navigationGroup = 'Trabalho';
    protected static ?string $navigationLabel = 'Operação Interna';
    protected static ?string $title = 'Operação Interna';
    protected static ?int $navigationSort = 2;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected string $view = 'filament.pages.centro-operacional-gestao';

    public string $operationalTab = 'workload';

    public function mount(): void
    {
        $tab = request()->query('aba');

        if (is_string($tab) && $this->isOperationalTabValid($tab)) {
            $this->operationalTab = $tab;
        }
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getSubNavigation(): array
    {
        return collect($this->getOperationalSubNavigationItems())
            ->map(fn (array $item): NavigationItem => NavigationItem::make($item['label'])
                ->icon($item['icon'])
                ->url(static::getUrl() . '?' . http_build_query(['aba' => $item['key']]))
                ->isActiveWhen(fn (): bool => $this->operationalTab === $item['key'])
                ->sort($item['sort']))
            ->all();
    }

    protected function getOperationalSubNavigationItems(): array
    {
        return [
            [
                'key' => 'workload',
                'label' => 'Workload da Equipe',
                'icon' => 'heroicon-o-users',
                'sort' => 1,
            ],
            [
                'key' => 'aprovacoes',
                'label' => 'Aprovações',
                'icon' => 'heroicon-o-check-badge',
                'sort' => 2,
            ],
            [
                'key' => 'financeiro',
                'label' => 'Pendências Financeiras',
                'icon' => 'heroicon-o-banknotes',
                'sort' => 3,
            ],
        ];
    }

    protected function isOperationalTabValid(string $tab): bool
    {
        return in_array($tab, ['workload', 'aprovacoes', 'financeiro'], true);
    }
}
