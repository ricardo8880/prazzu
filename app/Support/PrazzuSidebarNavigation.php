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
    private const SECTION_ACCOUNTING = 'Escritório Contábil';
    private const SECTION_SUPER_ADMIN = 'Super Admin';

    /**
     * Grupos operacionais que fazem parte do dia a dia do escritório contábil.
     */
    private const ACCOUNTING_GROUPS = [
        '',
        'Visão Geral',
        'Visão Geral Contábil',
        'Operação',
        'Central Operacional',
        'Pendências e Prazos',
        'Pendências e Alertas',
        'Calendário Operacional',
        'Clientes e Atendimentos',
        'Clientes',
        'Atendimentos',
        'Documentos e Modelos',
        'Documentos',
        'Contratos e Financeiro',
        'Contratos',
        'Financeiro',
        'Aprovações',
        'Relatórios e Auditoria',
        'Relatórios',
        'Auditoria e Riscos',
        'Trabalho',
        'Visualizações da Operação',
    ];

    /**
     * Labels liberadas na sidebar para usuários que não são super admin.
     *
     * A regra principal é por label para evitar que um grupo amplo como
     * "Cadastros e Configurações" exponha telas administrativas por engano.
     * O super admin continua vendo tudo que o Filament registrar.
     */
    private const ACCOUNTING_ALLOWED_LABELS = [
        'Home',
        'Resumo Executivo',

        // Dia a dia operacional do escritório contábil.
        'Tarefas Operacionais',
        'Mesa Operacional',
        'Pendências',
        'SLA e Prazos',
        'Calendário Operacional',
        'Aprovações',
        'Checklists',
        'Kanban',
        'Timeline Operacional',

        // Relacionamento com clientes.
        'Carteira de Clientes',
        'Clientes e Atendimentos',
        'Portal do Cliente',
        'Empresas Cadastradas',
        'Responsáveis',

        // Documentos e processos contratuais.
        'Documentos',
        'Gestão Documental',
        'Contratos',

        // Cobrança operacional do escritório. A tela de gateway financeiro fica fora
        // do usuário comum porque é configuração sensível.
        'Cobranças',
        'Assinaturas',

        // Análise e rastreabilidade operacional.
        'Relatórios Operacionais',
        'Auditoria e Rastreabilidade',
        'Riscos e Evidências',
    ];

    /**
     * Ordem e nomes finais dos blocos da área contábil na sidebar.
     */
    private const ACCOUNTING_GROUP_LABELS = [
        '' => 'Contabilidade · Visão Geral',
        'Visão Geral' => 'Contabilidade · Visão Geral',
        'Visão Geral Contábil' => 'Contabilidade · Visão Geral',
        'Operação' => 'Contabilidade · Operação',
        'Central Operacional' => 'Contabilidade · Operação',
        'Aprovações' => 'Contabilidade · Operação',
        'Trabalho' => 'Contabilidade · Operação',
        'Visualizações da Operação' => 'Contabilidade · Operação',
        'Pendências e Prazos' => 'Contabilidade · Pendências e Prazos',
        'Pendências e Alertas' => 'Contabilidade · Pendências e Prazos',
        'Calendário Operacional' => 'Contabilidade · Pendências e Prazos',
        'Clientes e Atendimentos' => 'Contabilidade · Clientes e Atendimento',
        'Clientes' => 'Contabilidade · Clientes e Atendimento',
        'Atendimentos' => 'Contabilidade · Clientes e Atendimento',
        'Documentos e Modelos' => 'Contabilidade · Documentos e Modelos',
        'Documentos' => 'Contabilidade · Documentos e Modelos',
        'Contratos e Financeiro' => 'Contabilidade · Contratos e Financeiro',
        'Contratos' => 'Contabilidade · Contratos e Financeiro',
        'Financeiro' => 'Contabilidade · Contratos e Financeiro',
        'Relatórios e Auditoria' => 'Contabilidade · Relatórios e Auditoria',
        'Relatórios' => 'Contabilidade · Relatórios e Auditoria',
        'Auditoria e Riscos' => 'Contabilidade · Relatórios e Auditoria',
    ];

    private const ACCOUNTING_GROUP_SORT = [
        'Contabilidade · Visão Geral' => 10,
        'Contabilidade · Operação' => 20,
        'Contabilidade · Pendências e Prazos' => 30,
        'Contabilidade · Clientes e Atendimento' => 40,
        'Contabilidade · Documentos e Modelos' => 50,
        'Contabilidade · Contratos e Financeiro' => 60,
        'Contabilidade · Relatórios e Auditoria' => 70,
        'Escritório Contábil · Configurações' => 80,
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

        $accountingSections = self::makeAccountingSections($accountingItems);

        $groups = $isSuperAdmin
            ? [
                $favoriteSection,
                ...$accountingSections,
                self::makeSection(self::SECTION_GLOBAL, $globalItems),
                self::makeSection(self::SECTION_SUPER_ADMIN, $superAdminItems),
            ]
            : [
                $favoriteSection,
                ...$accountingSections,
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

            // Super admin mantém a visão completa do sistema.
            if ($isSuperAdmin) {
                $allowed[] = $item;
                continue;
            }

            // Usuários comuns veem somente a lista fechada de telas de
            // contabilidade/escritório contábil definida acima.
            if (! in_array($item->getLabel(), self::ACCOUNTING_ALLOWED_LABELS, true)) {
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

    /**
     * Divide a antiga seção única de contabilidade em blocos menores e previsíveis.
     * Isso reduz carga cognitiva na sidebar e deixa cada área com um propósito claro.
     *
     * @return array<NavigationGroup>
     */
    private static function makeAccountingSections(array $items): array
    {
        return collect($items)
            ->groupBy(fn (NavigationItem $item): string => self::accountingGroupLabel($item))
            ->sortBy(fn ($items, string $label): int => self::ACCOUNTING_GROUP_SORT[$label] ?? 999)
            ->map(fn ($groupItems, string $label): ?NavigationGroup => self::makeSection($label, $groupItems->all()))
            ->filter()
            ->values()
            ->all();
    }

    private static function accountingGroupLabel(NavigationItem $item): string
    {
        $label = $item->getLabel();

        return match ($label) {
            'Home', 'Resumo Executivo' => 'Contabilidade · Visão Geral',
            'Tarefas Operacionais', 'Mesa Operacional', 'Aprovações', 'Checklists', 'Kanban', 'Timeline Operacional', 'Cronograma Gantt', 'Painéis - Tabelas' => 'Contabilidade · Operação',
            'Pendências', 'SLA e Prazos', 'Calendário Operacional' => 'Contabilidade · Pendências e Prazos',
            'Carteira de Clientes', 'Clientes e Atendimentos', 'Portal do Cliente', 'Empresas Cadastradas', 'Responsáveis' => 'Contabilidade · Clientes e Atendimento',
            'Documentos', 'Gestão Documental', 'Armazenamento' => 'Contabilidade · Documentos e Modelos',
            'Contratos', 'Assinaturas', 'Financeiro', 'Cobranças' => 'Contabilidade · Contratos e Financeiro',
            'Relatórios Operacionais', 'Relatórios Personalizados', 'Auditoria e Rastreabilidade', 'Riscos e Evidências' => 'Contabilidade · Relatórios e Auditoria',
            'Dados do Escritório', 'Parâmetros do Escritório', 'Modelos Operacionais', 'Categorias Operacionais', 'Tags Operacionais', 'Meus Atalhos' => 'Escritório Contábil · Configurações',
            default => self::ACCOUNTING_GROUP_LABELS[self::normalizeGroup($item->getGroup())] ?? self::SECTION_ACCOUNTING,
        };
    }

    private static function sectionFor(NavigationItem $item): string
    {
        $group = self::normalizeGroup($item->getGroup());
        $label = $item->getLabel();

        if (in_array($label, self::ACCOUNTING_ALLOWED_LABELS, true)) {
            return self::SECTION_ACCOUNTING;
        }

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
