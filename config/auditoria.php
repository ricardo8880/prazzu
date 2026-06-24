<?php

use App\Models\AiMarketComment;
use App\Models\AiMarketSource;
use App\Models\AiProductImprovementResolution;
use App\Models\AlertaEnviado;
use App\Models\Anexo;
use App\Models\AnexoItem;
use App\Models\Assinatura;
use App\Models\Atendimento;
use App\Models\AtendimentoInteracao;
use App\Models\AuditoriaDetalhada;
use App\Models\CategoriaItemControle;
use App\Models\CategoriaItemControleChecklistTemplate;
use App\Models\ClientePortalUser;
use App\Models\Comentario;
use App\Models\Configuracao;
use App\Models\CrmCliente;
use App\Models\DashboardWidgetConfiguracao;
use App\Models\Empresa;
use App\Models\FluxoOperacional;
use App\Models\FluxoOperacionalEtapa;
use App\Models\FluxoOperacionalExecucao;
use App\Models\HistoricoItem;
use App\Models\ItemControle;
use App\Models\ItemControleAnexo;
use App\Models\ItemControleAprovacao;
use App\Models\ItemControleAssinatura;
use App\Models\ItemControleChecklist;
use App\Models\ItemControleComentario;
use App\Models\ItemControleNotificacaoLog;
use App\Models\ItemControleTag;
use App\Models\ItemControleTimeline;
use App\Models\LogSistema;
use App\Models\NotificacaoInterna;
use App\Models\Pagamento;
use App\Models\PortalClienteToken;
use App\Models\PortalDocumento;
use App\Models\PortalMensagem;
use App\Models\PortalSolicitacao;
use App\Models\PrazzuAutomationRule;
use App\Models\PrazzuBillingRule;
use App\Models\PrazzuClientPortalMessage;
use App\Models\PrazzuCustomField;
use App\Models\PrazzuDependency;
use App\Models\PrazzuDocumentVersion;
use App\Models\PrazzuPermission;
use App\Models\PrazzuPermissionAudit;
use App\Models\PrazzuPermissionRule;
use App\Models\PrazzuRole;
use App\Models\PrazzuSlaRule;
use App\Models\PrazzuSubtask;
use App\Models\PrazzuTaskComment;
use App\Models\PrazzuTaskDependency;
use App\Models\PrazzuTaskSubtask;
use App\Models\PrazzuTemplate;
use App\Models\PrazzuUserPermission;
use App\Models\PrazzuUserRole;
use App\Models\RelatorioPersonalizado;
use App\Models\RelatorioPersonalizadoColuna;
use App\Models\RelatorioPersonalizadoFiltro;
use App\Models\Responsavel;
use App\Models\SugestaoMelhoria;
use App\Models\User;

return [
    'global_enabled' => env('AUDITORIA_GLOBAL_ENABLED', true),
    'manual_events_enabled' => env('AUDITORIA_MANUAL_EVENTS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Política de cobertura
    |--------------------------------------------------------------------------
    |
    | all_except_excluded = audita todos os models Eloquent, exceto técnicos/logs.
    | only_listed = audita apenas os models em auditable_models.
    |
    */
    'model_policy' => env('AUDITORIA_MODEL_POLICY', 'all_except_excluded'),

    /* Lista completa dos models de negócio conhecidos no projeto. */
    'auditable_models' => [
        AiProductImprovementResolution::class,
        Anexo::class,
        AnexoItem::class,
        Assinatura::class,
        Atendimento::class,
        AtendimentoInteracao::class,
        CategoriaItemControle::class,
        CategoriaItemControleChecklistTemplate::class,
        ClientePortalUser::class,
        Comentario::class,
        Configuracao::class,
        CrmCliente::class,
        DashboardWidgetConfiguracao::class,
        Empresa::class,
        FluxoOperacional::class,
        FluxoOperacionalEtapa::class,
        FluxoOperacionalExecucao::class,
        ItemControle::class,
        ItemControleAnexo::class,
        ItemControleAprovacao::class,
        ItemControleAssinatura::class,
        ItemControleChecklist::class,
        ItemControleComentario::class,
        ItemControleTag::class,
        Pagamento::class,
        PortalClienteToken::class,
        PortalDocumento::class,
        PortalMensagem::class,
        PortalSolicitacao::class,
        PrazzuAutomationRule::class,
        PrazzuBillingRule::class,
        PrazzuClientPortalMessage::class,
        PrazzuCustomField::class,
        PrazzuDependency::class,
        PrazzuDocumentVersion::class,
        PrazzuPermission::class,
        PrazzuPermissionRule::class,
        PrazzuRole::class,
        PrazzuSlaRule::class,
        PrazzuSubtask::class,
        PrazzuTaskComment::class,
        PrazzuTaskDependency::class,
        PrazzuTaskSubtask::class,
        PrazzuTemplate::class,
        PrazzuUserPermission::class,
        PrazzuUserRole::class,
        RelatorioPersonalizado::class,
        RelatorioPersonalizadoColuna::class,
        RelatorioPersonalizadoFiltro::class,
        Responsavel::class,
        SugestaoMelhoria::class,
        User::class,
    ],

    /* Models que não podem ficar sem cobertura. O comando auditoria:cobertura falha se algum deles estiver descoberto. */
    'required_models' => [
        Anexo::class,
        Assinatura::class,
        Atendimento::class,
        AtendimentoInteracao::class,
        ClientePortalUser::class,
        Comentario::class,
        Configuracao::class,
        Empresa::class,
        ItemControle::class,
        ItemControleAnexo::class,
        ItemControleAprovacao::class,
        ItemControleAssinatura::class,
        ItemControleChecklist::class,
        ItemControleComentario::class,
        Pagamento::class,
        PortalDocumento::class,
        PortalMensagem::class,
        PortalSolicitacao::class,
        PrazzuPermission::class,
        PrazzuPermissionRule::class,
        PrazzuRole::class,
        PrazzuUserPermission::class,
        PrazzuUserRole::class,
        Responsavel::class,
        User::class,
    ],

    'excluded_models' => [
        AuditoriaDetalhada::class,
        LogSistema::class,
        PrazzuPermissionAudit::class,
        HistoricoItem::class,
        ItemControleTimeline::class,
        ItemControleNotificacaoLog::class,
        NotificacaoInterna::class,
        AlertaEnviado::class,
        AiMarketComment::class,
        AiMarketSource::class,
        \Spatie\Activitylog\Models\Activity::class,
    ],

    'excluded_tables' => [
        'auditoria_detalhada',
        'activity_log',
        'logs_sistema',
        'prazzu_permission_audits',
        'historico_itens',
        'item_controle_timeline',
        'item_controle_notificacao_logs',
        'notificacoes_internas',
        'alertas_enviados',
        'ai_market_comments',
        'ai_market_sources',
        'audit_timeline',
        'cache',
        'cache_locks',
        'failed_jobs',
        'jobs',
        'job_batches',
        'migrations',
        'password_reset_tokens',
        'sessions',
    ],

    'ignored_fields' => [
        'created_at',
        'updated_at',
        'deleted_at',
        'email_verified_at',
        'remember_token',
        'last_access_at',
        'last_login_at',
        'last_seen_at',
    ],

    'sensitive_fields' => [
        'password',
        'senha',
        'token',
        'api_key',
        'secret',
        'webhook_token',
        'asaas_api_key',
        'asaas_webhook_token',
        'client_secret',
        'access_token',
        'refresh_token',
    ],

    'protected_value' => '[valor protegido]',
    'max_value_length' => 4000,
    'default_level' => 'info',
];
