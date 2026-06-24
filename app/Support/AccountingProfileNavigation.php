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
                    'Dashboard Executivo',
                    'Financeiro',
                    'Cobranças',
                    'Relatórios',
                    'Relatórios Exportáveis',
                    'Clientes',
                    'Contratos',
                    'Riscos',
                    'Central de Aprovações',
                    'Auditoria',
                    'Calendário',
                    'Usuários',
                    'Configurações',
                    'Perfis e Permissões',
                ],
                'aliases' => [
                    'Dashboard' => 'Dashboard Executivo',
                    'Aprovações' => 'Central de Aprovações',
                ],
            ],
            'gestor' => [
                'label' => 'Gestor',
                'hint' => 'Gestão da operação, equipe, prazos e aprovações',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Centro Operacional',
                    'Tarefas',
                    'Fluxos Operacionais',
                    'SLA e Prazos',
                    'Checklist',
                    'Pendências',
                    'Clientes',
                    'Portal do Cliente',
                    'Atendimentos',
                    'Documentos',
                    'Armazenamento',
                    'Contratos',
                    'Validades',
                    'Cobranças',
                    'Financeiro',
                    'Relatórios',
                    'Dashboard Executivo',
                    'Riscos',
                    'Calendário',
                    'Timeline Operacional',
                    'Central de Aprovações',
                    'Auditoria',
                ],
                'aliases' => [
                    'SLA' => 'SLA e Prazos',
                    'Prazos' => 'SLA e Prazos',
                    'Portal Cliente' => 'Portal do Cliente',
                    'Aprovações' => 'Central de Aprovações',
                ],
            ],
            'contador' => [
                'label' => 'Contador',
                'hint' => 'Execução contábil, documentos, clientes e prazos',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Centro Operacional',
                    'Tarefas',
                    'Fluxos Operacionais',
                    'SLA e Prazos',
                    'Checklist',
                    'Pendências',
                    'Clientes',
                    'Portal do Cliente',
                    'Atendimentos',
                    'Documentos',
                    'Armazenamento',
                    'Validades',
                    'Cobranças',
                    'Relatórios',
                    'Calendário',
                    'Timeline Operacional',
                    'Central de Aprovações',
                ],
                'aliases' => [
                    'SLA' => 'SLA e Prazos',
                    'Prazos' => 'SLA e Prazos',
                    'Portal Cliente' => 'Portal do Cliente',
                    'Aprovações' => 'Central de Aprovações',
                ],
            ],
            'fiscal' => [
                'label' => 'Fiscal',
                'hint' => 'Rotina fiscal, documentos, pendências e vencimentos',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Centro Operacional',
                    'Tarefas',
                    'SLA e Prazos',
                    'Checklist',
                    'Pendências',
                    'Clientes',
                    'Atendimentos',
                    'Documentos',
                    'Armazenamento',
                    'Validades',
                    'Calendário',
                    'Timeline Operacional',
                    'Relatórios',
                ],
                'aliases' => [
                    'SLA' => 'SLA e Prazos',
                    'Prazos' => 'SLA e Prazos',
                ],
            ],
            'departamento_pessoal' => [
                'label' => 'Departamento Pessoal',
                'hint' => 'Rotina de DP, documentos, checklists e prazos',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Centro Operacional',
                    'Tarefas',
                    'SLA e Prazos',
                    'Checklist',
                    'Pendências',
                    'Clientes',
                    'Atendimentos',
                    'Documentos',
                    'Armazenamento',
                    'Validades',
                    'Calendário',
                    'Timeline Operacional',
                    'Relatórios',
                ],
                'aliases' => [
                    'DP' => 'Departamento Pessoal',
                    'SLA' => 'SLA e Prazos',
                    'Prazos' => 'SLA e Prazos',
                ],
            ],
            'assistente' => [
                'label' => 'Assistente',
                'hint' => 'Atendimento, tarefas simples, documentos e checklist',
                'visible_labels' => [
                    'Home',
                    'Central de Evolução',
                    'Centro Operacional',
                    'Tarefas',
                    'Checklist',
                    'Pendências',
                    'Clientes',
                    'Atendimentos',
                    'Documentos',
                    'Calendário',
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
                    'Portal do Cliente',
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
            'Perfis e Permissões' => 'Permissões',
            'Permissões' => 'Perfis e Permissões',
            'Relatar Bug / Melhoria' => 'Central de Evolução',
            'Central de Evolução' => 'Central de Evolução',
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
        $aliases = [
            'Permissões' => 'Perfis e Permissões',
            'Relatar Bug / Melhoria' => 'Central de Evolução',
            'Central de Evolução' => 'Central de Evolução',
            'Perfis e Permissões' => 'Permissões',
        ];

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
                'Central Administrativa',
                'Empresa',
                'Usuários',
                'Equipes',
                'Central de Evolução',
                'Configurações',
                'Empresas',
                'Perfis e Permissões',
                'Permissões',
                'Armazenamento',
                'Assinatura',
                'Gestão de Planos',
                'Auditoria',
                'Auditoria Administrativa',
            ];
        }

        return [];
    }
}
