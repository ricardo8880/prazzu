<?php

namespace App\Services;


use App\Support\CachedSchema;
use App\Filament\Pages\Pendencias;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Filament\Resources\PrazzuTemplates\PrazzuTemplateResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PrazzuEnterpriseMaturityService
{
    private const DONE_STATUSES = ['concluido', 'concluído', 'finalizado', 'aprovado', 'aprovada', 'pago', 'paid'];

    public function page(string $module): array
    {
        return match ($module) {
            'centro-operacional' => $this->centroOperacional(),
            'central-aprovacoes' => $this->centralAprovacoes(),
            'gestao-documental' => $this->gestaoDocumental(),
            'automacao-visual' => $this->automacaoVisual(),
            'compliance-engine' => $this->complianceEngine(),
            'relatorios-exportaveis' => $this->relatoriosExportaveis(),
            'dashboard-executivo' => $this->dashboardExecutivo(),
            'timeline-global' => $this->timelineGlobal(),
            'templates-enterprise' => $this->templatesEnterprise(),
            'assistente-operacional' => $this->assistenteOperacional(),
            'navegacao-contextual' => $this->navegacaoContextual(),
            default => $this->centroOperacional(),
        };
    }

    private function centroOperacional(): array
    {
        $lateItems = $this->itemsBase()
            ->whereNotNull('item_controles.data_vencimento')
            ->whereDate('item_controles.data_vencimento', '<', now()->toDateString())
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->limit(8)
            ->get()
            ->map(fn ($item) => $this->decorateItem((array) $item, 'Atrasado'))
            ->all();

        $slaRisk = $this->hasColumn('item_controles', 'sla_limite_em')
            ? $this->itemsBase()
                ->whereNotNull('item_controles.sla_limite_em')
                ->whereNull('item_controles.sla_concluido_em')
                ->where('item_controles.sla_limite_em', '<=', now()->addHours(12))
                ->limit(8)
                ->get()
                ->map(fn ($item) => $this->decorateItem((array) $item, 'SLA em risco'))
                ->all()
            : [];

        $approvals = $this->approvalRows(8);
        $documents = $this->documentRows(8);
        $billing = $this->billingRows(8);

        return [
            'module' => 'centro-operacional',
            'group' => 'OPERAÇÃO',
            'title' => 'Centro Operacional',
            'subtitle' => 'Uma tela única para decidir o que precisa ser aprovado, cobrado, revisado, vencido ou corrigido hoje.',
            'cards' => [
                ['label' => 'Itens atrasados', 'value' => count($lateItems), 'tone' => 'danger', 'hint' => 'Exigem ação imediata'],
                ['label' => 'SLA em risco', 'value' => count($slaRisk), 'tone' => 'warning', 'hint' => 'Próximas 12 horas'],
                ['label' => 'Aprovações', 'value' => count($approvals), 'tone' => 'info', 'hint' => 'Pendentes ou recentes'],
                ['label' => 'Cobranças', 'value' => count($billing), 'tone' => 'success', 'hint' => 'Vencidas ou abertas'],
            ],
            'sections' => [
                ['title' => 'Ações críticas', 'description' => 'Itens que travam a operação.', 'items' => array_merge($lateItems, $slaRisk)],
                ['title' => 'Aprovações para decidir', 'description' => 'Documentos, contratos e tarefas aguardando validação.', 'items' => $approvals],
                ['title' => 'Documentos para revisar', 'description' => 'Arquivos sem versão aprovada, vencidos ou recém enviados.', 'items' => $documents],
                ['title' => 'Cobrança e recuperação', 'description' => 'Pendências financeiras que podem afetar acesso ou continuidade.', 'items' => $billing],
            ],
            'quickActions' => $this->quickActions(),
        ];
    }

    private function centralAprovacoes(): array
    {
        $rows = $this->approvalRows(40);
        return [
            'module' => 'central-aprovacoes',
            'group' => 'GOVERNANÇA',
            'title' => 'Central Única de Aprovações',
            'subtitle' => 'Inbox unificada para documentos, contratos, tarefas, pendências do cliente e compliance.',
            'cards' => [
                ['label' => 'Pendentes', 'value' => $this->countApprovals('pendente'), 'tone' => 'warning', 'hint' => 'Aguardando decisão'],
                ['label' => 'Aprovadas', 'value' => $this->countApprovals(['aprovado', 'aprovada']), 'tone' => 'success', 'hint' => 'Concluídas'],
                ['label' => 'Reprovadas', 'value' => $this->countApprovals(['reprovado', 'reprovada']), 'tone' => 'danger', 'hint' => 'Exigem correção'],
                ['label' => 'Hoje', 'value' => $this->approvalsToday(), 'tone' => 'info', 'hint' => 'Solicitadas hoje'],
            ],
            'sections' => [
                ['title' => 'Fila priorizada', 'description' => 'Clique no item para abrir o cadastro de origem e concluir a decisão.', 'items' => $rows],
            ],
            'quickActions' => $this->quickActions(),
        ];
    }

    private function gestaoDocumental(): array
    {
        $versions = $this->documentVersionRows(24);
        $documents = $this->documentRows(24);
        $diff = $this->diffCandidates();
        return [
            'module' => 'gestao-documental',
            'group' => 'DOCUMENTOS',
            'title' => 'Gestão Documental Enterprise',
            'subtitle' => 'Workflow documental com versionamento, comparação, aprovação e histórico operacional.',
            'cards' => [
                ['label' => 'Documentos', 'value' => $this->documentsCount(), 'tone' => 'info', 'hint' => 'Itens com arquivo/anexo'],
                ['label' => 'Versões', 'value' => $this->tableCount('prazzu_document_versions'), 'tone' => 'success', 'hint' => 'Histórico versionado'],
                ['label' => 'Vencidos', 'value' => $this->documentosVencidos(), 'tone' => 'danger', 'hint' => 'Compliance documental'],
                ['label' => 'Sem arquivo', 'value' => $this->documentsMissingFile(), 'tone' => 'warning', 'hint' => 'Precisa completar'],
            ],
            'workflow' => [
                ['step' => 'Rascunho', 'description' => 'Documento criado, mas ainda não enviado para revisão.', 'count' => $this->documentStatusCount(['rascunho', 'draft', null])],
                ['step' => 'Em revisão', 'description' => 'Aguardando validação interna ou do cliente.', 'count' => $this->documentStatusCount(['em_revisao', 'review', 'pendente'])],
                ['step' => 'Aprovado', 'description' => 'Versão aceita e pronta para uso.', 'count' => $this->documentStatusCount(['aprovado', 'approved'])],
                ['step' => 'Publicado', 'description' => 'Documento liberado para operação/portal.', 'count' => $this->documentStatusCount(['publicado', 'published'])],
                ['step' => 'Arquivado', 'description' => 'Documento encerrado para consulta histórica.', 'count' => $this->documentStatusCount(['arquivado', 'archived'])],
            ],
            'sections' => [
                ['title' => 'Comparação de versões', 'description' => 'Itens com duas ou mais versões para conferência manual de alterações.', 'items' => $diff],
                ['title' => 'Versões recentes', 'description' => 'Histórico documental pronto para auditoria.', 'items' => $versions],
                ['title' => 'Documentos operacionais', 'description' => 'Itens com arquivo, vencimento ou portal ativo.', 'items' => $documents],
            ],
            'quickActions' => $this->quickActions(),
        ];
    }

    private function automacaoVisual(): array
    {
        $rules = $this->automationRows(40);
        $suggestions = [
            ['title' => 'SE prazo venceu ENTÃO mover para atrasado', 'meta' => 'Tarefas e documentos', 'status' => 'Sugestão interna', 'tone' => 'danger', 'description' => 'Use para manter o Kanban e o dashboard sempre atualizados.'],
            ['title' => 'SE aprovação concluída ENTÃO avançar etapa', 'meta' => 'Workflow documental', 'status' => 'Sugestão interna', 'tone' => 'success', 'description' => 'Remove operação manual após decisão.'],
            ['title' => 'SE pagamento venceu ENTÃO sinalizar cliente em risco', 'meta' => 'Cobrança inteligente', 'status' => 'Sugestão interna', 'tone' => 'warning', 'description' => 'Não depende de gateway, usa a tabela de pagamentos local.'],
            ['title' => 'SE SLA entrar em risco ENTÃO criar alerta interno', 'meta' => 'Compliance e SLA', 'status' => 'Sugestão interna', 'tone' => 'info', 'description' => 'Mantém o time proativo antes do rompimento.'],
        ];

        return [
            'module' => 'automacao-visual',
            'group' => 'AUTOMAÇÃO',
            'title' => 'Builder Visual de Automação',
            'subtitle' => 'Leitura visual das regras internas no padrão SE / ENTÃO, com sugestões de maturidade operacional.',
            'cards' => [
                ['label' => 'Regras cadastradas', 'value' => count($rules), 'tone' => 'info', 'hint' => 'prazzu_automation_rules'],
                ['label' => 'Ativas', 'value' => $this->automationActiveCount(), 'tone' => 'success', 'hint' => 'Rodam na engine'],
                ['label' => 'Inativas', 'value' => max(count($rules) - $this->automationActiveCount(), 0), 'tone' => 'warning', 'hint' => 'Revisar configuração'],
                ['label' => 'Sugestões', 'value' => count($suggestions), 'tone' => 'danger', 'hint' => 'Prontas para cadastrar'],
            ],
            'sections' => [
                ['title' => 'Regras visuais cadastradas', 'description' => 'Cada card representa uma automação no formato SE / ENTÃO.', 'items' => $rules],
                ['title' => 'Biblioteca de automações recomendadas', 'description' => 'Regras que fecham maturidade sem depender de APIs externas.', 'items' => $suggestions],
            ],
            'quickActions' => $this->quickActions(),
        ];
    }

    private function complianceEngine(): array
    {
        $docs = $this->documentosVencidosRows(12);
        $contracts = $this->contractsDueRows(12);
        $sla = $this->slaRows(12);
        $billing = $this->billingRows(12);
        $risks = $this->riskRows(12);

        return [
            'module' => 'compliance-engine',
            'group' => 'COMPLIANCE',
            'title' => 'Compliance Engine Interno',
            'subtitle' => 'Motor de verificação local para documentos, contratos, SLA, cobrança e risco operacional.',
            'cards' => [
                ['label' => 'Docs vencidos', 'value' => count($docs), 'tone' => 'danger', 'hint' => 'Vencimento documental'],
                ['label' => 'Contratos vencendo', 'value' => count($contracts), 'tone' => 'warning', 'hint' => 'Próximos 30 dias'],
                ['label' => 'SLA rompido/risco', 'value' => count($sla), 'tone' => 'danger', 'hint' => 'Atuação imediata'],
                ['label' => 'Financeiro crítico', 'value' => count($billing), 'tone' => 'info', 'hint' => 'Cobrança local'],
            ],
            'sections' => [
                ['title' => 'Documentos vencidos', 'description' => 'Itens com vencimento expirado e ainda não encerrados.', 'items' => $docs],
                ['title' => 'Contratos vencendo', 'description' => 'Renovações e encerramentos dos próximos 30 dias.', 'items' => $contracts],
                ['title' => 'SLA crítico', 'description' => 'Prazos rompidos ou próximos do limite.', 'items' => $sla],
                ['title' => 'Cobrança crítica', 'description' => 'Pagamentos vencidos ou em aberto.', 'items' => $billing],
                ['title' => 'Score de risco operacional', 'description' => 'Pontuação por atraso, SLA, prioridade, cobrança e documentos.', 'items' => $risks],
            ],
            'quickActions' => $this->quickActions(),
        ];
    }

    private function relatoriosExportaveis(): array
    {
        $reports = [
            ['title' => 'Relatório operacional', 'meta' => 'Tarefas, status, prioridade, responsável e vencimento', 'status' => 'Pronto para exportar pela listagem', 'tone' => 'info', 'description' => 'Use como base para CSV/XLSX/PDF sem depender de API externa.'],
            ['title' => 'Relatório executivo', 'meta' => 'KPIs, SLA, atrasos, produtividade e risco', 'status' => 'Consolidado', 'tone' => 'success', 'description' => 'Resumo para gestão semanal/mensal.'],
            ['title' => 'Relatório documental', 'meta' => 'Documentos, versões, aprovação e vencimentos', 'status' => 'Governança', 'tone' => 'warning', 'description' => 'Foco em auditoria e compliance.'],
            ['title' => 'Relatório financeiro', 'meta' => 'Pagamentos, assinaturas, vencidos e recuperação', 'status' => 'Financeiro', 'tone' => 'danger', 'description' => 'Cobrança inteligente local.'],
        ];

        return [
            'module' => 'relatorios-exportaveis',
            'group' => 'RELATÓRIOS',
            'title' => 'Relatórios Exportáveis',
            'subtitle' => 'Central de relatórios gerenciais com estrutura pronta para PDF, Excel e CSV usando dados internos.',
            'cards' => [
                ['label' => 'Modelos', 'value' => count($reports), 'tone' => 'info', 'hint' => 'Executivo, operacional, documental e financeiro'],
                ['label' => 'Itens', 'value' => $this->tableCount('item_controles'), 'tone' => 'success', 'hint' => 'Base operacional'],
                ['label' => 'Documentos', 'value' => $this->documentsCount(), 'tone' => 'warning', 'hint' => 'Base documental'],
                ['label' => 'Pagamentos', 'value' => $this->tableCount('pagamentos'), 'tone' => 'danger', 'hint' => 'Base financeira'],
            ],
            'sections' => [
                ['title' => 'Modelos de relatório', 'description' => 'Cada modelo já indica o conjunto de dados e o objetivo da exportação.', 'items' => $reports],
                ['title' => 'Amostra operacional', 'description' => 'Itens mais importantes para validar filtros e exportação.', 'items' => $this->itemsBase()->limit(12)->get()->map(fn ($item) => $this->decorateItem((array) $item, 'Operacional'))->all()],
            ],
            'quickActions' => $this->quickActions(),
        ];
    }

    private function dashboardExecutivo(): array
    {
        $totalItems = max($this->tableCount('item_controles'), 1);
        $doneItems = $this->doneItemsCount();
        $openItems = $this->openItemsCount();
        $lateItems = $this->lateItemsCount();
        $completionRate = ($doneItems / $totalItems) * 100;
        $overdueValue = $this->overdueBillingValue();
        $revenueMonth = $this->revenueThisMonth();
        $health = $this->executiveHealthScore();

        return [
            'module' => 'dashboard-executivo',
            'group' => 'GESTÃO CONTÁBIL',
            'title' => 'Dashboard Executivo Contábil',
            'subtitle' => 'Painel simples para o sócio ou gestor entender clientes, prazos, documentos, equipe, gargalos e financeiro sem entrar em várias telas.',
            'cards' => [
                ['label' => 'Saúde do escritório', 'value' => $health['score'] . '/100', 'tone' => $health['tone'], 'hint' => $health['label']],
                ['label' => 'Clientes ativos', 'value' => $this->activeCompaniesCount(), 'tone' => 'success', 'hint' => 'Carteira em operação'],
                ['label' => 'Clientes em risco', 'value' => $this->lateCompaniesCount(), 'tone' => 'danger', 'hint' => 'Atraso, cobrança ou pendência'],
                ['label' => 'Clientes parados', 'value' => $this->companiesWithoutRecentInteractionCount(), 'tone' => 'warning', 'hint' => 'Sem movimento recente'],
                ['label' => 'Tarefas abertas', 'value' => $openItems, 'tone' => 'info', 'hint' => 'Demandas ainda não concluídas'],
                ['label' => 'Tarefas atrasadas', 'value' => $lateItems, 'tone' => 'danger', 'hint' => 'Vencidas e não finalizadas'],
                ['label' => 'Sem responsável', 'value' => $this->unassignedItemsCount(), 'tone' => 'warning', 'hint' => 'Precisam de dono claro'],
                ['label' => 'SLA crítico', 'value' => $this->slaCriticalCount(), 'tone' => 'warning', 'hint' => 'Rompido ou próximo do limite'],
                ['label' => 'Documentos pendentes', 'value' => $this->pendingDocumentsCount(), 'tone' => 'warning', 'hint' => 'Sem arquivo, vencidos ou aguardando aprovação'],
                ['label' => 'Equipe sobrecarregada', 'value' => $this->overloadedTeamCount(), 'tone' => 'danger', 'hint' => 'Responsáveis com atraso acumulado'],
                ['label' => 'Receita do mês', 'value' => 'R$ ' . number_format($revenueMonth, 2, ',', '.'), 'tone' => 'success', 'hint' => 'Pagamentos recebidos no mês'],
                ['label' => 'Valor vencido', 'value' => 'R$ ' . number_format($overdueValue, 2, ',', '.'), 'tone' => $overdueValue > 0 ? 'danger' : 'success', 'hint' => 'Cobranças em aberto vencidas'],
            ],
            'workflowTitle' => 'Funil executivo do escritório',
            'workflowDescription' => 'Da carteira ativa até conclusão: visão compacta para perceber volume, atraso e risco.',
            'workflow' => $this->executiveFunnelRows($completionRate),
            'sections' => [
                ['title' => 'Saúde do escritório', 'description' => 'Score único para o sócio entender em segundos se o escritório está saudável, em atenção ou crítico.', 'items' => $this->executiveHealthRows($health)],
                ['title' => 'O que precisa da sua atenção hoje?', 'description' => 'Leitura executiva simples: ações que mais reduzem risco, atraso, perda de receita e reclamação.', 'items' => $this->todayExecutiveAttentionRows($completionRate)],
                ['title' => 'Radar de risco da carteira', 'description' => 'Clientes classificados por risco alto, médio e baixo usando atraso, documentos, financeiro e falta de interação.', 'items' => $this->riskRadarRows()],
                ['title' => 'Ranking de clientes problemáticos', 'description' => 'Clientes que exigem ação prioritária por risco operacional, financeiro ou documental.', 'items' => $this->priorityCompanyRows(10)],
                ['title' => 'Ranking de clientes por receita', 'description' => 'Clientes que mais geraram receita recebida nos últimos 90 dias, com leitura simples de concentração da carteira.', 'items' => $this->topRevenueCompanyRows(10)],
                ['title' => 'Carteiras por responsável', 'description' => 'Visão por responsável para equilibrar clientes, tarefas abertas, atrasos e entregas.', 'items' => $this->portfolioByOwnerRows(12)],
                ['title' => 'Comparativo mensal', 'description' => 'Este mês contra o mês anterior: receita, novos trabalhos, conclusão e inadimplência.', 'items' => $this->monthlyComparisonRows()],
                ['title' => 'Tendências estratégicas', 'description' => 'Sinais de 30, 90 e 180 dias para o gestor saber se o escritório está melhorando ou piorando.', 'items' => $this->strategicTrendRows()],
                ['title' => 'Mapa de gargalos operacionais', 'description' => 'Agrupa atrasos, SLA e volume por tipo de trabalho para mostrar onde a operação está travando.', 'items' => $this->operationalBottleneckRows(10)],
                ['title' => 'Plano de ação recomendado', 'description' => 'Recomendações práticas para o gestor agir sem precisar interpretar todos os números.', 'items' => $this->actionPlanRows($completionRate)],
                ['title' => 'Alertas inteligentes', 'description' => 'Sinais automáticos calculados com o banco atual para evitar surpresa operacional.', 'items' => $this->smartAlertRows()],
                ['title' => 'Prazos, SLA e documentos críticos', 'description' => 'Fila objetiva de itens que podem gerar reclamação, multa, retrabalho ou perda de confiança.', 'items' => $this->riskRows(12)],
            ],
            'quickActions' => $this->quickActions(),
        ];
    }

    private function timelineGlobal(): array
    {
        return [
            'module' => 'timeline-global',
            'group' => 'AUDITORIA',
            'title' => 'Timeline Global Consolidada',
            'subtitle' => 'Linha do tempo única da empresa: eventos, comentários, anexos, aprovações e mudanças relevantes.',
            'cards' => [
                ['label' => 'Eventos', 'value' => $this->tableCount('item_controle_timeline'), 'tone' => 'info', 'hint' => 'Timeline operacional'],
                ['label' => 'Comentários', 'value' => $this->tableCount('item_controle_comentarios'), 'tone' => 'success', 'hint' => 'Interações'],
                ['label' => 'Anexos', 'value' => $this->tableCount('item_controle_anexos'), 'tone' => 'warning', 'hint' => 'Evidências'],
                ['label' => 'Aprovações', 'value' => $this->tableCount('item_controle_aprovacoes'), 'tone' => 'danger', 'hint' => 'Decisões'],
            ],
            'timeline' => $this->globalTimelineRows(60),
            'sections' => [
                ['title' => 'Eventos recentes', 'description' => 'Histórico consolidado para auditoria e acompanhamento.', 'items' => $this->globalTimelineRows(20)],
            ],
            'quickActions' => $this->quickActions(),
        ];
    }

    private function templatesEnterprise(): array
    {
        $templates = $this->templateRows(50);
        $templatesCollection = $this->hasTable('prazzu_templates')
            ? DB::table('prazzu_templates')->get()
            : collect();

        $tasks = 0;
        $checklists = 0;
        $customFields = 0;
        $automations = 0;
        $views = 0;
        $recurrences = 0;
        $docs = 0;
        $mindMapNodes = 0;

        foreach ($templatesCollection as $template) {
            $payload = json_decode((string) ($template->payload ?? ''), true) ?: [];
            $templateTasks = $payload['tasks'] ?? [];
            $tasks += count($templateTasks);
            $customFields += count($payload['custom_fields'] ?? []);
            $automations += count($payload['automations'] ?? []);
            $views += count($payload['views'] ?? []);
            $docs += count($payload['docs'] ?? []);
            $mindMapNodes += count($payload['mind_map'] ?? []);

            foreach ($templateTasks as $task) {
                $checklists += count($task['checklist'] ?? []);
                if (! empty($task['recurrence'])) {
                    $recurrences++;
                }
            }
        }

        $featureRows = [
            ['title' => 'Organização e visualização', 'meta' => 'Dashboards, Everything View, Kanban, Calendário, Me Mode e LineUp', 'status' => $views . ' visão(ões)', 'tone' => $views > 0 ? 'success' : 'warning', 'description' => 'Total de visões configuradas nos templates ativos e inativos.'],
            ['title' => 'Campos personalizados', 'meta' => 'Moeda, fórmula, etiqueta, menu, data, pessoa, número e texto', 'status' => $customFields . ' campo(s)', 'tone' => $customFields > 0 ? 'success' : 'warning', 'description' => 'Campos gravados no payload e reaproveitados nas tarefas criadas pelo template.'],
            ['title' => 'Automação e velocidade', 'meta' => 'Regras do tipo Quando/Faça e tarefas recorrentes', 'status' => ($automations + $recurrences) . ' regra(s)', 'tone' => ($automations + $recurrences) > 0 ? 'success' : 'warning', 'description' => 'Automações e recorrências salvas para orientar a execução operacional.'],
            ['title' => 'Colaboração e documentação', 'meta' => 'Checklists, aprovações, documentação interna, proofing e comentários', 'status' => ($checklists + $docs) . ' item(ns)', 'tone' => ($checklists + $docs) > 0 ? 'success' : 'warning', 'description' => 'Estrutura colaborativa que acompanha cada tarefa gerada.'],
            ['title' => 'Planejamento visual', 'meta' => 'Mind map e quebra do processo em tarefas executáveis', 'status' => $mindMapNodes . ' nó(s)', 'tone' => $mindMapNodes > 0 ? 'success' : 'warning', 'description' => 'Nós de mapa mental cadastrados nos templates para organizar fluxos complexos.'],
        ];

        return [
            'module' => 'templates-enterprise',
            'group' => 'TEMPLATES',
            'title' => 'Templates Enterprise',
            'subtitle' => 'Central para criar, organizar e aplicar modelos reais de trabalho com tarefas, checklists, campos personalizados, automações, visões e documentação.',
            'cards' => [
                ['label' => 'Templates salvos', 'value' => count($templates), 'tone' => 'info', 'hint' => 'Modelos cadastrados'],
                ['label' => 'Tarefas geráveis', 'value' => $tasks, 'tone' => 'success', 'hint' => 'Criadas ao aplicar'],
                ['label' => 'Automações/recorrências', 'value' => $automations + $recurrences, 'tone' => 'warning', 'hint' => 'Regras configuradas'],
                ['label' => 'Campos e visões', 'value' => $customFields + $views, 'tone' => 'danger', 'hint' => 'Organização avançada'],
            ],
            'sections' => [
                ['title' => 'Templates cadastrados no banco', 'description' => 'Use o botão “Gerenciar templates” para criar, editar e aplicar modelos em empresas reais.', 'items' => $templates],
                ['title' => 'Maturidade da biblioteca', 'description' => 'Resumo calculado a partir do payload dos templates cadastrados.', 'items' => $featureRows],
            ],
            'quickActions' => [
                ['label' => 'Gerenciar templates', 'url' => PrazzuTemplateResource::getUrl('index')],
                ['label' => 'Novo template', 'url' => PrazzuTemplateResource::getUrl('create')],
                ...$this->quickActions(),
            ],
        ];
    }

    private function assistenteOperacional(): array
    {
        $questions = [
            ['title' => 'Quais contratos vencem este mês?', 'meta' => 'Consulta interna', 'status' => $this->contractsDueThisMonthCount() . ' encontrado(s)', 'tone' => 'warning', 'description' => 'Baseado em contrato_fim_em, sem IA externa.'],
            ['title' => 'Quais clientes estão em atraso?', 'meta' => 'Consulta interna', 'status' => $this->lateCompaniesCount() . ' cliente(s)', 'tone' => 'danger', 'description' => 'Empresas vinculadas a itens vencidos ou cobrança vencida.'],
            ['title' => 'Quais tarefas estão atrasadas?', 'meta' => 'Consulta interna', 'status' => $this->lateItemsCount() . ' item(ns)', 'tone' => 'danger', 'description' => 'Itens com data_vencimento anterior a hoje e ainda abertos.'],
            ['title' => 'Quais documentos precisam revisão?', 'meta' => 'Consulta interna', 'status' => $this->documentsMissingFile() . ' pendência(s)', 'tone' => 'info', 'description' => 'Itens sem arquivo ou com vencimento documental crítico.'],
            ['title' => 'Onde o SLA está mais perigoso?', 'meta' => 'Consulta interna', 'status' => $this->slaCriticalCount() . ' alerta(s)', 'tone' => 'warning', 'description' => 'Itens com SLA rompido ou próximo do limite.'],
        ];

        return [
            'module' => 'assistente-operacional',
            'group' => 'ASSISTENTE',
            'title' => 'Assistente Operacional Interno',
            'subtitle' => 'Perguntas de negócio respondidas por consultas internas, sem usar API ou IA externa.',
            'cards' => [
                ['label' => 'Perguntas prontas', 'value' => count($questions), 'tone' => 'info', 'hint' => 'Consultas internas'],
                ['label' => 'Atrasos', 'value' => $this->lateItemsCount(), 'tone' => 'danger', 'hint' => 'Tarefas e documentos'],
                ['label' => 'SLA crítico', 'value' => $this->slaCriticalCount(), 'tone' => 'warning', 'hint' => 'Risco operacional'],
                ['label' => 'Contratos mês', 'value' => $this->contractsDueThisMonthCount(), 'tone' => 'success', 'hint' => 'Vencimentos'],
            ],
            'sections' => [
                ['title' => 'Perguntas operacionais', 'description' => 'Respostas úteis para operação diária sem depender de serviços externos.', 'items' => $questions],
                ['title' => 'Itens que explicam as respostas', 'description' => 'Amostra dos principais itens críticos.', 'items' => $this->riskRows(12)],
            ],
            'quickActions' => $this->quickActions(),
        ];
    }

    private function navegacaoContextual(): array
    {
        return [
            'module' => 'navegacao-contextual',
            'group' => 'CLIENTES',
            'title' => 'Navegação Contextual por Cliente',
            'subtitle' => 'Entre em um cliente e enxergue tarefas, documentos, contratos, cobranças, SLA e timeline em contexto.',
            'cards' => [
                ['label' => 'Clientes', 'value' => $this->tableCount('empresas'), 'tone' => 'info', 'hint' => 'Carteira'],
                ['label' => 'Com pendências', 'value' => $this->lateCompaniesCount(), 'tone' => 'danger', 'hint' => 'Atraso ou cobrança'],
                ['label' => 'Com documentos', 'value' => $this->companiesWithDocumentsCount(), 'tone' => 'success', 'hint' => 'Arquivo/anexo'],
                ['label' => 'Com financeiro', 'value' => $this->companiesWithPaymentsCount(), 'tone' => 'warning', 'hint' => 'Pagamentos'],
            ],
            'sections' => [
                ['title' => 'Clientes em contexto', 'description' => 'Cada card mostra o conjunto operacional do cliente.', 'items' => $this->companyContextRows(40)],
            ],
            'quickActions' => $this->quickActions(),
        ];
    }

    private function itemsBase()
    {
        if (! $this->hasTable('item_controles')) {
            return DB::query()->fromSub('select null as id where 1 = 0', 'empty');
        }

        $query = DB::table('item_controles')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
            ->select([
                'item_controles.id',
                'item_controles.titulo',
                'item_controles.descricao',
                'item_controles.tipo',
                'item_controles.status',
                $this->selectColumn('item_controles', 'prioridade'),
                'item_controles.data_vencimento',
                $this->selectColumn('item_controles', 'sla_limite_em'),
                $this->selectColumn('item_controles', 'sla_concluido_em'),
                $this->selectColumn('item_controles', 'contrato_fim_em'),
                $this->selectColumn('item_controles', 'contrato_valor'),
                'item_controles.empresa_id',
                'empresas.nome_fantasia',
                'empresas.razao_social',
                'responsaveis.nome as responsavel_nome',
            ]);

        if ($this->hasColumn('item_controles', 'prioridade')) {
            $query->orderByRaw("CASE WHEN item_controles.prioridade IN ('critica','crítica','alta','urgente') THEN 0 ELSE 1 END");
        }

        return $query
            ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.data_vencimento');
    }

    private function approvalRows(int $limit): array
    {
        $rows = [];
        if ($this->hasTable('item_controle_aprovacoes')) {
            $rows = DB::table('item_controle_aprovacoes')
                ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_aprovacoes.item_controle_id')
                ->leftJoin('empresas', 'empresas.id', '=', 'item_controle_aprovacoes.empresa_id')
                ->select([
                    'item_controle_aprovacoes.id',
                    'item_controle_aprovacoes.item_controle_id',
                    'item_controle_aprovacoes.status',
                    'item_controle_aprovacoes.observacao_solicitacao',
                    'item_controle_aprovacoes.respondido_em',
                    'item_controle_aprovacoes.solicitado_em',
                    'item_controles.titulo',
                    'item_controles.tipo',
                    $this->selectColumn('item_controles', 'prioridade'),
                    'item_controles.data_vencimento',
                    'empresas.nome_fantasia',
                    'empresas.razao_social',
                ])
                ->orderByRaw("CASE WHEN item_controle_aprovacoes.status = 'pendente' THEN 0 ELSE 1 END")
                ->orderByDesc('item_controle_aprovacoes.solicitado_em')
                ->limit($limit)
                ->get()
                ->map(fn ($item) => $this->decorateApproval((array) $item))
                ->all();
        }

        if (count($rows) < $limit && $this->hasColumn('item_controles', 'approval_required')) {
            $extra = $this->itemsBase()
                ->where('item_controles.approval_required', 1)
                ->whereNotIn('item_controles.approval_status', ['aprovado', 'aprovada'])
                ->limit($limit - count($rows))
                ->get()
                ->map(fn ($item) => $this->decorateItem((array) $item, 'Aprovação requerida'))
                ->all();
            $rows = array_merge($rows, $extra);
        }

        return $rows;
    }

    private function decorateApproval(array $item): array
    {
        $title = $item['titulo'] ?: 'Solicitação sem item vinculado';
        return [
            'title' => $title,
            'meta' => ($item['nome_fantasia'] ?: ($item['razao_social'] ?: 'Sem empresa')) . ' • ' . ucfirst((string) ($item['tipo'] ?: 'aprovação')),
            'status' => ucfirst(str_replace('_', ' ', (string) ($item['status'] ?: 'pendente'))),
            'tone' => ($item['status'] ?? '') === 'pendente' ? 'warning' : (in_array($item['status'] ?? '', ['reprovado', 'reprovada'], true) ? 'danger' : 'success'),
            'description' => Str::limit((string) ($item['observacao_solicitacao'] ?: 'Sem observação cadastrada.'), 180),
            'date' => $this->formatDateTime($item['solicitado_em'] ?? null),
            'url' => ! empty($item['item_controle_id']) ? ItemControleResource::getUrl('edit', ['record' => $item['item_controle_id']]) : null,
        ];
    }

    private function documentRows(int $limit): array
    {
        if (! $this->hasTable('item_controles')) return [];

        return DB::table('item_controles')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->select([
                'item_controles.id',
                'item_controles.titulo',
                'item_controles.tipo',
                'item_controles.status',
                $this->selectColumn('item_controles', 'arquivo'),
                'item_controles.data_vencimento',
                $this->selectColumn('item_controles', 'document_status'),
                $this->selectColumn('item_controles', 'portal_ativo'),
                'empresas.nome_fantasia',
                'empresas.razao_social',
            ])
            ->where(function ($query) {
                $query->whereNotNull('item_controles.arquivo')
                    ->orWhereIn('item_controles.tipo', ['documento', 'contrato', 'compliance', 'auditoria']);
            })
            ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.data_vencimento')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $item = (array) $item;
                return [
                    'title' => $item['titulo'] ?: 'Documento sem título',
                    'meta' => ($item['nome_fantasia'] ?: ($item['razao_social'] ?: 'Sem empresa')) . ' • ' . ucfirst((string) ($item['tipo'] ?: 'documento')),
                    'status' => ucfirst(str_replace('_', ' ', (string) ($item['document_status'] ?: ($item['status'] ?: 'sem status')))),
                    'tone' => ! empty($item['data_vencimento']) && Carbon::parse($item['data_vencimento'])->isPast() ? 'danger' : 'info',
                    'description' => ! empty($item['arquivo']) ? 'Arquivo principal cadastrado.' : 'Sem arquivo principal; verificar anexos/versionamento.',
                    'date' => $this->formatDate($item['data_vencimento'] ?? null),
                    'url' => ItemControleResource::getUrl('edit', ['record' => $item['id']]),
                ];
            })
            ->all();
    }

    private function documentVersionRows(int $limit): array
    {
        if (! $this->hasTable('prazzu_document_versions')) return [];

        return DB::table('prazzu_document_versions')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'prazzu_document_versions.item_controle_id')
            ->select('prazzu_document_versions.id', 'prazzu_document_versions.item_controle_id', 'prazzu_document_versions.document_type', 'prazzu_document_versions.version_number', 'prazzu_document_versions.status', 'prazzu_document_versions.notes', 'prazzu_document_versions.created_at', 'item_controles.titulo')
            ->orderByDesc('prazzu_document_versions.created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'title' => ($item->titulo ?: 'Documento') . ' • v' . ($item->version_number ?: '-'),
                'meta' => ucfirst((string) ($item->document_type ?: 'documento')),
                'status' => ucfirst(str_replace('_', ' ', (string) ($item->status ?: 'pendente'))),
                'tone' => in_array($item->status, ['approved', 'aprovado'], true) ? 'success' : 'warning',
                'description' => Str::limit((string) ($item->notes ?: 'Sem observação da versão.'), 180),
                'date' => $this->formatDateTime($item->created_at),
                'url' => ! empty($item->item_controle_id) ? ItemControleResource::getUrl('edit', ['record' => $item->item_controle_id]) : null,
            ])
            ->all();
    }

    private function diffCandidates(): array
    {
        if (! $this->hasTable('prazzu_document_versions')) return [];

        return DB::table('prazzu_document_versions')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'prazzu_document_versions.item_controle_id')
            ->select('prazzu_document_versions.item_controle_id', 'item_controles.titulo', DB::raw('COUNT(*) as total_versions'), DB::raw('MAX(prazzu_document_versions.version_number) as last_version'), DB::raw('MAX(prazzu_document_versions.created_at) as last_update'))
            ->groupBy('prazzu_document_versions.item_controle_id', 'item_controles.titulo')
            ->having('total_versions', '>=', 2)
            ->orderByDesc('last_update')
            ->limit(12)
            ->get()
            ->map(fn ($item) => [
                'title' => $item->titulo ?: 'Documento sem título',
                'meta' => $item->total_versions . ' versões cadastradas',
                'status' => 'Comparar v' . max(((int) $item->last_version) - 1, 1) . ' × v' . ((int) $item->last_version),
                'tone' => 'info',
                'description' => 'Candidato para diff documental: revisar alterações entre as duas versões mais recentes.',
                'date' => $this->formatDateTime($item->last_update),
                'url' => ! empty($item->item_controle_id) ? ItemControleResource::getUrl('edit', ['record' => $item->item_controle_id]) : null,
            ])
            ->all();
    }

    private function automationRows(int $limit): array
    {
        if (! $this->hasTable('prazzu_automation_rules')) return [];

        return DB::table('prazzu_automation_rules')
            ->orderByDesc('active')
            ->orderBy('module')
            ->limit($limit)
            ->get()
            ->map(fn ($rule) => [
                'title' => $rule->name ?: 'Regra sem nome',
                'meta' => 'SE ' . ($rule->condition_field ?: $rule->trigger_type ?: 'evento') . ' ' . ($rule->condition_operator ?: '') . ' ' . ($rule->condition_value ?: '') . ' ENTÃO ' . ($rule->action_type ?: 'ação'),
                'status' => ! empty($rule->active) ? 'Ativa' : 'Inativa',
                'tone' => ! empty($rule->active) ? 'success' : 'warning',
                'description' => 'Módulo: ' . ($rule->module ?: 'geral') . '. Valor da ação: ' . ($rule->action_value ?: '-'),
                'date' => $this->formatDateTime($rule->updated_at ?: $rule->created_at),
            ])
            ->all();
    }

    private function billingRows(int $limit): array
    {
        if (! $this->hasTable('pagamentos')) return [];

        return DB::table('pagamentos')
            ->leftJoin('empresas', 'empresas.id', '=', 'pagamentos.empresa_id')
            ->select('pagamentos.id', 'pagamentos.status', 'pagamentos.valor', 'pagamentos.vencimento', 'pagamentos.pago_em', 'empresas.nome_fantasia', 'empresas.razao_social')
            ->where(function ($query) {
                $query->whereNull('pagamentos.pago_em')
                    ->orWhereIn('pagamentos.status', ['PENDING', 'CREATED', 'PAYMENT_CREATED', 'OVERDUE', 'PAYMENT_OVERDUE']);
            })
            ->orderByRaw('CASE WHEN pagamentos.vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('pagamentos.vencimento')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'title' => $item->nome_fantasia ?: ($item->razao_social ?: 'Cliente não informado'),
                'meta' => 'Pagamento #' . $item->id,
                'status' => 'R$ ' . number_format((float) $item->valor, 2, ',', '.') . ' • ' . ($item->status ?: 'aberto'),
                'tone' => ! empty($item->vencimento) && Carbon::parse($item->vencimento)->isPast() ? 'danger' : 'warning',
                'description' => 'Vencimento financeiro local usado pela cobrança inteligente interna.',
                'date' => $this->formatDate($item->vencimento),
            ])
            ->all();
    }

    private function documentosVencidosRows(int $limit): array
    {
        return $this->itemsBase()
            ->whereIn('item_controles.tipo', ['documento', 'contrato', 'compliance', 'auditoria'])
            ->whereNotNull('item_controles.data_vencimento')
            ->whereDate('item_controles.data_vencimento', '<', now()->toDateString())
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->limit($limit)
            ->get()
            ->map(fn ($item) => $this->decorateItem((array) $item, 'Documento vencido'))
            ->all();
    }

    private function contractsDueRows(int $limit): array
    {
        if (! $this->hasColumn('item_controles', 'contrato_fim_em')) return [];
        return $this->itemsBase()
            ->whereNotNull('item_controles.contrato_fim_em')
            ->whereBetween('item_controles.contrato_fim_em', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->limit($limit)
            ->get()
            ->map(fn ($item) => $this->decorateItem((array) $item, 'Contrato vencendo'))
            ->all();
    }

    private function slaRows(int $limit): array
    {
        if (! $this->hasColumn('item_controles', 'sla_limite_em')) return [];
        return $this->itemsBase()
            ->whereNotNull('item_controles.sla_limite_em')
            ->whereNull('item_controles.sla_concluido_em')
            ->where('item_controles.sla_limite_em', '<=', now()->addHours(12))
            ->limit($limit)
            ->get()
            ->map(fn ($item) => $this->decorateItem((array) $item, 'SLA crítico'))
            ->all();
    }

    private function riskRows(int $limit): array
    {
        if (! $this->hasTable('item_controles')) return [];

        return $this->itemsBase()
            ->limit($limit * 3)
            ->get()
            ->map(function ($item) {
                $item = (array) $item;
                $score = 0;
                if (! empty($item['data_vencimento']) && Carbon::parse($item['data_vencimento'])->isPast() && ! in_array($item['status'] ?? '', self::DONE_STATUSES, true)) $score += 35;
                if (! empty($item['sla_limite_em']) && empty($item['sla_concluido_em']) && Carbon::parse($item['sla_limite_em'])->lte(now()->addHours(12))) $score += 30;
                if (in_array($item['prioridade'] ?? '', ['alta', 'critica', 'crítica'], true)) $score += 20;
                if (in_array($item['tipo'] ?? '', ['contrato', 'documento', 'compliance'], true)) $score += 10;
                return array_merge($this->decorateItem($item, 'Risco ' . min($score, 100)), ['score' => min($score, 100)]);
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->all();
    }

    private function decorateItem(array $item, string $status): array
    {
        return [
            'title' => $item['titulo'] ?: 'Item sem título',
            'meta' => ($item['nome_fantasia'] ?: ($item['razao_social'] ?: 'Sem empresa')) . ' • ' . ($item['responsavel_nome'] ?: 'Sem responsável'),
            'status' => $status,
            'tone' => str_contains(Str::lower($status), 'atras') || str_contains(Str::lower($status), 'crítico') || str_contains(Str::lower($status), 'vencido') ? 'danger' : 'warning',
            'description' => Str::limit((string) ($item['descricao'] ?: 'Sem descrição cadastrada.'), 180),
            'date' => $this->formatDate($item['data_vencimento'] ?? $item['contrato_fim_em'] ?? null),
            'url' => ! empty($item['id']) ? ItemControleResource::getUrl('edit', ['record' => $item['id']]) : null,
        ];
    }

    private function templateRows(int $limit): array
    {
        if (! $this->hasTable('prazzu_templates')) return [];
        return DB::table('prazzu_templates')
            ->orderByDesc('active')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'title' => $item->name ?: 'Template sem nome',
                'meta' => ucfirst((string) ($item->module ?: 'geral')),
                'status' => ! empty($item->active) ? 'Ativo' : 'Inativo',
                'tone' => ! empty($item->active) ? 'success' : 'warning',
                'description' => Str::limit((string) ($item->description ?: 'Sem descrição cadastrada.'), 180),
                'date' => $this->formatDateTime($item->updated_at ?: $item->created_at),
            ])
            ->all();
    }

    private function companyContextRows(int $limit): array
    {
        if (! $this->hasTable('empresas')) return [];

        return DB::table('empresas')
            ->select('empresas.id', 'empresas.nome_fantasia', 'empresas.razao_social', 'empresas.email', 'empresas.status')
            ->orderBy('empresas.nome_fantasia')
            ->limit($limit)
            ->get()
            ->map(function ($company) {
                $items = $this->hasTable('item_controles') ? DB::table('item_controles')->where('empresa_id', $company->id)->count() : 0;
                $late = $this->hasTable('item_controles') ? DB::table('item_controles')->where('empresa_id', $company->id)->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString())->whereNotIn('item_controles.status', self::DONE_STATUSES)->count() : 0;
                $docs = $this->hasTable('item_controles') ? DB::table('item_controles')->where('empresa_id', $company->id)->where(function ($q) { $q->whereNotNull('arquivo')->orWhereIn('tipo', ['documento','contrato','compliance']); })->count() : 0;
                $payments = $this->hasTable('pagamentos') ? DB::table('pagamentos')->where('empresa_id', $company->id)->whereNull('pago_em')->count() : 0;
                $tone = $late > 0 || $payments > 0 ? 'danger' : 'success';

                return [
                    'title' => $company->nome_fantasia ?: ($company->razao_social ?: 'Empresa sem nome'),
                    'meta' => $company->email ?: 'Sem e-mail',
                    'status' => $items . ' itens • ' . $docs . ' docs • ' . $payments . ' financeiro',
                    'tone' => $tone,
                    'description' => $late > 0 ? $late . ' pendência(s) atrasada(s) para priorizar.' : 'Operação sem atraso crítico encontrado.',
                ];
            })
            ->all();
    }

    private function globalTimelineRows(int $limit): array
    {
        $rows = [];
        if ($this->hasTable('item_controle_timeline')) {
            $rows = array_merge($rows, DB::table('item_controle_timeline')
                ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_timeline.item_controle_id')
                ->select('item_controle_timeline.titulo', 'item_controle_timeline.descricao', 'item_controle_timeline.tipo', 'item_controle_timeline.created_at', 'item_controles.titulo as item_titulo', 'item_controles.id as item_id')
                ->orderByDesc('item_controle_timeline.created_at')
                ->limit($limit)
                ->get()
                ->map(fn ($item) => [
                    'title' => $item->titulo ?: 'Evento de timeline',
                    'meta' => ($item->item_titulo ?: 'Sem item') . ' • ' . ucfirst((string) ($item->tipo ?: 'evento')),
                    'status' => 'Timeline',
                    'tone' => 'info',
                    'description' => Str::limit((string) ($item->descricao ?: 'Sem descrição.'), 180),
                    'date' => $this->formatDateTime($item->created_at),
                    'sort_date' => $item->created_at,
                    'url' => ! empty($item->item_id) ? ItemControleResource::getUrl('edit', ['record' => $item->item_id]) : null,
                ])->all());
        }

        if ($this->hasTable('item_controle_comentarios')) {
            $rows = array_merge($rows, DB::table('item_controle_comentarios')
                ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_comentarios.item_controle_id')
                ->select('item_controle_comentarios.comentario', 'item_controle_comentarios.tipo', 'item_controle_comentarios.created_at', 'item_controles.titulo as item_titulo', 'item_controles.id as item_id')
                ->orderByDesc('item_controle_comentarios.created_at')
                ->limit($limit)
                ->get()
                ->map(fn ($item) => [
                    'title' => 'Comentário',
                    'meta' => ($item->item_titulo ?: 'Sem item') . ' • ' . ucfirst((string) ($item->tipo ?: 'comentário')),
                    'status' => 'Comentário',
                    'tone' => 'success',
                    'description' => Str::limit((string) ($item->comentario ?: 'Comentário sem texto.'), 180),
                    'date' => $this->formatDateTime($item->created_at),
                    'sort_date' => $item->created_at,
                    'url' => ! empty($item->item_id) ? ItemControleResource::getUrl('edit', ['record' => $item->item_id]) : null,
                ])->all());
        }

        usort($rows, fn ($a, $b) => strcmp((string) ($b['sort_date'] ?? ''), (string) ($a['sort_date'] ?? '')));
        return array_slice($rows, 0, $limit);
    }

    private function weeklyKpiRows(): array
    {
        return [
            ['title' => 'Produtividade', 'meta' => 'Itens concluídos', 'status' => $this->doneItemsCount() . ' concluído(s)', 'tone' => 'success', 'description' => 'Mede saída operacional acumulada.'],
            ['title' => 'Gargalo', 'meta' => 'Itens atrasados', 'status' => $this->lateItemsCount() . ' atraso(s)', 'tone' => 'danger', 'description' => 'Principal métrica de atenção para gestão.'],
            ['title' => 'SLA', 'meta' => 'Crítico/risco', 'status' => $this->slaCriticalCount() . ' alerta(s)', 'tone' => 'warning', 'description' => 'Controle visual de cumprimento de prazo.'],
            ['title' => 'Financeiro', 'meta' => 'Valor vencido', 'status' => 'R$ ' . number_format($this->overdueBillingValue(), 2, ',', '.'), 'tone' => 'info', 'description' => 'Carteira local em recuperação.'],
        ];
    }

    private function quickActions(): array
    {
        return [
            ['label' => 'Criar item', 'url' => ItemControleResource::getUrl('create')],
            ['label' => 'Ver itens', 'url' => ItemControleResource::getUrl('index')],
            ['label' => 'Pendências', 'url' => Pendencias::getUrl()],
            ['label' => 'Relatórios internos', 'url' => ItemControleResource::getUrl('relatorios-internos')],
        ];
    }

    private function hasTable(string $table): bool { return CachedSchema::hasTable($table); }
    private function hasColumn(string $table, string $column): bool { return $this->hasTable($table) && CachedSchema::hasColumn($table, $column); }
    private function selectColumn(string $table, string $column, ?string $alias = null): mixed
    {
        $alias ??= $column;

        return $this->hasColumn($table, $column)
            ? DB::raw($table . '.' . $column . ' as ' . $alias)
            : DB::raw('null as ' . $alias);
    }
    private function tableCount(string $table): int { return $this->hasTable($table) ? DB::table($table)->count() : 0; }

    private function countApprovals(string|array $status): int
    {
        if (! $this->hasTable('item_controle_aprovacoes')) return 0;
        $query = DB::table('item_controle_aprovacoes');
        return is_array($status) ? $query->whereIn('status', $status)->count() : $query->where('status', $status)->count();
    }

    private function approvalsToday(): int
    {
        return $this->hasTable('item_controle_aprovacoes') ? DB::table('item_controle_aprovacoes')->whereDate('solicitado_em', now()->toDateString())->count() : 0;
    }

    private function documentsCount(): int
    {
        if (! $this->hasTable('item_controles')) return 0;
        return DB::table('item_controles')->where(function ($q) { $q->whereNotNull('arquivo')->orWhereIn('tipo', ['documento','contrato','compliance','auditoria']); })->count() + $this->tableCount('item_controle_anexos');
    }

    private function documentsMissingFile(): int
    {
        return $this->hasTable('item_controles') ? DB::table('item_controles')->whereIn('tipo', ['documento','contrato','compliance','auditoria'])->whereNull('arquivo')->count() : 0;
    }

    private function documentosVencidos(): int
    {
        return $this->hasTable('item_controles') ? DB::table('item_controles')->whereIn('tipo', ['documento','contrato','compliance','auditoria'])->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString())->whereNotIn('item_controles.status', self::DONE_STATUSES)->count() : 0;
    }

    private function documentStatusCount(array $statuses): int
    {
        if (! $this->hasColumn('item_controles', 'document_status')) return 0;
        $query = DB::table('item_controles')->whereIn('tipo', ['documento','contrato','compliance','auditoria']);
        if (in_array(null, $statuses, true)) {
            $nonNull = array_values(array_filter($statuses, fn ($status) => $status !== null));
            $query->where(function ($q) use ($nonNull) {
                $q->whereNull('document_status');
                if (! empty($nonNull)) $q->orWhereIn('document_status', $nonNull);
            });
        } else {
            $query->whereIn('document_status', $statuses);
        }
        return $query->count();
    }

    private function automationActiveCount(): int
    {
        return $this->hasTable('prazzu_automation_rules') ? DB::table('prazzu_automation_rules')->where('active', 1)->count() : 0;
    }

    private function activeTemplatesCount(): int
    {
        return $this->hasTable('prazzu_templates') ? DB::table('prazzu_templates')->where('active', 1)->count() : 0;
    }

    private function doneItemsCount(): int
    {
        return $this->hasTable('item_controles') ? DB::table('item_controles')->whereIn('item_controles.status', self::DONE_STATUSES)->count() : 0;
    }

    private function lateItemsCount(): int
    {
        return $this->hasTable('item_controles') ? DB::table('item_controles')->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString())->whereNotIn('item_controles.status', self::DONE_STATUSES)->count() : 0;
    }

    private function slaCriticalCount(): int
    {
        return $this->hasColumn('item_controles', 'sla_limite_em') ? DB::table('item_controles')->whereNotNull('sla_limite_em')->whereNull('sla_concluido_em')->where('sla_limite_em', '<=', now()->addHours(12))->count() : 0;
    }

    private function overdueBillingValue(): float
    {
        return $this->hasTable('pagamentos') ? (float) DB::table('pagamentos')->whereNull('pago_em')->whereDate('vencimento', '<', now()->toDateString())->sum('valor') : 0.0;
    }

    private function contractsDueThisMonthCount(): int
    {
        return $this->hasColumn('item_controles', 'contrato_fim_em') ? DB::table('item_controles')->whereNotNull('contrato_fim_em')->whereBetween('contrato_fim_em', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->count() : 0;
    }

    private function lateCompaniesCount(): int
    {
        if (! $this->hasTable('empresas')) return 0;
        $ids = [];
        if ($this->hasTable('item_controles')) {
            $ids = array_merge($ids, DB::table('item_controles')->whereNotNull('empresa_id')->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString())->whereNotIn('item_controles.status', self::DONE_STATUSES)->pluck('empresa_id')->all());
        }
        if ($this->hasTable('pagamentos')) {
            $ids = array_merge($ids, DB::table('pagamentos')->whereNotNull('empresa_id')->whereNull('pago_em')->whereDate('vencimento', '<', now()->toDateString())->pluck('empresa_id')->all());
        }
        return count(array_unique(array_filter($ids)));
    }

    private function companiesWithDocumentsCount(): int
    {
        return $this->hasTable('item_controles') ? DB::table('item_controles')->whereNotNull('empresa_id')->where(function ($q) { $q->whereNotNull('arquivo')->orWhereIn('tipo', ['documento','contrato','compliance']); })->distinct('empresa_id')->count('empresa_id') : 0;
    }

    private function companiesWithPaymentsCount(): int
    {
        return $this->hasTable('pagamentos') ? DB::table('pagamentos')->whereNotNull('empresa_id')->distinct('empresa_id')->count('empresa_id') : 0;
    }


    private function activeCompaniesCount(): int
    {
        if (! $this->hasTable('empresas')) return 0;

        $query = DB::table('empresas');

        if ($this->hasColumn('empresas', 'ativo')) {
            $query->where('ativo', 1);
        }

        if ($this->hasColumn('empresas', 'status')) {
            $query->where(function ($q) {
                $q->whereNull('status')->orWhereIn('status', ['ativo', 'ativa', 'active']);
            });
        }

        return $query->count();
    }

    private function openItemsCount(): int
    {
        return $this->hasTable('item_controles')
            ? DB::table('item_controles')->whereNotIn('item_controles.status', self::DONE_STATUSES)->count()
            : 0;
    }

    private function pendingDocumentsCount(): int
    {
        if (! $this->hasTable('item_controles')) return 0;

        return DB::table('item_controles')
            ->whereIn('tipo', ['documento', 'contrato', 'compliance', 'auditoria'])
            ->where(function ($q) {
                $q->whereNull('arquivo')
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('data_vencimento')
                            ->whereDate('data_vencimento', '<', now()->toDateString());
                    });

                if ($this->hasColumn('item_controles', 'document_status')) {
                    $q->orWhereIn('document_status', ['pendente', 'em_revisao', 'aguardando_aprovacao', 'reprovado']);
                }
            })
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->count();
    }

    private function revenueThisMonth(): float
    {
        if (! $this->hasTable('pagamentos')) return 0.0;

        return (float) DB::table('pagamentos')
            ->whereNotNull('pago_em')
            ->whereBetween('pago_em', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('valor');
    }

    private function executiveFunnelRows(float $completionRate): array
    {
        return [
            ['step' => 'Carteira ativa', 'count' => $this->activeCompaniesCount(), 'description' => 'Clientes ativos que compõem a base do escritório.'],
            ['step' => 'Demandas abertas', 'count' => $this->openItemsCount(), 'description' => 'Tudo que ainda exige execução da equipe.'],
            ['step' => 'Atrasos', 'count' => $this->lateItemsCount(), 'description' => 'Backlog vencido que precisa de decisão.'],
            ['step' => 'SLA crítico', 'count' => $this->slaCriticalCount(), 'description' => 'Risco de quebra de prazo e insatisfação.'],
            ['step' => 'Conclusão', 'count' => (int) round($completionRate), 'description' => 'Percentual geral de itens finalizados.'],
        ];
    }

    private function executiveKpiRows(float $completionRate): array
    {
        $lateItems = $this->lateItemsCount();
        $overdueValue = $this->overdueBillingValue();
        $pendingDocuments = $this->pendingDocumentsCount();
        $sla = $this->slaCriticalCount();

        return [
            ['title' => 'Saúde operacional', 'meta' => 'Conclusão geral', 'status' => number_format($completionRate, 1, ',', '.') . '% concluído', 'tone' => $completionRate >= 70 ? 'success' : ($completionRate >= 45 ? 'warning' : 'danger'), 'description' => 'Quanto maior a conclusão, menor a pressão operacional acumulada.'],
            ['title' => 'Backlog vencido', 'meta' => 'Prazos', 'status' => $lateItems . ' item(ns)', 'tone' => $lateItems > 0 ? 'danger' : 'success', 'description' => 'Tarefas e controles vencidos que ainda não foram encerrados.'],
            ['title' => 'Exposição financeira', 'meta' => 'Cobranças vencidas', 'status' => 'R$ ' . number_format($overdueValue, 2, ',', '.'), 'tone' => $overdueValue > 0 ? 'danger' : 'success', 'description' => 'Valor vencido afeta caixa, continuidade e negociação com clientes.'],
            ['title' => 'Documentos que travam entrega', 'meta' => 'Documental', 'status' => $pendingDocuments . ' pendência(s)', 'tone' => $pendingDocuments > 0 ? 'warning' : 'success', 'description' => 'Documentos ausentes, vencidos ou aguardando regularização.'],
            ['title' => 'Risco de SLA', 'meta' => 'Atendimento e execução', 'status' => $sla . ' alerta(s)', 'tone' => $sla > 0 ? 'warning' : 'success', 'description' => 'Itens próximos do limite ou já sem conclusão registrada.'],
        ];
    }

    private function teamProductivityRows(int $limit): array
    {
        if (! $this->hasTable('responsaveis') || ! $this->hasTable('item_controles')) return [];

        return DB::table('responsaveis')
            ->leftJoin('item_controles', 'item_controles.responsavel_id', '=', 'responsaveis.id')
            ->select([
                'responsaveis.id',
                'responsaveis.nome',
                'responsaveis.cargo',
                DB::raw('COUNT(item_controles.id) as total_itens'),
                DB::raw("SUM(CASE WHEN item_controles.status IN ('" . implode("','", self::DONE_STATUSES) . "') THEN 1 ELSE 0 END) as concluidos"),
                DB::raw("SUM(CASE WHEN item_controles.data_vencimento IS NOT NULL AND item_controles.data_vencimento < CURRENT_DATE AND item_controles.status NOT IN ('" . implode("','", self::DONE_STATUSES) . "') THEN 1 ELSE 0 END) as atrasados"),
            ])
            ->groupBy('responsaveis.id', 'responsaveis.nome', 'responsaveis.cargo')
            ->orderByDesc('atrasados')
            ->orderByDesc('total_itens')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $total = (int) $row->total_itens;
                $done = (int) $row->concluidos;
                $late = (int) $row->atrasados;
                $rate = $total > 0 ? ($done / $total) * 100 : 0;

                return [
                    'title' => $row->nome ?: 'Responsável sem nome',
                    'meta' => $row->cargo ?: 'Equipe',
                    'status' => $late > 0 ? $late . ' atraso(s)' : number_format($rate, 0, ',', '.') . '% concluído',
                    'tone' => $late > 0 ? 'danger' : ($total > 0 ? 'success' : 'info'),
                    'description' => $total . ' item(ns) vinculados, ' . $done . ' concluído(s). Use este indicador para equilibrar carteira, cobrança e prazos.',
                ];
            })
            ->all();
    }

    private function operationalBottleneckRows(int $limit): array
    {
        if (! $this->hasTable('item_controles')) return [];

        $done = "'" . implode("','", self::DONE_STATUSES) . "'";
        $select = [
            DB::raw("COALESCE(NULLIF(tipo, ''), 'Sem tipo') as tipo_label"),
            DB::raw('COUNT(*) as total_itens'),
            DB::raw("SUM(CASE WHEN status NOT IN ($done) THEN 1 ELSE 0 END) as abertos"),
            DB::raw("SUM(CASE WHEN data_vencimento IS NOT NULL AND data_vencimento < CURRENT_DATE AND status NOT IN ($done) THEN 1 ELSE 0 END) as atrasados"),
        ];

        if ($this->hasColumn('item_controles', 'sla_limite_em') && $this->hasColumn('item_controles', 'sla_concluido_em')) {
            $select[] = DB::raw("SUM(CASE WHEN sla_limite_em IS NOT NULL AND sla_concluido_em IS NULL AND sla_limite_em <= DATE_ADD(NOW(), INTERVAL 12 HOUR) THEN 1 ELSE 0 END) as sla_critico");
        } else {
            $select[] = DB::raw('0 as sla_critico');
        }

        return DB::table('item_controles')
            ->select($select)
            ->groupBy(DB::raw("COALESCE(NULLIF(tipo, ''), 'Sem tipo')"))
            ->orderByDesc('atrasados')
            ->orderByDesc('sla_critico')
            ->orderByDesc('abertos')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $late = (int) $row->atrasados;
                $sla = (int) $row->sla_critico;
                $open = (int) $row->abertos;
                $total = (int) $row->total_itens;
                $tone = ($late + $sla) > 0 ? 'danger' : ($open > 0 ? 'warning' : 'success');

                return [
                    'title' => Str::headline((string) $row->tipo_label),
                    'meta' => $total . ' item(ns) no total • ' . $open . ' aberto(s)',
                    'status' => ($late + $sla) . ' crítico(s)',
                    'tone' => $tone,
                    'description' => $late . ' atraso(s) e ' . $sla . ' SLA(s) crítico(s). Este bloco mostra onde o gestor deve reforçar processo, responsável ou cobrança do cliente.',
                ];
            })
            ->all();
    }

    private function priorityCompanyRows(int $limit): array
    {
        if (! $this->hasTable('empresas')) return [];

        return DB::table('empresas')
            ->select('empresas.id', 'empresas.nome_fantasia', 'empresas.razao_social', 'empresas.email', 'empresas.updated_at')
            ->get()
            ->map(function ($company) {
                $late = $this->hasTable('item_controles')
                    ? DB::table('item_controles')->where('empresa_id', $company->id)->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString())->whereNotIn('item_controles.status', self::DONE_STATUSES)->count()
                    : 0;
                $open = $this->hasTable('item_controles')
                    ? DB::table('item_controles')->where('empresa_id', $company->id)->whereNotIn('item_controles.status', self::DONE_STATUSES)->count()
                    : 0;
                $docs = $this->hasTable('item_controles')
                    ? DB::table('item_controles')->where('empresa_id', $company->id)->whereIn('tipo', ['documento', 'contrato', 'compliance', 'auditoria'])->where(function ($q) {
                        $q->whereNull('arquivo')->orWhere(function ($sub) {
                            $sub->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString());
                        });
                    })->whereNotIn('item_controles.status', self::DONE_STATUSES)->count()
                    : 0;
                $billing = $this->hasTable('pagamentos')
                    ? (float) DB::table('pagamentos')->where('empresa_id', $company->id)->whereNull('pago_em')->whereDate('vencimento', '<', now()->toDateString())->sum('valor')
                    : 0.0;
                $lastActivity = $this->lastCompanyActivity($company->id, $company->updated_at);
                $daysWithoutActivity = $lastActivity ? now()->diffInDays(Carbon::parse($lastActivity)) : 999;
                $score = ($late * 4) + ($docs * 3) + ($billing > 0 ? 5 : 0) + ($open * 1) + ($daysWithoutActivity >= 30 ? 2 : 0);

                return [
                    'score' => $score,
                    'title' => $company->nome_fantasia ?: ($company->razao_social ?: 'Empresa sem nome'),
                    'meta' => $company->email ?: 'Sem e-mail cadastrado',
                    'status' => $score > 0 ? 'Prioridade ' . $score : 'Saudável',
                    'tone' => $score >= 8 ? 'danger' : ($score >= 3 ? 'warning' : 'success'),
                    'description' => $late . ' atraso(s), ' . $docs . ' documento(s) pendente(s), R$ ' . number_format($billing, 2, ',', '.') . ' vencido(s) e última movimentação há ' . $daysWithoutActivity . ' dia(s).',
                ];
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->map(fn ($row) => collect($row)->except('score')->all())
            ->all();
    }

    private function actionPlanRows(float $completionRate): array
    {
        $late = $this->lateItemsCount();
        $sla = $this->slaCriticalCount();
        $docs = $this->pendingDocumentsCount();
        $billing = $this->overdueBillingValue();
        $unassigned = $this->unassignedItemsCount();
        $overloaded = $this->overloadedTeamCount();

        $rows = [
            ['title' => 'Resolver atrasos de maior impacto', 'meta' => 'Prazos vencidos', 'status' => $late . ' item(ns)', 'tone' => $late > 0 ? 'danger' : 'success', 'description' => $late > 0 ? 'Comece pelos itens vencidos com cliente e responsável definidos. Isso reduz reclamação, multa e retrabalho.' : 'Não há atraso operacional vencido no momento.'],
            ['title' => 'Blindar SLA antes de virar reclamação', 'meta' => 'Próximas 12 horas', 'status' => $sla . ' alerta(s)', 'tone' => $sla > 0 ? 'warning' : 'success', 'description' => $sla > 0 ? 'Priorize retorno rápido ao cliente e registre conclusão ou justificativa no item.' : 'Não há SLA crítico calculado com os campos atuais.'],
            ['title' => 'Cobrar documentos que travam entrega', 'meta' => 'Documental', 'status' => $docs . ' pendência(s)', 'tone' => $docs > 0 ? 'warning' : 'success', 'description' => $docs > 0 ? 'Transforme documentos pendentes em cobrança objetiva para o cliente, com prazo e responsável.' : 'Base documental sem pendência crítica encontrada.'],
            ['title' => 'Revisar carteira financeira vencida', 'meta' => 'Cobrança', 'status' => 'R$ ' . number_format($billing, 2, ',', '.'), 'tone' => $billing > 0 ? 'danger' : 'success', 'description' => $billing > 0 ? 'Acompanhe clientes com cobrança vencida antes de liberar novas demandas complexas.' : 'Nenhum valor vencido encontrado em pagamentos.'],
            ['title' => 'Dar dono para tudo que está solto', 'meta' => 'Responsabilidade', 'status' => $unassigned . ' item(ns)', 'tone' => $unassigned > 0 ? 'warning' : 'success', 'description' => $unassigned > 0 ? 'Itens sem responsável tendem a desaparecer da rotina. Atribua responsável antes de cobrar prazo.' : 'Todos os itens possuem responsável pelos campos atuais.'],
            ['title' => 'Equilibrar equipe', 'meta' => 'Sobrecarga', 'status' => $overloaded . ' pessoa(s)', 'tone' => $overloaded > 0 ? 'danger' : 'success', 'description' => $overloaded > 0 ? 'Reveja distribuição de tarefas dos responsáveis com maior acúmulo de atraso.' : 'Não foi encontrada sobrecarga crítica por responsável.'],
            ['title' => 'Acompanhar conclusão geral', 'meta' => 'Saúde da operação', 'status' => number_format($completionRate, 1, ',', '.') . '%', 'tone' => $completionRate >= 70 ? 'success' : ($completionRate >= 45 ? 'warning' : 'danger'), 'description' => 'Indicador simples para reunião semanal: abaixo de 45% pede revisão de processo e foco no backlog.'],
        ];

        return $rows;
    }

    private function smartAlertRows(): array
    {
        $stopped = $this->companiesWithoutRecentInteractionCount();
        $unassigned = $this->unassignedItemsCount();
        $urgentTickets = $this->urgentAttendancesCount();
        $contracts = $this->contractsDueThisMonthCount();
        $blocked = $this->blockedItemsCount();

        return [
            ['title' => 'Clientes sem movimento recente', 'meta' => 'Relacionamento', 'status' => $stopped . ' cliente(s)', 'tone' => $stopped > 0 ? 'warning' : 'success', 'description' => $stopped > 0 ? 'Clientes sem atualização operacional ou atendimento recente podem estar sem acompanhamento claro.' : 'A carteira mostra movimentação recente suficiente.'],
            ['title' => 'Itens sem responsável definido', 'meta' => 'Governança operacional', 'status' => $unassigned . ' item(ns)', 'tone' => $unassigned > 0 ? 'warning' : 'success', 'description' => $unassigned > 0 ? 'Sem dono, não há cobrança justa de prazo. Ação recomendada: atribuir responsável.' : 'Nenhum item sem responsável encontrado.'],
            ['title' => 'Atendimentos urgentes abertos', 'meta' => 'Cliente e suporte', 'status' => $urgentTickets . ' chamado(s)', 'tone' => $urgentTickets > 0 ? 'danger' : 'success', 'description' => $urgentTickets > 0 ? 'Chamados urgentes abertos precisam de retorno rápido e registro de primeira resposta.' : 'Não há atendimento urgente aberto.'],
            ['title' => 'Contratos vencendo no mês', 'meta' => 'Receita e renovação', 'status' => $contracts . ' contrato(s)', 'tone' => $contracts > 0 ? 'warning' : 'success', 'description' => $contracts > 0 ? 'Renovação deve ser acompanhada antes do vencimento para evitar perda de receita.' : 'Nenhum contrato vencendo neste mês pelos campos atuais.'],
            ['title' => 'Itens bloqueados por dependência', 'meta' => 'Fluxo operacional', 'status' => $blocked . ' bloqueio(s)', 'tone' => $blocked > 0 ? 'danger' : 'success', 'description' => $blocked > 0 ? 'Bloqueios indicam dependência entre etapas. A gestão deve remover impedimentos antes de cobrar execução.' : 'Sem bloqueios ativos por dependência.'],
        ];
    }

    private function overloadedTeamCount(): int
    {
        if (! $this->hasTable('responsaveis') || ! $this->hasTable('item_controles')) return 0;

        $done = "'" . implode("','", self::DONE_STATUSES) . "'";

        return DB::table('responsaveis')
            ->leftJoin('item_controles', 'item_controles.responsavel_id', '=', 'responsaveis.id')
            ->select('responsaveis.id')
            ->groupBy('responsaveis.id')
            ->havingRaw("SUM(CASE WHEN item_controles.data_vencimento IS NOT NULL AND item_controles.data_vencimento < CURRENT_DATE AND item_controles.status NOT IN ($done) THEN 1 ELSE 0 END) >= 3")
            ->count();
    }

    private function unassignedItemsCount(): int
    {
        if (! $this->hasTable('item_controles')) return 0;

        if (! $this->hasColumn('item_controles', 'responsavel_id')) return 0;

        return DB::table('item_controles')
            ->where(function ($q) { $q->whereNull('responsavel_id')->orWhere('responsavel_id', 0); })
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->count();
    }

    private function companiesWithoutRecentInteractionCount(): int
    {
        if (! $this->hasTable('empresas')) return 0;

        return DB::table('empresas')
            ->get(['id', 'updated_at'])
            ->filter(fn ($company) => $this->lastCompanyActivity($company->id, $company->updated_at) === null || Carbon::parse($this->lastCompanyActivity($company->id, $company->updated_at))->lt(now()->subDays(30)))
            ->count();
    }

    private function lastCompanyActivity(int|string $companyId, $fallback = null): mixed
    {
        $dates = [];

        if (! empty($fallback)) $dates[] = $fallback;

        if ($this->hasTable('item_controles')) {
            $lastItem = DB::table('item_controles')->where('empresa_id', $companyId)->max('updated_at');
            if (! empty($lastItem)) $dates[] = $lastItem;
        }

        if ($this->hasTable('atendimentos')) {
            $lastAttendance = DB::table('atendimentos')->where('empresa_id', $companyId)->max('updated_at');
            if (! empty($lastAttendance)) $dates[] = $lastAttendance;
        }

        if ($this->hasTable('pagamentos')) {
            $lastPayment = DB::table('pagamentos')->where('empresa_id', $companyId)->max('updated_at');
            if (! empty($lastPayment)) $dates[] = $lastPayment;
        }

        if (empty($dates)) return null;

        rsort($dates);
        return $dates[0];
    }

    private function urgentAttendancesCount(): int
    {
        if (! $this->hasTable('atendimentos')) return 0;

        return DB::table('atendimentos')
            ->where('prioridade', 'urgente')
            ->whereNotIn('status', ['resolvido', 'fechado', 'cancelado'])
            ->count();
    }

    private function blockedItemsCount(): int
    {
        if (! $this->hasColumn('item_controles', 'blocked_by_dependency')) return 0;

        return DB::table('item_controles')
            ->where('blocked_by_dependency', 1)
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->count();
    }

    private function monthlyEvolutionRows(int $months): array
    {
        $rows = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = now()->subMonths($i)->endOfMonth();
            $label = $start->translatedFormat('M/Y');

            $created = $this->hasTable('item_controles')
                ? DB::table('item_controles')->whereBetween('created_at', [$start, $end])->count()
                : 0;
            $done = $this->hasTable('item_controles')
                ? DB::table('item_controles')->whereIn('item_controles.status', self::DONE_STATUSES)->whereBetween('updated_at', [$start, $end])->count()
                : 0;
            $paid = $this->hasTable('pagamentos')
                ? (float) DB::table('pagamentos')->whereNotNull('pago_em')->whereBetween('pago_em', [$start, $end])->sum('valor')
                : 0.0;
            $overdue = $this->hasTable('pagamentos')
                ? (float) DB::table('pagamentos')->whereNull('pago_em')->whereBetween('vencimento', [$start->toDateString(), $end->toDateString()])->sum('valor')
                : 0.0;

            $tone = $overdue > 0 ? 'warning' : ($done >= $created ? 'success' : 'info');

            $rows[] = [
                'title' => ucfirst($label),
                'meta' => 'Criados: ' . $created . ' • Concluídos: ' . $done,
                'status' => 'Recebido R$ ' . number_format($paid, 2, ',', '.'),
                'tone' => $tone,
                'description' => 'Valor vencido no mês: R$ ' . number_format($overdue, 2, ',', '.') . '. Ajuda a comparar volume operacional com caixa recebido.',
            ];
        }

        return $rows;
    }

    private function executiveHealthScore(): array
    {
        $activeCompanies = max($this->activeCompaniesCount(), 1);
        $openItems = max($this->openItemsCount(), 1);
        $lateItems = $this->lateItemsCount();
        $slaCritical = $this->slaCriticalCount();
        $pendingDocuments = $this->pendingDocumentsCount();
        $lateCompanies = $this->lateCompaniesCount();
        $stoppedCompanies = $this->companiesWithoutRecentInteractionCount();
        $overloadedTeam = $this->overloadedTeamCount();
        $overdueValue = $this->overdueBillingValue();
        $revenueMonth = max($this->revenueThisMonth(), 1);

        $penalty = 0;
        $penalty += min(28, ($lateItems / $openItems) * 28);
        $penalty += min(18, ($slaCritical / $openItems) * 18);
        $penalty += min(16, ($pendingDocuments / $openItems) * 16);
        $penalty += min(14, ($lateCompanies / $activeCompanies) * 14);
        $penalty += min(10, ($stoppedCompanies / $activeCompanies) * 10);
        $penalty += min(8, $overloadedTeam * 2);
        $penalty += min(6, ($overdueValue / $revenueMonth) * 6);

        $score = max(0, min(100, (int) round(100 - $penalty)));

        return [
            'score' => $score,
            'label' => $score >= 90 ? 'Excelente' : ($score >= 75 ? 'Atenção' : 'Crítico'),
            'tone' => $score >= 90 ? 'success' : ($score >= 75 ? 'warning' : 'danger'),
        ];
    }

    private function executiveHealthRows(array $health): array
    {
        return [
            ['title' => 'Score executivo', 'meta' => 'Saúde geral do escritório', 'status' => $health['score'] . '/100 • ' . $health['label'], 'tone' => $health['tone'], 'description' => 'Calculado com atraso, SLA crítico, documentos pendentes, clientes em risco, clientes sem movimento, sobrecarga da equipe e inadimplência.'],
            ['title' => 'Regra de leitura', 'meta' => 'Interpretação simples', 'status' => '90+ excelente • 75-89 atenção • abaixo de 75 crítico', 'tone' => 'info', 'description' => 'O objetivo é o gestor bater o olho e saber se precisa agir hoje ou apenas acompanhar.'],
            ['title' => 'Maior objetivo da tela', 'meta' => 'Decisão rápida', 'status' => 'Reduzir ruído', 'tone' => 'success', 'description' => 'O dashboard prioriza leitura simples para cliente final: primeiro mostra o que importa, depois mostra onde clicar para resolver.'],
        ];
    }

    private function todayExecutiveAttentionRows(float $completionRate): array
    {
        $rows = [];
        $late = $this->lateItemsCount();
        $sla = $this->slaCriticalCount();
        $docs = $this->pendingDocumentsCount();
        $billing = $this->overdueBillingValue();
        $contracts = $this->contractsDueThisMonthCount();
        $riskCompanies = $this->lateCompaniesCount();
        $stopped = $this->companiesWithoutRecentInteractionCount();

        if ($late > 0) $rows[] = ['title' => 'Resolver atrasos vencidos', 'meta' => 'Operação', 'status' => $late . ' item(ns)', 'tone' => 'danger', 'description' => 'Atraso vencido é o primeiro ponto de reclamação e multa. Priorize por cliente mais crítico e responsável definido.'];
        if ($sla > 0) $rows[] = ['title' => 'Blindar SLA crítico', 'meta' => 'Prazo e atendimento', 'status' => $sla . ' alerta(s)', 'tone' => 'warning', 'description' => 'Itens próximos do limite precisam de retorno rápido ou justificativa registrada antes de virar problema.'];
        if ($docs > 0) $rows[] = ['title' => 'Cobrar documentos pendentes', 'meta' => 'Documental', 'status' => $docs . ' documento(s)', 'tone' => 'warning', 'description' => 'Documentos faltantes travam entrega. Transforme cada pendência em solicitação clara para o cliente.'];
        if ($billing > 0) $rows[] = ['title' => 'Atuar na inadimplência', 'meta' => 'Financeiro', 'status' => 'R$ ' . number_format($billing, 2, ',', '.'), 'tone' => 'danger', 'description' => 'Valor vencido reduz caixa e aumenta risco de continuar atendendo cliente sem previsibilidade.'];
        if ($contracts > 0) $rows[] = ['title' => 'Renovar contratos vencendo', 'meta' => 'Receita', 'status' => $contracts . ' contrato(s)', 'tone' => 'warning', 'description' => 'Contrato perto do vencimento deve aparecer antes da perda de receita, não depois.'];
        if ($riskCompanies > 0) $rows[] = ['title' => 'Revisar clientes em risco', 'meta' => 'Carteira', 'status' => $riskCompanies . ' cliente(s)', 'tone' => 'danger', 'description' => 'Clientes com atraso, cobrança ou documento pendente precisam de plano de contato e dono definido.'];
        if ($stopped > 0) $rows[] = ['title' => 'Reativar clientes sem movimento', 'meta' => 'Relacionamento', 'status' => $stopped . ' cliente(s)', 'tone' => 'warning', 'description' => 'Cliente sem movimentação pode parecer abandonado. Use atendimento ou tarefa para registrar próximo passo.'];

        $rows[] = ['title' => 'Conclusão geral da operação', 'meta' => 'Entrega', 'status' => number_format($completionRate, 1, ',', '.') . '%', 'tone' => $completionRate >= 70 ? 'success' : ($completionRate >= 45 ? 'warning' : 'danger'), 'description' => 'Use este número na reunião semanal para saber se a equipe está fechando mais trabalho do que abre.'];

        return array_slice($rows, 0, 8);
    }

    private function riskRadarRows(): array
    {
        $scores = $this->companyRiskScores();
        $high = $scores->filter(fn ($row) => $row['score'] >= 8)->count();
        $medium = $scores->filter(fn ($row) => $row['score'] >= 3 && $row['score'] < 8)->count();
        $low = $scores->filter(fn ($row) => $row['score'] < 3)->count();

        return [
            ['title' => 'Risco alto', 'meta' => 'Atraso, financeiro ou documento crítico', 'status' => $high . ' cliente(s)', 'tone' => $high > 0 ? 'danger' : 'success', 'description' => 'Clientes que precisam de ação direta do gestor ou responsável da carteira.'],
            ['title' => 'Risco médio', 'meta' => 'Atenção preventiva', 'status' => $medium . ' cliente(s)', 'tone' => $medium > 0 ? 'warning' : 'success', 'description' => 'Clientes com sinais de acúmulo que ainda podem ser resolvidos antes de virar crise.'],
            ['title' => 'Risco baixo', 'meta' => 'Carteira saudável', 'status' => $low . ' cliente(s)', 'tone' => 'success', 'description' => 'Clientes sem sinal crítico relevante pelos dados atuais.'],
        ];
    }

    private function companyRiskScores()
    {
        if (! $this->hasTable('empresas')) return collect();

        return DB::table('empresas')
            ->select('empresas.id', 'empresas.nome_fantasia', 'empresas.razao_social', 'empresas.email', 'empresas.updated_at')
            ->get()
            ->map(function ($company) {
                $late = $this->hasTable('item_controles')
                    ? DB::table('item_controles')->where('empresa_id', $company->id)->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString())->whereNotIn('item_controles.status', self::DONE_STATUSES)->count()
                    : 0;
                $open = $this->hasTable('item_controles')
                    ? DB::table('item_controles')->where('empresa_id', $company->id)->whereNotIn('item_controles.status', self::DONE_STATUSES)->count()
                    : 0;
                $docs = $this->hasTable('item_controles')
                    ? DB::table('item_controles')->where('empresa_id', $company->id)->whereIn('tipo', ['documento', 'contrato', 'compliance', 'auditoria'])->where(function ($q) {
                        $q->whereNull('arquivo')->orWhere(function ($sub) {
                            $sub->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString());
                        });
                    })->whereNotIn('item_controles.status', self::DONE_STATUSES)->count()
                    : 0;
                $billing = $this->hasTable('pagamentos')
                    ? (float) DB::table('pagamentos')->where('empresa_id', $company->id)->whereNull('pago_em')->whereDate('vencimento', '<', now()->toDateString())->sum('valor')
                    : 0.0;
                $lastActivity = $this->lastCompanyActivity($company->id, $company->updated_at);
                $daysWithoutActivity = $lastActivity ? now()->diffInDays(Carbon::parse($lastActivity)) : 999;
                $score = ($late * 4) + ($docs * 3) + ($billing > 0 ? 5 : 0) + min(4, $open) + ($daysWithoutActivity >= 30 ? 2 : 0);

                return [
                    'id' => $company->id,
                    'score' => $score,
                    'title' => $company->nome_fantasia ?: ($company->razao_social ?: 'Empresa sem nome'),
                    'email' => $company->email,
                    'late' => $late,
                    'docs' => $docs,
                    'billing' => $billing,
                    'open' => $open,
                    'days_without_activity' => $daysWithoutActivity,
                ];
            });
    }

    private function topRevenueCompanyRows(int $limit): array
    {
        if (! $this->hasTable('pagamentos') || ! $this->hasTable('empresas')) return [];

        return DB::table('pagamentos')
            ->leftJoin('empresas', 'empresas.id', '=', 'pagamentos.empresa_id')
            ->select('empresas.id', 'empresas.nome_fantasia', 'empresas.razao_social', DB::raw('SUM(pagamentos.valor) as receita'), DB::raw('COUNT(pagamentos.id) as pagamentos_total'))
            ->whereNotNull('pagamentos.pago_em')
            ->where('pagamentos.pago_em', '>=', now()->subDays(90))
            ->groupBy('empresas.id', 'empresas.nome_fantasia', 'empresas.razao_social')
            ->orderByDesc('receita')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $risk = $row->id ? $this->companyRiskScores()->firstWhere('id', $row->id) : null;
                $score = (int) ($risk['score'] ?? 0);
                return [
                    'title' => $row->nome_fantasia ?: ($row->razao_social ?: 'Cliente sem nome'),
                    'meta' => $row->pagamentos_total . ' pagamento(s) recebido(s) nos últimos 90 dias',
                    'status' => 'R$ ' . number_format((float) $row->receita, 2, ',', '.'),
                    'tone' => $score >= 8 ? 'danger' : ($score >= 3 ? 'warning' : 'success'),
                    'description' => 'Risco atual da carteira: ' . ($score >= 8 ? 'alto' : ($score >= 3 ? 'médio' : 'baixo')) . '. Use para proteger clientes relevantes financeiramente.',
                ];
            })
            ->all();
    }

    private function portfolioByOwnerRows(int $limit): array
    {
        if (! $this->hasTable('responsaveis') || ! $this->hasTable('item_controles')) return [];

        $done = "'" . implode("','", self::DONE_STATUSES) . "'";

        return DB::table('responsaveis')
            ->leftJoin('item_controles', 'item_controles.responsavel_id', '=', 'responsaveis.id')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->select([
                'responsaveis.id',
                'responsaveis.nome',
                'responsaveis.cargo',
                DB::raw('COUNT(DISTINCT empresas.id) as clientes'),
                DB::raw('COUNT(item_controles.id) as itens'),
                DB::raw("SUM(CASE WHEN item_controles.status NOT IN ($done) THEN 1 ELSE 0 END) as abertos"),
                DB::raw("SUM(CASE WHEN item_controles.data_vencimento IS NOT NULL AND item_controles.data_vencimento < CURRENT_DATE AND item_controles.status NOT IN ($done) THEN 1 ELSE 0 END) as atrasados"),
                DB::raw("SUM(CASE WHEN item_controles.status IN ($done) THEN 1 ELSE 0 END) as concluidos"),
            ])
            ->groupBy('responsaveis.id', 'responsaveis.nome', 'responsaveis.cargo')
            ->orderByDesc('atrasados')
            ->orderByDesc('clientes')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $clientes = (int) $row->clientes;
                $abertos = (int) $row->abertos;
                $atrasados = (int) $row->atrasados;
                $concluidos = (int) $row->concluidos;

                return [
                    'title' => $row->nome ?: 'Responsável sem nome',
                    'meta' => ($row->cargo ?: 'Carteira operacional') . ' • ' . $clientes . ' cliente(s)',
                    'status' => $atrasados > 0 ? $atrasados . ' atraso(s)' : $concluidos . ' concluído(s)',
                    'tone' => $atrasados > 0 ? 'danger' : ($abertos > 0 ? 'warning' : 'success'),
                    'description' => $abertos . ' aberto(s), ' . $concluidos . ' concluído(s). Ajuda o gestor a redistribuir carga sem depender de planilha externa.',
                ];
            })
            ->all();
    }

    private function monthlyComparisonRows(): array
    {
        $currentStart = now()->startOfMonth();
        $currentEnd = now()->endOfMonth();
        $previousStart = now()->subMonth()->startOfMonth();
        $previousEnd = now()->subMonth()->endOfMonth();

        $currentRevenue = $this->revenueBetween($currentStart, $currentEnd);
        $previousRevenue = $this->revenueBetween($previousStart, $previousEnd);
        $currentCreated = $this->itemsCreatedBetween($currentStart, $currentEnd);
        $previousCreated = $this->itemsCreatedBetween($previousStart, $previousEnd);
        $currentDone = $this->itemsDoneBetween($currentStart, $currentEnd);
        $previousDone = $this->itemsDoneBetween($previousStart, $previousEnd);
        $currentOverdue = $this->overdueBetween($currentStart, $currentEnd);
        $previousOverdue = $this->overdueBetween($previousStart, $previousEnd);

        return [
            $this->comparisonRow('Receita recebida', 'Financeiro', $currentRevenue, $previousRevenue, true, 'Quanto entrou neste mês em comparação com o mês anterior.'),
            $this->comparisonRow('Novos trabalhos', 'Operação', $currentCreated, $previousCreated, false, 'Volume de itens criados no mês. Crescimento sem conclusão pode gerar acúmulo.'),
            $this->comparisonRow('Entregas concluídas', 'Produtividade', $currentDone, $previousDone, false, 'Itens finalizados neste mês. Deve acompanhar ou superar os novos trabalhos.'),
            $this->comparisonRow('Valor vencido', 'Inadimplência', $currentOverdue, $previousOverdue, true, 'Aqui queda é bom: indica menor exposição financeira vencida.', true),
        ];
    }

    private function comparisonRow(string $title, string $meta, float|int $current, float|int $previous, bool $money, string $description, bool $inverse = false): array
    {
        $diff = $previous == 0 ? ($current > 0 ? 100 : 0) : (($current - $previous) / max(abs((float) $previous), 1)) * 100;
        $good = $inverse ? $diff <= 0 : $diff >= 0;
        $value = $money ? 'R$ ' . number_format((float) $current, 2, ',', '.') : number_format((float) $current, 0, ',', '.');
        $signal = $diff > 0 ? '+' : '';

        return [
            'title' => $title,
            'meta' => $meta . ' • mês atual vs anterior',
            'status' => $value . ' (' . $signal . number_format($diff, 1, ',', '.') . '%)',
            'tone' => $good ? 'success' : 'warning',
            'description' => $description,
        ];
    }

    private function strategicTrendRows(): array
    {
        return [
            $this->trendRow('30 dias', now()->subDays(30), now(), 'Curto prazo para detectar problema antes da reunião mensal.'),
            $this->trendRow('90 dias', now()->subDays(90), now(), 'Mostra tendência real de carteira, receita e produtividade sem depender de um único mês.'),
            $this->trendRow('180 dias', now()->subDays(180), now(), 'Visão estratégica para o sócio decidir contratação, reajuste ou revisão de carteira.'),
        ];
    }

    private function trendRow(string $label, Carbon $start, Carbon $end, string $description): array
    {
        $created = $this->itemsCreatedBetween($start, $end);
        $done = $this->itemsDoneBetween($start, $end);
        $revenue = $this->revenueBetween($start, $end);
        $overdue = $this->overdueBillingValue();
        $balance = $done - $created;
        $tone = $balance >= 0 && $overdue <= $revenue ? 'success' : ($balance >= -5 ? 'warning' : 'danger');

        return [
            'title' => 'Tendência de ' . $label,
            'meta' => 'Criados ' . $created . ' • Concluídos ' . $done,
            'status' => 'Receita R$ ' . number_format($revenue, 2, ',', '.'),
            'tone' => $tone,
            'description' => $description . ' Saldo operacional do período: ' . ($balance >= 0 ? '+' : '') . $balance . ' item(ns).',
        ];
    }

    private function revenueBetween(Carbon $start, Carbon $end): float
    {
        return $this->hasTable('pagamentos')
            ? (float) DB::table('pagamentos')->whereNotNull('pago_em')->whereBetween('pago_em', [$start, $end])->sum('valor')
            : 0.0;
    }

    private function overdueBetween(Carbon $start, Carbon $end): float
    {
        return $this->hasTable('pagamentos')
            ? (float) DB::table('pagamentos')->whereNull('pago_em')->whereBetween('vencimento', [$start->toDateString(), $end->toDateString()])->sum('valor')
            : 0.0;
    }

    private function itemsCreatedBetween(Carbon $start, Carbon $end): int
    {
        return $this->hasTable('item_controles')
            ? DB::table('item_controles')->whereBetween('created_at', [$start, $end])->count()
            : 0;
    }

    private function itemsDoneBetween(Carbon $start, Carbon $end): int
    {
        return $this->hasTable('item_controles')
            ? DB::table('item_controles')->whereIn('item_controles.status', self::DONE_STATUSES)->whereBetween('updated_at', [$start, $end])->count()
            : 0;
    }

    private function formatDate($date): string
    {
        return empty($date) ? '-' : Carbon::parse($date)->format('d/m/Y');
    }

    private function formatDateTime($date): string
    {
        return empty($date) ? '-' : Carbon::parse($date)->format('d/m/Y H:i');
    }
}
