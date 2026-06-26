<?php

namespace App\Support;

class PrazzuAccountingTemplateCatalog
{
    public static function templates(): array
    {
        return array_merge(
            self::fiscalTemplates(),
            self::contabilTemplates(),
            self::dpTemplates(),
            self::societarioTemplates(),
            self::certificadosTemplates(),
            self::financeiroTemplates(),
            self::consultivoTemplates(),
        );
    }

    private static function fiscalTemplates(): array
    {
        return [
            self::make('contabil', 'fiscal', 'Fechamento Fiscal', 'Fechamento fiscal mensal com documentos, apuração, obrigações acessórias, guias e aprovação final.', ['Solicitar documentos fiscais do período', 'Importar e validar documentos fiscais', 'Apurar impostos do período', 'Revisar inconsistências fiscais', 'Emitir guias e obrigações acessórias', 'Aprovar fechamento fiscal'], ['Competência', 'Regime tributário', 'Status da apuração'], 'mensal'),
            self::make('contabil', 'fiscal', 'Apuração Simples Nacional', 'Rotina de apuração mensal do Simples Nacional com PGDAS, segregação de receitas e DAS.', ['Coletar faturamento do mês', 'Conferir anexos e segregação de receitas', 'Apurar PGDAS-D', 'Gerar DAS', 'Enviar guia e memória ao cliente', 'Arquivar recibos e comprovantes'], ['Competência', 'Anexo', 'Faturamento bruto'], 'mensal'),
            self::make('contabil', 'fiscal', 'Apuração Lucro Presumido', 'Apuração dos tributos de empresas no Lucro Presumido com bases, retenções, guias e conferência.', ['Coletar receitas e retenções', 'Calcular IRPJ e CSLL', 'Calcular PIS e COFINS', 'Calcular ISS/ICMS/IPI quando aplicável', 'Emitir guias', 'Revisar e liberar apuração'], ['Competência', 'Atividade principal', 'Retenções'], 'mensal'),
            self::make('contabil', 'fiscal', 'Apuração Lucro Real', 'Rotina de apuração do Lucro Real com bases fiscais, compensações, livros e revisão técnica.', ['Coletar balancete e ajustes fiscais', 'Calcular PIS/COFINS não cumulativo', 'Apurar IRPJ/CSLL', 'Validar LALUR/LACS quando aplicável', 'Emitir guias', 'Submeter revisão fiscal'], ['Competência', 'Forma de apuração', 'Compensações'], 'mensal'),
            self::make('contabil', 'fiscal', 'SPED Fiscal ICMS/IPI', 'Preparação, validação, transmissão e arquivamento do SPED Fiscal ICMS/IPI.', ['Importar documentos fiscais', 'Gerar arquivo EFD ICMS/IPI', 'Validar no PVA', 'Corrigir erros e advertências', 'Transmitir obrigação', 'Arquivar recibo de entrega'], ['Competência', 'UF', 'Inscrição estadual'], 'mensal'),
            self::make('contabil', 'fiscal', 'EFD Contribuições', 'Geração e transmissão da EFD Contribuições com conferência de bases de PIS/COFINS.', ['Consolidar receitas e créditos', 'Gerar arquivo EFD Contribuições', 'Validar arquivo', 'Corrigir inconsistências', 'Transmitir obrigação', 'Salvar recibo e relatório'], ['Competência', 'Regime PIS/COFINS', 'Créditos apurados'], 'mensal'),
            self::make('contabil', 'fiscal', 'DCTFWeb', 'Conferência, fechamento e transmissão da DCTFWeb com guias e recibos.', ['Conferir eventos vinculados', 'Validar débitos e créditos', 'Transmitir DCTFWeb', 'Emitir DARF', 'Enviar guia ao cliente', 'Arquivar recibos'], ['Competência', 'Categoria da declaração', 'Status eSocial/Reinf'], 'mensal'),
            self::make('contabil', 'fiscal', 'EFD-Reinf', 'Rotina de conferência e transmissão da EFD-Reinf para retenções e informações fiscais.', ['Coletar notas com retenção', 'Conferir tomadores e prestadores', 'Transmitir eventos Reinf', 'Validar retorno', 'Integrar com DCTFWeb', 'Arquivar comprovantes'], ['Competência', 'Retenções', 'Evento principal'], 'mensal'),
            self::make('contabil', 'fiscal', 'ISS Mensal', 'Apuração mensal de ISS com conferência de notas, retenções, guia municipal e protocolo.', ['Conferir NFS-e emitidas e tomadas', 'Validar retenções de ISS', 'Apurar imposto municipal', 'Gerar guia', 'Enviar ao cliente', 'Arquivar comprovante'], ['Competência', 'Município', 'Alíquota'], 'mensal'),
            self::make('contabil', 'fiscal', 'ICMS Mensal', 'Apuração de ICMS com entradas, saídas, créditos, débitos, guias e obrigações vinculadas.', ['Conferir entradas e saídas', 'Validar CFOP/CST', 'Apurar débitos e créditos', 'Gerar guia estadual', 'Revisar inconsistências', 'Liberar fechamento'], ['Competência', 'UF', 'Saldo credor anterior'], 'mensal'),
            self::make('contabil', 'fiscal', 'DEFIS', 'Entrega anual da DEFIS para empresas optantes pelo Simples Nacional.', ['Coletar dados anuais', 'Conferir faturamento e distribuição', 'Preencher DEFIS', 'Revisar informações', 'Transmitir declaração', 'Arquivar recibo'], ['Ano-calendário', 'Faturamento anual', 'Distribuição de lucros'], 'anual'),
            self::make('contabil', 'fiscal', 'Parcelamento Tributário', 'Acompanhamento de parcelamentos tributários com adesão, parcelas, vencimentos e comprovantes.', ['Levantar débitos disponíveis', 'Simular parcelamento', 'Enviar opção ao cliente', 'Formalizar adesão', 'Cadastrar vencimentos', 'Monitorar pagamentos'], ['Órgão', 'Número do parcelamento', 'Quantidade de parcelas'], 'sob_demanda'),
        ];
    }

    private static function contabilTemplates(): array
    {
        return [
            self::make('contabil', 'contabil', 'Fechamento Contábil', 'Fechamento contábil mensal com conciliações, lançamentos, balancete, revisão e entrega gerencial.', ['Solicitar extratos e documentos contábeis', 'Realizar conciliação bancária', 'Classificar lançamentos contábeis', 'Gerar balancete de verificação', 'Revisar demonstrações e relatórios', 'Aprovar fechamento contábil'], ['Competência', 'Pendências de conciliação', 'Responsável técnico'], 'mensal'),
            self::make('contabil', 'contabil', 'Conciliação Bancária', 'Processo dedicado à conciliação bancária por conta, competência e pendências identificadas.', ['Importar extratos bancários', 'Conferir lançamentos do sistema', 'Conciliar entradas', 'Conciliar saídas', 'Classificar pendências', 'Aprovar conciliação'], ['Competência', 'Banco', 'Conta'], 'mensal'),
            self::make('contabil', 'contabil', 'Balancete Mensal', 'Geração e revisão do balancete mensal com comparação, ajustes e validação técnica.', ['Consolidar lançamentos do período', 'Gerar balancete', 'Conferir contas patrimoniais', 'Conferir contas de resultado', 'Registrar ajustes', 'Liberar balancete'], ['Competência', 'Plano de contas', 'Status da revisão'], 'mensal'),
            self::make('contabil', 'contabil', 'Balanço Patrimonial', 'Preparação do balanço patrimonial com composição de saldos, revisão e aprovação.', ['Consolidar saldos contábeis', 'Revisar ativo', 'Revisar passivo', 'Revisar patrimônio líquido', 'Preparar demonstração', 'Aprovar entrega'], ['Ano-base', 'Responsável técnico', 'Status de assinatura'], 'anual'),
            self::make('contabil', 'contabil', 'DRE Gerencial', 'Preparação da DRE gerencial com análise de receitas, custos, despesas e margem.', ['Coletar dados contábeis', 'Classificar receitas e custos', 'Classificar despesas', 'Gerar DRE', 'Analisar variações', 'Enviar relatório ao cliente'], ['Competência', 'Centro de custo', 'Formato de entrega'], 'mensal'),
            self::make('contabil', 'contabil', 'ECD', 'Rotina anual de Escrituração Contábil Digital com geração, validação e transmissão.', ['Preparar escrituração', 'Gerar arquivo ECD', 'Validar no PVA', 'Corrigir advertências', 'Assinar e transmitir', 'Arquivar recibo'], ['Ano-calendário', 'Signatário', 'Livro'], 'anual'),
            self::make('contabil', 'contabil', 'ECF', 'Rotina anual de Escrituração Contábil Fiscal com apuração, validação, assinatura e entrega.', ['Consolidar dados contábeis e fiscais', 'Gerar arquivo ECF', 'Validar blocos', 'Corrigir inconsistências', 'Assinar e transmitir', 'Arquivar recibo'], ['Ano-calendário', 'Regime tributário', 'Responsável fiscal'], 'anual'),
            self::make('contabil', 'contabil', 'Distribuição de Lucros', 'Processo de análise e formalização de distribuição de lucros com base contábil.', ['Levantar resultado disponível', 'Conferir obrigações e pendências', 'Preparar memória de cálculo', 'Preparar documento de deliberação', 'Enviar ao cliente', 'Arquivar aprovação'], ['Período', 'Valor pretendido', 'Sócios'], 'sob_demanda'),
            self::make('contabil', 'contabil', 'Implantação Contábil de Cliente', 'Checklist de onboarding contábil para implantação de novo cliente no escritório.', ['Coletar documentos iniciais', 'Cadastrar empresa e responsáveis', 'Importar plano de contas', 'Configurar integrações', 'Validar saldos iniciais', 'Liberar operação recorrente'], ['Data de início', 'Sistema anterior', 'Responsável interno'], 'sob_demanda'),
        ];
    }

    private static function dpTemplates(): array
    {
        return [
            self::make('rh', 'dp', 'Folha de Pagamento', 'Processamento mensal da folha, variáveis, encargos, recibos e fechamento ao cliente.', ['Coletar variáveis da folha', 'Processar folha de pagamento', 'Conferir encargos e guias', 'Enviar folha para aprovação', 'Disponibilizar recibos e guias', 'Arquivar fechamento'], ['Competência', 'Quantidade de colaboradores', 'Status da aprovação'], 'mensal'),
            self::make('rh', 'dp', 'Admissão', 'Admissão de colaborador com coleta documental, cadastro, contrato, eventos trabalhistas e comunicação.', ['Coletar dados e documentos do novo colaborador', 'Validar informações admissionais', 'Cadastrar colaborador no sistema de folha', 'Gerar documentos admissionais', 'Enviar eventos eSocial/admissionais', 'Confirmar admissão concluída'], ['Nome do colaborador', 'CPF', 'Data de admissão'], 'sob_demanda'),
            self::make('rh', 'dp', 'Demissão', 'Desligamento com cálculo rescisório, documentos, guias, conferências e formalização.', ['Receber solicitação de desligamento', 'Coletar dados para cálculo rescisório', 'Calcular rescisão', 'Gerar documentos e guias rescisórias', 'Enviar para aprovação do cliente', 'Finalizar desligamento'], ['Colaborador', 'Tipo de desligamento', 'Data de desligamento'], 'sob_demanda'),
            self::make('rh', 'dp', 'Férias', 'Programação, cálculo e formalização de férias com recibos e comunicação ao cliente.', ['Receber solicitação de férias', 'Validar período aquisitivo', 'Calcular férias', 'Gerar aviso e recibo', 'Enviar para aprovação', 'Arquivar documentos'], ['Colaborador', 'Período aquisitivo', 'Data de início'], 'sob_demanda'),
            self::make('rh', 'dp', '13º Salário', 'Processamento da primeira e segunda parcela do 13º salário com encargos e recibos.', ['Definir competência e parcela', 'Processar cálculo', 'Conferir encargos', 'Emitir recibos e guias', 'Enviar ao cliente', 'Arquivar comprovantes'], ['Ano', 'Parcela', 'Quantidade de colaboradores'], 'anual'),
            self::make('rh', 'dp', 'eSocial Mensal', 'Fechamento mensal de eventos periódicos do eSocial com conferência e recibos.', ['Conferir eventos de folha', 'Transmitir eventos periódicos', 'Validar retornos', 'Fechar competência', 'Integrar com DCTFWeb', 'Arquivar recibos'], ['Competência', 'Evento', 'Status do fechamento'], 'mensal'),
            self::make('rh', 'dp', 'Afastamento Trabalhista', 'Registro e acompanhamento de afastamento com documentos, eventos e retorno.', ['Receber atestado/comunicação', 'Validar tipo de afastamento', 'Registrar no sistema de folha', 'Transmitir evento quando aplicável', 'Monitorar retorno', 'Arquivar documentos'], ['Colaborador', 'Tipo de afastamento', 'Data de retorno prevista'], 'sob_demanda'),
            self::make('rh', 'dp', 'Alteração Salarial ou Cargo', 'Processo de alteração contratual trabalhista com atualização cadastral e documentos.', ['Receber solicitação de alteração', 'Validar data e condições', 'Atualizar cadastro', 'Gerar termo de alteração', 'Transmitir evento quando aplicável', 'Confirmar conclusão'], ['Colaborador', 'Novo cargo', 'Novo salário'], 'sob_demanda'),
        ];
    }

    private static function societarioTemplates(): array
    {
        return [
            self::make('contabil', 'societario', 'Abertura de Empresa', 'Abertura de empresa desde coleta de dados, viabilidade, contrato, CNPJ, inscrições e entrega.', ['Coletar dados dos sócios e atividade', 'Realizar análise de viabilidade', 'Preparar ato constitutivo', 'Protocolar abertura nos órgãos competentes', 'Obter CNPJ e inscrições', 'Entregar empresa aberta ao cliente'], ['Razão social pretendida', 'CNAE principal', 'Regime tributário sugerido'], 'sob_demanda'),
            self::make('contabil', 'societario', 'Alteração Contratual', 'Alteração contratual com coleta de alterações, preparação do ato, protocolo e atualização cadastral.', ['Mapear alteração solicitada', 'Coletar documentos e aprovações dos sócios', 'Elaborar alteração contratual', 'Protocolar alteração', 'Atualizar cadastros e inscrições', 'Finalizar alteração contratual'], ['Tipo de alteração', 'Órgão de registro', 'Protocolo'], 'sob_demanda'),
            self::make('contabil', 'societario', 'Baixa de Empresa', 'Encerramento de empresa com análise de pendências, protocolos, baixas e comprovantes.', ['Levantar pendências fiscais e cadastrais', 'Coletar aprovações dos sócios', 'Preparar distrato/baixa', 'Protocolar baixa nos órgãos', 'Confirmar baixa de inscrições', 'Entregar comprovantes finais'], ['Razão social', 'CNPJ', 'Motivo da baixa'], 'sob_demanda'),
            self::make('contabil', 'societario', 'Enquadramento ME/EPP', 'Solicitação e acompanhamento de enquadramento ou reenquadramento de porte empresarial.', ['Verificar requisitos de porte', 'Coletar documentos', 'Preparar requerimento', 'Protocolar solicitação', 'Acompanhar deferimento', 'Atualizar cadastro interno'], ['Porte atual', 'Porte solicitado', 'Protocolo'], 'sob_demanda'),
            self::make('contabil', 'societario', 'Alteração de CNAE', 'Processo societário e fiscal para inclusão, exclusão ou alteração de atividade econômica.', ['Validar CNAE solicitado', 'Avaliar impacto tributário', 'Preparar alteração', 'Protocolar nos órgãos', 'Atualizar inscrições', 'Comunicar cliente'], ['CNAE atual', 'CNAE novo', 'Impacto tributário'], 'sob_demanda'),
            self::make('contabil', 'societario', 'Entrada ou Saída de Sócio', 'Alteração societária para entrada, saída ou cessão de quotas entre sócios.', ['Mapear composição societária', 'Coletar documentos dos sócios', 'Preparar alteração de quotas', 'Enviar para assinatura', 'Protocolar alteração', 'Atualizar cadastros'], ['Sócio envolvido', 'Percentual de quotas', 'Valor da operação'], 'sob_demanda'),
            self::make('contabil', 'societario', 'Transformação de Natureza Jurídica', 'Processo para transformação societária com análise, atos, protocolos e cadastros.', ['Avaliar natureza jurídica atual', 'Definir natureza pretendida', 'Preparar ato de transformação', 'Coletar assinaturas', 'Protocolar transformação', 'Atualizar cadastros fiscais'], ['Natureza atual', 'Natureza pretendida', 'Órgão de registro'], 'sob_demanda'),
        ];
    }

    private static function certificadosTemplates(): array
    {
        return [
            self::make('contabil', 'certificados', 'Renovação e-CNPJ', 'Acompanhamento de renovação de certificado digital e-CNPJ com prazos, documentos e instalação.', ['Identificar vencimento do certificado', 'Solicitar documentos ao cliente', 'Agendar emissão/renovação', 'Acompanhar validação', 'Testar certificado', 'Registrar novo vencimento'], ['CNPJ', 'Data de vencimento', 'Autoridade certificadora'], 'anual'),
            self::make('contabil', 'certificados', 'Renovação e-CPF', 'Acompanhamento de renovação de certificado digital e-CPF de sócios ou responsáveis.', ['Identificar certificado a vencer', 'Confirmar titular e documentos', 'Agendar renovação', 'Acompanhar emissão', 'Testar acesso', 'Atualizar controle de validade'], ['Titular', 'CPF', 'Data de vencimento'], 'anual'),
            self::make('contabil', 'certificados', 'Procuração Eletrônica', 'Criação, renovação ou conferência de procurações eletrônicas para operação fiscal e contábil.', ['Mapear poderes necessários', 'Solicitar acesso/certificado', 'Cadastrar procuração', 'Validar poderes concedidos', 'Registrar validade', 'Comunicar equipe operacional'], ['Outorgante', 'Sistema', 'Validade'], 'sob_demanda'),
        ];
    }

    private static function financeiroTemplates(): array
    {
        return [
            self::make('contabil', 'financeiro', 'Cobrança de Honorários', 'Rotina de cobrança de honorários contábeis com vencimentos, inadimplência e comunicação.', ['Gerar cobrança do período', 'Enviar cobrança ao cliente', 'Monitorar vencimento', 'Registrar pagamento', 'Tratar inadimplência', 'Arquivar comprovante'], ['Competência', 'Valor', 'Data de vencimento'], 'mensal'),
            self::make('contabil', 'financeiro', 'Reajuste de Honorários', 'Processo anual ou contratual de revisão e comunicação de reajuste de honorários.', ['Identificar contratos elegíveis', 'Calcular reajuste', 'Preparar comunicação', 'Enviar ao cliente', 'Atualizar cobrança', 'Arquivar aceite'], ['Índice', 'Percentual', 'Data de vigência'], 'anual'),
            self::make('contabil', 'financeiro', 'Cobrança de Documentos Pendentes', 'Fluxo operacional para cobrança estruturada de documentos pendentes do cliente.', ['Listar documentos pendentes', 'Enviar primeira cobrança', 'Registrar retorno', 'Escalonar pendência crítica', 'Atualizar status operacional', 'Encerrar cobrança'], ['Competência', 'Tipo de documento', 'Responsável do cliente'], 'sob_demanda'),
        ];
    }

    private static function consultivoTemplates(): array
    {
        return [
            self::make('contabil', 'consultivo', 'Revisão Tributária', 'Análise consultiva de oportunidades, riscos e enquadramento tributário do cliente.', ['Coletar dados fiscais e contábeis', 'Mapear regime atual', 'Simular cenários tributários', 'Identificar riscos e oportunidades', 'Preparar relatório', 'Apresentar recomendação'], ['Período analisado', 'Regime atual', 'Regime simulado'], 'sob_demanda'),
            self::make('contabil', 'consultivo', 'Planejamento Tributário Anual', 'Planejamento tributário para o próximo exercício com simulações e plano de ação.', ['Coletar histórico anual', 'Projetar receitas e custos', 'Simular regimes', 'Comparar carga tributária', 'Preparar plano de ação', 'Aprovar recomendação com cliente'], ['Ano projetado', 'Faturamento estimado', 'Regime recomendado'], 'anual'),
            self::make('contabil', 'consultivo', 'Due Diligence Contábil', 'Revisão contábil, fiscal, trabalhista e societária para avaliação de riscos.', ['Definir escopo da diligência', 'Coletar documentos', 'Revisar fiscal e contábil', 'Revisar trabalhista e societário', 'Classificar riscos', 'Entregar relatório final'], ['Período', 'Escopo', 'Nível de risco'], 'sob_demanda'),
            self::make('contabil', 'consultivo', 'Regularização de Pendências Fiscais', 'Processo para identificar, priorizar e resolver pendências fiscais do cliente.', ['Consultar pendências em órgãos', 'Classificar risco e urgência', 'Definir plano de regularização', 'Executar correções/entregas', 'Emitir comprovantes', 'Validar regularidade'], ['Órgão', 'Tipo de pendência', 'Prazo crítico'], 'sob_demanda'),
        ];
    }

    private static function make(string $module, string $area, string $name, string $description, array $taskTitles, array $customFields, string $recurrence): array
    {
        return [
            'module' => $module,
            'name' => $name,
            'description' => $description,
            'payload' => self::payload($area, $taskTitles, $customFields, $recurrence),
        ];
    }

    private static function payload(string $area, array $taskTitles, array $customFields, string $recurrence): array
    {
        return [
            'family' => 'templates_contabeis',
            'area' => $area,
            'official' => true,
            'version' => 2,
            'recurrence' => $recurrence,
            'tasks' => array_map(fn (string $title, int $index): array => self::task($area, $title, $index), $taskTitles, array_keys($taskTitles)),
            'custom_fields' => array_map(fn (string $field): array => [
                'name' => $field,
                'type' => str_contains(mb_strtolower($field), 'data') ? 'date' : 'text',
                'default' => null,
                'options' => null,
            ], $customFields),
            'views' => [
                ['name' => 'Lista operacional', 'type' => 'list', 'filter' => 'family:templates_contabeis'],
                ['name' => 'Kanban do processo', 'type' => 'kanban', 'filter' => 'status'],
                ['name' => 'Calendário de prazos', 'type' => 'calendar', 'filter' => 'data_vencimento'],
                ['name' => 'Gantt do template', 'type' => 'gantt', 'filter' => 'template_process.instance_id'],
            ],
            'automations' => [
                ['trigger' => 'Template aplicado', 'action' => 'Criar tarefas operacionais com SLA, checklist e documentos previstos'],
                ['trigger' => 'Tarefa atrasada', 'action' => 'Sinalizar pendência operacional e risco de SLA'],
                ['trigger' => 'Tarefa com aprovação obrigatória concluída', 'action' => 'Enviar para aprovação do responsável'],
            ],
            'docs' => self::docsForArea($area),
            'mind_map' => [
                ['node' => 'Início', 'parent' => null],
                ['node' => 'Coleta/Preparação', 'parent' => 'Início'],
                ['node' => 'Execução técnica', 'parent' => 'Coleta/Preparação'],
                ['node' => 'Revisão/Aprovação', 'parent' => 'Execução técnica'],
                ['node' => 'Entrega ao cliente', 'parent' => 'Revisão/Aprovação'],
            ],
            'proofing' => [
                ['name' => 'Evidência de execução', 'required' => true],
                ['name' => 'Comprovante/recibo quando aplicável', 'required' => false],
            ],
        ];
    }

    private static function task(string $area, string $title, int $index): array
    {
        $approvalRequired = $index >= 4;
        $priority = $index >= 2 ? 'alta' : 'media';

        return [
            'key' => str($title)->slug()->toString(),
            'title' => $title,
            'type' => $area === 'dp' ? 'rh' : 'tarefa',
            'priority' => $approvalRequired ? 'urgente' : $priority,
            'days_after_start' => $index === 0 ? 0 : ($index * 2),
            'sla_hours' => $approvalRequired ? 24 : 36,
            'estimated_minutes' => $approvalRequired ? 90 : 120,
            'approval_required' => $approvalRequired,
            'description' => $title,
            'checklist' => [
                ['titulo' => 'Confirmar escopo e competência da etapa'],
                ['titulo' => 'Executar a atividade conforme padrão interno'],
                ['titulo' => 'Registrar evidência, observações e pendências'],
            ],
            'docs' => $approvalRequired ? [
                ['title' => 'Comprovante - ' . $title, 'type' => 'evidencia'],
            ] : [],
            'depends_on' => $index > 0 ? [$index - 1] : [],
        ];
    }

    private static function docsForArea(string $area): array
    {
        $base = [
            ['title' => 'Orientação de uso', 'content' => 'Use este template como roteiro operacional padrão. Ajuste responsáveis, prazos e documentos conforme o contrato do cliente.'],
            ['title' => 'Checklist de revisão final', 'content' => 'Confirme conclusão das etapas, evidências anexadas, aprovação interna e comunicação ao cliente.'],
        ];

        return match ($area) {
            'fiscal' => array_merge($base, [['title' => 'Memória de apuração', 'content' => 'Documento previsto para registrar bases, tributos, guias e observações fiscais.']]),
            'contabil' => array_merge($base, [['title' => 'Relatório contábil', 'content' => 'Documento previsto para balancete, conciliações, demonstrações e observações técnicas.']]),
            'dp' => array_merge($base, [['title' => 'Dossiê trabalhista', 'content' => 'Documento previsto para eventos, recibos, guias e comunicações de Departamento Pessoal.']]),
            'societario' => array_merge($base, [['title' => 'Dossiê societário', 'content' => 'Documento previsto para atos, protocolos, comprovantes e cadastros societários.']]),
            default => $base,
        };
    }
}
