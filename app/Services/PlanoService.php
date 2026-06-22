<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Arr;

class PlanoService
{
    public const STARTER = 'starter';
    public const PROFISSIONAL = 'profissional';
    public const PROFESSIONAL = 'professional';
    public const BUSINESS = 'business';
    public const BUSINESS_PLUS = 'business_plus';
    public const ENTERPRISE = 'enterprise';

    public const FEATURE_TAREFAS = 'tarefas';
    public const FEATURE_KANBAN = 'kanban';
    public const FEATURE_CALENDARIO = 'calendario';
    public const FEATURE_CATEGORIAS_TAGS = 'categorias_tags';
    public const FEATURE_CHECKLIST = 'checklist';
    public const FEATURE_NOTIFICACOES_BASICAS = 'notificacoes_basicas';
    public const FEATURE_ASSISTENTE_VIRTUAL = 'assistente_virtual';
    public const FEATURE_APROVACOES = 'aprovacoes';
    public const FEATURE_ASSINATURAS_DOCUMENTOS = 'assinaturas_documentos';
    public const FEATURE_COMENTARIOS = 'comentarios';
    public const FEATURE_TIMELINE = 'timeline';
    public const FEATURE_MULTIPLOS_ANEXOS = 'multiplos_anexos';
    public const FEATURE_PORTAL_CLIENTE = 'portal_cliente';
    public const FEATURE_DASHBOARD_OPERACIONAL = 'dashboard_operacional';
    public const FEATURE_SLA = 'sla';
    public const FEATURE_ALERTAS_MANUAIS = 'alertas_manuais';
    public const FEATURE_CONTRATOS = 'contratos';
    public const FEATURE_FINANCEIRO_COBRANCAS = 'financeiro_cobrancas';
    public const FEATURE_ONBOARDING = 'onboarding';
    public const FEATURE_PROJETOS = 'projetos';
    public const FEATURE_RISCOS = 'riscos';
    public const FEATURE_RELATORIOS_INTERNOS = 'relatorios_internos';
    public const FEATURE_EXPORTACOES = 'exportacoes';
    public const FEATURE_AUDITORIA = 'auditoria';
    public const FEATURE_METRICAS_PRODUTIVIDADE = 'metricas_produtividade';
    public const FEATURE_BI_AVANCADO = 'bi_avancado';
    public const FEATURE_BUSCA_GLOBAL = 'busca_global';
    public const FEATURE_PORTAL_AVANCADO = 'portal_avancado';
    public const FEATURE_WHITE_LABEL = 'white_label';
    public const FEATURE_GESTAO_PLANOS = 'gestao_planos';
    public const FEATURE_FLUXOS_OPERACIONAIS = 'fluxos_operacionais';
    public const FEATURE_RELATORIOS_PERSONALIZADOS = 'relatorios_personalizados';
    public const FEATURE_DASHBOARDS_PERSONALIZADOS = 'dashboards_personalizados';
    public const FEATURE_IA_DOCUMENTOS = 'ia_documentos';

    public static function planos(): array
    {
        return [
            self::STARTER => [
                'nome' => 'Starter',
                'nome_comercial' => 'Plano Starter',
                'preco' => 'R$ 0/mês',
                'valor_mensal' => 0.00,
                'tag' => 'Comece grátis',
                'destaque' => false,
                'descricao' => 'Plano de entrada para escritórios que precisam controlar usuários, documentos e rotina básica sem excesso de módulos.',
                'limite_usuarios' => 3,
                'limite_itens' => 200,
                'limite_armazenamento_mb' => 6144,
                'limite_interacoes_ia' => 150,
                'limite_anexos' => 100,
                'limite_assinaturas' => 0,
                'features' => [
                    self::FEATURE_TAREFAS,
                    self::FEATURE_CHECKLIST,
                    self::FEATURE_COMENTARIOS,
                    self::FEATURE_TIMELINE,
                    self::FEATURE_NOTIFICACOES_BASICAS,
                ],
                'itens' => [
                    '1 usuário para iniciar a organização',
                    '20 itens ou solicitações por mês',
                    '20 anexos centralizados',
                    'Checklist básico por item',
                    'Comentários internos da equipe',
                    'Timeline básica de acompanhamento',
                    'Notificações essenciais',
                    'Sem IA e sem Clicksign no gratuito',
                ],
                'servicos' => [
                    'Itens de controle',
                    'Checklist',
                    'Comentários internos',
                    'Timeline básica',
                    'Anexos essenciais',
                ],
                'nao_incluso' => [
                    'IA',
                    'Clicksign',
                    'Auditoria avançada',
                    'White label',
                ],
                'configuraveis' => [],
            ],

            self::PROFISSIONAL => [
                'nome' => 'Professional',
                'nome_comercial' => 'Plano Professional',
                'preco' => 'R$ 39/mês',
                'valor_mensal' => 39.00,
                'tag' => 'Mais escolhido',
                'destaque' => true,
                'descricao' => 'Para pequenas contabilidades saírem do WhatsApp e das planilhas, centralizando solicitações, documentos e pendências.',
                'limite_usuarios' => 3,
                'limite_itens' => 200,
                'limite_interacoes_ia' => 2000,
                'limite_armazenamento_mb' => 15360,
                'limite_anexos' => 100,
                'limite_assinaturas' => 0,
                'features' => [
                    self::FEATURE_TAREFAS,
                    self::FEATURE_KANBAN,
                    self::FEATURE_CALENDARIO,
                    self::FEATURE_CATEGORIAS_TAGS,
                    self::FEATURE_CHECKLIST,
                    self::FEATURE_NOTIFICACOES_BASICAS,
                    self::FEATURE_COMENTARIOS,
                    self::FEATURE_TIMELINE,
                    self::FEATURE_MULTIPLOS_ANEXOS,
                    self::FEATURE_DASHBOARD_OPERACIONAL,
                    self::FEATURE_BUSCA_GLOBAL,
                ],
                'itens' => [
                    'Até 3 usuários',
                    '200 itens, tarefas ou solicitações por mês',
                    '100 anexos centralizados',
                    'Dashboard operacional',
                    'Kanban e calendário de prazos',
                    'Checklist, comentários e timeline completa',
                    'Categorias, tags, filtros e busca global',
                    'Notificações e controle de pendências',
                    'Sem IA e sem Clicksign inclusos',
                ],
                'servicos' => [
                    'Solicitações e itens de controle',
                    'Kanban operacional',
                    'Calendário de prazos',
                    'Categorias e tags',
                    'Checklist por item',
                    'Comentários internos',
                    'Timeline completa',
                    'Anexos múltiplos',
                    'Dashboard operacional',
                    'Busca global',
                    'Notificações essenciais',
                ],
                'configuraveis' => [
                    'usuarios' => [
                        ['label' => '3 usuários inclusos', 'valor' => 0],
                        ['label' => '5 usuários', 'valor' => 19],
                        ['label' => '10 usuários', 'valor' => 39],
                    ],
                    'itens' => [
                        ['label' => '200 itens/mês inclusos', 'valor' => 0],
                        ['label' => '500 itens/mês', 'valor' => 19],
                        ['label' => '1.000 itens/mês', 'valor' => 39],
                    ],
                    'anexos' => [
                        ['label' => '100 anexos inclusos', 'valor' => 0],
                        ['label' => '500 anexos', 'valor' => 19],
                        ['label' => '1.000 anexos', 'valor' => 39],
                    ],
                ],
            ],

            self::BUSINESS => [
                'nome' => 'Pro',
                'nome_comercial' => 'Plano Pro',
                'preco' => 'R$ 89/mês',
                'valor_mensal' => 89.00,
                'tag' => 'Para crescer',
                'destaque' => false,
                'descricao' => 'Para escritórios em crescimento que precisam de controle, aprovações, portal do cliente, relatórios e automações.',
                'limite_usuarios' => 10,
                'limite_itens' => 1000,
                'limite_interacoes_ia' => 2000,
                'limite_armazenamento_mb' => 10240,
                'limite_anexos' => 500,
                'limite_assinaturas' => 0,
                'features' => [
                    self::FEATURE_TAREFAS,
                    self::FEATURE_KANBAN,
                    self::FEATURE_CALENDARIO,
                    self::FEATURE_CATEGORIAS_TAGS,
                    self::FEATURE_CHECKLIST,
                    self::FEATURE_NOTIFICACOES_BASICAS,
                    self::FEATURE_APROVACOES,
                    self::FEATURE_COMENTARIOS,
                    self::FEATURE_TIMELINE,
                    self::FEATURE_MULTIPLOS_ANEXOS,
                    self::FEATURE_PORTAL_CLIENTE,
                    self::FEATURE_DASHBOARD_OPERACIONAL,
                    self::FEATURE_SLA,
                    self::FEATURE_ALERTAS_MANUAIS,
                    self::FEATURE_CONTRATOS,
                    self::FEATURE_FINANCEIRO_COBRANCAS,
                    self::FEATURE_ONBOARDING,
                    self::FEATURE_PROJETOS,
                    self::FEATURE_RISCOS,
                    self::FEATURE_RELATORIOS_INTERNOS,
                    self::FEATURE_EXPORTACOES,
                    self::FEATURE_BUSCA_GLOBAL,
                ],
                'itens' => [
                    'Tudo do Starter',
                    'Até 10 usuários',
                    '1.000 itens, tarefas ou solicitações por mês',
                    '500 anexos centralizados',
                    'Portal do cliente para envio e acompanhamento',
                    'Aprovações internas com histórico',
                    'Contratos, documentos e anexos organizados',
                    'SLA, alertas e cobranças operacionais',
                    'Projetos, riscos e onboarding',
                    'Relatórios internos e exportações',
                    'IA e Clicksign opcionais somente em planos pagos',
                ],
                'servicos' => [
                    'Todos os serviços do Starter',
                    'Portal do cliente',
                    'Aprovações internas',
                    'Controle de contratos',
                    'Documentos e anexos avançados',
                    'SLA operacional',
                    'Alertas manuais',
                    'Financeiro e cobranças',
                    'Onboarding de clientes',
                    'Projetos e riscos',
                    'Relatórios internos',
                    'Exportações',
                ],
                'configuraveis' => [
                    'assinaturas' => [
                        ['label' => 'Sem Clicksign incluso', 'valor' => 0],
                        ['label' => '10 assinaturas/mês', 'valor' => 29],
                        ['label' => '50 assinaturas/mês', 'valor' => 79],
                        ['label' => '200 assinaturas/mês', 'valor' => 199],
                    ],
                    'ia' => [
                        ['label' => 'Sem IA inclusa', 'valor' => 0],
                        ['label' => '100 interações IA/mês', 'valor' => 19],
                        ['label' => '500 interações IA/mês', 'valor' => 49],
                        ['label' => '2.000 interações IA/mês', 'valor' => 129],
                    ],
                    'usuarios' => [
                        ['label' => '10 usuários inclusos', 'valor' => 0],
                        ['label' => '20 usuários', 'valor' => 39],
                        ['label' => '50 usuários', 'valor' => 99],
                    ],
                ],
            ],

            self::BUSINESS_PLUS => [
                'nome' => 'Business',
                'nome_comercial' => 'Plano Business',
                'preco' => 'R$ 179/mês',
                'valor_mensal' => 179.00,
                'tag' => 'Operação avançada',
                'destaque' => false,
                'descricao' => 'Para contabilidades maiores que precisam de auditoria, BI, portal avançado, indicadores e alto volume operacional.',
                'limite_usuarios' => 30,
                'limite_itens' => 5000,
                'limite_interacoes_ia' => 5000,
                'limite_armazenamento_mb' => 20480,
                'limite_anexos' => 2000,
                'limite_assinaturas' => 0,
                'features' => [
                    self::FEATURE_TAREFAS,
                    self::FEATURE_KANBAN,
                    self::FEATURE_CALENDARIO,
                    self::FEATURE_CATEGORIAS_TAGS,
                    self::FEATURE_CHECKLIST,
                    self::FEATURE_NOTIFICACOES_BASICAS,
                    self::FEATURE_APROVACOES,
                    self::FEATURE_ASSINATURAS_DOCUMENTOS,
                    self::FEATURE_COMENTARIOS,
                    self::FEATURE_TIMELINE,
                    self::FEATURE_MULTIPLOS_ANEXOS,
                    self::FEATURE_PORTAL_CLIENTE,
                    self::FEATURE_DASHBOARD_OPERACIONAL,
                    self::FEATURE_SLA,
                    self::FEATURE_ALERTAS_MANUAIS,
                    self::FEATURE_CONTRATOS,
                    self::FEATURE_FINANCEIRO_COBRANCAS,
                    self::FEATURE_ONBOARDING,
                    self::FEATURE_PROJETOS,
                    self::FEATURE_RISCOS,
                    self::FEATURE_RELATORIOS_INTERNOS,
                    self::FEATURE_EXPORTACOES,
                    self::FEATURE_AUDITORIA,
                    self::FEATURE_METRICAS_PRODUTIVIDADE,
                    self::FEATURE_BI_AVANCADO,
                    self::FEATURE_BUSCA_GLOBAL,
                    self::FEATURE_PORTAL_AVANCADO,
                    self::FEATURE_FLUXOS_OPERACIONAIS,
                    self::FEATURE_RELATORIOS_PERSONALIZADOS,
                    self::FEATURE_DASHBOARDS_PERSONALIZADOS,
                ],
                'itens' => [
                    'Tudo do Pro',
                    'Até 30 usuários',
                    '5.000 itens, tarefas ou solicitações por mês',
                    '2.000 anexos centralizados',
                    'Auditoria completa de alterações',
                    'Métricas de produtividade e BI operacional',
                    'Portal avançado para clientes',
                    'Fluxos operacionais avançados',
                    'Relatórios e dashboards personalizados',
                    'Clicksign e IA como adicionais pagos',
                    'Opção sob consulta para volumes maiores',
                ],
                'servicos' => [
                    'Todos os serviços do Pro',
                    'Auditoria completa',
                    'Métricas de produtividade',
                    'BI operacional avançado',
                    'Portal avançado',
                    'Fluxos operacionais',
                    'Relatórios personalizados',
                    'Dashboards personalizados',
                    'Alto volume de itens e anexos',
                ],
                'configuraveis' => [
                    'volume' => [
                        ['label' => '5.000 itens/mês inclusos', 'valor' => 0],
                        ['label' => '10.000 itens/mês', 'valor' => 99],
                        ['label' => '25.000 itens/mês', 'valor' => 249],
                        ['label' => 'Sob consulta', 'valor' => null],
                    ],
                    'assinaturas' => [
                        ['label' => 'Sem Clicksign incluso', 'valor' => 0],
                        ['label' => '100 assinaturas/mês', 'valor' => 249],
                        ['label' => '500 assinaturas/mês', 'valor' => 899],
                        ['label' => 'Sob consulta', 'valor' => null],
                    ],
                    'ia' => [
                        ['label' => 'Sem IA inclusa', 'valor' => 0],
                        ['label' => '1.000 interações IA/mês', 'valor' => 89],
                        ['label' => '5.000 interações IA/mês', 'valor' => 299],
                        ['label' => 'Sob consulta', 'valor' => null],
                    ],
                ],
            ],

            self::ENTERPRISE => [
                'nome' => 'Enterprise',
                'nome_comercial' => 'Plano Enterprise',
                'preco' => 'Sob consulta',
                'valor_mensal' => 0.00,
                'tag' => 'Sob medida',
                'destaque' => false,
                'descricao' => 'Para operações com necessidade de white label, implantação assistida, volumes especiais e personalização comercial.',
                'limite_usuarios' => 999999,
                'limite_itens' => 999999,
                'limite_interacoes_ia' => 15000,
                'limite_armazenamento_mb' => 40960,
                'limite_anexos' => 999999,
                'limite_assinaturas' => 0,
                'features' => [
                    self::FEATURE_TAREFAS,
                    self::FEATURE_KANBAN,
                    self::FEATURE_CALENDARIO,
                    self::FEATURE_CATEGORIAS_TAGS,
                    self::FEATURE_CHECKLIST,
                    self::FEATURE_NOTIFICACOES_BASICAS,
                    self::FEATURE_APROVACOES,
                    self::FEATURE_ASSINATURAS_DOCUMENTOS,
                    self::FEATURE_COMENTARIOS,
                    self::FEATURE_TIMELINE,
                    self::FEATURE_MULTIPLOS_ANEXOS,
                    self::FEATURE_PORTAL_CLIENTE,
                    self::FEATURE_DASHBOARD_OPERACIONAL,
                    self::FEATURE_SLA,
                    self::FEATURE_ALERTAS_MANUAIS,
                    self::FEATURE_CONTRATOS,
                    self::FEATURE_FINANCEIRO_COBRANCAS,
                    self::FEATURE_ONBOARDING,
                    self::FEATURE_PROJETOS,
                    self::FEATURE_RISCOS,
                    self::FEATURE_RELATORIOS_INTERNOS,
                    self::FEATURE_EXPORTACOES,
                    self::FEATURE_AUDITORIA,
                    self::FEATURE_METRICAS_PRODUTIVIDADE,
                    self::FEATURE_BI_AVANCADO,
                    self::FEATURE_BUSCA_GLOBAL,
                    self::FEATURE_PORTAL_AVANCADO,
                    self::FEATURE_WHITE_LABEL,
                    self::FEATURE_GESTAO_PLANOS,
                    self::FEATURE_FLUXOS_OPERACIONAIS,
                    self::FEATURE_RELATORIOS_PERSONALIZADOS,
                    self::FEATURE_DASHBOARDS_PERSONALIZADOS,
                    self::FEATURE_IA_DOCUMENTOS,
                ],
                'itens' => [
                    'Tudo do Business',
                    'Usuários, itens e anexos sob demanda',
                    'White label e identidade da empresa',
                    'Fluxos operacionais personalizados',
                    'Relatórios e dashboards sob medida',
                    'IA documental contratada sob demanda',
                    'Clicksign contratado sob demanda',
                    'Implantação assistida',
                    'Suporte prioritário',
                    'Condição comercial personalizada',
                ],
                'servicos' => [
                    'White label',
                    'Fluxos personalizados',
                    'Relatórios personalizados',
                    'Dashboards personalizados',
                    'IA documental sob demanda',
                    'Clicksign sob demanda',
                    'Implantação assistida',
                    'Suporte prioritário',
                ],
                'configuraveis' => [
                    'usuarios' => [
                        ['label' => 'Sob consulta', 'valor' => null],
                    ],
                    'volume' => [
                        ['label' => 'Sob consulta', 'valor' => null],
                    ],
                    'assinaturas' => [
                        ['label' => 'Sob consulta', 'valor' => null],
                    ],
                    'ia' => [
                        ['label' => 'Sob consulta', 'valor' => null],
                    ],
                ],
            ],
        ];
    }

    public static function grupoNavegacaoPorFeature(string $feature): string
    {
        return match ($feature) {
            self::FEATURE_AUDITORIA,
            self::FEATURE_METRICAS_PRODUTIVIDADE,
            self::FEATURE_BI_AVANCADO => 'COMPLIANCE',

            self::FEATURE_PORTAL_CLIENTE,
            self::FEATURE_PORTAL_AVANCADO => 'CLIENTES',

            self::FEATURE_CONTRATOS,
            self::FEATURE_MULTIPLOS_ANEXOS,
            self::FEATURE_APROVACOES,
            self::FEATURE_ASSINATURAS_DOCUMENTOS,
            self::FEATURE_IA_DOCUMENTOS => 'DOCUMENTOS',

            self::FEATURE_RELATORIOS_INTERNOS,
            self::FEATURE_RELATORIOS_PERSONALIZADOS,
            self::FEATURE_DASHBOARDS_PERSONALIZADOS,
            self::FEATURE_EXPORTACOES => 'RELATÓRIOS',

            self::FEATURE_FINANCEIRO_COBRANCAS => 'FINANCEIRO',

            self::FEATURE_SLA,
            self::FEATURE_CHECKLIST,
            self::FEATURE_KANBAN,
            self::FEATURE_CALENDARIO,
            self::FEATURE_TAREFAS,
            self::FEATURE_FLUXOS_OPERACIONAIS => 'TRABALHO',

            default => 'TRABALHO',
        };
    }

    public static function options(): array
    {
        return collect(self::planos())
            ->mapWithKeys(fn (array $plano, string $key): array => [$key => $plano['nome']])
            ->toArray();
    }

    public static function normalizarPlano(?string $plano): string
    {
        return match ($plano) {
            'free', 'gratuito', 'gratis', 'starter', 'essencial', 'basico', 'basic' => self::STARTER,
            'pro', 'professional', 'profissional' => self::PROFISSIONAL,
            'empresarial', 'premium', 'business' => self::BUSINESS,
            'business_plus', 'scale', 'growth' => self::BUSINESS_PLUS,
            'enterprise', 'enterprise_plus' => self::ENTERPRISE,
            default => self::STARTER,
        };
    }

    public static function dados(?string $plano): array
    {
        $codigo = self::normalizarPlano($plano);

        return self::planos()[$codigo];
    }

    public static function nome(?string $plano): string
    {
        return self::dados($plano)['nome'];
    }

    public static function nomeComercial(?string $plano): string
    {
        return self::dados($plano)['nome_comercial'] ?? self::nome($plano);
    }

    public static function limiteUsuarios(?string $plano): int
    {
        return (int) self::dados($plano)['limite_usuarios'];
    }

    public static function limiteItens(?string $plano): int
    {
        return (int) self::dados($plano)['limite_itens'];
    }

    public static function limiteArmazenamentoMb(?string $plano): int
    {
        return (int) (self::dados($plano)['limite_armazenamento_mb'] ?? 0);
    }

    public static function limiteInteracoesIa(?string $plano): int
    {
        return (int) self::dados($plano)['limite_interacoes_ia'];
    }

    public static function valorMensal(?string $plano): float
    {
        return (float) (self::dados($plano)['valor_mensal'] ?? 0);
    }

    public static function preco(?string $plano): string
    {
        return (string) (self::dados($plano)['preco'] ?? 'Sob consulta');
    }

    public static function descricaoAssinatura(?string $plano): string
    {
        return sprintf('Assinatura Prazzu - %s', self::nomeComercial($plano));
    }

    public static function features(?string $plano): array
    {
        return Arr::get(self::dados($plano), 'features', []);
    }

    public static function itensComerciais(?string $plano): array
    {
        return Arr::get(self::dados($plano), 'itens', []);
    }

    public static function planoPossuiFeature(?string $plano, string $feature): bool
    {
        return in_array($feature, self::features($plano), true);
    }

    public static function empresaPossuiFeature(?Empresa $empresa, string $feature): bool
    {
        if (! $empresa || ! $empresa->isAtivo()) {
            return false;
        }

        return self::planoPossuiFeature($empresa->plano, $feature);
    }

    public static function usuarioPossuiFeature(?User $user, string $feature): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return self::empresaPossuiFeature($user->empresa, $feature);
    }
}
