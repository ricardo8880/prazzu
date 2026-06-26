<?php

namespace App\Support;

use App\Models\User;

class AccountingProfileNavigation
{
    public static function profiles(): array
    {
        return [
            'socio' => [
                'label' => 'Sócio',
                'hint' => 'Visão estratégica, financeira e de auditoria',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Visão Geral Contábil',
                    'Financeiro',
                    'Relatórios',
                    'Clientes',
                    'Contratos',
                    'Auditoria e Riscos',
                    'Aprovações',
                    'Calendário Operacional',
                    'Administração',
                    'Configurações',
                    'Conta',
                ],
                'aliases' => [
                    'Dashboard' => 'Visão Geral Contábil',
                    'Dashboard Executivo' => 'Visão Geral Contábil',
                    'Dashboard Executivo Contábil' => 'Visão Geral Contábil',
                    'Cobranças' => 'Financeiro',
                    'Relatórios Exportáveis' => 'Relatórios',
                    'Relatórios Personalizados' => 'Relatórios',
                    'Dashboards' => 'Visão Geral Contábil',
                    'Painéis' => 'Visão Geral Contábil',
                    'Dashboard Configurável' => 'Visão Geral Contábil',
                    'Riscos' => 'Auditoria e Riscos',
                    'Auditoria' => 'Auditoria e Riscos',
                    'Auditoria completa' => 'Auditoria e Riscos',
                    'Auditoria Completa' => 'Auditoria e Riscos',
                    'Compliance Interno' => 'Auditoria e Riscos',
                    'Central de Aprovações' => 'Aprovações',
                    'Aprovações' => 'Aprovações',
                    'Tarefas Operacionais' => 'Central Operacional',
                    'Kanban Operacional' => 'Visualizações da Operação',
                    'Cronograma Gantt' => 'Visualizações da Operação',
                ],
            ],
            'gestor' => [
                'label' => 'Gestor',
                'hint' => 'Gestão da operação, equipe, prazos e aprovações',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Central Operacional',
                    'Fluxos Operacionais',
                    'SLA e Prazos',
                    'Checklist',
                    'Pendências e Alertas',
                    'Clientes',
                    'Atendimentos',
                    'Documentos',
                    'Contratos',
                    'Financeiro',
                    'Relatórios',
                    'Visão Geral Contábil',
                    'Auditoria e Riscos',
                    'Calendário Operacional',
                    'Visualizações da Operação',
                    'Timeline Operacional',
                    'Aprovações',
                    'Auditoria',
                ],
                'aliases' => [
                    'SLA' => 'Calendário Operacional',
                    'SLA e Prazos' => 'Calendário Operacional',
                    'Monitor de SLA e Prazos' => 'Calendário Operacional',
                    'Prazos' => 'Calendário Operacional',
                    'Portal Cliente' => 'Portal do Cliente',
                    'Central de Aprovações' => 'Aprovações',
                    'Aprovações' => 'Aprovações',
                    'Tarefas Operacionais' => 'Central Operacional',
                    'Kanban Operacional' => 'Visualizações da Operação',
                    'Cronograma Gantt' => 'Visualizações da Operação',
                ],
            ],
            'contador' => [
                'label' => 'Contador',
                'hint' => 'Execução contábil, documentos, clientes e prazos',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Visão Geral Contábil',
                    'Central Operacional',
                    'Fluxos Operacionais',
                    'SLA e Prazos',
                    'Checklist',
                    'Pendências e Alertas',
                    'Clientes',
                    'Atendimentos',
                    'Documentos',
                    'Cobranças',
                    'Relatórios',
                    'Calendário Operacional',
                    'Visualizações da Operação',
                    'Timeline Operacional',
                    'Aprovações',
                ],
                'aliases' => [
                    'SLA' => 'Calendário Operacional',
                    'SLA e Prazos' => 'Calendário Operacional',
                    'Monitor de SLA e Prazos' => 'Calendário Operacional',
                    'Prazos' => 'Calendário Operacional',
                    'Portal Cliente' => 'Portal do Cliente',
                    'Central de Aprovações' => 'Aprovações',
                    'Aprovações' => 'Aprovações',
                    'Tarefas Operacionais' => 'Central Operacional',
                    'Kanban Operacional' => 'Visualizações da Operação',
                    'Cronograma Gantt' => 'Visualizações da Operação',
                ],
            ],
            'fiscal' => [
                'label' => 'Fiscal',
                'hint' => 'Rotina fiscal, documentos, pendências e vencimentos',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Central Operacional',
                    'SLA e Prazos',
                    'Checklist',
                    'Pendências e Alertas',
                    'Clientes',
                    'Atendimentos',
                    'Documentos',
                    'Calendário Operacional',
                    'Visualizações da Operação',
                    'Timeline Operacional',
                    'Relatórios',
                ],
                'aliases' => [
                    'SLA' => 'Calendário Operacional',
                    'SLA e Prazos' => 'Calendário Operacional',
                    'Monitor de SLA e Prazos' => 'Calendário Operacional',
                    'Prazos' => 'Calendário Operacional',
                ],
            ],
            'departamento_pessoal' => [
                'label' => 'Departamento Pessoal',
                'hint' => 'Rotina de DP, documentos, checklists e prazos',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Central Operacional',
                    'SLA e Prazos',
                    'Checklist',
                    'Pendências e Alertas',
                    'Clientes',
                    'Atendimentos',
                    'Documentos',
                    'Calendário Operacional',
                    'Visualizações da Operação',
                    'Timeline Operacional',
                    'Relatórios',
                ],
                'aliases' => [
                    'DP' => 'Departamento Pessoal',
                    'SLA' => 'Calendário Operacional',
                    'SLA e Prazos' => 'Calendário Operacional',
                    'Monitor de SLA e Prazos' => 'Calendário Operacional',
                    'Prazos' => 'Calendário Operacional',
                ],
            ],
            'assistente' => [
                'label' => 'Assistente',
                'hint' => 'Atendimento, tarefas simples, documentos e checklist',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Central Operacional',
                    'Checklist',
                    'Pendências e Alertas',
                    'Clientes',
                    'Atendimentos',
                    'Documentos',
                    'Calendário Operacional',
                    'Visualizações da Operação',
                    'Timeline Operacional',
                ],
                'aliases' => [],
            ],
            'cliente' => [
                'label' => 'Cliente',
                'hint' => 'Portal, documentos, atendimentos e cobranças',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Atendimentos',
                    'Documentos',
                    'Cobranças',
                    'Contratos',
                ],
                'aliases' => [
                    'Portal Cliente' => 'Portal do Cliente',
                ],
            ],
        ];
    }

    public static function browserPayload(): array
    {
        return collect(self::profiles())
            ->map(fn (array $profile): array => [
                'label' => $profile['label'],
                'hint' => $profile['hint'],
                'visibleLabels' => $profile['visible_labels'],
                'aliases' => array_merge(self::globalAliases(), $profile['aliases'] ?? []),
                'visibleCount' => count($profile['visible_labels']),
            ])
            ->all();
    }

    private static function globalAliases(): array
    {
        return [
            'Relatar Bug / Melhoria' => 'Central de Evolução',
            'Dashboard' => 'Visão Geral Contábil',
            'Dashboard Executivo' => 'Visão Geral Contábil',
            'Dashboard Executivo Contábil' => 'Visão Geral Contábil',
            'Dashboards' => 'Visão Geral Contábil',
            'Painéis' => 'Visão Geral Contábil',
            'Dashboard Configurável' => 'Visão Geral Contábil',
            'Centro Operacional' => 'Central Operacional',
            'Operação Interna' => 'Central Operacional',
            'Gestão da Operação' => 'Central Operacional',
            'Tarefas' => 'Central Operacional',
            'Tarefas Operacionais' => 'Central Operacional',
            'Fluxos Operacionais' => 'Central Operacional',
            'Pendências' => 'Pendências e Alertas',
            'SLA' => 'Calendário Operacional',
            'SLA e Prazos' => 'Calendário Operacional',
            'Calendário' => 'Calendário Operacional',
            'Portal Cliente' => 'Atendimentos',
            'Portal do Cliente' => 'Atendimentos',
            'Armazenamento' => 'Documentos',
            'Validades' => 'Documentos',
            'Assinaturas' => 'Contratos',
            'Cobranças' => 'Financeiro',
            'Central de Aprovações' => 'Aprovações',
            'Timeline Operacional' => 'Visualizações da Operação',
            'Timeline' => 'Visualizações da Operação',
            'Kanban' => 'Visualizações da Operação',
            'Gantt Enterprise' => 'Visualizações da Operação',
            'Cronograma Gantt' => 'Visualizações da Operação',
            'Relatórios Exportáveis' => 'Relatórios',
            'Relatórios Personalizados' => 'Relatórios',
            'Riscos' => 'Auditoria e Riscos',
            'Auditoria' => 'Auditoria e Riscos',
            'Auditoria Administrativa' => 'Auditoria e Riscos',
            'Auditoria completa' => 'Auditoria e Riscos',
            'Auditoria Completa' => 'Auditoria e Riscos',
            'Compliance Interno' => 'Auditoria e Riscos',
            'Configurações e Administração' => 'Administração',
            'Central Administrativa' => 'Administração',
            'Usuários' => 'Administração',
            'Perfis e Permissões' => 'Administração',
            'Permissões' => 'Administração',
            'Empresa' => 'Administração',
            'Empresas' => 'Administração',
            'Equipes' => 'Administração',
            'Gestão de Planos' => 'Conta',
            'Assinatura' => 'Conta',
            'Onboarding' => 'Conta',
            'White Label' => 'Conta',
        ];
    }

    public static function currentProfileKey(?User $user): ?string
    {
        if (! $user || $user->isSuperAdmin()) {
            return null;
        }

        $perfil = $user->perfil_contabil ?: User::perfilContabilPadraoPorRole($user->role);

        return array_key_exists($perfil, self::profiles()) ? $perfil : null;
    }


    public static function allowedLabelsFor(?User $user): array
    {
        if (! $user || $user->isSuperAdmin()) {
            return [];
        }

        $profileKey = self::currentProfileKey($user);
        $profileLabels = $profileKey ? (self::profiles()[$profileKey]['visible_labels'] ?? []) : [];

        return array_values(array_unique(array_merge(
            $profileLabels,
            self::administrativeLabelsFor($user)
        )));
    }

    public static function canAccessLabel(?User $user, ?string $label): bool
    {
        if (! $user || blank($label)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $allowedLabels = self::allowedLabelsFor($user);

        foreach (self::labelVariants($label) as $variant) {
            if (in_array($variant, $allowedLabels, true)) {
                return true;
            }
        }

        return false;
    }

    private static function labelVariants(string $label): array
    {
        $aliases = array_merge(self::globalAliases(), [
            'Administração' => 'Administração',
            'Configurações' => 'Configurações',
            'Conta' => 'Conta',
        ]);

        return array_values(array_unique(array_filter([
            $label,
            $aliases[$label] ?? null,
        ])));
    }

    public static function administrativeLabelsFor(?User $user): array
    {
        if (! $user) {
            return [];
        }

        if ($user->isAdmin()) {
            return [
                'Administração',
                'Configurações',
                'Conta',
                'Central Administrativa',
                'Empresa',
                'Empresas',
                'Usuários',
                'Equipes',
                'Perfis e Permissões',
                'Permissões',
                'Assinatura',
                'Gestão de Planos',
                'Onboarding',
                'White Label',
                'Central de Evolução',
                'Auditoria e Riscos',
                'Auditoria',
                'Auditoria Administrativa',
            ];
        }

        return [];
    }
}
