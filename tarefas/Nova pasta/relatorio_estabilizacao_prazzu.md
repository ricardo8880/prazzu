# RELATÓRIO DE ESTABILIZAÇÃO DO PRAZZU

## Objetivo
Este relatório serve como guia operacional para outro ChatGPT ou desenvolvedor continuar a estabilização do Prazzu sem chutar ajustes.

**Produto central:** SaaS Laravel/Filament para escritórios contábeis/operacionais não perderem prazos de clientes.

**Promessa principal:** todo prazo deve ter responsável, visibilidade, alerta, acompanhamento, evidência e histórico.

## Premissas obrigatórias
1. Não converter o projeto para migrations. O projeto usa banco direto; o SQL é a fonte oficial.
2. Não mudar identidade visual sem necessidade.
3. Não deletar módulos imaturos; preferir ocultar por permissão/feature flag/sidebar.
4. Não espalhar CSS; concentrar ajustes residuais em CSS global.
5. Toda alteração deve respeitar multiempresa/tenant.
6. Toda ação crítica deve gerar auditoria.
7. O fluxo principal deve ser testado ponta a ponta antes de publicar.

## Fluxo principal esperado
```text
Cliente / Empresa
  -> demanda, obrigação, documento ou contrato
  -> gera Tarefa Operacional / Pendência / Atendimento
  -> recebe responsável, prioridade, status e vencimento
  -> aparece em Mesa Operacional, Pendências, SLA e Calendário
  -> dispara notificações internas e, se necessário, Portal do Cliente
  -> responsável executa, solicita documento/aprovação ou conclui
  -> auditoria registra tudo
  -> relatório mostra cumprimento, atraso, gargalo e risco evitado
```

## Sidebar final recomendada

| Grupo final | Abas | Perfil |
|---|---|---|
| Visão Geral | Home; Resumo Executivo | Todos/Gestor |
| Operação | Mesa Operacional; Tarefas Operacionais; Fluxos Operacionais; Aprovações | Gestor/Admin |
| Execução/Prazos | Pendências; SLA e Prazos; Calendário Operacional | Executor/Gestor |
| Clientes | Carteira de Clientes; Atendimentos; Portal do Cliente | Gestor/Atendimento |
| Documentos | Documentos; Armazenamento | Gestor/Admin |
| Financeiro | Contratos; Assinaturas; Cobranças; Financeiro | Admin/Financeiro |
| Relatórios | Relatórios Operacionais; Auditoria e Rastreabilidade | Gestor/Admin |
| Administração | Usuários; Equipes; Perfis e Permissões; Dados do Escritório; Responsáveis; Categorias; Tags; Configurações | Admin |
| Conta | Conta; Onboarding; White Label | Admin |
| Governança | Saúde do Sistema; Central de Logs; Gestão de Planos | Super admin |

## Decisão por aba

| Aba atual | Decisão |
|---|---|
| Home | Manter visível; deve mostrar vencidos, hoje, aguardando cliente, aprovações e CTA para Mesa/Pendências. |
| Resumo Executivo | Manter para gestores; visão gerencial, não execução diária. |
| Mesa Operacional | Prioridade máxima; tela central do produto. |
| Fluxos Operacionais | Manter para admin/gestor; documentar relação com templates. |
| Tarefas Operacionais | Manter como núcleo técnico; não obrigar usuário comum a trabalhar por ela. |
| Templates de Checklist | Manter para admin. |
| Cronograma Gantt | Ocultar inicialmente. |
| Projetos | Manter se fizer sentido por cliente/projeto. |
| Aprovações | Manter; fluxo crítico. |
| Checklists | Avaliar ocultar como aba; preferir subfluxo. |
| Kanban | Manter como visualização alternativa. |
| Timeline Operacional | Ocultar/restringir até definir diferença com Calendário/Gantt. |
| Pendências | Prioridade alta; tela do executor. |
| SLA e Prazos | Prioridade alta; sustenta promessa principal. |
| Calendário Operacional | Manter; visão temporal. |
| Clientes e Atendimentos | Manter; entrada de demanda. |
| Portal do Cliente | Manter, mas testar segurança e tenant. |
| Carteira de Clientes | Mover para grupo Clientes. |
| Documentos | Manter; fluxo essencial. |
| Gestão Documental | Ocultar inicialmente. |
| Armazenamento | Manter para admin; cuidado com retenção. |
| Contratos | Manter se rotas/páginas estiverem completas. |
| Assinaturas | Manter para admin/financeiro. |
| Financeiro | Restringir até gateway estar validado. |
| Cobranças | Manter quando Asaas estiver validado. |
| Relatórios Operacionais | Manter; focar em vencidos/cumpridos/gargalos. |
| Relatórios Personalizados | Ocultar inicialmente. |
| Auditoria e Rastreabilidade | Manter para gestor/admin. |
| Auditoria Detalhada | Restringir. |
| Central de Logs | Somente super admin. |
| Riscos e Evidências | Ocultar inicialmente. |
| Conformidade Interna | Ocultar inicialmente. |
| Administração | Manter. |
| Usuários | Consolidar duplicidade Page vs Resource. |
| Equipes | Manter. |
| Perfis e Permissões | Manter e testar muito. |
| Meus Atalhos | Manter opcional. |
| Conta | Manter para admin. |
| Gestão de Planos | Somente super admin. |
| White Label | Ocultar/restringir no MVP. |
| Onboarding | Priorizar. |
| Saúde do Sistema | Somente super admin. |

## Lotes recomendados

| Lote | Tema | Critério de aceite |
|---|---|---|
| 0 | Mapa técnico e baseline | Matriz de telas, resources, services, rotas e tabelas. |
| 1 | Navegação/sidebar por perfil | Sidebar limpa; nenhuma tela essencial escondida. |
| 2 | Duplicidades críticas | Uma experiência principal por domínio. |
| 3 | Home enxuta | Cards acionáveis e CTA para Mesa/Pendências. |
| 4 | Mesa Operacional - leitura/filtros | Dados corretos por empresa e perfil. |
| 5 | Mesa Operacional - ações | Ações atualizam item, auditoria e notificações. |
| 6 | Pendências do executor | Executor trabalha sem entrar em Tarefas. |
| 7 | SLA e prazos | Mesa/Pendências/SLA/Calendário batem. |
| 8 | Notificações | Alertas disparam uma vez, na antecedência correta, com log. |
| 9 | Calendário | Reflete prazos reais e respeita permissões. |
| 10 | ItemControle como fonte única | Alterar tarefa propaga para todas as visões. |
| 11 | Checklists/subtarefas | Checklist funcional no lugar certo. |
| 12 | Fluxos/templates | Template/fluxo gera tarefas previsíveis. |
| 13 | Aprovações | Aprovação/reprovação com histórico e notificação. |
| 14 | Clientes/Carteira 360 | Cliente mostra saúde e próximos riscos. |
| 15 | Atendimentos | Demanda relevante vira tarefa/pendência/documento. |
| 16 | Portal - autenticação | Cliente acessa apenas a própria empresa. |
| 17 | Portal - comunicação | Mensagem/anexo gera alerta e histórico. |
| 18 | Documentos | Documento solicitado/recebido/resolvido com rastreio. |
| 19 | Armazenamento/retenção | Retenção segura com log e confirmação. |
| 20 | Contratos | Sem rota quebrada; status claro. |
| 21 | Cobranças/Asaas | Sem duplicidade; webhook e pagamento consistentes. |
| 22 | Conta/bloqueio | Bloqueio só ocorre quando correto. |
| 23 | Relatórios essenciais | Relatórios provam valor operacional. |
| 24 | Auditoria | Toda ação crítica rastreável. |
| 25 | Permissões/roles | Sem vazamento por menu ou URL direta. |
| 26 | Equipes/responsáveis | Redistribuição e carga de trabalho funcionam. |
| 27 | Onboarding | Escritório chega ao primeiro prazo monitorado. |
| 28 | Ocultar enterprise/futuro | MVP limpo, sem módulos confusos. |
| 29 | Saúde do Sistema | Sem falhas críticas. |
| 30 | Teste ponta a ponta | Pronto para piloto controlado. |

## Critérios para piloto
1. Criar empresa/admin/gestor/executor/cliente sem intervenção manual.
2. Criar cliente, responsável e tarefa com vencimento.
3. Tarefa aparece em Home, Mesa, Pendências, SLA e Calendário.
4. Notificação dispara e fica registrada.
5. Executor conclui ou solicita aprovação.
6. Gestor aprova/reprova com motivo.
7. Cliente usa portal, envia mensagem e anexo.
8. Documento aparece para equipe e é resolvido.
9. Auditoria registra ações.
10. Relatórios mostram vencidos, concluídos e aguardando cliente.
11. Perfil comum não acessa super admin nem outra empresa.
12. Financeiro não bloqueia indevidamente.
13. Health check sem erro crítico.

## Prompt para outro ChatGPT
```text
Você está trabalhando no projeto Prazzu, um SaaS Laravel/Filament para escritórios não perderem prazos de clientes. Use o relatório de estabilização como fonte de verdade. Execute somente o lote solicitado. Antes de alterar, revise os arquivos do domínio do lote e os impactos em Mesa, Pendências, SLA, Portal, Auditoria e Permissões. Não altere layout sem necessidade. Não crie migrations; o projeto usa SQL direto. Ao final, entregue somente um ZIP com os arquivos alterados e explique brevemente o que foi modificado e como testar.
```
