<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PrazzuEnterpriseModuleData
{
    private const DONE_STATUSES = ['concluido', 'concluida', 'concluído', 'finalizado', 'finalizada', 'cancelado', 'cancelada'];

    public static function for(string $module): array
    {
        $config = self::config($module);

        return [
            'module' => $module,
            'config' => $config,
            'stats' => self::stats($module),
            'features' => self::features($module),
            'onboarding' => self::onboarding($module),
            'quickActions' => self::quickActions($module),
            'globalSearch' => self::globalSearch($module),
            'kanban' => self::kanban($module),
            'items' => self::items($module, 18),
            'calendar' => self::calendar($module),
            'gantt' => self::gantt($module),
            'timeline' => self::timeline($module),
            'dependencies' => self::dependencies($module),
            'approvals' => self::approvals($module),
            'documents' => self::documents($module),
            'documentWorkflow' => self::documentWorkflow($module),
            'comments' => self::comments($module),
            'notifications' => self::notifications($module),
            'automations' => self::automations($module),
            'automationBuilder' => self::automationBuilder($module),
            'compliance' => self::compliance($module),
            'billing' => self::billing($module),
            'kpis' => self::kpis($module),
            'reports' => self::reports($module),
            'timeTracking' => self::timeTracking($module),
            'whiteLabel' => self::whiteLabel(),
            'permissions' => self::permissions($module),
            'userManagement' => self::userManagement($module),
            'advancedPermissions' => self::advancedPermissions($module),
            'clientCrm' => self::clientCrm(),
            'portalExperience' => self::portalExperience(),
            'searchPlaceholder' => $config['searchPlaceholder'] ?? 'Buscar em clientes, tarefas, documentos, contratos, cobranças e responsáveis...',
        ];
    }

    public static function config(string $module): array
    {
        $configs = [
            'clientes' => ['group' => 'CLIENTES', 'title' => 'Clientes', 'subtitle' => 'Gestão interna/CRM com status de contrato, LTV, decisor, última reunião, saúde do cliente e onboarding.', 'primary' => 'Carteira ativa', 'secondary' => 'Clientes em risco', 'searchPlaceholder' => 'Buscar cliente, CNPJ, decisor, status, contrato, LTV ou última reunião...'],
            'portal-cliente' => ['group' => 'CLIENTES', 'title' => 'Portal do Cliente', 'subtitle' => 'Experiência do cliente com progresso do projeto, revisões, documentos, atas, calendário, suporte e chat.', 'primary' => 'Portais ativos', 'secondary' => 'Pendências do cliente'],
            'atendimentos' => ['group' => 'CLIENTES', 'title' => 'Atendimentos', 'subtitle' => 'Mesa operacional com conversas, comentários, SLA, subtarefas, responsáveis e evolução por cliente.', 'primary' => 'Atendimentos abertos', 'secondary' => 'SLA em risco'],
            'auditoria' => ['group' => 'COMPLIANCE', 'title' => 'Auditoria', 'subtitle' => 'Timeline completa de evidências, alterações, responsáveis, aprovações e rastreabilidade.', 'primary' => 'Eventos auditados', 'secondary' => 'Evidências pendentes'],
            'riscos' => ['group' => 'COMPLIANCE', 'title' => 'Riscos', 'subtitle' => 'Matriz de risco operacional com criticidade, probabilidade, impacto, plano de ação e SLA.', 'primary' => 'Riscos críticos', 'secondary' => 'Planos atrasados'],
            'pendencias' => ['group' => 'COMPLIANCE', 'title' => 'Pendências', 'subtitle' => 'Backlog priorizado com dependências, subtarefas, vencimentos, bloqueios e responsáveis.', 'primary' => 'Pendências abertas', 'secondary' => 'Atrasadas'],
            'cobrancas' => ['group' => 'FINANCEIRO', 'title' => 'Cobranças Inteligentes', 'subtitle' => 'Régua interna de cobrança, inadimplência, recuperação, bloqueio e histórico financeiro.', 'primary' => 'Valor em aberto', 'secondary' => 'Clientes inadimplentes'],
            'assinaturas' => ['group' => 'FINANCEIRO', 'title' => 'Assinaturas', 'subtitle' => 'Gestão recorrente de planos, vencimentos, contratos, bloqueios e renovações.', 'primary' => 'Assinaturas ativas', 'secondary' => 'Renovações próximas'],
            'financeiro' => ['group' => 'FINANCEIRO', 'title' => 'Financeiro', 'subtitle' => 'KPIs financeiros, receita prevista, inadimplência, cobranças vencidas e recuperação.', 'primary' => 'Receita prevista', 'secondary' => 'Vencidos'],
            'relatorios' => ['group' => 'RELATÓRIOS', 'title' => 'Relatórios Gerenciais', 'subtitle' => 'Relatórios executivos com KPIs, SLA, operação, documentos, produtividade e cobrança.', 'primary' => 'Relatórios ativos', 'secondary' => 'KPIs monitorados'],
            'dashboards' => ['group' => 'RELATÓRIOS', 'title' => 'Dashboards', 'subtitle' => 'Dashboard inteligente com operação, compliance, financeiro, SLA, documentos e produtividade.', 'primary' => 'Widgets ativos', 'secondary' => 'Alertas críticos'],
            'dashboard-configuravel' => ['group' => 'RELATÓRIOS', 'title' => 'Dashboard Configurável', 'subtitle' => 'Monte painéis por perfil, módulo, cliente, indicador, período e responsável.', 'primary' => 'Widgets configurados', 'secondary' => 'Visões salvas'],
            'configuracoes' => ['group' => 'CONFIGURAÇÕES', 'title' => 'Configurações', 'subtitle' => 'Parâmetros da plataforma, white label, SLA padrão, automações e preferências operacionais.', 'primary' => 'Parâmetros ativos', 'secondary' => 'Automações ativas'],
            'usuarios' => ['group' => 'CONFIGURAÇÕES', 'title' => 'Usuários', 'subtitle' => 'Gestão de equipe com papéis, responsabilidades, carga operacional e acesso por módulo.', 'primary' => 'Usuários ativos', 'secondary' => 'Responsáveis operacionais'],
            'permissoes' => ['group' => 'CONFIGURAÇÕES', 'title' => 'Permissões Avançadas', 'subtitle' => 'ACL por módulo, ação, perfil, cliente, documento, financeiro e portal.', 'primary' => 'Perfis configurados', 'secondary' => 'Módulos protegidos'],
            'gantt' => ['group' => 'TRABALHO', 'title' => 'Gantt Enterprise', 'subtitle' => 'Planejamento visual por prazo, progresso, dependências, bloqueios e responsável.', 'primary' => 'Itens planejados', 'secondary' => 'Bloqueios'],
            'timeline-operacional' => ['group' => 'TRABALHO', 'title' => 'Timeline Operacional', 'subtitle' => 'Linha do tempo consolidada com auditoria, comentários, documentos, aprovações e SLA.', 'primary' => 'Eventos', 'secondary' => 'Evidências'],
            'automacoes' => ['group' => 'AUTOMAÇÃO', 'title' => 'Automações Internas', 'subtitle' => 'Builder visual SE/ENTÃO para regras condicionais sem integrações externas.', 'primary' => 'Regras ativas', 'secondary' => 'Ações internas'],
            'white-label' => ['group' => 'CONFIGURAÇÕES', 'title' => 'White Label', 'subtitle' => 'Central de marca, aparência, plano, limites e identidade por empresa.', 'primary' => 'Empresas', 'secondary' => 'Branding'],
            'onboarding' => ['group' => 'CONFIGURAÇÕES', 'title' => 'Onboarding', 'subtitle' => 'Primeiros passos guiados para cliente, projeto, tarefa, documento, SLA e portal.', 'primary' => 'Etapas', 'secondary' => 'Pronto para uso'],
        ];

        return $configs[$module] ?? $configs['dashboards'];
    }

    public static function stats(string $module): array
    {
        $totalItems = self::tableCount('item_controles');
        $open = self::itemQuery()->whereNotIn('status', self::DONE_STATUSES)->count();
        $late = self::lateItemsQuery()->count();
        $slaRisk = self::hasColumn('item_controles', 'sla_limite_em') ? self::itemQuery()->whereNotNull('sla_limite_em')->whereNull('sla_concluido_em')->where('sla_limite_em', '<=', now()->addHours(8))->count() : 0;
        $docs = self::tableCount('item_controle_anexos') + self::itemQuery()->whereNotNull('arquivo')->count();
        $comments = self::tableCount('item_controle_comentarios') + self::tableCount('comentarios') + self::tableCount('prazzu_task_comments');
        $companies = self::tableCount('empresas');
        $paymentsOpen = self::hasTable('pagamentos') ? DB::table('pagamentos')->whereIn('status', ['PENDING', 'CREATED', 'PAYMENT_CREATED', 'OVERDUE', 'PAYMENT_OVERDUE'])->count() : 0;
        $paymentsLateValue = self::hasTable('pagamentos') ? (float) DB::table('pagamentos')->where(function ($q) { $q->whereIn('status', ['OVERDUE', 'PAYMENT_OVERDUE'])->orWhereDate('vencimento', '<', now()->toDateString()); })->sum('valor') : 0;

        $map = [
            'clientes' => [[$companies, 'Clientes cadastrados', 'Carteira operacional'], [$open, 'Itens abertos', 'Demandas por cliente'], [$docs, 'Documentos', 'Base documental'], [$late, 'Atrasos', 'Ação imediata']],
            'portal-cliente' => [[self::safeItemCount('portal_ativo', 1), 'Portais ativos', 'Acesso externo'], [self::tableCount('item_controle_aprovacoes'), 'Aprovações', 'Fila do cliente'], [$docs, 'Documentos', 'Arquivos e anexos'], [$comments, 'Mensagens', 'Histórico']],
            'atendimentos' => [[$comments, 'Interações', 'Comentários e mensagens'], [$open, 'Abertos', 'Em tratamento'], [$slaRisk, 'SLA em risco', 'Prioridade visual'], [$late, 'Atrasados', 'Requer ação']],
            'auditoria' => [[self::tableCount('item_controle_timeline') + self::tableCount('auditoria_detalhada') + self::tableCount('activity_log'), 'Eventos', 'Rastreabilidade'], [$docs, 'Evidências', 'Documentos'], [self::tableCount('item_controle_aprovacoes'), 'Aprovações', 'Governança'], [$late, 'Não conformidades', 'Pendências vencidas']],
            'riscos' => [[self::riskItemsQuery()->count(), 'Críticos', 'Alta prioridade'], [$late, 'Atrasados', 'Fora do prazo'], [$slaRisk, 'SLA em risco', 'Intervenção'], [$open, 'Planos abertos', 'Mitigação']],
            'pendencias' => [[$open, 'Pendências abertas', 'Backlog'], [$late, 'Atrasadas', 'Vencidas'], [$slaRisk, 'SLA em risco', 'Próximas do limite'], [self::tableCount('item_controle_checklists') + self::tableCount('prazzu_subtasks'), 'Subtarefas', 'Checklist operacional']],
            'cobrancas' => [['R$ '.number_format($paymentsLateValue, 2, ',', '.'), 'Valor vencido', 'Recuperação'], [$paymentsOpen, 'Cobranças abertas', 'Régua interna'], [self::tableCount('assinaturas'), 'Assinaturas', 'Receita recorrente'], [self::tableCount('prazzu_billing_locks'), 'Bloqueios', 'Controle interno']],
            'assinaturas' => [[self::tableCount('assinaturas'), 'Assinaturas', 'Planos'], [$paymentsOpen, 'Pagamentos abertos', 'Financeiro'], ['R$ '.number_format(self::sumTable('assinaturas', 'valor'), 2, ',', '.'), 'MRR previsto', 'Carteira'], [self::tableCount('prazzu_billing_locks'), 'Bloqueios', 'Acesso']],
            'financeiro' => [['R$ '.number_format(self::sumTable('pagamentos', 'valor'), 2, ',', '.'), 'Total lançado', 'Histórico'], [$paymentsOpen, 'Em aberto', 'Cobranças'], ['R$ '.number_format($paymentsLateValue, 2, ',', '.'), 'Vencido', 'Risco'], [self::tableCount('assinaturas'), 'Assinaturas', 'Recorrência']],
            'relatorios' => [[self::tableCount('relatorios_personalizados'), 'Relatórios', 'Salvos'], [count(self::kpis($module)), 'KPIs', 'Monitorados'], [$late, 'Atrasos', 'Operação'], [$slaRisk, 'SLA risco', 'Gestão']],
            'dashboards' => [[self::tableCount('dashboard_widget_configuracoes'), 'Widgets', 'Painéis'], [$late, 'Alertas críticos', 'Atrasos'], [$open, 'Itens abertos', 'Operação'], [$docs, 'Documentos', 'Gestão']],
            'dashboard-configuravel' => [[self::tableCount('dashboard_widget_configuracoes'), 'Widgets', 'Configuráveis'], [self::tableCount('relatorios_personalizados'), 'Visões', 'Relatórios'], [$open, 'Fontes ativas', 'Operação'], [$slaRisk, 'Alertas', 'SLA']],
            'configuracoes' => [[self::tableCount('configuracoes'), 'Configurações', 'Parâmetros'], [self::tableCount('prazzu_automation_rules'), 'Automações', 'Regras'], [self::tableCount('prazzu_sla_rules'), 'SLAs', 'Políticas'], [$companies, 'Empresas', 'White label']],
            'usuarios' => [[self::tableCount('users'), 'Usuários', 'Acesso'], [self::tableCount('responsaveis'), 'Responsáveis', 'Operação'], [self::tableCount('prazzu_user_roles'), 'Vínculos', 'Papéis'], [self::tableCount('prazzu_time_entries'), 'Registros tempo', 'Produtividade']],
            'permissoes' => [[self::tableCount('prazzu_roles'), 'Papéis', 'Perfis'], [self::tableCount('prazzu_permissions'), 'Permissões', 'Ações'], [self::tableCount('prazzu_permission_rules'), 'Regras', 'Escopo'], [self::tableCount('prazzu_user_roles'), 'Usuários vinculados', 'ACL']],
        ];

        $default = [[$totalItems, 'Itens totais', 'Base'], [$open, 'Abertos', 'Operação'], [$late, 'Atrasados', 'Ação'], [$slaRisk, 'SLA em risco', 'Prioridade']];

        return collect($map[$module] ?? $default)->map(fn ($stat) => ['value' => $stat[0], 'label' => $stat[1], 'hint' => $stat[2]])->all();
    }

    public static function features(string $module): array
    {
        $features = [
            'Lista', 'Kanban', 'Calendário', 'Timeline', 'Gantt', 'Tabela', 'Subtarefas', 'Dependências', 'Comentários', 'Menções', 'Notificações internas', 'Dashboard inteligente', 'Campos personalizados', 'Templates', 'Tags', 'Busca global', 'Automação condicional', 'Auditoria', 'Permissões', 'Time tracking', 'KPIs', 'SLA visual', 'Portal cliente', 'Documentos versionados', 'Compliance', 'Cobrança interna', 'White label'
        ];

        return collect($features)->map(function (string $feature): array {
            $need = match ($feature) {
                'Subtarefas' => self::hasAnyTable(['item_controle_checklists', 'prazzu_subtasks', 'prazzu_task_subtasks']),
                'Dependências' => self::hasAnyTable(['prazzu_dependencies', 'prazzu_task_dependencies', 'task_dependencies']),
                'Comentários', 'Menções' => self::hasAnyTable(['item_controle_comentarios', 'comentarios', 'prazzu_task_comments']),
                'Notificações internas' => self::hasAnyTable(['notifications', 'notificacoes_internas']),
                'Campos personalizados' => self::hasTable('prazzu_custom_fields'),
                'Templates' => self::hasTable('prazzu_templates'),
                'Tags' => self::hasAnyTable(['tags', 'item_controle_tags', 'task_tags']),
                'Automação condicional' => self::hasTable('prazzu_automation_rules'),
                'Auditoria' => self::hasAnyTable(['auditoria_detalhada', 'item_controle_timeline', 'activity_log']),
                'Permissões' => self::hasAnyTable(['prazzu_permissions', 'prazzu_roles', 'prazzu_permission_rules']),
                'Time tracking' => self::hasTable('prazzu_time_entries'),
                'SLA visual' => self::hasColumn('item_controles', 'sla_limite_em'),
                'Portal cliente' => self::hasColumn('item_controles', 'portal_ativo'),
                'Documentos versionados' => self::hasAnyTable(['prazzu_document_versions', 'document_versions', 'item_controle_anexos']),
                'Cobrança interna' => self::hasAnyTable(['pagamentos', 'assinaturas', 'prazzu_billing_rules']),
                default => true,
            };
            return ['name' => $feature, 'status' => $need ? 'ativo' : 'pendente'];
        })->all();
    }

    public static function onboarding(string $module): array
    {
        return [
            ['title' => 'Cadastre ou selecione o cliente', 'done' => self::tableCount('empresas') > 0, 'hint' => 'Toda operação fica ligada a uma empresa/cliente.'],
            ['title' => 'Crie o primeiro item operacional', 'done' => self::tableCount('item_controles') > 0, 'hint' => 'Use como tarefa, contrato, documento, atendimento ou pendência.'],
            ['title' => 'Defina responsável e prazo', 'done' => self::itemQuery()->whereNotNull('responsavel_id')->whereNotNull('data_vencimento')->exists(), 'hint' => 'Isso ativa calendário, SLA, Gantt e cobrança de prazo.'],
            ['title' => 'Adicione subtarefas/checklist', 'done' => self::hasAnyTable(['item_controle_checklists', 'prazzu_subtasks']) && (self::tableCount('item_controle_checklists') + self::tableCount('prazzu_subtasks')) > 0, 'hint' => 'Quebre o trabalho em passos claros.'],
            ['title' => 'Anexe evidências e documentos', 'done' => self::tableCount('item_controle_anexos') > 0 || self::itemQuery()->whereNotNull('arquivo')->exists(), 'hint' => 'Alimenta compliance, auditoria e portal.'],
            ['title' => 'Configure regras internas', 'done' => self::tableCount('prazzu_automation_rules') > 0 || self::tableCount('prazzu_sla_rules') > 0, 'hint' => 'Automatize mudança de status, prioridade e alertas internos.'],
        ];
    }

    public static function quickActions(string $module): array
    {
        return match ($module) {
            'portal-cliente' => ['Ver progresso', 'Itens para revisão', 'Documentos', 'Calendário', 'Suporte/Chat'],
            'cobrancas', 'financeiro' => ['Criar cobrança interna', 'Aplicar régua', 'Marcar inadimplência', 'Bloquear acesso', 'Registrar recuperação'],
            'permissoes' => ['Criar papel', 'Liberar módulo', 'Restringir financeiro', 'Auditar acesso', 'Copiar perfil'],
            'automacoes' => ['Nova regra SE/ENTÃO', 'Ativar SLA checker', 'Ativar cobrança checker', 'Simular regra', 'Ver histórico'],
            'white-label' => ['Definir logo', 'Definir cor', 'Plano/limites', 'Identidade do portal', 'Preview'],
            'clientes' => ['Novo cliente', 'Atualizar status', 'Registrar reunião', 'Enviar e-mail', 'Criar onboarding'],
            default => ['Nova tarefa/processo', 'Anexar documento', 'Enviar para aprovação', 'Criar subtarefa', 'Registrar tempo', 'Aplicar template'],
        };
    }

    public static function globalSearch(string $module): array
    {
        $terms = self::items($module, 5);
        $docs = array_slice(self::documents($module), 0, 4);
        $comments = array_slice(self::comments($module), 0, 4);

        return [
            'tasks' => $terms,
            'documents' => $docs,
            'comments' => $comments,
            'filters' => ['Status', 'Responsável', 'Cliente', 'Vencimento', 'SLA', 'Documento', 'Aprovação', 'Cobrança'],
        ];
    }

    public static function kanban(string $module): array
    {
        $statuses = ['pendente' => 'Pendente', 'em_andamento' => 'Em andamento', 'em_aprovacao' => 'Em aprovação', 'concluido' => 'Concluído'];
        $items = self::items($module, 80);
        $board = [];

        foreach ($statuses as $key => $label) {
            $board[] = [
                'key' => $key,
                'label' => $label,
                'items' => array_values(array_filter($items, fn ($item) => self::normalizeStatus($item['status'] ?? 'pendente') === $key)),
            ];
        }

        return $board;
    }

    public static function items(string $module, int $limit = 12): array
    {
        if (! self::hasTable('item_controles')) {
            return [];
        }

        $select = self::safeSelect('item_controles', ['id', 'titulo', 'descricao', 'tipo', 'status', 'prioridade', 'data_vencimento', 'created_at', 'updated_at', 'sla_limite_em', 'sla_concluido_em', 'sla_status', 'portal_ativo', 'contrato_valor', 'contrato_fim_em', 'blocked_by_dependency', 'bloqueado_por_dependencia', 'estimated_minutes', 'actual_minutes', 'approval_required', 'approval_status', 'document_status', 'risk_score'], 'item_controles');
        $select = array_merge($select, ['empresas.nome_fantasia', 'empresas.razao_social', 'responsaveis.nome as responsavel_nome']);

        $query = DB::table('item_controles')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
            ->select($select);

        match ($module) {
            'portal-cliente' => self::hasColumn('item_controles', 'portal_ativo') ? $query->where('item_controles.portal_ativo', 1) : null,
            'riscos' => $query->where(function ($q) { $q->whereIn('item_controles.prioridade', ['alta', 'critica', 'crítica'])->orWhereDate('item_controles.data_vencimento', '<', now()->toDateString()); }),
            'pendencias', 'atendimentos' => $query->whereNotIn('item_controles.status', self::DONE_STATUSES),
            'auditoria' => $query->whereIn('item_controles.tipo', ['documento', 'contrato', 'auditoria', 'compliance']),
            'cobrancas', 'assinaturas', 'financeiro' => $query->whereIn('item_controles.tipo', ['contrato', 'financeiro', 'cobranca', 'cobrança']),
            default => null,
        };

        return $query
            ->orderByRaw("CASE WHEN item_controles.prioridade IN ('critica','crítica','alta') THEN 0 ELSE 1 END")
            ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.data_vencimento')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => self::decorateItem((array) $item))
            ->all();
    }

    public static function decorateItem(array $item): array
    {
        $due = $item['data_vencimento'] ?? null;
        $sla = $item['sla_limite_em'] ?? null;
        $done = in_array($item['status'] ?? '', self::DONE_STATUSES, true);
        $item['empresa'] = ($item['nome_fantasia'] ?? null) ?: (($item['razao_social'] ?? null) ?: 'Sem empresa');
        $item['status_normalized'] = self::normalizeStatus($item['status'] ?? 'pendente');
        $item['is_late'] = $due && Carbon::parse($due)->isPast() && ! $done;
        $item['is_blocked'] = (bool) (($item['blocked_by_dependency'] ?? false) || ($item['bloqueado_por_dependencia'] ?? false));
        $item['sla_state'] = ! $sla ? 'sem_sla' : (($item['sla_concluido_em'] ?? null) ? 'concluido' : (Carbon::parse($sla)->isPast() ? 'vencido' : (abs(Carbon::parse($sla)->diffInHours(now(), false)) <= 8 ? 'risco' : 'ok')));
        $item['progress'] = match ($item['status_normalized']) {'concluido' => 100, 'em_aprovacao' => 75, 'em_andamento' => 45, default => 15};
        if (! empty($item['actual_minutes']) && ! empty($item['estimated_minutes'])) {
            $item['progress'] = min(100, (int) round((((int) $item['actual_minutes']) / max(1, (int) $item['estimated_minutes'])) * 100));
        }
        return $item;
    }

    public static function calendar(string $module): array
    {
        return array_values(array_filter(self::items($module, 40), fn ($i) => ! empty($i['data_vencimento'])));
    }

    public static function gantt(string $module): array
    {
        return array_slice(array_map(function ($i) {
            $start = ! empty($i['created_at']) ? Carbon::parse($i['created_at']) : now()->subDays(7);
            $end = ! empty($i['data_vencimento']) ? Carbon::parse($i['data_vencimento']) : now()->addDays(7);
            $totalDays = max(1, $start->diffInDays($end, false));
            $elapsed = max(0, $start->diffInDays(now(), false));
            $timeProgress = min(100, (int) round(($elapsed / $totalDays) * 100));

            return [
                'title' => $i['titulo'] ?? 'Sem título',
                'start' => $start->format('d/m/Y'),
                'end' => $end->format('d/m/Y'),
                'progress' => $i['progress'] ?? $timeProgress,
                'time_progress' => $timeProgress,
                'status' => $i['status'] ?? 'pendente',
                'sla_state' => $i['sla_state'] ?? 'sem_sla',
                'is_blocked' => $i['is_blocked'] ?? false,
                'empresa' => $i['empresa'] ?? '-',
            ];
        }, self::items($module, 24)), 0, 14);
    }

    public static function timeline(string $module): array
    {
        $rows = [];
        if (self::hasTable('item_controle_timeline')) {
            $rows = array_merge($rows, DB::table('item_controle_timeline')
                ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_timeline.item_controle_id')
                ->select('item_controle_timeline.titulo', 'item_controle_timeline.descricao', 'item_controle_timeline.tipo', 'item_controle_timeline.created_at', 'item_controles.titulo as item_titulo')
                ->orderByDesc('item_controle_timeline.created_at')->limit(10)->get()->map(fn ($i) => (array) $i)->all());
        }
        if (self::hasTable('auditoria_detalhada')) {
            $rows = array_merge($rows, DB::table('auditoria_detalhada')
                ->selectRaw("CONCAT(evento, ' · ', campo) as titulo, CONCAT('Alteração de ', COALESCE(valor_anterior, '-'), ' para ', COALESCE(valor_novo, '-')) as descricao, 'auditoria' as tipo, created_at, auditable_type as item_titulo")
                ->orderByDesc('created_at')->limit(8)->get()->map(fn ($i) => (array) $i)->all());
        }
        if (self::hasTable('activity_log')) {
            $rows = array_merge($rows, DB::table('activity_log')
                ->selectRaw("description as titulo, event as descricao, 'log' as tipo, created_at, subject_type as item_titulo")
                ->orderByDesc('created_at')->limit(8)->get()->map(fn ($i) => (array) $i)->all());
        }

        usort($rows, fn ($a, $b) => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));
        return array_slice($rows, 0, 18);
    }

    public static function dependencies(string $module): array
    {
        $table = self::hasTable('prazzu_dependencies') ? 'prazzu_dependencies' : (self::hasTable('prazzu_task_dependencies') ? 'prazzu_task_dependencies' : null);
        if (! $table) return [];
        $typeColumn = self::hasColumn($table, 'dependency_type') ? 'dependency_type' : 'type';

        return DB::table($table)
            ->leftJoin('item_controles as atual', 'atual.id', '=', $table.'.item_controle_id')
            ->leftJoin('item_controles as depende', 'depende.id', '=', $table.'.depends_on_item_controle_id')
            ->select($table.'.id', $table.'.notes', $table.'.created_at', $table.'.'.$typeColumn.' as type', 'atual.titulo as atual', 'atual.status as atual_status', 'depende.titulo as depende', 'depende.status as depende_status')
            ->orderByDesc($table.'.created_at')
            ->limit(12)->get()->map(fn ($i) => (array) $i)->all();
    }

    public static function approvals(string $module): array
    {
        if (! self::hasTable('item_controle_aprovacoes')) return [];
        return DB::table('item_controle_aprovacoes')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_aprovacoes.item_controle_id')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controle_aprovacoes.empresa_id')
            ->select('item_controle_aprovacoes.id', 'item_controle_aprovacoes.status', 'item_controle_aprovacoes.solicitado_em', 'item_controle_aprovacoes.respondido_em', 'item_controle_aprovacoes.observacao_solicitacao', 'item_controles.titulo', 'item_controles.prioridade', 'empresas.nome_fantasia', 'empresas.razao_social')
            ->orderByRaw("CASE WHEN item_controle_aprovacoes.status = 'pendente' THEN 0 ELSE 1 END")
            ->orderByDesc('item_controle_aprovacoes.solicitado_em')
            ->limit(12)->get()->map(fn ($i) => (array) $i)->all();
    }

    public static function documents(string $module): array
    {
        if (self::hasTable('item_controle_anexos')) {
            $cols = self::safeSelect('item_controle_anexos', ['id', 'nome_original', 'nome', 'caminho', 'created_at', 'updated_at'], 'item_controle_anexos');
            return DB::table('item_controle_anexos')
                ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_anexos.item_controle_id')
                ->select(array_merge($cols, ['item_controles.titulo']))
                ->orderByDesc('item_controle_anexos.created_at')->limit(12)->get()->map(fn ($i) => (array) $i)->all();
        }
        return self::itemQuery()->whereNotNull('arquivo')->select('id', 'titulo', 'arquivo as nome_original', 'updated_at as created_at')->limit(8)->get()->map(fn ($i) => (array) $i)->all();
    }

    public static function documentWorkflow(string $module): array
    {
        $items = self::items($module, 30);
        return [
            ['label' => 'Rascunho', 'count' => count(array_filter($items, fn ($i) => ($i['document_status'] ?? '') === 'rascunho' || ($i['status_normalized'] ?? '') === 'pendente'))],
            ['label' => 'Em aprovação', 'count' => count(array_filter($items, fn ($i) => ($i['approval_status'] ?? '') === 'pendente' || ($i['status_normalized'] ?? '') === 'em_aprovacao'))],
            ['label' => 'Aprovado', 'count' => count(array_filter($items, fn ($i) => in_array(($i['approval_status'] ?? ''), ['aprovado', 'aprovada'], true)))],
            ['label' => 'Vencido', 'count' => count(array_filter($items, fn ($i) => $i['is_late'] ?? false))],
        ];
    }

    public static function comments(string $module): array
    {
        $table = self::hasTable('item_controle_comentarios') ? 'item_controle_comentarios' : (self::hasTable('comentarios') ? 'comentarios' : (self::hasTable('prazzu_task_comments') ? 'prazzu_task_comments' : null));
        if (! $table) return [];
        $commentColumn = self::hasColumn($table, 'comentario') ? 'comentario' : (self::hasColumn($table, 'body') ? 'body' : 'comment');
        return DB::table($table)
            ->leftJoin('item_controles', 'item_controles.id', '=', $table.'.item_controle_id')
            ->select($table.'.'.$commentColumn.' as comentario', $table.'.created_at', 'item_controles.titulo')
            ->orderByDesc($table.'.created_at')->limit(10)->get()->map(fn ($i) => (array) $i)->all();
    }

    public static function notifications(string $module): array
    {
        if (self::hasTable('notificacoes_internas')) {
            return DB::table('notificacoes_internas')->orderByDesc('created_at')->limit(8)->get()->map(fn ($i) => (array) $i)->all();
        }
        if (self::hasTable('notifications')) {
            return DB::table('notifications')->orderByDesc('created_at')->limit(8)->get()->map(fn ($i) => (array) $i)->all();
        }
        return [];
    }

    public static function automations(string $module): array
    {
        if (self::hasTable('prazzu_automation_rules')) {
            return DB::table('prazzu_automation_rules')
                ->where(function ($q) use ($module) { $q->where('module', $module)->orWhere('module', '')->orWhere('module', 'item_controles'); })
                ->orderByDesc('active')->orderByDesc('created_at')->limit(12)->get()->map(fn ($i) => (array) $i)->all();
        }
        return [];
    }

    public static function automationBuilder(string $module): array
    {
        $rules = self::automations($module);
        if ($rules) {
            return collect($rules)->map(fn ($r) => [
                'if' => ($r['trigger_type'] ?? 'evento').' · '.($r['condition_field'] ?? 'campo').' '.($r['condition_operator'] ?? '=').' '.($r['condition_value'] ?? '-'),
                'then' => ($r['action_type'] ?? 'ação').' → '.($r['action_value'] ?? '-'),
                'active' => (bool) ($r['active'] ?? false),
            ])->all();
        }
        return [
            ['if' => 'scheduled · sla_status = vencido', 'then' => 'prioridade → alta', 'active' => true],
            ['if' => 'event · approval_status = aprovado', 'then' => 'status → concluido', 'active' => true],
            ['if' => 'scheduled · pagamento = vencido', 'then' => 'régua → bloqueio interno', 'active' => true],
        ];
    }

    public static function compliance(string $module): array
    {
        return [
            ['label' => 'Documentos vencidos', 'value' => self::lateItemsQuery()->whereIn('tipo', ['documento', 'compliance'])->count(), 'state' => 'danger'],
            ['label' => 'Contratos vencendo em 30 dias', 'value' => self::hasColumn('item_controles', 'contrato_fim_em') ? self::itemQuery()->whereBetween('contrato_fim_em', [now()->toDateString(), now()->addDays(30)->toDateString()])->count() : 0, 'state' => 'warning'],
            ['label' => 'SLA vencido', 'value' => self::hasColumn('item_controles', 'sla_limite_em') ? self::itemQuery()->whereNotNull('sla_limite_em')->whereNull('sla_concluido_em')->where('sla_limite_em', '<', now())->count() : 0, 'state' => 'danger'],
            ['label' => 'Sem responsável', 'value' => self::itemQuery()->whereNull('responsavel_id')->count(), 'state' => 'warning'],
            ['label' => 'Sem vencimento', 'value' => self::itemQuery()->whereNull('data_vencimento')->count(), 'state' => 'neutral'],
        ];
    }

    public static function billing(string $module): array
    {
        if (! self::hasTable('pagamentos')) return [];
        return DB::table('pagamentos')
            ->leftJoin('empresas', 'empresas.id', '=', 'pagamentos.empresa_id')
            ->select('pagamentos.id', 'pagamentos.status', 'pagamentos.valor', 'pagamentos.vencimento', 'pagamentos.pago_em', 'empresas.nome_fantasia', 'empresas.razao_social')
            ->orderByRaw("CASE WHEN pagamentos.status IN ('OVERDUE','PAYMENT_OVERDUE') OR pagamentos.vencimento < CURDATE() THEN 0 ELSE 1 END")
            ->orderBy('pagamentos.vencimento')
            ->limit(12)->get()->map(fn ($i) => (array) $i)->all();
    }

    public static function kpis(string $module): array
    {
        $total = max(1, self::tableCount('item_controles'));
        $done = self::itemQuery()->whereIn('status', self::DONE_STATUSES)->count();
        $late = self::lateItemsQuery()->count();
        $slaDone = self::hasColumn('item_controles', 'sla_concluido_em') ? self::itemQuery()->whereNotNull('sla_concluido_em')->count() : 0;
        $timeSeconds = self::hasTable('prazzu_time_entries') && self::hasColumn('prazzu_time_entries', 'total_seconds') ? (int) DB::table('prazzu_time_entries')->sum('total_seconds') : 0;

        return [
            ['label' => 'Taxa de conclusão', 'value' => round(($done / $total) * 100).'%'],
            ['label' => 'Taxa de atraso', 'value' => round(($late / $total) * 100).'%'],
            ['label' => 'SLA concluído', 'value' => $slaDone],
            ['label' => 'Horas registradas', 'value' => number_format($timeSeconds / 3600, 1, ',', '.')],
            ['label' => 'Documentos por item', 'value' => number_format((self::tableCount('item_controle_anexos') / $total), 1, ',', '.')],
            ['label' => 'Comentários por item', 'value' => number_format(((self::tableCount('item_controle_comentarios') + self::tableCount('comentarios')) / $total), 1, ',', '.')],
        ];
    }

    public static function reports(string $module): array
    {
        return [
            ['title' => 'Operação executiva', 'description' => 'Abertos, concluídos, atrasos, prioridade e responsáveis.'],
            ['title' => 'SLA e prazos', 'description' => 'Itens em risco, vencidos, concluídos e tempo médio.'],
            ['title' => 'Documentos e compliance', 'description' => 'Vencimentos, aprovações, evidências e pendências.'],
            ['title' => 'Financeiro e cobrança', 'description' => 'Em aberto, vencidos, recuperação e bloqueios.'],
            ['title' => 'Produtividade', 'description' => 'Tempo registrado, throughput, gargalos e capacidade.'],
        ];
    }

    public static function timeTracking(string $module): array
    {
        if (! self::hasTable('prazzu_time_entries')) return [];
        return DB::table('prazzu_time_entries')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'prazzu_time_entries.item_controle_id')
            ->select('prazzu_time_entries.id', 'prazzu_time_entries.total_seconds', 'prazzu_time_entries.notes', 'prazzu_time_entries.started_at', 'prazzu_time_entries.ended_at', 'prazzu_time_entries.created_at', 'item_controles.titulo')
            ->orderByDesc('prazzu_time_entries.created_at')->limit(10)->get()->map(fn ($i) => (array) $i)->all();
    }

    public static function whiteLabel(): array
    {
        $empresa = self::hasTable('empresas') ? DB::table('empresas')->orderByDesc('updated_at')->first() : null;
        return [
            ['label' => 'Empresa principal', 'value' => $empresa ? (($empresa->nome_fantasia ?: $empresa->razao_social) ?: 'Sem nome') : 'Não configurada'],
            ['label' => 'Plano', 'value' => $empresa->plano ?? 'Não definido'],
            ['label' => 'Usuários permitidos', 'value' => $empresa->limite_usuarios ?? 'Sem limite definido'],
            ['label' => 'Itens permitidos', 'value' => $empresa->limite_itens ?? 'Sem limite definido'],
            ['label' => 'Status', 'value' => isset($empresa->ativo) ? ((bool) $empresa->ativo ? 'Ativo' : 'Inativo') : 'Sem status'],
        ];
    }

    public static function permissions(string $module): array
    {
        if (self::hasTable('prazzu_permissions')) {
            return DB::table('prazzu_permissions')->limit(10)->get()->map(function ($p) {
                $p = (array) $p;
                return ['area' => $p['name'] ?? $p['module'] ?? 'Permissão', 'level' => ($p['action'] ?? 'ação').' · '.($p['scope'] ?? 'escopo')];
            })->all();
        }
        return [
            ['area' => 'Visualizar', 'level' => 'Perfil / responsável / cliente'],
            ['area' => 'Criar', 'level' => 'Por módulo e papel'],
            ['area' => 'Editar', 'level' => 'Por etapa, status e dono'],
            ['area' => 'Aprovar', 'level' => 'Aprovadores definidos'],
            ['area' => 'Financeiro', 'level' => 'Restrito por perfil'],
            ['area' => 'Auditoria', 'level' => 'Somente leitura rastreável'],
        ];
    }


    public static function userManagement(string $module): array
    {
        if ($module !== 'usuarios') {
            return [];
        }

        $empresa = self::currentEmpresa();
        $users = self::usersList();
        $totalSeats = (int) (($empresa['limite_usuarios'] ?? 0) ?: max(count($users), self::tableCount('users')));
        $usedSeats = self::tableCount('users');
        $availableSeats = $totalSeats > 0 ? max(0, $totalSeats - $usedSeats) : 'Sem limite';
        $guests = self::countUsersByRole(['guest', 'convidado', 'freelancer', 'cliente']);
        $inactive = self::inactiveUsersCount(90);

        return [
            'seats' => [
                ['label' => 'Assentos do plano', 'value' => $totalSeats ?: 'Sem limite definido', 'hint' => 'Limite contratado para usuários internos e acessos ativos.'],
                ['label' => 'Assentos usados', 'value' => $usedSeats, 'hint' => 'Usuários cadastrados no sistema.'],
                ['label' => 'Convites disponíveis', 'value' => $availableSeats, 'hint' => 'Quantidade ainda liberada antes de cobrar/atingir o limite.'],
                ['label' => 'Convidados', 'value' => $guests, 'hint' => 'Clientes, freelancers ou acessos externos limitados.'],
                ['label' => 'Sem acesso há 90 dias', 'value' => $inactive, 'hint' => 'Base para remover acesso e economizar assentos.'],
            ],
            'sections' => [
                ['title' => 'Gerenciamento de Assentos (Seats)', 'description' => 'Acompanhe o total contratado, assentos usados e convites ainda disponíveis para controlar custo por usuário.'],
                ['title' => 'Gestão de Convidados (Guests)', 'description' => 'Adicione clientes, freelancers ou parceiros sem expor tudo: o convidado só deve ver o que foi compartilhado com ele.'],
                ['title' => 'Filtro de Último Acesso', 'description' => 'Identifique quem não entra há meses, remova acessos inativos e libere cobrança de assentos.'],
                ['title' => 'Alteração de Cargo (Roles)', 'description' => 'Mude rapidamente alguém de Member para Admin, Guest, Gestor, Estagiário ou outro cargo personalizado.'],
                ['title' => 'Visualização de Grupos (Teams)', 'description' => 'Veja quais grupos internos aquele usuário participa para entender acesso, operação e responsabilidades.'],
            ],
            'users' => $users,
            'teams' => self::teamsList(),
            'roles' => self::rolesList(),
        ];
    }

    public static function advancedPermissions(string $module): array
    {
        if ($module !== 'permissoes') {
            return [];
        }

        return [
            'cards' => [
                ['title' => 'Criação de Cargos Personalizados', 'description' => 'Crie cargos como Estagiário, Gestor ou Visualizador Externo com travas específicas, em vez de limitar tudo a Member/Admin.', 'status' => self::hasTable('prazzu_roles') ? 'Configurado' : 'Execute o SQL'],
                ['title' => 'Restrição de Exclusão', 'description' => 'Impeça membros comuns de excluir Listas, Pastas ou Espaços e reduza o risco de apagarem projetos inteiros.', 'status' => self::permissionExists('delete') ? 'Configurado' : 'Pendente'],
                ['title' => 'Bloqueio de Exportação', 'description' => 'Proíba download de dados da empresa em CSV ou Excel para proteger informações sensíveis.', 'status' => self::permissionExists('export') ? 'Configurado' : 'Pendente'],
                ['title' => 'Permissões de Visualização', 'description' => 'Defina se novos Espaços nascem públicos para toda a empresa ou privados por padrão.', 'status' => self::permissionExists('visibility') ? 'Configurado' : 'Pendente'],
                ['title' => 'Gestão de Tags e Status', 'description' => 'Controle quem pode criar etiquetas e mudar etapas do fluxo para evitar status duplicados ou bagunçados.', 'status' => self::permissionExists('manage_tags_status') ? 'Configurado' : 'Pendente'],
            ],
            'roles' => self::rolesList(),
            'rules' => self::permissionRulesList(),
            'permissions' => self::permissions('permissoes'),
        ];
    }


    public static function clientCrm(): array
    {
        if (! self::hasTable('empresas')) {
            return [
                'clients' => [],
                'statusSummary' => [],
                'healthSummary' => [],
                'onboarding' => [],
                'emailHistory' => [],
            ];
        }

        $clients = DB::table('empresas')
            ->select(self::safeSelect('empresas', ['id', 'razao_social', 'nome_fantasia', 'cnpj', 'email', 'telefone', 'responsavel_nome', 'status', 'plano', 'ativo', 'created_at', 'updated_at'], 'empresas') ?: ['empresas.id'])
            ->orderByDesc(self::hasColumn('empresas', 'updated_at') ? 'empresas.updated_at' : 'empresas.id')
            ->limit(30)
            ->get()
            ->map(function ($company) {
                $row = (array) $company;
                $companyId = (int) ($row['id'] ?? 0);
                $openItems = self::companyOpenItems($companyId);
                $lateItems = self::companyLateItems($companyId);
                $reviewItems = self::companyReviewItems($companyId);
                $lastMeeting = self::companyLastMeeting($companyId);
                $ltv = self::companyLtv($companyId);
                $health = self::companyHealthScore($row, $openItems, $lateItems, $reviewItems, $lastMeeting);

                return [
                    'id' => $companyId,
                    'name' => ($row['nome_fantasia'] ?? null) ?: (($row['razao_social'] ?? null) ?: 'Cliente sem nome'),
                    'legal_name' => $row['razao_social'] ?? '-',
                    'document' => $row['cnpj'] ?? '-',
                    'contract_status' => self::contractStatusLabel($row, $openItems, $lateItems),
                    'ltv' => $ltv,
                    'contact_name' => ($row['responsavel_nome'] ?? null) ?: (($row['nome_fantasia'] ?? null) ?: 'Não informado'),
                    'contact_email' => $row['email'] ?? null,
                    'contact_whatsapp' => $row['telefone'] ?? null,
                    'last_meeting' => $lastMeeting,
                    'health_label' => $health['label'],
                    'health_tone' => $health['tone'],
                    'health_score' => $health['score'],
                    'open_items' => $openItems,
                    'late_items' => $lateItems,
                    'review_items' => $reviewItems,
                    'updated_at' => $row['updated_at'] ?? null,
                ];
            })
            ->all();

        return [
            'clients' => $clients,
            'statusSummary' => self::clientStatusSummary($clients),
            'healthSummary' => self::clientHealthSummary($clients),
            'onboarding' => self::clientOnboardingQueue($clients),
            'emailHistory' => self::clientEmailHistory(),
        ];
    }

    public static function portalExperience(): array
    {
        $items = self::items('portal-cliente', 60);
        $visible = array_values(array_filter($items, fn ($item) => in_array(($item['status_normalized'] ?? ''), ['em_aprovacao', 'concluido'], true) || (bool) ($item['portal_ativo'] ?? false)));

        return [
            'progress' => self::portalProgress($items),
            'visibleItems' => array_slice($visible, 0, 16),
            'documents' => self::documents('portal-cliente'),
            'meetingNotes' => self::portalMeetingNotes(),
            'calendar' => self::calendar('portal-cliente'),
            'supportQueue' => self::portalSupportQueue(),
            'chat' => self::comments('portal-cliente'),
        ];
    }

    private static function companyOpenItems(int $companyId): int
    {
        return self::hasTable('item_controles') ? (int) self::itemQuery()->where('empresa_id', $companyId)->whereNotIn('status', self::DONE_STATUSES)->count() : 0;
    }

    private static function companyLateItems(int $companyId): int
    {
        return self::hasTable('item_controles') ? (int) self::lateItemsQuery()->where('empresa_id', $companyId)->count() : 0;
    }

    private static function companyReviewItems(int $companyId): int
    {
        if (! self::hasTable('item_controles')) return 0;
        return (int) self::itemQuery()
            ->where('empresa_id', $companyId)
            ->where(function ($query) {
                $query->whereIn('status', ['em_aprovacao', 'aprovacao', 'em aprovação']);
                if (self::hasColumn('item_controles', 'approval_status')) {
                    $query->orWhere('approval_status', 'pendente');
                }
            })
            ->count();
    }

    private static function companyLastMeeting(int $companyId): ?string
    {
        if (! self::hasTable('item_controle_timeline')) return null;

        $query = DB::table('item_controle_timeline')
            ->join('item_controles', 'item_controles.id', '=', 'item_controle_timeline.item_controle_id')
            ->where('item_controles.empresa_id', $companyId)
            ->where(function ($q) {
                $q->where('item_controle_timeline.titulo', 'like', '%reuni%')
                    ->orWhere('item_controle_timeline.descricao', 'like', '%reuni%')
                    ->orWhere('item_controle_timeline.titulo', 'like', '%call%')
                    ->orWhere('item_controle_timeline.descricao', 'like', '%call%')
                    ->orWhere('item_controle_timeline.titulo', 'like', '%ata%')
                    ->orWhere('item_controle_timeline.descricao', 'like', '%ata%');
            });

        $date = $query->max('item_controle_timeline.created_at');
        return $date ? Carbon::parse($date)->format('d/m/Y') : null;
    }

    private static function companyLtv(int $companyId): float
    {
        if (! self::hasTable('pagamentos')) return 0.0;

        $query = DB::table('pagamentos')->where('empresa_id', $companyId);
        if (self::hasColumn('pagamentos', 'pago_em')) {
            $query->where(function ($q) {
                $q->whereNotNull('pago_em')->orWhereIn('status', ['RECEIVED', 'CONFIRMED', 'PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED']);
            });
        }

        return (float) $query->sum('valor');
    }

    private static function companyHealthScore(array $company, int $openItems, int $lateItems, int $reviewItems, ?string $lastMeeting): array
    {
        $score = 100;
        $score -= min(45, $lateItems * 15);
        $score -= min(20, max(0, $openItems - 5) * 3);
        $score -= min(15, $reviewItems * 5);

        if ($lastMeeting === null) {
            $score -= 15;
        }

        if (($company['ativo'] ?? 1) == 0 || strtolower((string) ($company['status'] ?? '')) !== 'ativo') {
            $score -= 25;
        }

        $score = max(0, min(100, $score));

        return match (true) {
            $score >= 80 => ['label' => 'Saudável', 'tone' => 'ok', 'score' => $score],
            $score >= 55 => ['label' => 'Atenção', 'tone' => 'warning', 'score' => $score],
            default => ['label' => 'Em risco', 'tone' => 'danger', 'score' => $score],
        };
    }

    private static function contractStatusLabel(array $company, int $openItems, int $lateItems): string
    {
        $status = strtolower((string) ($company['status'] ?? ''));
        if (($company['ativo'] ?? 1) == 0 || in_array($status, ['churn', 'cancelado', 'inativo'], true)) return 'Churn/Inativo';
        if ($lateItems > 0) return 'Em risco';
        if ($openItems > 0) return 'Em implementação';
        return 'Ativo';
    }

    private static function clientStatusSummary(array $clients): array
    {
        return collect($clients)->groupBy('contract_status')->map(fn ($rows, $status) => ['label' => $status, 'count' => count($rows)])->values()->all();
    }

    private static function clientHealthSummary(array $clients): array
    {
        return collect($clients)->groupBy('health_label')->map(fn ($rows, $label) => ['label' => $label, 'count' => count($rows), 'tone' => $rows[0]['health_tone'] ?? 'neutral'])->values()->all();
    }

    private static function clientOnboardingQueue(array $clients): array
    {
        return collect($clients)
            ->filter(fn ($client) => in_array($client['contract_status'], ['Em implementação', 'Ativo'], true))
            ->take(8)
            ->map(fn ($client) => [
                'client' => $client['name'],
                'status' => $client['contract_status'],
                'tasks' => max(1, $client['open_items']),
                'health' => $client['health_label'],
            ])
            ->values()
            ->all();
    }

    private static function clientEmailHistory(): array
    {
        if (! self::hasTable('item_controle_comentarios')) return [];

        $commentColumn = self::hasColumn('item_controle_comentarios', 'comentario') ? 'comentario' : 'comment';
        return DB::table('item_controle_comentarios')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_comentarios.item_controle_id')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->select('item_controle_comentarios.created_at', 'item_controles.titulo', 'empresas.nome_fantasia', 'empresas.razao_social', 'item_controle_comentarios.'.$commentColumn.' as mensagem')
            ->where(function ($q) use ($commentColumn) {
                $q->where('item_controle_comentarios.'.$commentColumn, 'like', '%@%')
                    ->orWhere('item_controle_comentarios.'.$commentColumn, 'like', '%email%')
                    ->orWhere('item_controle_comentarios.'.$commentColumn, 'like', '%e-mail%');
            })
            ->orderByDesc('item_controle_comentarios.created_at')
            ->limit(8)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private static function portalProgress(array $items): array
    {
        $total = count($items);
        $done = count(array_filter($items, fn ($item) => ($item['status_normalized'] ?? '') === 'concluido'));
        $review = count(array_filter($items, fn ($item) => ($item['status_normalized'] ?? '') === 'em_aprovacao' || ($item['approval_status'] ?? '') === 'pendente'));
        $percent = $total > 0 ? (int) round(($done / $total) * 100) : 0;

        return [
            'total' => $total,
            'done' => $done,
            'review' => $review,
            'percent' => $percent,
            'pending' => max(0, $total - $done),
        ];
    }

    private static function portalMeetingNotes(): array
    {
        if (! self::hasTable('item_controle_timeline')) return [];

        return DB::table('item_controle_timeline')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_timeline.item_controle_id')
            ->select('item_controle_timeline.titulo', 'item_controle_timeline.descricao', 'item_controle_timeline.created_at', 'item_controles.titulo as item_titulo')
            ->where(function ($q) {
                $q->where('item_controle_timeline.titulo', 'like', '%reuni%')
                    ->orWhere('item_controle_timeline.descricao', 'like', '%reuni%')
                    ->orWhere('item_controle_timeline.titulo', 'like', '%ata%')
                    ->orWhere('item_controle_timeline.descricao', 'like', '%ata%')
                    ->orWhere('item_controle_timeline.titulo', 'like', '%call%')
                    ->orWhere('item_controle_timeline.descricao', 'like', '%call%');
            })
            ->orderByDesc('item_controle_timeline.created_at')
            ->limit(8)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private static function portalSupportQueue(): array
    {
        return array_slice(array_filter(self::items('portal-cliente', 40), fn ($item) => in_array(($item['tipo'] ?? ''), ['suporte', 'atendimento', 'solicitacao', 'solicitação'], true) || ($item['is_late'] ?? false)), 0, 10);
    }

    private static function currentEmpresa(): array
    {
        if (! self::hasTable('empresas')) {
            return [];
        }

        $query = DB::table('empresas');
        $user = auth()->user();
        if ($user && isset($user->empresa_id) && self::hasColumn('empresas', 'id')) {
            $query->where('id', $user->empresa_id);
        }

        $order = self::hasColumn('empresas', 'updated_at') ? 'updated_at' : 'id';
        return (array) ($query->orderByDesc($order)->first() ?? []);
    }

    private static function usersList(int $limit = 12): array
    {
        if (! self::hasTable('users')) {
            return [];
        }

        $columns = self::safeSelect('users', ['id', 'name', 'email', 'role', 'empresa_id', 'created_at', 'email_verified_at', 'last_access_at', 'last_login_at', 'last_seen_at'], 'users');
        $order = self::hasColumn('users', 'last_access_at') ? 'last_access_at' : (self::hasColumn('users', 'last_login_at') ? 'last_login_at' : (self::hasColumn('users', 'created_at') ? 'created_at' : 'id'));

        return DB::table('users')
            ->select($columns ?: ['id'])
            ->orderByDesc($order)
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                $row = (array) $user;
                $row['display_role'] = self::displayRole($row['role'] ?? 'Member');
                $row['last_access_display'] = self::lastAccessDisplay($row);
                $row['access_type'] = self::isGuestRole($row['role'] ?? null) ? 'Guest' : 'Seat';
                return $row;
            })
            ->all();
    }

    private static function teamsList(int $limit = 10): array
    {
        if (! self::hasTable('prazzu_teams')) {
            return [
                ['name' => 'Administrativo', 'description' => 'Equipe administrativa e backoffice.', 'active' => 1],
                ['name' => 'Operacional', 'description' => 'Equipe responsável pela execução.', 'active' => 1],
                ['name' => 'Financeiro', 'description' => 'Equipe com acesso financeiro controlado.', 'active' => 1],
            ];
        }

        return DB::table('prazzu_teams')
            ->select(self::safeSelect('prazzu_teams', ['id', 'name', 'description', 'active', 'created_at'], 'prazzu_teams') ?: ['id'])
            ->orderByDesc(self::hasColumn('prazzu_teams', 'created_at') ? 'created_at' : 'id')
            ->limit($limit)
            ->get()
            ->map(fn ($team) => (array) $team)
            ->all();
    }

    private static function rolesList(int $limit = 12): array
    {
        if (! self::hasTable('prazzu_roles')) {
            return [
                ['name' => 'Admin', 'description' => 'Administrador da empresa com acesso amplo.', 'active' => 1],
                ['name' => 'Member', 'description' => 'Usuário interno com acesso operacional controlado.', 'active' => 1],
                ['name' => 'Guest', 'description' => 'Convidado externo com acesso limitado.', 'active' => 1],
                ['name' => 'Estagiário', 'description' => 'Perfil restrito, sem exclusão e sem exportação.', 'active' => 1],
                ['name' => 'Visualizador Externo', 'description' => 'Perfil somente leitura para cliente/auditor.', 'active' => 1],
            ];
        }

        return DB::table('prazzu_roles')
            ->select(self::safeSelect('prazzu_roles', ['id', 'name', 'description', 'active', 'created_at'], 'prazzu_roles') ?: ['id'])
            ->orderByDesc(self::hasColumn('prazzu_roles', 'created_at') ? 'created_at' : 'id')
            ->limit($limit)
            ->get()
            ->map(fn ($role) => (array) $role)
            ->all();
    }

    private static function permissionRulesList(int $limit = 12): array
    {
        if (! self::hasTable('prazzu_permission_rules')) {
            return [];
        }

        return DB::table('prazzu_permission_rules')
            ->select(self::safeSelect('prazzu_permission_rules', ['id', 'role', 'module', 'can_view', 'can_create', 'can_update', 'can_delete', 'scope', 'created_at'], 'prazzu_permission_rules') ?: ['id'])
            ->orderByDesc(self::hasColumn('prazzu_permission_rules', 'created_at') ? 'created_at' : 'id')
            ->limit($limit)
            ->get()
            ->map(fn ($rule) => (array) $rule)
            ->all();
    }

    private static function countUsersByRole(array $roles): int
    {
        if (! self::hasColumn('users', 'role')) {
            return 0;
        }

        return (int) DB::table('users')->whereRaw('LOWER(role) in ('.collect($roles)->map(fn () => '?')->implode(',').')', array_map('strtolower', $roles))->count();
    }

    private static function inactiveUsersCount(int $days): int
    {
        if (! self::hasTable('users')) {
            return 0;
        }

        $column = self::hasColumn('users', 'last_access_at') ? 'last_access_at' : (self::hasColumn('users', 'last_login_at') ? 'last_login_at' : (self::hasColumn('users', 'last_seen_at') ? 'last_seen_at' : null));
        if (! $column) {
            return 0;
        }

        return (int) DB::table('users')
            ->where(function ($query) use ($column, $days) {
                $query->whereNull($column)->orWhere($column, '<', now()->subDays($days));
            })
            ->count();
    }

    private static function permissionExists(string $action): bool
    {
        return self::hasTable('prazzu_permissions')
            && self::hasColumn('prazzu_permissions', 'action')
            && DB::table('prazzu_permissions')->where('action', $action)->exists();
    }

    private static function displayRole(?string $role): string
    {
        $role = trim((string) $role);
        if ($role === '') {
            return 'Member';
        }

        return Str::of($role)->replace('_', ' ')->title()->toString();
    }

    private static function isGuestRole(?string $role): bool
    {
        $role = Str::of((string) $role)->lower()->ascii()->toString();
        return in_array($role, ['guest', 'convidado', 'freelancer', 'cliente', 'visualizador externo'], true);
    }

    private static function lastAccessDisplay(array $row): string
    {
        $last = $row['last_access_at'] ?? $row['last_login_at'] ?? $row['last_seen_at'] ?? null;
        if (! $last) {
            return 'Sem registro';
        }

        return Carbon::parse($last)->format('d/m/Y H:i');
    }

    private static function normalizeStatus(string $status): string
    {
        $status = Str::of($status)->lower()->ascii()->replace('-', '_')->replace(' ', '_')->toString();
        return match ($status) {
            'andamento', 'em_execucao', 'em_progresso' => 'em_andamento',
            'aprovacao', 'em_analise' => 'em_aprovacao',
            'concluida', 'concluido', 'finalizado', 'finalizada', 'cancelado', 'cancelada' => 'concluido',
            default => $status ?: 'pendente',
        };
    }

    private static function itemQuery()
    {
        return self::hasTable('item_controles') ? DB::table('item_controles') : DB::query()->fromRaw('(select 1 as id) as empty')->whereRaw('1 = 0');
    }

    private static function lateItemsQuery()
    {
        return self::itemQuery()->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString())->whereNotIn('status', self::DONE_STATUSES);
    }

    private static function riskItemsQuery()
    {
        return self::itemQuery()->where(function ($q) {
            $q->whereIn('prioridade', ['alta', 'critica', 'crítica']);
            if (self::hasColumn('item_controles', 'risk_score')) {
                $q->orWhere('risk_score', '>=', 70);
            }
        });
    }

    private static function safeItemCount(string $column, mixed $value): int
    {
        return self::hasColumn('item_controles', $column) ? self::itemQuery()->where($column, $value)->count() : 0;
    }

    private static function safeSelect(string $table, array $columns, string $prefix): array
    {
        return collect($columns)->filter(fn ($column) => self::hasColumn($table, $column))->map(fn ($column) => $prefix.'.'.$column)->values()->all();
    }

    private static function hasAnyTable(array $tables): bool
    {
        foreach ($tables as $table) if (self::hasTable($table)) return true;
        return false;
    }

    private static function hasTable(string $table): bool { return CachedSchema::hasTable($table); }
    private static function hasColumn(string $table, string $column): bool { return CachedSchema::hasTable($table) && CachedSchema::hasColumn($table, $column); }
    private static function tableCount(string $table): int { return self::hasTable($table) ? DB::table($table)->count() : 0; }
    private static function sumTable(string $table, string $column): float { return self::hasTable($table) && self::hasColumn($table, $column) ? (float) DB::table($table)->sum($column) : 0.0; }
}
