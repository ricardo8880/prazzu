Minha sugestão adicional (e a que considero decisiva)

Existe uma coisa que eu faria antes mesmo do Lote 0, porque ela vai economizar muito tempo em todos os outros lotes.

Lote -1 — Engenharia Reversa Completa (Blueprint do Sistema)

Eu passaria alguns dias apenas entendendo o sistema, sem alterar uma linha de código.

Geraria documentação técnica completa:

arquitetura de pastas;
diagrama de dependências;
mapa de todos os Services;
mapa de todos os Models e relacionamentos;
mapa das tabelas (103);
mapa de todos os fluxos (Portal, Mesa, SLA, Financeiro, Auditoria, Documentos etc.);
matriz de permissões por perfil;
grafo de chamadas entre controllers, pages, services e jobs;
inventário de código morto, duplicado e legado.

Isso transforma um projeto de "caixa-preta" em um sistema totalmente conhecido. A partir daí, cada lote é executado com muito mais segurança.

O que eu faria no seu lugar

Eu não começaria a corrigir nada ainda.

Primeiro faria essa engenharia reversa completa. Quando ela terminar, eu espero conseguir responder qualquer pergunta sobre o Prazzu sem precisar procurar no código, como:

"Quem cria um ItemControle?"
"Quem altera o SLA?"
"O que acontece quando um cliente envia um documento?"
"Quem atualiza o status operacional?"
"Onde uma cobrança do Asaas muda a assinatura?"
"Quais telas dependem diretamente do ItemControle?"
"Quais comandos do scheduler impactam a Mesa Operacional?"

Quando esse nível de entendimento for atingido, a execução dos 16 lotes deixa de ser tentativa e erro e passa a ser uma estabilização controlada, exatamente como seria conduzida por uma equipe sênior em um projeto prestes a entrar em produção.




# Engenharia reversa inicial do Prazzu
Base analisada: `/mnt/data/prazzu.zip` + banco atual `/mnt/data/prazzu.sql`. Não foram feitas alterações no código.
## 1. Inventário executivo
- Stack: Laravel/Filament, PHP, SQL MySQL direto.
- Arquivos PHP em `app`: 317.
- Filament Pages: 41.
- Filament Resources/arquivos internos: 86.
- Models: 62.
- Services: 42.
- Controllers HTTP: 11.
- Middlewares HTTP: 5.
- Commands: 10.
- Banco atual: 104 tabelas.

## 2. Diferença banco atual vs dump anterior
O banco atual enviado possui 104 tabelas. O dump anterior mais novo no projeto possui 103. A tabela nova no banco atual é `user_sidebar_favorites`, o que corrige parcialmente um risco visto antes na sidebar.

## 3. Models sem tabela no banco atual
- `AiMarketComment` espera `ai_market_comments`: ausente.
- `AiMarketSource` espera `ai_market_sources`: ausente.
- `AiProductImprovementResolution` espera `ai_product_improvement_resolutions`: ausente.

## 4. Tabelas principais por domínio
### ItemControle
- `item_controles`
- `item_controle_alertas`
- `item_controle_anexos`
- `item_controle_aprovacoes`
- `item_controle_assinaturas`
- `item_controle_checklists`
- `item_controle_comentarios`
- `item_controle_notificacao_logs`
- `item_controle_tags`
- `item_controle_tag_relations`
- `item_controle_timeline`
- `item_controle_timelines`
### Portal
- `portal_cliente_tokens`
- `portal_documentos`
- `portal_mensagens`
- `portal_solicitacoes`
### Cliente portal auth
- `cliente_portal_password_reset_tokens`
- `cliente_portal_users`
### Módulos Prazzu/enterprise/permissões
- `prazzu_automation_executions`
- `prazzu_automation_rules`
- `prazzu_billing_locks`
- `prazzu_billing_rules`
- `prazzu_client_portal_messages`
- `prazzu_custom_fields`
- `prazzu_custom_field_values`
- `prazzu_dependencies`
- `prazzu_document_versions`
- `prazzu_permissions`
- `prazzu_permission_audits`
- `prazzu_permission_rules`
- `prazzu_roles`
- `prazzu_sla_policies`
- `prazzu_sla_rules`
- `prazzu_subtasks`
- `prazzu_task_comments`
- `prazzu_task_dependencies`
- `prazzu_task_subtasks`
- `prazzu_teams`
- `prazzu_team_user`
- `prazzu_templates`
- `prazzu_time_entries`
- `prazzu_time_tracking`
- `prazzu_user_permissions`
- `prazzu_user_roles`
### Financeiro novo
- `financeiro_assinaturas_cliente`
- `financeiro_clientes`
- `financeiro_cobrancas`
- `financeiro_gateway_integracoes`
- `financeiro_recebimentos`
- `financeiro_webhook_logs`
### CRM
- `crm_clientes`
- `crm_historicos`
- `crm_pendencias`
### Fluxos
- `fluxos_operacionais`
- `fluxos_operacionais_etapas`
- `fluxos_operacionais_execucoes`
### Auditoria
- `auditoria_detalhada`
### Logs
- `logs_sistema`

## 5. Filament Pages e risco de acesso
| Page | Grupo | Label | canAccess |
|---|---|---|---|
| `Armazenamento` | Documentos e Modelos | Armazenamento | `return static::canAdvancedPermission('armazenamento.view');` |
| `Assinaturas` | Contratos e Financeiro | Assinaturas | `return auth()->check();` |
| `Atendimentos` | Clientes e Atendimentos | Clientes e Atendimentos | `return static::canAdvancedPermission('atendimentos.view');` |
| `Auditoria` | Relatórios e Auditoria | Auditoria e Rastreabilidade | `return app(AuditoriaAccessService::class)->canView(auth()->user());` |
| `AuditoriaAdministrativa` | Relatórios e Auditoria | Central de Logs | `return app(AuditoriaAccessService::class)->canView(auth()->user());` |
| `Calendario` | Pendências e Prazos | Calendário Operacional | `default/no explicit canAccess` |
| `CentralAdministrativa` | Administração | Administração | `$user = Auth::user(); if (! $user) { return false; } if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) { return true; } return static::canAdvancedPermission('gover` |
| `CentralAprovacoes` | Operação | Aprovações | `return static::canAdvancedPermission('aprovacoes.view') && PrazzuAccessControl::canUseAprovacoes(PrazzuAccessControl::user());` |
| `CentroOperacional` | Operação | Mesa Operacional | `default/no explicit canAccess` |
| `Checklist` | Operação | Checklists | `return PrazzuAccessControl::canUseChecklist();` |
| `Clientes` | Cadastros e Configurações | Carteira de Clientes | `return static::canAdvancedPermission('clientes.view');` |
| `ComplianceInterno` | Relatórios e Auditoria | Conformidade Interna | `return auth()->check();` |
| `Configuracoes` | Cadastros e Configurações | Parâmetros do Escritório | `$user = auth()->user(); return $user?->isSuperAdmin() === true || $user?->isAdminEmpresa() === true;` |
| `Contratos` | Contratos e Financeiro | Contratos | `return true; } protected function getViewData(): array { return ['resumo' => $this->resumo(), 'contratos' => $this->contratos()];` |
| `ControleCobrancas` | Contratos e Financeiro | Cobranças | `return static::canAdvancedPermission('cobrancas.view');` |
| `DashboardExecutivoContabil` | Visão Geral | Resumo Executivo | `return Filament::auth()->check();` |
| `Documentos` | Documentos e Modelos | Documentos | `return static::canAdvancedPermission('documentos.view');` |
| `EmpresaAdministrativa` | Cadastros e Configurações | Dados do Escritório | `$user = Auth::user(); if (! $user) { return false; } return $user->isSuperAdmin() || $user->isAdminEmpresa() || static::canAdvancedPermission('configuracoes.view') || static::canAd` |
| `Equipes` | Administração | Equipes | `$user = Auth::user(); return (bool) $user && ($user->isSuperAdmin() || static::canAdvancedPermission('governanca.view'));` |
| `Financeiro` | Contratos e Financeiro | Financeiro | `return static::canAdvancedPermission('financeiro.view');` |
| `Gantt` | Operação | Cronograma Gantt | `return auth()->check();` |
| `GestaoDocumentalEnterprise` | Documentos e Modelos | Gestão Documental | `default/no explicit canAccess` |
| `GestaoPlanos` | Conta | Gestão de Planos | `$user = auth()->user(); return $user?->isSuperAdmin() === true || static::canAdvancedPermission('governanca.view');` |
| `Home` | Visão Geral | Home | `default/no explicit canAccess` |
| `Kanban` | Operação | Kanban | `default/no explicit canAccess` |
| `MeusAtalhos` | Configurações | Meus Atalhos | `default/no explicit canAccess` |
| `Onboarding` | Conta | Onboarding | `return auth()->check();` |
| `Pendencias` | Pendências e Prazos | Pendências | `return auth()->check();` |
| `Permissoes` | Administração | Perfis e Permissões | `return static::canAdvancedPermission('governanca.view');` |
| `PlanosBilling` | Conta | Conta | `$user = auth()->user(); return $user?->isSuperAdmin() === true || static::canAdvancedPermission('governanca.view');` |
| `PortalCliente` | Clientes e Atendimentos | Portal do Cliente | `return PrazzuAccessControl::canUsePortalCliente();` |
| `Projetos` | Operação | Projetos | `default/no explicit canAccess` |
| `Relatorios` | Relatórios e Auditoria | Relatórios Operacionais | `return static::canAdvancedPermission('relatorios.view');` |
| `Riscos` | Relatórios e Auditoria | Riscos e Evidências | `return auth()->check();` |
| `SlaPrazos` | Pendências e Prazos | SLA e Prazos | `default/no explicit canAccess` |
| `SystemHealthDashboard` | Governança | Saúde do Sistema | `return auth()->check();` |
| `TimelineOperacional` | Operação | Timeline Operacional | `return PrazzuAccessControl::canUseTimeline();` |
| `Usuarios` | Administração | Usuários | `return static::canAdvancedPermission('governanca.view');` |
| `WhiteLabel` | Conta | White Label | `default/no explicit canAccess` |
| `clientes.blade` |  |  | `default/no explicit canAccess` |

## 6. Rotas públicas e internas sensíveis
- Portal cliente autenticado: `/portal-cliente/*`.
- Portal público por token: `/portal/cliente/{token}` e `/portal/itens/{token}`.
- Webhooks Asaas: `/webhooks/asaas` e `/asaas/webhook`.
- Billing: `/billing/*`.
- PDF público/autenticado a revisar: `/item-controles/{itemControle}/pdf`.
- Busca/admin/auditoria: `/admin/busca-global`, `/admin/auditoria-detalhada/exportar`.

## 7. Scheduler detectado
- `item-controle:notificar-vencimentos` diário 08:00.
- `itens-controle:atualizar-vencidos` diário 00:05.
- `asaas:reconciliar-assinaturas --limit=100` horário.
- `prazzu:processar-roadmap-interno --silent-notifications`.
- `centro-operacional:processar --silent-notifications`.
- `storage:processar-retencao --limit=100`.

## 8. Conclusão operacional
O sistema deve ser estabilizado em lotes de domínio. A ordem recomendada é: ambiente/secrets, banco, segurança/tenant, ItemControle, Mesa/Pendências/SLA/Calendário, notificações, portal/documentos, financeiro, auditoria, UX/sidebar, performance/testes/homologação.
