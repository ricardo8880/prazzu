<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PrazzuEnterprisePageData
{
    public static function for(string $module): array
    {
        return match ($module) {
            'clientes' => self::clientes(),
            'portal-cliente' => self::portalCliente(),
            'atendimentos' => self::atendimentos(),
            'auditoria' => self::auditoria(),
            'riscos' => self::riscos(),
            'pendencias' => self::pendencias(),
            'cobrancas' => self::cobrancas(),
            'assinaturas' => self::assinaturas(),
            'financeiro' => self::financeiro(),
            'relatorios' => self::relatorios(),
            'dashboards' => self::dashboards(),
            'dashboard-configuravel' => self::dashboardConfiguravel(),
            'configuracoes' => self::configuracoes(),
            'usuarios' => self::usuarios(),
            'permissoes' => self::permissoes(),
            default => self::base($module, 'Prazzu Enterprise', 'Gestão operacional, documental, compliance, portal cliente e cobrança inteligente.'),
        };
    }

    private static function clientes(): array
    {
        return self::base('clientes', 'Clientes', 'CRM operacional com contratos, documentos, portal, SLA, cobrança e saúde do cliente.', [
            ['label' => 'Clientes ativos', 'value' => self::count('empresas', ['ativo' => 1]), 'tone' => 'success'],
            ['label' => 'Contatos', 'value' => self::count('responsaveis'), 'tone' => 'info'],
            ['label' => 'Itens em aberto', 'value' => self::countOpenItems(), 'tone' => 'warning'],
            ['label' => 'Clientes inadimplentes', 'value' => self::countOverduePayments(), 'tone' => 'danger'],
        ], [
            'Kanban por cliente com status comercial/operacional', 'Linha do tempo de contratos, documentos e atendimentos', 'Score de risco e saúde do cliente', 'Campos personalizados por segmento', 'Portal e permissões por cliente', 'Relatórios de retenção e inadimplência'
        ], self::clientRows());
    }

    private static function portalCliente(): array
    {
        return self::base('portal-cliente', 'Portal do Cliente Premium', 'Área externa para documentos, aprovações, mensagens, status do processo e acompanhamento.', [
            ['label' => 'Portais ativos', 'value' => self::countWhereColumn('item_controles','portal_ativo',1), 'tone' => 'success'],
            ['label' => 'Acessos expirados', 'value' => self::countExpiredPortals(), 'tone' => 'danger'],
            ['label' => 'Clientes únicos', 'value' => self::distinct('item_controles','portal_cliente_email'), 'tone' => 'info'],
            ['label' => 'Sem e-mail', 'value' => self::countPortalNoEmail(), 'tone' => 'warning'],
        ], [
            'Envio e recebimento de documentos', 'Mensagens com cliente e anexos', 'Aprovação/reprovação de entregas', 'Status do processo em tempo real', 'Histórico de acessos e auditoria', 'Links com validade e token seguro'
        ], self::portalRows(), self::portalActions());
    }

    private static function atendimentos(): array
    {
        return self::base('atendimentos', 'Atendimentos', 'Central de relacionamento com comentários, menções, anexos, SLA e histórico por cliente.', [
            ['label' => 'Interações', 'value' => self::countAny(['item_controle_comentarios','comentarios','prazzu_task_comments']), 'tone' => 'info'],
            ['label' => 'Hoje', 'value' => self::countTodayAny(['item_controle_comentarios','comentarios','prazzu_task_comments']), 'tone' => 'success'],
            ['label' => 'SLA em risco', 'value' => self::countSlaRisk(), 'tone' => 'warning'],
            ['label' => 'Atrasados', 'value' => self::countOverdueItems(), 'tone' => 'danger'],
        ], [
            'Comentários com @menções', 'Notificações internas', 'Anexos e documentos no atendimento', 'SLA visual por atendimento', 'Templates de resposta', 'Linha do tempo completa'
        ], self::attendanceRows(), self::attendanceActions());
    }

    private static function auditoria(): array
    {
        return self::base('auditoria', 'Auditoria', 'Timeline completa de alterações, eventos críticos, aprovações, documentos e ações dos usuários.', [
            ['label' => 'Eventos registrados', 'value' => self::countAny(['auditoria_detalhada','activity_log','prazzu_audit_timeline']), 'tone' => 'info'],
            ['label' => 'Hoje', 'value' => self::countTodayAny(['auditoria_detalhada','activity_log','prazzu_audit_timeline']), 'tone' => 'success'],
            ['label' => 'Críticos', 'value' => self::countAuditCritical(), 'tone' => 'danger'],
            ['label' => 'Usuários auditados', 'value' => self::distinctAny(['auditoria_detalhada','activity_log'],'user_id'), 'tone' => 'warning'],
        ], [
            'Antes/depois de cada alteração', 'Filtro por usuário, empresa e módulo', 'Eventos de login e permissões', 'Exportação gerencial', 'Trilha LGPD/compliance', 'Alertas para ações sensíveis'
        ], self::auditRows());
    }

    private static function riscos(): array
    {
        return self::base('riscos', 'Riscos', 'Matriz de risco operacional, documental, financeiro e de compliance com prioridade visual.', [
            ['label' => 'Críticos', 'value' => self::countCriticalItems(), 'tone' => 'danger'],
            ['label' => 'Vencidos', 'value' => self::countOverdueItems(), 'tone' => 'danger'],
            ['label' => 'SLA vencido', 'value' => self::countSlaExpired(), 'tone' => 'warning'],
            ['label' => 'Falhas de notificação', 'value' => self::countFailureNotifications(), 'tone' => 'info'],
        ], [
            'Score de risco por cliente/processo', 'Mapa de calor de vencimentos', 'Alertas automáticos', 'Dependências bloqueadoras', 'Histórico de mitigação', 'Plano de ação por risco'
        ], self::riskRows(), self::riskActions());
    }

    private static function pendencias(): array
    {
        return self::base('pendencias', 'Pendências', 'Fila operacional com subtarefas, dependências, SLA, responsáveis, prioridade e próximos passos.', [
            ['label' => 'Abertas', 'value' => self::countOpenItems(), 'tone' => 'warning'],
            ['label' => 'Vencidas', 'value' => self::countOverdueItems(), 'tone' => 'danger'],
            ['label' => 'Alta prioridade', 'value' => self::countCriticalItems(), 'tone' => 'danger'],
            ['label' => 'Sem responsável', 'value' => self::countWithoutResponsible(), 'tone' => 'info'],
        ], [
            'Subtarefas e checklist', 'Dependências entre tarefas', 'Comentários e menções', 'SLA visual', 'Automação de status', 'Kanban/Calendário/Gantt/Timeline'
        ], self::pendingRows(), self::pendingActions());
    }

    private static function cobrancas(): array
    {
        return self::base('cobrancas', 'Cobranças Inteligentes', 'Controle de cobranças com inadimplência, recuperação, bloqueio automático e integração futura Asaas.', [
            ['label' => 'Recebido', 'value' => self::money(self::sumPayments(['RECEIVED','PAYMENT_RECEIVED'])), 'tone' => 'success'],
            ['label' => 'Pendente', 'value' => self::money(self::sumPayments(['PENDING','CREATED','PAYMENT_CREATED'])), 'tone' => 'warning'],
            ['label' => 'Vencido', 'value' => self::money(self::sumPayments(['OVERDUE','PAYMENT_OVERDUE'])), 'tone' => 'danger'],
            ['label' => 'Cobranças', 'value' => self::count('pagamentos'), 'tone' => 'info'],
        ], [
            'Régua de cobrança automática', 'Bloqueio por inadimplência', 'Recuperação de pagamentos', 'Histórico financeiro por cliente', 'Alertas de vencimento', 'Preparado para integração Asaas'
        ], self::paymentRows(), self::billingActions());
    }

    private static function assinaturas(): array
    {
        return self::base('assinaturas', 'Assinaturas', 'Gestão de planos, recorrência, churn, upgrade/downgrade e retenção.', [
            ['label' => 'Assinaturas', 'value' => self::count('assinaturas'), 'tone' => 'info'],
            ['label' => 'Ativas', 'value' => self::countSubscriptionStatus(['ACTIVE','ATIVA','active','ativa']), 'tone' => 'success'],
            ['label' => 'Inadimplentes', 'value' => self::countSubscriptionStatus(['OVERDUE','INADIMPLENTE','overdue','inadimplente']), 'tone' => 'danger'],
            ['label' => 'Canceladas', 'value' => self::countSubscriptionStatus(['CANCELLED','CANCELADA','cancelled','cancelada']), 'tone' => 'warning'],
        ], [
            'Plano atual e limites', 'Renovação e cobrança recorrente', 'Churn e risco de cancelamento', 'Bloqueio automático', 'Histórico de pagamentos', 'White label por cliente'
        ], self::subscriptionRows(), self::subscriptionActions());
    }

    private static function financeiro(): array
    {
        return self::base('financeiro', 'Financeiro', 'Visão gerencial de receita, cobranças, assinaturas, inadimplência e forecast.', [
            ['label' => 'Receita recebida', 'value' => self::money(self::sumPayments(['RECEIVED','PAYMENT_RECEIVED','CONFIRMED','PAYMENT_CONFIRMED'])), 'tone' => 'success'],
            ['label' => 'A receber', 'value' => self::money(self::sumPayments(['PENDING','CREATED','PAYMENT_CREATED'])), 'tone' => 'warning'],
            ['label' => 'Vencido', 'value' => self::money(self::sumPayments(['OVERDUE','PAYMENT_OVERDUE'])), 'tone' => 'danger'],
            ['label' => 'Assinaturas', 'value' => self::count('assinaturas'), 'tone' => 'info'],
        ], [
            'MRR/ARR planejado', 'Aging de inadimplência', 'Receita por cliente', 'Previsão de caixa', 'Bloqueio e desbloqueio', 'Relatórios exportáveis'
        ], self::financeRows(), self::financeActions());
    }

    private static function relatorios(): array
    {
        return self::base('relatorios', 'Relatórios Gerenciais', 'Relatórios por cliente, operação, compliance, financeiro, SLA e produtividade.', [
            ['label' => 'Relatórios salvos', 'value' => self::count('relatorios_personalizados'), 'tone' => 'info'],
            ['label' => 'Itens monitorados', 'value' => self::count('item_controles'), 'tone' => 'success'],
            ['label' => 'KPIs críticos', 'value' => self::countCriticalItems() + self::countOverdueItems(), 'tone' => 'danger'],
            ['label' => 'Dashboards', 'value' => self::count('dashboard_widget_configuracoes'), 'tone' => 'warning'],
        ], [
            'Relatório por nicho', 'Filtros reutilizáveis', 'KPIs e SLA', 'Exportação gerencial', 'Comparativo por período', 'Alertas por relatório'
        ], self::reportRows(), self::reportActions());
    }

    private static function dashboards(): array
    {
        return self::base('dashboards', 'Dashboard Inteligente', 'Visão executiva do Prazzu com KPIs, gargalos, SLA, riscos e próximos passos.', [
            ['label' => 'Itens', 'value' => self::count('item_controles'), 'tone' => 'info'],
            ['label' => 'Atrasados', 'value' => self::countOverdueItems(), 'tone' => 'danger'],
            ['label' => 'Concluídos', 'value' => self::countDoneItems(), 'tone' => 'success'],
            ['label' => 'Clientes', 'value' => self::count('empresas'), 'tone' => 'warning'],
        ], [
            'KPIs operacionais', 'SLA visual', 'Kanban resumido', 'Riscos e pendências', 'Próximos vencimentos', 'Assistente operacional IA'
        ], self::dashboardRows(), self::dashboardActions());
    }

    private static function dashboardConfiguravel(): array
    {
        $data = self::dashboards();
        $data['title'] = 'Dashboard Configurável';
        $data['subtitle'] = 'Monte dashboards por perfil, empresa, nicho, KPI, SLA e relatório gerencial.';
        $data['features'] = ['Widgets por perfil', 'KPIs customizados', 'Filtros salvos', 'Cards financeiros', 'SLA por operação', 'Permissões por dashboard'];
        return $data;
    }

    private static function configuracoes(): array
    {
        return self::base('configuracoes', 'Configurações', 'Parâmetros enterprise para notificações, SLA, automações, portal, compliance e financeiro.', [
            ['label' => 'Configurações', 'value' => self::count('configuracoes'), 'tone' => 'info'],
            ['label' => 'E-mail ativo', 'value' => self::countWhereColumn('configuracoes','enviar_email',1), 'tone' => 'success'],
            ['label' => 'Sistema ativo', 'value' => self::countWhereColumn('configuracoes','enviar_sistema',1), 'tone' => 'warning'],
            ['label' => 'Automações', 'value' => self::count('prazzu_automation_rules'), 'tone' => 'danger'],
        ], [
            'SLA padrão por tipo', 'Templates de notificação', 'Regras de automação', 'Campos personalizados', 'Configuração do portal', 'White label e limites'
        ], self::configRows(), self::configActions());
    }

    private static function usuarios(): array
    {
        return self::base('usuarios', 'Usuários', 'Gestão de usuários, empresas, perfis, acessos, auditoria e produtividade.', [
            ['label' => 'Usuários', 'value' => self::count('users'), 'tone' => 'info'],
            ['label' => 'Admins', 'value' => self::countUsersRole(['admin','super_admin','gestor']), 'tone' => 'success'],
            ['label' => 'Verificados', 'value' => self::countUsersVerified(), 'tone' => 'warning'],
            ['label' => 'Empresas', 'value' => self::distinct('users','empresa_id'), 'tone' => 'danger'],
        ], [
            'Perfis por empresa', 'Permissões por módulo', 'Auditoria de acesso', 'Produtividade por usuário', 'Gestores e times', 'Convites e bloqueios'
        ], self::userRows(), self::userActions());
    }

    private static function permissoes(): array
    {
        return self::base('permissoes', 'Permissões Avançadas', 'Matriz de permissões por perfil, empresa, módulo, ação e tipo de dado.', [
            ['label' => 'Perfis', 'value' => self::distinct('users','role'), 'tone' => 'info'],
            ['label' => 'Usuários', 'value' => self::count('users'), 'tone' => 'success'],
            ['label' => 'Admins', 'value' => self::countUsersRole(['admin','super_admin','gestor']), 'tone' => 'warning'],
            ['label' => 'Regras ACL', 'value' => self::count('prazzu_permission_rules'), 'tone' => 'danger'],
        ], [
            'Permissão por módulo e ação', 'Escopo por empresa/cliente', 'Bloqueio financeiro', 'Auditoria de permissão', 'Perfis reutilizáveis', 'Acesso ao portal cliente'
        ], self::permissionRows(), self::permissionActions());
    }

    private static function base(string $key, string $title, string $subtitle, array $metrics = [], array $features = [], array $rows = [], array $actions = []): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'subtitle' => $subtitle,
            'metrics' => $metrics ?: [
                ['label' => 'Registros', 'value' => self::count('item_controles'), 'tone' => 'info'],
                ['label' => 'Pendências', 'value' => self::countOpenItems(), 'tone' => 'warning'],
                ['label' => 'SLA em risco', 'value' => self::countSlaRisk(), 'tone' => 'danger'],
                ['label' => 'Clientes', 'value' => self::count('empresas'), 'tone' => 'success'],
            ],
            'features' => $features,
            'rows' => $rows,
            'actions' => $actions ?: ['Criar template', 'Configurar automação', 'Exportar relatório', 'Abrir dashboard'],
            'views' => ['Lista', 'Kanban', 'Calendário', 'Timeline', 'Gantt', 'Tabela'],
            'kanban' => self::kanban(),
            'timeline' => self::timeline(),
            'kpis' => self::kpis(),
            'ai' => self::aiInsights(),
        ];
    }

    private static function kanban(): array
    {
        if (! CachedSchema::hasTable('item_controles')) return [];
        return DB::table('item_controles')
            ->select(DB::raw("COALESCE(status, 'sem_status') as status"), DB::raw('COUNT(*) as total'))
            ->groupBy('status')->orderByDesc('total')->limit(6)->get()->map(fn($i)=>(array)$i)->all();
    }

    private static function timeline(): array
    {
        if (CachedSchema::hasTable('item_controle_timelines')) {
            return DB::table('item_controle_timelines')->orderByDesc('created_at')->limit(6)->get()->map(fn($i)=>(array)$i)->all();
        }
        if (CachedSchema::hasTable('activity_log')) {
            return DB::table('activity_log')->select('description as titulo','event as status','created_at')->orderByDesc('created_at')->limit(6)->get()->map(fn($i)=>(array)$i)->all();
        }
        return [];
    }

    private static function kpis(): array
    {
        $total = max(1, self::count('item_controles'));
        return [
            ['label' => 'Conclusão', 'value' => round((self::countDoneItems() / $total) * 100) . '%'],
            ['label' => 'Atraso', 'value' => round((self::countOverdueItems() / $total) * 100) . '%'],
            ['label' => 'Risco crítico', 'value' => round((self::countCriticalItems() / $total) * 100) . '%'],
        ];
    }

    private static function aiInsights(): array
    {
        return [
            'Quais contratos vencem este mês?',
            'Quais clientes estão em atraso?',
            'Quais tarefas estão atrasadas?',
            'Quais documentos aguardam aprovação?',
        ];
    }

    private static function portalActions(): array { return ['Liberar documento', 'Enviar mensagem', 'Solicitar aprovação', 'Gerar link seguro']; }
    private static function attendanceActions(): array { return ['Novo atendimento', 'Responder com template', 'Mencionar responsável', 'Abrir SLA']; }
    private static function riskActions(): array { return ['Criar plano de ação', 'Notificar responsável', 'Mitigar risco', 'Gerar relatório']; }
    private static function pendingActions(): array { return ['Criar subtarefa', 'Adicionar dependência', 'Comentar', 'Automatizar status']; }
    private static function billingActions(): array { return ['Gerar cobrança', 'Enviar lembrete', 'Bloquear acesso', 'Recuperar inadimplência']; }
    private static function subscriptionActions(): array { return ['Alterar plano', 'Renovar assinatura', 'Analisar churn', 'Bloquear inadimplente']; }
    private static function financeActions(): array { return ['Exportar financeiro', 'Ver aging', 'Gerar forecast', 'Reconciliar pagamentos']; }
    private static function reportActions(): array { return ['Novo relatório', 'Salvar filtro', 'Exportar', 'Agendar envio']; }
    private static function dashboardActions(): array { return ['Adicionar widget', 'Filtrar período', 'Ver gargalos', 'Acionar IA']; }
    private static function configActions(): array { return ['Criar regra', 'Configurar SLA', 'Editar portal', 'Definir template']; }
    private static function userActions(): array { return ['Convidar usuário', 'Editar perfil', 'Ver auditoria', 'Bloquear acesso']; }
    private static function permissionActions(): array { return ['Criar perfil', 'Editar matriz', 'Auditar acesso', 'Aplicar por empresa']; }

    private static function clientRows(): array { return self::rowsFrom('responsaveis', ['nome','email','cargo','created_at']); }
    private static function portalRows(): array { return self::items(['portal_ativo' => 1]); }
    private static function attendanceRows(): array { return self::rowsFromAny(['item_controle_comentarios','comentarios','prazzu_task_comments'], ['comentario','comment','created_at']); }
    private static function auditRows(): array { return self::rowsFromAny(['auditoria_detalhada','activity_log','prazzu_audit_timeline'], ['description','acao','action','created_at']); }
    private static function riskRows(): array { return self::items([], true); }
    private static function pendingRows(): array { return self::items([], false, true); }
    private static function paymentRows(): array { return self::rowsFrom('pagamentos', ['status','valor','created_at','updated_at']); }
    private static function subscriptionRows(): array { return self::rowsFrom('assinaturas', ['status','valor','created_at','updated_at']); }
    private static function financeRows(): array { return self::paymentRows(); }
    private static function reportRows(): array { return self::rowsFromAny(['relatorios_personalizados','dashboard_widget_configuracoes','item_controles'], ['titulo','nome','status','created_at']); }
    private static function dashboardRows(): array { return self::items(); }
    private static function configRows(): array { return self::rowsFrom('configuracoes', ['dias_alerta','dias_lembrete','updated_at']); }
    private static function userRows(): array { return self::rowsFrom('users', ['name','email','role','created_at']); }
    private static function permissionRows(): array {
        return [
            ['titulo'=>'Admin', 'status'=>'Acesso total', 'detalhe'=>'Dashboard, documentos, financeiro e configurações'],
            ['titulo'=>'Gestor', 'status'=>'Operação e relatórios', 'detalhe'=>'Clientes, atendimentos, compliance e dashboards'],
            ['titulo'=>'Usuário', 'status'=>'Execução', 'detalhe'=>'Pendências, comentários, documentos permitidos e portal'],
        ];
    }

    private static function items(array $where = [], bool $risk = false, bool $open = false): array
    {
        if (! CachedSchema::hasTable('item_controles')) return [];
        $q = DB::table('item_controles')->leftJoin('empresas','empresas.id','=','item_controles.empresa_id')
            ->select('item_controles.id','item_controles.titulo','item_controles.tipo','item_controles.status','item_controles.prioridade','item_controles.data_vencimento','item_controles.sla_limite_em','empresas.nome_fantasia','empresas.razao_social')
            ->limit(12);
        foreach ($where as $c=>$v) if (CachedSchema::hasColumn('item_controles',$c)) $q->where('item_controles.'.$c,$v);
        if ($risk) $q->where(fn($x)=>$x->whereIn('item_controles.prioridade',['alta','critica','crítica'])->orWhereDate('item_controles.data_vencimento','<',now()->toDateString()));
        if ($open) $q->whereNotIn('item_controles.status',['concluido','concluído','finalizado']);
        return $q->orderByRaw('item_controles.data_vencimento IS NULL')->orderBy('item_controles.data_vencimento')->get()->map(fn($i)=>(array)$i)->all();
    }

    private static function rowsFrom(string $table, array $cols): array
    {
        if (! CachedSchema::hasTable($table)) return [];
        $select=['id'];
        foreach ($cols as $col) if (CachedSchema::hasColumn($table,$col)) $select[]=$col;
        return DB::table($table)->select($select)->orderByDesc(CachedSchema::hasColumn($table,'created_at')?'created_at':'id')->limit(12)->get()->map(fn($i)=>(array)$i)->all();
    }

    private static function rowsFromAny(array $tables, array $cols): array
    {
        foreach ($tables as $table) if (CachedSchema::hasTable($table)) return self::rowsFrom($table,$cols);
        return [];
    }

    private static function count(string $table, array $where=[]): int
    {
        if (! CachedSchema::hasTable($table)) return 0;
        $q=DB::table($table);
        foreach($where as $c=>$v) if(CachedSchema::hasColumn($table,$c)) $q->where($c,$v);
        return (int)$q->count();
    }
    private static function countAny(array $tables): int { foreach($tables as $t) if(CachedSchema::hasTable($t)) return self::count($t); return 0; }
    private static function countTodayAny(array $tables): int { foreach($tables as $t) if(CachedSchema::hasTable($t) && CachedSchema::hasColumn($t,'created_at')) return (int)DB::table($t)->whereDate('created_at',now()->toDateString())->count(); return 0; }
    private static function distinctAny(array $tables,string $col): int { foreach($tables as $t) if(CachedSchema::hasTable($t) && CachedSchema::hasColumn($t,$col)) return self::distinct($t,$col); return 0; }
    private static function distinct(string $table,string $col): int { return CachedSchema::hasTable($table)&&CachedSchema::hasColumn($table,$col)?(int)DB::table($table)->whereNotNull($col)->distinct($col)->count($col):0; }
    private static function countWhereColumn(string $table,string $col,$value): int { return CachedSchema::hasTable($table)&&CachedSchema::hasColumn($table,$col)?(int)DB::table($table)->where($col,$value)->count():0; }
    private static function countOpenItems(): int { return CachedSchema::hasTable('item_controles')?(int)DB::table('item_controles')->whereNotIn('status',['concluido','concluído','finalizado'])->count():0; }
    private static function countDoneItems(): int { return CachedSchema::hasTable('item_controles')?(int)DB::table('item_controles')->whereIn('status',['concluido','concluído','finalizado'])->count():0; }
    private static function countOverdueItems(): int { return CachedSchema::hasTable('item_controles')&&CachedSchema::hasColumn('item_controles','data_vencimento')?(int)DB::table('item_controles')->whereNotNull('data_vencimento')->whereDate('data_vencimento','<',now()->toDateString())->whereNotIn('status',['concluido','concluído','finalizado'])->count():0; }
    private static function countCriticalItems(): int { return CachedSchema::hasTable('item_controles')&&CachedSchema::hasColumn('item_controles','prioridade')?(int)DB::table('item_controles')->whereIn('prioridade',['alta','critica','crítica'])->count():0; }
    private static function countWithoutResponsible(): int { return CachedSchema::hasTable('item_controles')&&CachedSchema::hasColumn('item_controles','responsavel_id')?(int)DB::table('item_controles')->whereNull('responsavel_id')->count():0; }
    private static function countSlaExpired(): int { return CachedSchema::hasTable('item_controles')&&CachedSchema::hasColumn('item_controles','sla_limite_em')?(int)DB::table('item_controles')->whereNotNull('sla_limite_em')->where('sla_limite_em','<',now())->whereNull('sla_concluido_em')->count():0; }
    private static function countSlaRisk(): int { return CachedSchema::hasTable('item_controles')&&CachedSchema::hasColumn('item_controles','sla_limite_em')?(int)DB::table('item_controles')->whereNotNull('sla_limite_em')->whereBetween('sla_limite_em',[now(),now()->addHours(8)])->whereNull('sla_concluido_em')->count():0; }
    private static function countFailureNotifications(): int { return CachedSchema::hasTable('item_controles')&&CachedSchema::hasColumn('item_controles','ultima_falha_notificacao_em')?(int)DB::table('item_controles')->whereNotNull('ultima_falha_notificacao_em')->count():0; }
    private static function countExpiredPortals(): int { return CachedSchema::hasTable('item_controles')&&CachedSchema::hasColumn('item_controles','portal_expira_em')?(int)DB::table('item_controles')->where('portal_ativo',1)->whereNotNull('portal_expira_em')->where('portal_expira_em','<',now())->count():0; }
    private static function countPortalNoEmail(): int { return CachedSchema::hasTable('item_controles')&&CachedSchema::hasColumn('item_controles','portal_cliente_email')?(int)DB::table('item_controles')->where('portal_ativo',1)->whereNull('portal_cliente_email')->count():0; }
    private static function countAuditCritical(): int { return CachedSchema::hasTable('auditoria_detalhada')&&CachedSchema::hasColumn('auditoria_detalhada','nivel')?(int)DB::table('auditoria_detalhada')->whereIn('nivel',['critico','crítico','critical'])->count():0; }
    private static function countOverduePayments(): int { return CachedSchema::hasTable('pagamentos')&&CachedSchema::hasColumn('pagamentos','status')?(int)DB::table('pagamentos')->whereIn('status',['OVERDUE','PAYMENT_OVERDUE'])->count():0; }
    private static function sumPayments(array $statuses): float { return CachedSchema::hasTable('pagamentos')&&CachedSchema::hasColumn('pagamentos','valor')?(float)DB::table('pagamentos')->whereIn('status',$statuses)->sum('valor'):0.0; }
    private static function countSubscriptionStatus(array $statuses): int { return CachedSchema::hasTable('assinaturas')&&CachedSchema::hasColumn('assinaturas','status')?(int)DB::table('assinaturas')->whereIn('status',$statuses)->count():0; }
    private static function countUsersRole(array $roles): int { return CachedSchema::hasTable('users')&&CachedSchema::hasColumn('users','role')?(int)DB::table('users')->whereIn('role',$roles)->count():0; }
    private static function countUsersVerified(): int { return CachedSchema::hasTable('users')&&CachedSchema::hasColumn('users','email_verified_at')?(int)DB::table('users')->whereNotNull('email_verified_at')->count():0; }
    private static function money(float $v): string { return 'R$ '.number_format($v,2,',','.'); }
}
