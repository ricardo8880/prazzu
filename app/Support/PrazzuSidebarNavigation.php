<?php

namespace App\Support;

use App\Models\UserSidebarFavorite;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Illuminate\Support\Facades\Schema;
use Throwable;
use UnitEnum;

class PrazzuSidebarNavigation
{
    private const SECTION_FAVORITES = 'Favoritos';
    private const SECTION_GLOBAL = 'Global';
    private const SECTION_ACCOUNTING = 'Escritório Contábil / Contabilidade';
    private const SECTION_SUPER_ADMIN = 'Super Admin';

    /**
     * Grupos operacionais que fazem parte do dia a dia do escritório contábil.
     */
    private const ACCOUNTING_GROUPS = [
        '',
        'Visão Geral Contábil',
        'Central Operacional',
        'Pendências e Alertas',
        'Clientes',
        'Atendimentos',
        'Documentos',
        'Contratos',
        'Financeiro',
        'Aprovações',
        'Calendário Operacional',
        'Relatórios',
        'Auditoria e Riscos',
    ];

    /**
     * Grupos globais liberados também para administradores comuns.
     */
    private const GLOBAL_GROUPS = [
        'Administração',
        'Configurações',
        'Conta',
    ];

    /**
     * Itens dentro dos grupos globais que são exclusivos do super admin.
     */
    private const SUPER_ADMIN_ONLY_LABELS = [
        'Gestão de Planos',
        'White Label',
        'Onboarding',
        'Automações úteis',
        'Central de Evolução',
        'Configurações',
        'Empresas',
    ];

    /**
     * Grupos inteiros que são exclusivos do super admin.
     */
    private const SUPER_ADMIN_GROUPS = [
        'Governança',
        'Trabalho',
        'Visualizações da Operação',
    ];

    public static function build(NavigationBuilder $builder, Panel $panel): NavigationBuilder
    {
        $allowedItems = self::allowedNavigationItems($panel);
        $isSuperAdmin = self::isSuperAdmin();

        $globalItems = [];
        $accountingItems = [];
        $superAdminItems = [];

        foreach ($allowedItems as $item) {
            $section = self::sectionFor($item);

            match ($section) {
                self::SECTION_GLOBAL => $globalItems[] = $item,
                self::SECTION_ACCOUNTING => $accountingItems[] = $item,
                default => $superAdminItems[] = $item,
            };
        }

        $favoriteItems = self::favoriteNavigationItems($allowedItems);
        $favoriteSection = self::makeSection(self::SECTION_FAVORITES, $favoriteItems, false);

        $groups = $isSuperAdmin
            ? [
                $favoriteSection,
                self::makeSection(self::SECTION_GLOBAL, $globalItems),
                self::makeSection(self::SECTION_ACCOUNTING, $accountingItems),
                self::makeSection(self::SECTION_SUPER_ADMIN, $superAdminItems),
            ]
            : [
                $favoriteSection,
                self::makeSection(self::SECTION_ACCOUNTING, $accountingItems),
                self::makeSection(self::SECTION_GLOBAL, $globalItems),
            ];

        return $builder->groups(array_values(array_filter($groups)));
    }

    /**
     * Opções disponíveis na tela Meus Atalhos.
     *
     * @return array<string, string>
     */
    public static function favoriteOptions(?Panel $panel = null): array
    {
        $panel ??= Filament::getCurrentPanel();

        if (! $panel) {
            return [];
        }

        return collect(self::allowedNavigationItems($panel))
            ->reject(fn (NavigationItem $item): bool => $item->getLabel() === 'Meus Atalhos')
            ->mapWithKeys(function (NavigationItem $item): array {
                $group = self::normalizeGroup($item->getGroup());
                $label = $item->getLabel();

                return [self::itemKey($item) => ($group ? "{$group} — {$label}" : $label)];
            })
            ->sort()
            ->all();
    }

    /**
     * @return array<NavigationItem>
     */
    private static function allowedNavigationItems(Panel $panel): array
    {
        $items = self::navigationItemsFromPanel($panel);
        $isSuperAdmin = self::isSuperAdmin();
        $allowed = [];

        foreach ($items as $item) {
            if (! $item->isVisible()) {
                continue;
            }

            $section = self::sectionFor($item);

            if ($section === self::SECTION_SUPER_ADMIN && ! $isSuperAdmin) {
                continue;
            }

            if ($section === self::SECTION_GLOBAL && self::isSuperAdminOnlyItem($item) && ! $isSuperAdmin) {
                continue;
            }

            $allowed[] = $item;
        }

        return $allowed;
    }

    /**
     * @return array<NavigationItem>
     */
    private static function favoriteNavigationItems(array $allowedItems): array
    {
        $user = auth()->user();

        if (! $user || ! self::favoritesTableExists()) {
            return [];
        }

        $itemsByKey = collect($allowedItems)->keyBy(fn (NavigationItem $item): string => self::itemKey($item));

        return UserSidebarFavorite::query()
            ->where('user_id', $user->id)
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(function (UserSidebarFavorite $favorite) use ($itemsByKey): ?NavigationItem {
                /** @var NavigationItem|null $original */
                $original = $itemsByKey->get($favorite->item_key);

                if (! $original) {
                    return null;
                }

                return NavigationItem::make($original->getLabel())
                    ->group(self::SECTION_FAVORITES)
                    ->icon($original->getIcon())
                    ->activeIcon($original->getActiveIcon())
                    ->url($original->getUrl(), $original->shouldOpenUrlInNewTab())
                    ->sort((int) $favorite->position)
                    ->isActiveWhen(fn (): bool => $original->isActive());
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<NavigationItem>
     */
    private static function navigationItemsFromPanel(Panel $panel): array
    {
        $items = [];

        foreach ($panel->getPages() as $page) {
            if (! self::shouldExposeNavigationClass($page)) {
                continue;
            }

            $items = [
                ...$items,
                ...$page::getNavigationItems(),
            ];
        }

        foreach ($panel->getResources() as $resource) {
            if (! self::shouldExposeNavigationClass($resource)) {
                continue;
            }

            $items = [
                ...$items,
                ...$resource::getNavigationItems(),
            ];
        }

        return $items;
    }

    private static function shouldExposeNavigationClass(string $class): bool
    {
        try {
            if (method_exists($class, 'shouldRegisterNavigation') && ! $class::shouldRegisterNavigation()) {
                return false;
            }

            if (method_exists($class, 'canAccess') && ! $class::canAccess()) {
                return false;
            }
        } catch (Throwable) {
            return false;
        }

        return method_exists($class, 'getNavigationItems');
    }

    private static function makeSection(string $label, array $items, bool $sortByNavigationSort = true): ?NavigationGroup
    {
        $items = collect($items)
            ->unique(fn (NavigationItem $item): string => self::itemKey($item))
            ->when($sortByNavigationSort, fn ($collection) => $collection->sortBy(fn (NavigationItem $item): int => $item->getSort()))
            ->values()
            ->all();

        if (empty($items)) {
            return null;
        }

        return NavigationGroup::make($label)
            ->collapsible(false)
            ->items($items);
    }

    private static function sectionFor(NavigationItem $item): string
    {
        $group = self::normalizeGroup($item->getGroup());

        if (in_array($group, self::GLOBAL_GROUPS, true)) {
            return self::SECTION_GLOBAL;
        }

        if (in_array($group, self::ACCOUNTING_GROUPS, true)) {
            return self::SECTION_ACCOUNTING;
        }

        return self::SECTION_SUPER_ADMIN;
    }

    private static function isSuperAdminOnlyItem(NavigationItem $item): bool
    {
        $group = self::normalizeGroup($item->getGroup());
        $label = $item->getLabel();

        return in_array($group, self::SUPER_ADMIN_GROUPS, true)
            || in_array($label, self::SUPER_ADMIN_ONLY_LABELS, true);
    }

    private static function normalizeGroup(string|UnitEnum|null $group): string
    {
        if ($group instanceof UnitEnum) {
            return $group->name;
        }

        return trim((string) $group);
    }

    private static function itemKey(NavigationItem $item): string
    {
        return self::normalizeGroup($item->getGroup()) . '|' . $item->getLabel() . '|' . ($item->getUrl() ?? '');
    }

    private static function favoritesTableExists(): bool
    {
        try {
            return Schema::hasTable('user_sidebar_favorites');
        } catch (Throwable) {
            return false;
        }
    }

    private static function isSuperAdmin(): bool
    {
        $user = auth()->user();

        return $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
    }
}
