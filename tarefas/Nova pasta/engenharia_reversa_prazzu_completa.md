
# Engenharia reversa cirúrgica do Prazzu — validações pré-lotes

Base: projeto `prazzu.zip` + banco atual `prazzu.sql`. Objetivo: entender o sistema antes de alterar código e preparar os lotes de estabilização para produção/piloto.

## 1. Resumo executivo

- Banco atual: **104 tabelas** detectadas no SQL enviado.
- Código analisado: **446 arquivos PHP próprios** sem vendor/node/storage cache.
- Models: **62**; Pages Filament: **40**; Resources Filament principais: **14**; Services: **42**; Controllers: **11**; Commands: **10**.
- Veredito: agora há material suficiente para planejar os lotes com segurança, mas antes de codar ainda precisa rodar a validação dinâmica no ambiente com PHP correto, porque neste ambiente o Artisan quebra sem `ext-mbstring`.

## 2. Banco de dados atual

Tabelas principais detectadas por domínio:
- **Auditoria / Logs** (4): activity_log, auditoria_detalhada, audit_timeline, logs_sistema
- **SLA / Notificações** (5): alerta_enviados, notifications, prazzu_sla_policies, prazzu_sla_rules, sla_rules
- **ItemControle / Operação** (15): anexos, comentarios, historico_items, item_controles, item_controle_alertas, item_controle_anexos, item_controle_aprovacoes, item_controle_assinaturas, item_controle_checklists, item_controle_comentarios, item_controle_notificacao_logs, item_controle_tags, item_controle_tag_relations, item_controle_timeline, item_controle_timelines
- **Documentos / Storage** (5): anexo_items, document_versions, file_retention_events, file_retention_policies, prazzu_document_versions
- **Financeiro / Asaas** (10): assinaturas, financeiro_assinaturas_cliente, financeiro_clientes, financeiro_cobrancas, financeiro_gateway_integracoes, financeiro_recebimentos, financeiro_webhook_logs, pagamentos, prazzu_billing_locks, prazzu_billing_rules
- **CRM / Atendimentos** (5): atendimentos, atendimento_interacoes, crm_clientes, crm_historicos, crm_pendencias
- **Outras / Sistema** (41): automation_rules, backup_prazzu_client_messages, cache, cache_locks, categorias_item_controle, categoria_item_controle_checklist_templates, configuracoes, dashboard_widget_configuracoes, failed_jobs, fluxos_operacionais, fluxos_operacionais_etapas, fluxos_operacionais_execucoes, jobs, job_batches, migrations, notificacoes_internas, password_reset_tokens, prazzu_automation_executions, prazzu_automation_rules, prazzu_custom_fields, prazzu_custom_field_values, prazzu_dependencies, prazzu_subtasks, prazzu_task_comments, prazzu_task_dependencies, prazzu_task_subtasks, prazzu_templates, prazzu_time_entries, prazzu_time_tracking, relatorios_personalizados...
- **Portal Cliente** (8): backup_client_portal_messages, cliente_portal_password_reset_tokens, cliente_portal_users, portal_cliente_tokens, portal_documentos, portal_mensagens, portal_solicitacoes, prazzu_client_portal_messages
- **Usuários / Permissões / Empresa** (11): empresas, prazzu_permissions, prazzu_permission_audits, prazzu_permission_rules, prazzu_roles, prazzu_teams, prazzu_team_user, prazzu_user_permissions, prazzu_user_roles, responsaveis, users

### 2.1 Models sem tabela no banco atual

| Model | Tabela esperada | Arquivo |
| --- | --- | --- |
| AiMarketComment | ai_market_comments | app/Models/AiMarketComment.php |
| AiMarketSource | ai_market_sources | app/Models/AiMarketSource.php |
| AiProductImprovementResolution | ai_product_improvement_resolutions | app/Models/AiProductImprovementResolution.php |

### 2.2 Observações de schema

- `user_sidebar_favorites` existe no banco atual, então a funcionalidade de favoritos da sidebar tem base de tabela.
- Ainda existem famílias duplicadas/legadas que precisam decisão antes de mexer: `sla_rules` vs `prazzu_sla_rules`, `task_comments` vs `prazzu_task_comments`, `document_versions` vs `prazzu_document_versions`, `automation_rules` vs `prazzu_automation_rules`, `item_controle_timeline` vs `item_controle_timelines`.
- Há muito uso de `Schema::hasTable/hasColumn` ou `CachedSchema`: isso reduz crashes, mas mascara schema incompleto. Top arquivos:
| Qtd | Arquivo |
| --- | --- |
| 50 | app/Support/PrazzuEnterprisePageData.php |
| 47 | app/Services/HomeDashboardService.php |
| 42 | app/Support/GestaoDocumentalEnterpriseData.php |
| 38 | app/Support/ComplianceModuleData.php |
| 38 | app/Filament/Pages/Armazenamento.php |
| 31 | app/Http/Controllers/PortalClientePublicoController.php |
| 29 | app/Support/PortalClienteData.php |
| 27 | app/Filament/Pages/CentralAprovacoes.php |
| 26 | app/Console/Commands/DiagnosticoProducao.php |
| 25 | app/Filament/Pages/Permissoes.php |
| 22 | app/Support/AtendimentosData.php |
| 18 | app/Services/StorageRetentionService.php |
| 18 | app/Filament/Pages/Clientes.php |
| 18 | app/Filament/Pages/CentroOperacional.php |
| 18 | app/Console/Commands/ProcessarRoadmapPrazzuInterno.php |

## 3. Rotas reais

As rotas se concentram em: portal cliente autenticado, portal público por token, rotas admin auxiliares, billing e webhooks.
| Arquivo | Linha | Rota/declaração |
| --- | --- | --- |
| routes/web.php | 25 | Route::get('/portal-cliente/cadastro/{token}', [PortalClienteAuthController::class, 'cadastroForm']) |
| routes/web.php | 29 | Route::post('/portal-cliente/cadastro/{token}', [PortalClienteAuthController::class, 'cadastrar']) |
| routes/web.php | 34 | Route::middleware(['guest:portal_cliente'])->group(function (): void { |
| routes/web.php | 35 | Route::get('/portal-cliente/login', [PortalClienteAuthController::class, 'loginForm']) |
| routes/web.php | 38 | Route::post('/portal-cliente/login', [PortalClienteAuthController::class, 'login']) |
| routes/web.php | 43 | Route::post('/portal-cliente/logout', [PortalClienteAuthController::class, 'logout']) |
| routes/web.php | 47 | Route::middleware(['guest:portal_cliente'])->group(function (): void { |
| routes/web.php | 48 | Route::get('/portal-cliente/esqueci-senha', [PortalClientePasswordController::class, 'forgotForm']) |
| routes/web.php | 51 | Route::post('/portal-cliente/esqueci-senha', [PortalClientePasswordController::class, 'sendResetLink']) |
| routes/web.php | 55 | Route::get('/portal-cliente/resetar-senha/{token}', [PortalClientePasswordController::class, 'resetForm']) |
| routes/web.php | 59 | Route::post('/portal-cliente/resetar-senha/{token}', [PortalClientePasswordController::class, 'resetPassword']) |
| routes/web.php | 64 | Route::get('/portal-cliente/convite/{token}', [PortalClientePasswordController::class, 'conviteForm']) |
| routes/web.php | 68 | Route::post('/portal-cliente/convite/{token}', [PortalClientePasswordController::class, 'aceitarConvite']) |
| routes/web.php | 76 | Route::middleware(['auth'])->group(function (): void { |
| routes/web.php | 79 | Route::get('/admin/session/keepalive', function () { |
| routes/web.php | 83 | Route::post('/admin/portal-cliente/debug-log', function (Request $request) { |
| routes/web.php | 109 | Route::post('/admin/portal-cliente/mensagem-visualizada', function (Request $request) { |
| routes/web.php | 134 | Route::get('/admin/portal-cliente/mensagens-novas', function (Request $request) { |
| routes/web.php | 181 | Route::get('/admin/portal-cliente/mensagem', function () { |
| routes/web.php | 187 | Route::post('/admin/portal-cliente/mensagem', function (Request $request) { |
| routes/web.php | 295 | Route::get('/auth/white-label/sso', [WhiteLabelSsoController::class, 'redirect']) |
| routes/web.php | 299 | Route::get('/', function () { |
| routes/web.php | 303 | Route::get('/planos', function () { |
| routes/web.php | 307 | Route::get('/item-controles/{itemControle}/pdf', function ( |
| routes/web.php | 314 | Route::get('/cadastro-empresa', [PublicEmpresaCadastroController::class, 'create']) |
| routes/web.php | 317 | Route::post('/cadastro-empresa', [PublicEmpresaCadastroController::class, 'store']) |
| routes/web.php | 320 | Route::middleware([ValidatePortalPublicAccess::class])->group(function (): void { |
| routes/web.php | 321 | Route::get('/portal/cliente/{token}', [PortalClientePublicoController::class, 'show']) |
| routes/web.php | 325 | Route::get('/portal/cliente/{token}/mensagens-novas', [PortalClientePublicoController::class, 'mensagensNovas']) |
| routes/web.php | 330 | Route::post('/portal/cliente/{token}/mensagem', [PortalClientePublicoController::class, 'mensagem']) |
| routes/web.php | 335 | Route::post('/portal/cliente/{token}/mensagem-visualizada', [PortalClientePublicoController::class, 'mensagemVisualizada']) |
| routes/web.php | 340 | Route::post('/portal/cliente/{token}/debug-log', [PortalClientePublicoController::class, 'debugLog']) |
| routes/web.php | 345 | Route::post('/portal/cliente/{token}/solicitacao', [PortalClientePublicoController::class, 'solicitacao']) |
| routes/web.php | 350 | Route::post('/portal/cliente/{token}/solicitacoes', [PortalClientePublicoController::class, 'solicitacao']) |
| routes/web.php | 355 | Route::post('/portal/cliente/{token}/pendencia/{solicitacao}/responder', [PortalClientePublicoController::class, 'responderPendencia']) |
| routes/web.php | 361 | Route::get('/portal/itens/{token}', [PortalItemControleController::class, 'show']) |
| routes/web.php | 365 | Route::post('/portal/itens/{token}/assinar', [PortalItemControleController::class, 'assinar']) |
| routes/web.php | 369 | Route::post('/portal/itens/{token}/mensagem', [PortalItemControleController::class, 'mensagem']) |
| routes/web.php | 373 | Route::post('/portal/itens/{token}/documentos', [PortalItemControleController::class, 'enviarDocumento']) |
| routes/web.php | 379 | Route::get('/admin/busca-global', GlobalSearchController::class) |
| routes/web.php | 383 | Route::post('/admin/auditoria/debug-log', function (\Illuminate\Http\Request $request) { |
| routes/web.php | 415 | Route::get('/admin/auditoria-detalhada/exportar', AuditoriaDetalhadaExportController::class) |
| routes/web.php | 419 | Route::get('/billing/sucesso', [BillingController::class, 'sucesso']) |
| routes/web.php | 422 | Route::get('/billing/bloqueado', [BillingController::class, 'bloqueado']) |
| routes/web.php | 426 | Route::get('/billing/empresas/{empresa}/pagar', [BillingController::class, 'pagar']) |
| routes/web.php | 430 | Route::post('/billing/assinaturas/{assinatura}/cancelar', [BillingController::class, 'cancelar']) |
| routes/web.php | 434 | Route::post('/webhooks/asaas', AsaasWebhookController::class) |
| routes/web.php | 437 | Route::post('/asaas/webhook', AsaasWebhookController::class) |

### Riscos nas rotas

- `/webhooks/asaas` e `/asaas/webhook` apontam para o mesmo controller. Precisa decidir se mantém aliases ou padroniza um endpoint oficial.
- Existem rotas públicas com token para portal de cliente e item. O middleware `ValidatePortalPublicAccess` ajuda contra token malformado/debug, mas a autorização real precisa ficar nos controllers.
- Existem closures admin em `routes/web.php` para chat/debug/mensagens. Precisam revisar autorização e tenant em cada query.

## 4. Pages/Resources e permissões

Classificação: `SEM canAccess explícito` depende de padrão Filament ou de lógica indireta; `auth()->check()`/`return true` é fraco para produção em página sensível.
| Grupo | Label | Classe | canAccess detectado | Arquivo |
| --- | --- | --- | --- | --- |
|  | clientes.blade | clientes.blade | SEM canAccess explícito | app/Filament/Pages/clientes.blade.php |
| Administração | Administração | CentralAdministrativa | $user = Auth::user(); if (! $user) { return false; | app/Filament/Pages/CentralAdministrativa.php |
| Administração | Equipes | Equipes | $user = Auth::user(); return (bool) $user && ($user->isSuperAdmin() // static::canAdvancedPermission('governanca.view')); | app/Filament/Pages/Equipes.php |
| Administração | Perfis e Permissões | Permissoes | return static::canAdvancedPermission('governanca.view'); | app/Filament/Pages/Permissoes.php |
| Administração | Usuários | Usuarios | return static::canAdvancedPermission('governanca.view'); | app/Filament/Pages/Usuarios.php |
| Cadastros e Configurações | Carteira de Clientes | Clientes | return static::canAdvancedPermission('clientes.view'); | app/Filament/Pages/Clientes.php |
| Cadastros e Configurações | Dados do Escritório | EmpresaAdministrativa | $user = Auth::user(); if (! $user) { return false; | app/Filament/Pages/EmpresaAdministrativa.php |
| Cadastros e Configurações | Parâmetros do Escritório | Configuracoes | $user = auth()->user(); return $user?->isSuperAdmin() === true // $user?->isAdminEmpresa() === true; | app/Filament/Pages/Configuracoes.php |
| Clientes e Atendimentos | Clientes e Atendimentos | Atendimentos | return static::canAdvancedPermission('atendimentos.view'); | app/Filament/Pages/Atendimentos.php |
| Clientes e Atendimentos | Portal do Cliente | PortalCliente | return PrazzuAccessControl::canUsePortalCliente(); | app/Filament/Pages/PortalCliente.php |
| Configurações | Meus Atalhos | MeusAtalhos | SEM canAccess explícito | app/Filament/Pages/MeusAtalhos.php |
| Conta | Conta | PlanosBilling | $user = auth()->user(); return $user?->isSuperAdmin() === true // static::canAdvancedPermission('governanca.view'); | app/Filament/Pages/PlanosBilling.php |
| Conta | Gestão de Planos | GestaoPlanos | $user = auth()->user(); return $user?->isSuperAdmin() === true // static::canAdvancedPermission('governanca.view'); | app/Filament/Pages/GestaoPlanos.php |
| Conta | Onboarding | Onboarding | return auth()->check(); | app/Filament/Pages/Onboarding.php |
| Conta | White Label | WhiteLabel | SEM canAccess explícito | app/Filament/Pages/WhiteLabel.php |
| Contratos e Financeiro | Assinaturas | Assinaturas | return auth()->check(); | app/Filament/Pages/Assinaturas.php |
| Contratos e Financeiro | Cobranças | ControleCobrancas | return static::canAdvancedPermission('cobrancas.view'); | app/Filament/Pages/ControleCobrancas.php |
| Contratos e Financeiro | Contratos | Contratos | return true; } protected function getViewData(): array { return ['resumo' => $this->resumo(), 'contratos' => $this->contratos()]; | app/Filament/Pages/Contratos.php |
| Contratos e Financeiro | Financeiro | Financeiro | return static::canAdvancedPermission('financeiro.view'); | app/Filament/Pages/Financeiro.php |
| Documentos e Modelos | Armazenamento | Armazenamento | return static::canAdvancedPermission('armazenamento.view'); | app/Filament/Pages/Armazenamento.php |
| Documentos e Modelos | Documentos | Documentos | return static::canAdvancedPermission('documentos.view'); | app/Filament/Pages/Documentos.php |
| Documentos e Modelos | Gestão Documental | GestaoDocumentalEnterprise | SEM canAccess explícito | app/Filament/Pages/GestaoDocumentalEnterprise.php |
| Governança | Saúde do Sistema | SystemHealthDashboard | return auth()->check(); | app/Filament/Pages/SystemHealthDashboard.php |
| Operação | Aprovações | CentralAprovacoes | return static::canAdvancedPermission('aprovacoes.view') && PrazzuAccessControl::canUseAprovacoes(PrazzuAccessControl::user()); | app/Filament/Pages/CentralAprovacoes.php |
| Operação | Checklists | Checklist | return PrazzuAccessControl::canUseChecklist(); | app/Filament/Pages/Checklist.php |
| Operação | Cronograma Gantt | Gantt | return auth()->check(); | app/Filament/Pages/Gantt.php |
| Operação | Kanban | Kanban | SEM canAccess explícito | app/Filament/Pages/Kanban.php |
| Operação | Mesa Operacional | CentroOperacional | SEM canAccess explícito | app/Filament/Pages/CentroOperacional.php |
| Operação | Projetos | Projetos | SEM canAccess explícito | app/Filament/Pages/Projetos.php |
| Operação | Timeline Operacional | TimelineOperacional | return PrazzuAccessControl::canUseTimeline(); | app/Filament/Pages/TimelineOperacional.php |
| Pendências e Prazos | Calendário Operacional | Calendario | SEM canAccess explícito | app/Filament/Pages/Calendario.php |
| Pendências e Prazos | Pendências | Pendencias | return auth()->check(); | app/Filament/Pages/Pendencias.php |
| Pendências e Prazos | SLA e Prazos | SlaPrazos | SEM canAccess explícito | app/Filament/Pages/SlaPrazos.php |
| Relatórios e Auditoria | Auditoria e Rastreabilidade | Auditoria | return app(AuditoriaAccessService::class)->canView(auth()->user()); | app/Filament/Pages/Auditoria.php |
| Relatórios e Auditoria | Central de Logs | AuditoriaAdministrativa | return app(AuditoriaAccessService::class)->canView(auth()->user()); | app/Filament/Pages/AuditoriaAdministrativa.php |
| Relatórios e Auditoria | Conformidade Interna | ComplianceInterno | return auth()->check(); | app/Filament/Pages/ComplianceInterno.php |
| Relatórios e Auditoria | Relatórios Operacionais | Relatorios | return static::canAdvancedPermission('relatorios.view'); | app/Filament/Pages/Relatorios.php |
| Relatórios e Auditoria | Riscos e Evidências | Riscos | return auth()->check(); | app/Filament/Pages/Riscos.php |
| Visão Geral | Home | Home | SEM canAccess explícito | app/Filament/Pages/Home.php |
| Visão Geral | Resumo Executivo | DashboardExecutivoContabil | return Filament::auth()->check(); | app/Filament/Pages/DashboardExecutivoContabil.php |

### 4.1 Pages com atenção imediata

- **SystemHealthDashboard**: `auth()->check()`; deve ser super admin/suporte.
- **Riscos**: `auth()->check()`; deve ficar oculto/restrito no MVP.
- **ComplianceInterno**: `auth()->check()`; deve ficar oculto/restrito no MVP.
- **Gantt**: `auth()->check()`; deve ficar oculto/restrito no MVP.
- **Contratos**: `return true`; não pode permanecer assim.
- **Assinaturas/Pendências/Onboarding**: usam autenticação genérica; precisam regra por plano/perfil/empresa.
- Pages sem `canAccess` explícito precisam validação de classe pai/trait e teste por URL direta.

### 4.2 Resources

| Grupo | Resource | Model | canAccess | Arquivo |
| --- | --- | --- | --- | --- |
| Administração | UserResource | User::class | SEM canAccess explícito | app/Filament/Resources/Users/UserResource.php |
| Cadastros e Configurações | ConfiguracaoResource | Configuracao::class | SEM canAccess explícito | app/Filament/Resources/Configuracoes/ConfiguracaoResource.php |
| Cadastros e Configurações | EmpresaResource | Empresa::class | SEM canAccess explícito | app/Filament/Resources/Empresas/EmpresaResource.php |
| Cadastros e Configurações | PrazzuTemplateResource | PrazzuTemplate::class | SEM canAccess explícito | app/Filament/Resources/PrazzuTemplates/PrazzuTemplateResource.php |
| Cadastros e Configurações | ResponsavelResource | Responsavel::class | SEM canAccess explícito | app/Filament/Resources/Responsaveis/ResponsavelResource.php |
| Configurações | CategoriaItemControleResource | CategoriaItemControle::class | SEM canAccess explícito | app/Filament/Resources/CategoriaItemControles/CategoriaItemControleResource.php |
| Configurações | ItemControleTagResource | ItemControleTag::class | SEM canAccess explícito | app/Filament/Resources/ItemControleTags/ItemControleTagResource.php |
| Configurações | PrazzuAutomationRuleResource | PrazzuAutomationRule::class | return auth()->check() && CachedSchema::hasTable('prazzu_automation_rules'); | app/Filament/Resources/PrazzuAutomationRules/PrazzuAutomationRuleResource.php |
| Configurações | SugestaoMelhoriaResource | SugestaoMelhoria::class | SEM canAccess explícito | app/Filament/Resources/SugestaoMelhorias/SugestaoMelhoriaResource.php |
| Operação | CategoriaChecklistTemplateResource | CategoriaItemControleChecklistTemplate::class | SEM canAccess explícito | app/Filament/Resources/CategoriaChecklistTemplateResource.php |
| Operação | FluxoOperacionalResource | FluxoOperacional::class | SEM canAccess explícito | app/Filament/Resources/FluxosOperacionais/FluxoOperacionalResource.php |
| Operação | ItemControleResource | ItemControle::class | SEM canAccess explícito | app/Filament/Resources/ItemControles/ItemControleResource.php |
| Relatórios e Auditoria | AuditoriaDetalhadaResource | AuditoriaDetalhada::class | return app(AuditoriaAccessService::class)->canView(Filament::auth()->user()); | app/Filament/Resources/AuditoriaDetalhada/AuditoriaDetalhadaResource.php |
| Relatórios e Auditoria | RelatorioPersonalizadoResource | RelatorioPersonalizado::class | SEM canAccess explícito | app/Filament/Resources/RelatoriosPersonalizados/RelatorioPersonalizadoResource.php |

## 5. Fluxos reais do sistema


### 5.1 Fluxo central ItemControle

Fonte principal: `app/Models/ItemControle.php`, `app/Filament/Resources/ItemControles/*`, `app/Services/ItemControleStatusService.php`, `ItemControleFluxoService`, `PrazzuSlaService`, `CentroOperacionalService`.
Fluxo esperado validado no código: criação de item com empresa ativa → responsável/categoria/status/vencimento → exibição em Mesa/Pendências/SLA/Calendário → ações de portal/documento/aprovação → timeline/auditoria → relatórios.
Ponto positivo: existe `ItemControleStatusService` para registrar assinatura, documento, mensagem e solicitação do portal com timeline segura.
Risco: ainda há updates diretos em vários arquivos; toda alteração de status/prazo/responsável deveria passar por camada única ou registrar auditoria/timeline.

### 5.2 Portal do Cliente

Arquivos-chave: `PortalClienteAuthController`, `PortalClientePasswordController`, `PortalClientePublicoController`, `PortalItemControleController`, `ValidatePortalPublicAccess`, views em `resources/views/portal/cliente`.
Fluxos: cadastro por token, login, reset/convite, portal público por token da empresa, portal por token de item, mensagens, solicitações, resposta de pendência, assinatura e upload de documento.
Pontos positivos: há throttle em auth/reset, middleware anti-debug/token inválido, headers de segurança, validação de upload e integração com `ItemControleStatusService`.
Riscos: anexos públicos, token público, consultas por token precisam teste de tenant, enumeração e expiração. Mensagem/anexo deve gerar alerta interno rastreável.

### 5.3 Asaas / Financeiro

Arquivos-chave: `AsaasService`, `AsaasWebhookController`, `ReconciliarAssinaturasAsaas`, `CheckEmpresaPagamento`, `BillingController`, Pages `ControleCobrancas`, `Financeiro`, `Assinaturas`, `PlanosBilling`.
Pontos positivos: webhook valida token, `Pagamento::updateOrCreate` usa `gateway_payment_id`, reconciliação roda hourly, bloqueio respeita super admin.
Riscos: o SQL tem `financeiro_webhook_logs`, mas o controller registra em logs/auditoria manual e não parece persistir payload bruto nessa tabela. Webhook duplicado, fora de ordem e sem assinatura local precisam testes.

### 5.4 SLA / Notificações / Scheduler

Commands e agenda detectados:
| Comando agendado | Frequência/configuração |
| --- | --- |
| item-controle:notificar-vencimentos | ->dailyAt('08:00') ->withoutOverlapping() ->runInBackground() |
| itens-controle:atualizar-vencidos | ->dailyAt('00:05') ->withoutOverlapping() ->runInBackground() |
| asaas:reconciliar-assinaturas --limit=100 | ->hourly() ->withoutOverlapping() ->runInBackground() |
| prazzu:processar-roadmap-interno --silent-notifications | ->hourly() ->withoutOverlapping() ->runInBackground() |
| centro-operacional:processar --silent-notifications | ->hourly() ->withoutOverlapping() ->runInBackground() |
| storage:processar-retencao --limit=100 | ->dailyAt('02:20') ->withoutOverlapping() ->runInBackground() |
Commands existentes:
| Classe | Signature | Arquivo |
| --- | --- | --- |
| AtualizarItensControleVencidos | itens-controle:atualizar-vencidos | app/Console/Commands/AtualizarItensControleVencidos.php |
| AtualizarStatusItensVencidos | itens:atualizar-vencidos | app/Console/Commands/AtualizarStatusItensVencidos.php |
| AuditoriaCoberturaCommand | auditoria:cobertura {--somente-problemas : Mostra apenas models obrigatórios sem cobertura} | app/Console/Commands/AuditoriaCoberturaCommand.php |
| DiagnosticoProducao | sistemrh:diagnostico         {--limite=500 : Quantidade máxima de registros analisados em consultas de amostragem pesada}         {--arquivo= : Caminho opcional para salvar o relatório em JSON}         {--sem-arquivos : Não validar existência física de anexos no disco public}         {--somente-erros : Exibir no console apenas erros e avisos} | app/Console/Commands/DiagnosticoProducao.php |
| NotificarVencimentoItensControle | item-controle:notificar-vencimentos | app/Console/Commands/NotificarVencimentoItensControle.php |
| ProcessarCentroOperacional | centro-operacional:processar {--silent-notifications : Evita excesso de saída no scheduler} | app/Console/Commands/ProcessarCentroOperacional.php |
| ProcessarRetencaoArquivos | storage:processar-retencao {--limit=100 : Quantidade máxima de arquivos processados por execução} | app/Console/Commands/ProcessarRetencaoArquivos.php |
| ProcessarRoadmapPrazzuInterno | prazzu:processar-roadmap-interno {--silent-notifications : Não cria notificações internas de risco/SLA} | app/Console/Commands/ProcessarRoadmapPrazzuInterno.php |
| ReconciliarAssinaturasAsaas | asaas:reconciliar-assinaturas {--limit=50 : Quantidade máxima de assinaturas sincronizadas nesta execução} | app/Console/Commands/ReconciliarAssinaturasAsaas.php |
| SystemHealthSnapshot | sistemrh:saude         {--limite=500 : Quantidade máxima de registros analisados em consultas pesadas}         {--json= : Caminho opcional para salvar o relatório JSON} | app/Console/Commands/SystemHealthSnapshot.php |
Riscos: existem dois comandos parecidos de vencidos (`itens-controle:atualizar-vencidos` e `itens:atualizar-vencidos`); precisa decidir oficial e garantir que scheduler usa o correto. Scheduler só funciona em produção se cron/worker estiver configurado.

### 5.5 Documentos / Storage

Arquivos com uso de Storage/upload/download/FileUpload:
- `app/Console/Commands/DiagnosticoProducao.php`
- `app/Filament/Pages/Armazenamento.php`
- `app/Filament/Pages/Atendimentos.php`
- `app/Filament/Pages/Auditoria.php`
- `app/Filament/Pages/Documentos.php`
- `app/Filament/Pages/PortalCliente.php`
- `app/Filament/Pages/Relatorios.php`
- `app/Filament/Pages/WhiteLabel.php`
- `app/Filament/Resources/ItemControles/Pages/EditItemControle.php`
- `app/Filament/Resources/ItemControles/Pages/ListItemControles.php`
- `app/Filament/Resources/ItemControles/Schemas/ItemControleForm.php`
- `app/Filament/Resources/ItemControles/Widgets/ItemControleAnexosResumoWidget.php`
- `app/Filament/Resources/ItemControles/Widgets/ItemControleAnexosWidget.php`
- `app/Filament/Resources/ItemControles/Widgets/ItemControleVersionamentoWidget.php`
- `app/Http/Controllers/PortalClientePublicoController.php`
- `app/Http/Controllers/PortalItemControleController.php`
- `app/Models/ItemControleAnexo.php`
- `app/Services/AuditoriaManualService.php`
- `app/Services/ItemControleFluxoService.php`
- `app/Services/ItemControlePdfService.php`
- `app/Services/StorageRetentionService.php`
- `app/Services/SystemHealth/Checks/StorageHealthCheck.php`
- `app/Support/ItemControleAnexoUploader.php`
- `app/Support/PortalChatMessageContract.php`
- `app/Support/PortalClienteData.php`
- `app/Support/PrazzuEnterpriseModuleData.php`
- `resources/views/filament/pages/portal-cliente.blade.php`
- `resources/views/filament/pages/partials/atendimentos-detail-modal.blade.php`
- `resources/views/portal/cliente/show.blade.php`
- `routes/web.php`
Riscos: revisar disco usado, visibilidade pública/privada, nomes originais, extensões, tamanho, autorização de download, retenção e exclusão auditada.

### 5.6 Auditoria

Base: `Loggable`, `AuditoriaManualService`, `AuditoriaDetalhadaService`, `AuditoriaAccessService`, `Auditoria.php`, `AuditoriaAdministrativa.php`, tabelas `auditoria_detalhada`, `audit_timeline`, `activity_log`, `logs_sistema`.
Risco: auditoria existe, mas precisa matriz de cobertura. O comando `auditoria:cobertura` deve virar etapa obrigatória de homologação.

## 6. Updates críticos encontrados para revisão

Lista de pontos onde há update/save/delete/forceFill com termos críticos. Não significa bug automaticamente; significa ponto que precisa validação de service, tenant e auditoria.
| Arquivo | Linha | Trecho |
| --- | --- | --- |
| app/Console/Commands/ProcessarCentroOperacional.php | 91 | ItemControle::query()->whereIn('id', $ids)->update($payload); |
| app/Console/Commands/ProcessarRoadmapPrazzuInterno.php | 72 | DB::table('item_controles')->where('blocked_by_dependency', 1)->update($resetPayload); |
| app/Console/Commands/ProcessarRoadmapPrazzuInterno.php | 87 | return DB::table('item_controles')->whereIn('id', $blockedIds)->update($payload); |
| app/Console/Commands/ProcessarRoadmapPrazzuInterno.php | 102 | ->update(['sla_status' => 'concluido_no_prazo', 'updated_at' => $now]); |
| app/Console/Commands/ProcessarRoadmapPrazzuInterno.php | 107 | ->update(['sla_status' => 'concluido_atrasado', 'updated_at' => $now]); |
| app/Console/Commands/ProcessarRoadmapPrazzuInterno.php | 113 | ->update(['sla_status' => 'vencido', 'updated_at' => $now]); |
| app/Console/Commands/ProcessarRoadmapPrazzuInterno.php | 119 | ->update(['sla_status' => 'risco', 'updated_at' => $now]); |
| app/Console/Commands/ProcessarRoadmapPrazzuInterno.php | 125 | ->update(['sla_status' => 'ok', 'updated_at' => $now]); |
| app/Console/Commands/ProcessarRoadmapPrazzuInterno.php | 178 | DB::table('item_controles')->where('id', $item->id)->update($payload); |
| app/Filament/Pages/Assinaturas.php | 208 | DB::table('financeiro_assinaturas_cliente')->where('id', $assinatura->id)->update([ |
| app/Filament/Pages/Atendimentos.php | 1254 | $atendimento->update(['item_controle_id' => $item->id, 'updated_at' => now()]); |
| app/Filament/Pages/Atendimentos.php | 1298 | $atendimento->update(['item_controle_id' => $item->id, 'updated_at' => now()]); |
| app/Filament/Pages/CentroOperacional.php | 705 | $item->update(['responsavel_id' => $novoResponsavel->id]); |
| app/Filament/Pages/GestaoPlanos.php | 74 | $assinatura->forceFill([ |
| app/Filament/Pages/Permissoes.php | 189 | $role = PrazzuRole::query()->updateOrCreate( |
| app/Filament/Pages/Permissoes.php | 293 | $role->forceFill(['active' => ! (bool) $role->active])->save(); |
| app/Filament/Pages/Usuarios.php | 67 | $user->forceFill(['role' => $role])->save(); |
| app/Policies/ItemControlePolicy.php | 31 | public function delete(User $user, ItemControle $item): bool |
| app/Services/AsaasService.php | 32 | $assinatura = Assinatura::query()->updateOrCreate( |
| app/Services/AsaasService.php | 145 | $response = $this->delete("/subscriptions/{$assinatura->gateway_subscription_id}"); |
| app/Services/AsaasService.php | 234 | $assinatura->forceFill([ |
| app/Services/AsaasService.php | 355 | $assinatura->forceFill(['gateway_subscription_id' => $subscriptionId])->save(); |
| app/Services/AsaasService.php | 417 | $assinatura->forceFill([ |
| app/Services/AsaasService.php | 517 | $assinatura->forceFill([ |
| app/Services/AsaasService.php | 549 | $assinatura->forceFill([ |
| app/Services/AsaasService.php | 570 | $assinatura->forceFill([ |
| app/Services/AsaasService.php | 637 | $assinatura->forceFill([ |
| app/Services/AsaasService.php | 656 | $assinatura->forceFill([ |
| app/Services/AsaasService.php | 661 | $assinatura->empresa?->forceFill([ |
| app/Services/ItemControleAssinaturaService.php | 66 | $item->forceFill(['portal_ativo' => true])->save(); |
| app/Services/PrazzuAutomationEngine.php | 124 | DB::table('item_controles')->where('id', $itemId)->update($payload); |
| app/Services/StorageRetentionService.php | 257 | 'Anexo' => CachedSchema::hasTable('item_controle_anexos') && CachedSchema::hasColumn('item_controle_anexos', 'caminho') ? DB::table('item_controle_anexos')->where('id', $file['id'] |
| app/Services/StorageRetentionService.php | 258 | 'Documento' => CachedSchema::hasTable('item_controles') && CachedSchema::hasColumn('item_controles', 'arquivo') ? DB::table('item_controles')->where('id', $file['id'])->update(['ar |
| app/Services/StorageRetentionService.php | 267 | 'Anexo' => CachedSchema::hasTable('item_controle_anexos') ? DB::table('item_controle_anexos')->where('id', $file['id'])->delete() : null, |
| app/Services/StorageRetentionService.php | 268 | 'Documento' => CachedSchema::hasTable('item_controles') && CachedSchema::hasColumn('item_controles', 'arquivo') ? DB::table('item_controles')->where('id', $file['id'])->update(['ar |
| app/Support/AtendimentoActionService.php | 117 | $atendimento->update(['item_controle_id' => $item->id, 'updated_at' => now()]); |
| app/Support/FinanceiroClienteData.php | 283 | $query->update(['status' => 'vencida', 'updated_at' => now()]); |
| app/Support/FinanceiroModuleData.php | 174 | return DB::table('pagamentos')->where('id', $id)->update([ |
| app/Support/FinanceiroModuleData.php | 187 | return DB::table('pagamentos')->where('id', $id)->update([ |
| app/Support/FinanceiroModuleData.php | 210 | return DB::table('assinaturas')->where('id', $id)->update($payload) > 0; |
| app/Support/PrazzuWorkPlanningData.php | 188 | DB::table('item_controles')->where('id', $id)->update($update); |
| app/Support/PrazzuWorkPlanningData.php | 213 | DB::table('item_controles')->where('id', $id)->update($update); |
| app/Support/PrazzuWorkPlanningData.php | 247 | if ($update) DB::table('item_controles')->where('id', $id)->update($update); |
| app/Support/PrazzuWorkPlanningData.php | 263 | DB::table('item_controles')->where('id', $item->id)->update(['custom_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE)]); |
| app/Support/PrazzuWorkPlanningData.php | 277 | DB::table('item_controles')->where('id', $id)->update(['custom_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE)]); |
| app/Support/PrazzuWorkPlanningData.php | 289 | DB::table('item_controles')->where('id', $id)->update($update); |
| app/Support/PrazzuWorkPlanningData.php | 310 | DB::table('item_controles')->update([$flagColumn => 0]); |
| app/Support/PrazzuWorkPlanningData.php | 311 | if ($blockedIds) DB::table('item_controles')->whereIn('id', $blockedIds)->update([$flagColumn => 1]); |

## 7. Decisão: é suficiente para começar os lotes?

Sim, agora é suficiente para desenhar e iniciar os lotes **desde que** o primeiro lote seja de baseline/infra e inclua rodar o projeto em ambiente PHP correto. Ainda não é suficiente para sair alterando telas aleatórias.

## 8. Lotes recomendados após esta engenharia reversa

| Lote | Tema | O que faz |
| --- | --- | --- |
| 0 | Baseline executável | Subir ambiente correto, Composer, Artisan, health, route:list, schema check, backup do SQL, sem alterar regra de negócio. |
| 1 | Banco e schema oficial | Definir SQL oficial, models sem tabela, duplicidades legadas, índices/constraints, scripts incrementais. |
| 2 | Segurança e permissões | canAccess, policies/gates, URL direta, multiempresa, governança/super admin, ocultar enterprise. |
| 3 | Núcleo ItemControle | Consolidar status/prazo/responsável/prioridade/timeline como fonte única. |
| 4 | Mesa/Pendências/SLA/Calendário | Garantir consistência entre todas as visões operacionais. |
| 5 | Notificações/Scheduler | Vencimentos, logs anti-duplicidade, mail/database notifications, cron/queue. |
| 6 | Portal do Cliente e Portal Item | Auth, token, tenant, mensagens, solicitações, anexos, assinatura, alertas internos. |
| 7 | Documentos/Storage/Retenção | Upload/download, autorização, disco, retenção segura, exclusão auditada. |
| 8 | Financeiro/Asaas/Billing | Assinatura, cobrança, webhook, reconciliação, bloqueio, sandbox, idempotência. |
| 9 | Auditoria/Relatórios | Cobertura de ações críticas, relatórios operacionais essenciais, exportações seguras. |
| 10 | UX/Sidebar/Home | Reorganizar navegação por perfil e ocultar módulos futuros depois da segurança. |
| 11 | Performance e qualidade | N+1, cache, índices, PHPStan/Larastan/Pint, testes com SQL real. |
| 12 | Homologação ponta a ponta | Fluxo completo empresa→cliente→prazo→notificação→portal→documento→aprovação→auditoria→relatório→financeiro. |

## 9. Checklist mínimo para começar o Lote 0

- Instalar `ext-mbstring` e demais extensões exigidas pelo Composer.
- Criar `.env` limpo local, sem usar segredos vazados.
- Importar `prazzu.sql` em MySQL/MariaDB de teste.
- Rodar `composer install`, `php artisan about`, `php artisan route:list`, `php artisan sistemrh:diagnostico --arquivo=...`, `php artisan sistemrh:saude --json=...`.
- Salvar outputs como baseline antes de qualquer patch.