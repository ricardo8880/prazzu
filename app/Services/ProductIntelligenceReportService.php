<?php

namespace App\Services;

use App\Models\AiMarketComment;
use App\Models\AiMarketSource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductIntelligenceReportService
{
    private const PROBLEM_RULES = [
        'lentidao_performance' => [
            'label' => 'Lentidão / performance',
            'severity' => 9,
            'impact' => 10,
            'real_pain' => 'Frustração, perda de produtividade e sensação de ferramenta pesada.',
            'insight' => 'Usuários abandonam ou evitam ferramentas que parecem pesadas no dia a dia.',
            'market_learning' => 'Velocidade percebida é parte do valor do produto, não apenas detalhe técnico.',
            'what_to_do' => 'Medir carregamento das telas principais e priorizar uma experiência rápida nas ações mais repetidas.',
            'what_not_to_do' => 'Não adicionar componentes pesados, dashboards poluídos ou recursos que aumentem tempo de carregamento sem gerar valor claro.',
            'keywords' => ['lento', 'lenta', 'lentidao', 'travando', 'trava', 'demora', 'demorado', 'pesado', 'pesada', 'desempenho', 'problema de desempenho', 'problemas de desempenho', 'nao funciona', 'não funciona', 'nao funcionava', 'não funcionava', 'inutilizavel', 'inutilizável', 'unusable', 'broken', 'slow', 'lag', 'lags', 'buggy', 'loading', 'performance'],
            'opportunity' => 'Posicionar o SaaS como uma alternativa leve, rápida e sem travamentos.',
            'recommended_action' => 'Auditar páginas lentas, reduzir queries, otimizar carregamento inicial e medir tempo por tela.',
            'seo' => ['alternativa leve ao ClickUp', 'software de gestão rápido', 'sistema de tarefas sem travar', 'gestão de projetos leve'],
            'complexity' => 'média',
        ],
        'muitos_cliques' => [
            'label' => 'Muitos cliques / fluxo burocrático',
            'severity' => 10,
            'impact' => 9,
            'real_pain' => 'Perda de tempo em tarefas simples e sensação de burocracia.',
            'insight' => 'Usuários valorizam resolver tarefas críticas sem navegar por várias telas.',
            'market_learning' => 'Menos cliques pode ser um diferencial competitivo maior que ter mais funcionalidades.',
            'what_to_do' => 'Transformar ações repetidas em atalhos, edição lateral, ações rápidas e conclusão sem sair da tela.',
            'what_not_to_do' => 'Não criar novos passos, modais encadeados ou menus extras para ações simples.',
            'keywords' => ['muitos cliques', 'muito clique', 'cliques demais', 'muitas telas', 'abrir muitas telas', 'muitas etapas', 'etapas demais', 'burocratico', 'burocracia', 'complicado para fazer', 'too many clicks', 'too many steps'],
            'opportunity' => 'Criar fluxos em tela única, ações rápidas e conclusão sem sair do contexto.',
            'recommended_action' => 'Criar ações rápidas, edição lateral e conclusão de tarefa em uma única tela.',
            'seo' => ['gestão com menos cliques', 'sistema simples de tarefas', 'produtividade em uma tela', 'software fácil de usar'],
            'complexity' => 'média',
        ],
        'gestao_tarefas_fraca' => [
            'label' => 'Gestão de tarefas fraca / tarefas difíceis de usar',
            'severity' => 8,
            'impact' => 8,
            'real_pain' => 'Usuário não confia no fluxo principal de tarefas e precisa adaptar a ferramenta ao trabalho real.',
            'insight' => 'Quando o sistema de tarefas não encaixa no dia a dia, o usuário procura outra ferramenta mesmo gostando de outras partes do produto.',
            'market_learning' => 'A gestão de tarefas precisa ser simples, clara e útil antes de qualquer recurso avançado.',
            'what_to_do' => 'Criar fluxo de tarefa simples, com responsáveis, prazo, status, anexos e conclusão sem depender de arquitetura complexa.',
            'what_not_to_do' => 'Não tratar tarefas como recurso secundário se elas são o principal motivo de uso operacional.',
            'keywords' => ['sistema de tarefas', 'tarefas no notion', 'nunca gostamos do sistema de tarefas', 'task system', 'task management', 'tasks are bad', 'tarefas ruins', 'gestao de tarefas ruim', 'gestão de tarefas ruim'],
            'opportunity' => 'Criar uma experiência de tarefas mais direta, confiável e menos adaptada que ferramentas genéricas.',
            'recommended_action' => 'Revisar o fluxo principal de criação, edição, acompanhamento e conclusão de tarefas para reduzir adaptação manual.',
            'seo' => ['sistema de tarefas simples', 'alternativa ao Notion para tarefas', 'gestão de tarefas fácil para empresas'],
            'complexity' => 'média',
        ],
        'onboarding_complexo' => [
            'label' => 'Onboarding complexo / muitas decisões iniciais',
            'severity' => 10,
            'impact' => 10,
            'real_pain' => 'Sobrecarga cognitiva antes do usuário enxergar valor no produto.',
            'insight' => 'Usuários querem produzir antes de aprender a arquitetura do sistema.',
            'market_learning' => 'O primeiro valor precisa aparecer antes da primeira configuração complexa.',
            'what_to_do' => 'Criar primeiro acesso guiado por nicho, com template automático e primeira tarefa pronta.',
            'what_not_to_do' => 'Não exigir que o usuário entenda espaços, pastas, listas, tipos ou configurações antes de fazer algo útil.',
            'keywords' => ['onboarding', 'primeiros minutos', 'primeiro acesso', 'primeira vez', 'comecar', 'começando', 'configurar antes', 'configuracao inicial', 'integracao inicial', 'processo de integracao', 'espacos', 'pastas', 'listas', 'visualizacoes', 'setup', 'getting started', 'too many decisions', 'first 10 minutes', '10 decisoes'],
            'opportunity' => 'Esconder a arquitetura e entregar valor em menos de 60 segundos com templates prontos por nicho.',
            'recommended_action' => 'Criar primeiro acesso guiado por nicho, com template automático e primeira tarefa pronta.',
            'seo' => ['sistema pronto para contabilidade', 'software simples para começar rápido', 'alternativa simples ao ClickUp', 'onboarding rápido para equipes'],
            'complexity' => 'baixa/média',
        ],
        'interface_confusa' => [
            'label' => 'Interface confusa / excesso visual',
            'severity' => 8,
            'impact' => 8,
            'real_pain' => 'Usuário se sente perdido e evita usar o sistema por medo de errar.',
            'insight' => 'Quando tudo parece importante, nada guia o usuário para a próxima ação.',
            'market_learning' => 'Clareza visual reduz suporte, aumenta ativação e melhora retenção.',
            'what_to_do' => 'Reduzir botões visíveis, reorganizar hierarquia visual e destacar próximas ações críticas.',
            'what_not_to_do' => 'Não colocar todas as funcionalidades visíveis na mesma tela apenas para parecer completo.',
            'keywords' => ['confuso', 'confusa', 'poluido', 'poluida', 'dificil de entender', 'nao encontro', 'perdido', 'perdida', 'complexo', 'complexa', 'complexidade', 'complicado', 'complicada', 'overwhelming', 'confusing', 'cluttered'],
            'opportunity' => 'Simplificar telas principais, reduzir botões visíveis e priorizar tarefas críticas.',
            'recommended_action' => 'Reduzir botões visíveis, reorganizar hierarquia visual e destacar próximas ações críticas.',
            'seo' => ['software de gestão intuitivo', 'sistema de tarefas simples', 'gestão operacional fácil', 'alternativa simples ao Asana'],
            'complexity' => 'baixa',
        ],
        'notificacoes_alertas' => [
            'label' => 'Notificações ruins / alertas ignorados',
            'severity' => 9,
            'impact' => 9,
            'real_pain' => 'Falta de confiança nos avisos e risco de perder prazos importantes.',
            'insight' => 'Alertas só têm valor quando ajudam o usuário a agir antes do prejuízo.',
            'market_learning' => 'Notificação demais vira ruído; notificação crítica precisa parecer impossível de ignorar.',
            'what_to_do' => 'Separar alertas críticos de informativos e destacar risco, prazo, multa e bloqueios obrigatórios.',
            'what_not_to_do' => 'Não mandar várias notificações iguais sem contexto, prioridade ou próxima ação clara.',
            'keywords' => ['notificacao', 'notificacoes', 'alerta', 'alertas', 'lembrete', 'lembretes', 'notification', 'notifications', 'reminder', 'reminders'],
            'opportunity' => 'Criar alertas críticos orientados a risco, prazo, multa e bloqueios obrigatórios.',
            'recommended_action' => 'Separar alertas críticos de informativos e destacar risco financeiro/prazo.',
            'seo' => ['controle de vencimentos', 'sistema para não perder prazo', 'alerta de tarefas críticas', 'evitar multas por atraso'],
            'complexity' => 'média',
        ],
        'preco_valor' => [
            'label' => 'Preço / valor percebido',
            'severity' => 6,
            'impact' => 7,
            'real_pain' => 'Cliente não enxerga retorno claro para justificar a assinatura.',
            'insight' => 'Preço dói menos quando o produto é vendido pelo prejuízo evitado.',
            'market_learning' => 'Empresas pagam melhor quando entendem o custo de não resolver o problema.',
            'what_to_do' => 'Melhorar posicionamento, mostrando prejuízo evitado, multas prevenidas e retrabalho reduzido.',
            'what_not_to_do' => 'Não vender apenas lista de funcionalidades; vender resultado operacional e risco reduzido.',
            'keywords' => ['caro', 'preco alto', 'preço alto', 'custo alto', 'muito caro', 'expensive', 'price', 'pricing', 'cost'],
            'opportunity' => 'Vender pelo prejuízo evitado e não por quantidade de funcionalidades.',
            'recommended_action' => 'Melhorar posicionamento, mostrando prejuízo evitado, multas prevenidas e retrabalho reduzido.',
            'seo' => ['software que evita multas', 'sistema para reduzir retrabalho', 'gestão de prazos para empresas'],
            'complexity' => 'baixa',
        ],
        'suporte_atendimento' => [
            'label' => 'Suporte / atendimento',
            'severity' => 7,
            'impact' => 7,
            'real_pain' => 'Usuário fica bloqueado e perde confiança quando precisa de ajuda.',
            'insight' => 'Dúvidas recorrentes indicam pontos onde o produto ainda não é autoexplicativo.',
            'market_learning' => 'Ajuda contextual pode reduzir dependência de suporte e acelerar ativação.',
            'what_to_do' => 'Criar ajuda contextual nas telas críticas e respostas rápidas para dúvidas recorrentes.',
            'what_not_to_do' => 'Não esconder instruções importantes em documentações longas que o usuário não lê.',
            'keywords' => ['suporte ruim', 'atendimento ruim', 'demoram responder', 'demora no suporte', 'resposta lenta', 'support is bad', 'bad support', 'poor support', 'slow support', 'helpdesk ruim'],
            'opportunity' => 'Criar base de ajuda objetiva e suporte com contexto da tarefa/processo.',
            'recommended_action' => 'Criar ajuda contextual nas telas críticas e respostas rápidas para dúvidas recorrentes.',
            'seo' => ['sistema com suporte rápido', 'software de gestão com atendimento próximo'],
            'complexity' => 'baixa/média',
        ],
        'integracoes' => [
            'label' => 'Integrações / automações',
            'severity' => 7,
            'impact' => 7,
            'real_pain' => 'Trabalho manual repetitivo por falta de conexão entre ferramentas.',
            'insight' => 'Integração só é diferencial quando reduz trabalho real no nicho escolhido.',
            'market_learning' => 'Automações precisam economizar tempo, não apenas aumentar a sensação de produto completo.',
            'what_to_do' => 'Mapear integrações que economizam tempo real e priorizar as que eliminam retrabalho operacional.',
            'what_not_to_do' => 'Não adicionar integrações apenas para parecer completo se elas não reduzem esforço do usuário.',
            'keywords' => ['api', 'webhook', 'integracoes', 'integrações', 'integrar com', 'integrado com', 'nao se integram', 'não se integram', 'nao integra', 'não integra', 'nao integram', 'não integram', 'nao se conecta', 'não se conecta', 'automatizacao', 'automacao', 'automation', 'automations', 'integration', 'integrations'],
            'opportunity' => 'Priorizar integrações que reduzem trabalho manual nos nichos escolhidos.',
            'recommended_action' => 'Mapear integrações que economizam tempo real e não adicionar integrações apenas por parecer completo.',
            'seo' => ['sistema com automação de prazos', 'gestão operacional automatizada'],
            'complexity' => 'média/alta',
        ],
        'prazos_risco' => [
            'label' => 'Prazos / risco / multas',
            'severity' => 10,
            'impact' => 10,
            'real_pain' => 'Medo de prejuízo, multa, retrabalho e falha operacional com cliente.',
            'insight' => 'Controle de prazo tem valor financeiro direto quando evita multa e retrabalho.',
            'market_learning' => 'A dor mais vendável não é organizar tarefas, é evitar prejuízo operacional.',
            'what_to_do' => 'Criar dashboard de risco operacional por cliente, prazo e impacto.',
            'what_not_to_do' => 'Não tratar vencimentos como tarefas comuns; prazos críticos precisam de hierarquia visual própria.',
            'keywords' => ['prazo', 'prazos', 'vencimento', 'vencimentos', 'multa', 'multas', 'atraso', 'atrasado', 'deadline', 'due date', 'late', 'penalty'],
            'opportunity' => 'Posicionar como sistema de prevenção de prejuízo operacional para tarefas críticas.',
            'recommended_action' => 'Criar dashboard de risco operacional por cliente, prazo e impacto.',
            'seo' => ['sistema para evitar multas', 'controle de prazos contábeis', 'gestão de obrigações', 'controle de vencimentos de clientes'],
            'complexity' => 'média/alta',
        ],
        'excesso_funcionalidades' => [
            'label' => 'Excesso de funcionalidades / produto inchado',
            'severity' => 8,
            'impact' => 8,
            'real_pain' => 'Usuário sente que a ferramenta faz tudo, mas exige esforço demais para o básico.',
            'insight' => 'Produto completo demais pode virar obstáculo para quem só quer executar o trabalho.',
            'market_learning' => 'Ser menor e mais claro pode vencer ser maior e mais confuso.',
            'what_to_do' => 'Manter recursos avançados escondidos e priorizar caminhos simples para tarefas críticas.',
            'what_not_to_do' => 'Não copiar recursos avançados dos concorrentes se eles aumentarem complexidade para novos usuários.',
            'keywords' => ['faz tudo', 'tudo em um', 'all in one', 'all-in-one', 'usuarios avancados', 'advanced users', 'feature bloat', 'muitas funcionalidades', 'excesso de funcionalidades'],
            'opportunity' => 'Vender simplicidade, foco no trabalho crítico e evitar copiar recursos que aumentam complexidade.',
            'recommended_action' => 'Manter recursos avançados escondidos e priorizar caminhos simples para tarefas críticas.',
            'seo' => ['alternativa simples ao ClickUp', 'sistema sem complexidade', 'software operacional simples'],
            'complexity' => 'baixa',
        ],
        'falta_funcionalidades' => [
            'label' => 'Falta de funcionalidades importantes',
            'severity' => 6,
            'impact' => 6,
            'real_pain' => 'Usuário sente que precisa recorrer a outras ferramentas para concluir o trabalho.',
            'insight' => 'Nem toda falta de funcionalidade merece desenvolvimento; algumas indicam nicho errado ou expectativa desalinhada.',
            'market_learning' => 'Pedidos de funcionalidade precisam ser filtrados por repetição, nicho e impacto real.',
            'what_to_do' => 'Agrupar pedidos por resultado esperado e desenvolver apenas o que reduzir dor recorrente do nicho escolhido.',
            'what_not_to_do' => 'Não criar funcionalidades isoladas baseadas em pedidos raros ou sem ligação com a proposta central.',
            'keywords' => ['falta', 'faltando', 'nao tem', 'não tem', 'missing feature', 'needs feature', 'wish it had', 'gostaria que tivesse'],
            'opportunity' => 'Separar pedidos recorrentes de desejos isolados para evitar crescimento desordenado do produto.',
            'recommended_action' => 'Validar se o pedido aparece em múltiplas fontes antes de colocar no roadmap.',
            'seo' => ['sistema completo sem complicar', 'software com funções essenciais'],
            'complexity' => 'variável',
        ],
    ];

    private const STRENGTH_RULES = [
        'facilidade_uso' => [
            'label' => 'Ponto forte: facilidade de uso',
            'impact' => 9,
            'insight' => 'Usuários elogiam quando conseguem entender e executar sem treinamento pesado.',
            'market_learning' => 'Facilidade de uso deve ser tratada como diferencial central, não como detalhe visual.',
            'what_to_do' => 'Preservar fluxos simples e usar esse ponto forte como referência para qualquer nova tela.',
            'what_not_to_do' => 'Não sacrificar simplicidade para adicionar recursos avançados que poucos usam.',
            'keywords' => ['facil', 'fácil', 'simples', 'intuitivo', 'intuitiva', 'easy to use', 'simple', 'intuitive'],
            'seo' => ['software fácil de usar', 'sistema simples para equipes', 'gestão intuitiva'],
        ],
        'rapidez_leveza' => [
            'label' => 'Ponto forte: rapidez / leveza',
            'impact' => 9,
            'insight' => 'Usuários percebem velocidade como sinal de qualidade e confiabilidade.',
            'market_learning' => 'Leveza pode virar posicionamento comercial contra ferramentas grandes e lentas.',
            'what_to_do' => 'Manter carregamento rápido como requisito de produto em telas críticas.',
            'what_not_to_do' => 'Não adicionar gráficos, scripts ou componentes que comprometam a sensação de velocidade.',
            'keywords' => ['rapido', 'rápido', 'leve', 'carrega rapido', 'fast', 'lightweight', 'snappy', 'quick'],
            'seo' => ['alternativa rápida ao ClickUp', 'software de gestão leve', 'sistema rápido para tarefas'],
        ],
        'centralizacao' => [
            'label' => 'Ponto forte: centralização / tudo em um útil',
            'impact' => 8,
            'insight' => 'Usuários valorizam centralizar trabalho quando isso não aumenta complexidade.',
            'market_learning' => 'Centralização é vantagem para usuários avançados, mas precisa ser escondida dos iniciantes.',
            'what_to_do' => 'Oferecer visão centralizada sem obrigar novos usuários a configurar toda a estrutura antes de usar.',
            'what_not_to_do' => 'Não transformar centralização em excesso de menus, níveis e decisões iniciais.',
            'keywords' => ['faz tudo', 'fazer tudo', 'consegue fazer tudo', 'tudo em um', 'all in one', 'all-in-one', 'centraliza', 'centralizado', 'everything in one', 'rico em recursos', 'muitos recursos', 'valor para os recursos', 'great features', 'feature rich'],
            'seo' => ['sistema centralizado para empresas', 'gestão operacional em um lugar'],
        ],
        'automacoes_uteis' => [
            'label' => 'Ponto forte: automações úteis',
            'impact' => 8,
            'insight' => 'Usuários elogiam automações quando elas eliminam trabalho repetitivo claramente.',
            'market_learning' => 'Automação boa é a que economiza tempo e evita erro, não a que só parece sofisticada.',
            'what_to_do' => 'Priorizar automações ligadas a prazos, documentos, aprovações e bloqueios de erro.',
            'what_not_to_do' => 'Não criar automações difíceis de configurar ou sem benefício operacional imediato.',
            'keywords' => ['automacao boa', 'automação boa', 'automatiza', 'automatizado', 'automation is great', 'automations are great'],
            'seo' => ['automação de tarefas críticas', 'sistema que automatiza prazos'],
        ],
        'suporte_bom' => [
            'label' => 'Ponto forte: suporte bom',
            'impact' => 7,
            'insight' => 'Atendimento rápido aumenta confiança, principalmente em produtos operacionais.',
            'market_learning' => 'Suporte pode ser diferencial enquanto o produto ainda está ganhando maturidade.',
            'what_to_do' => 'Transformar dúvidas frequentes em ajuda contextual dentro das telas.',
            'what_not_to_do' => 'Não depender apenas de suporte humano para explicar fluxos que poderiam ser autoexplicativos.',
            'keywords' => ['suporte bom', 'bom atendimento', 'atendimento util', 'atendimento útil', 'atendimento ao cliente tem sido util', 'atendimento ao cliente tem sido útil', 'responde rapido', 'great support', 'helpful support', 'customer support is great'],
            'seo' => ['software com suporte próximo', 'sistema com atendimento rápido'],
        ],
        'satisfacao_geral' => [
            'label' => 'Ponto forte: satisfação geral / confiança',
            'impact' => 8,
            'insight' => 'Usuários satisfeitos tendem a valorizar confiabilidade, estabilidade e sensação de boa escolha.',
            'market_learning' => 'Comentários positivos mostram o que o mercado considera obrigatório preservar ao criar uma alternativa.',
            'what_to_do' => 'Preservar confiabilidade, estabilidade e clareza de valor antes de adicionar complexidade.',
            'what_not_to_do' => 'Não interpretar elogios gerais como autorização para copiar todas as funcionalidades do concorrente.',
            'keywords' => ['melhor ferramenta', 'melhores ferramentas', 'melhor escolha', 'uma das melhores', 'de longe uma das melhores', 'nunca tive grandes problemas', 'sem grandes problemas', 'boa escolha', 'excelente escolha', 'continua melhorando', 'continua melhor', 'gostamos dele', 'realmente gostamos', 'melhor valor', 'ritmo de desenvolvimento', 'best choice', 'one of the best', 'no major problems', 'great value'],
            'seo' => ['software confiável de gestão', 'sistema de gestão estável', 'alternativa confiável ao ClickUp'],
        ],
    ];

    private const POSITIVE_WORDS = ['amo', 'adorei', 'gostei', 'gostamos', 'gostava', 'excelente', 'otimo', 'ótimo', 'otima escolha', 'ótima escolha', 'bom', 'boa', 'recomendo', 'perfeito', 'funciona bem', 'melhor escolha', 'uma das melhores', 'de longe uma das melhores', 'melhor valor', 'valor para os recursos', 'ritmo de desenvolvimento', 'continua melhorando', 'nunca tive grandes problemas', 'sem grandes problemas', 'atendimento util', 'atendimento útil', 'continua melhorando', 'love', 'great', 'excellent', 'amazing', 'recommend', 'best choice', 'one of the best', 'helpful support', 'no major problems'];
    private const NEGATIVE_WORDS = ['odeio', 'horrivel', 'horrível', 'pior', 'pesadelo', 'ruim', 'nao gostei', 'não gostei', 'nunca gostei', 'nunca gostamos', 'nao funciona', 'não funciona', 'nao funcionava', 'não funcionava', 'inutilizavel', 'inutilizável', 'nao posso recomendar', 'não posso recomendar', 'problema de desempenho', 'problemas de desempenho', 'frustrante', 'terrible', 'awful', 'hate', 'nightmare', 'worst', 'unusable', 'muitas decisoes', 'muitas decisões', 'antes de fazer algo util', 'antes de fazer algo útil', 'antes mesmo de concluir', 'configurar antes', 'too many decisions', 'too many clicks', 'too many steps'];

    private const MIXED_SENTIMENT_CONNECTORS = ['mas', 'porem', 'porém', 'so que', 'só que', 'apesar', 'embora', 'no entanto', 'entretanto', 'however', 'but', 'although'];

    public function classifyText(string $text, ?int $rating = null): array
    {
        $normalized = Str::of($text)->lower()->ascii()->toString();
        $problemMatches = [];
        $strengthMatches = [];
        $matchedKeywords = [];

        foreach (self::PROBLEM_RULES as $key => $rule) {
            $score = $this->scoreRule($normalized, $rule['keywords'], $localMatches);

            if ($score > 0) {
                $problemMatches[$key] = ($score * $rule['severity']) + ($rule['impact'] ?? 0);
                $matchedKeywords['problem_'.$key] = $localMatches;
            }
        }

        foreach (self::STRENGTH_RULES as $key => $rule) {
            $score = $this->scoreRule($normalized, $rule['keywords'], $localMatches);

            if ($score > 0) {
                $strengthMatches[$key] = ($score * 7) + ($rule['impact'] ?? 0);
                $matchedKeywords['strength_'.$key] = $localMatches;
            }
        }

        $sentiment = $this->detectSentiment($normalized, $rating, $problemMatches, $strengthMatches, $matchedKeywords);
        $this->applyContextualBoosts($normalized, $problemMatches, $sentiment);
        $sentiment = $this->detectSentiment($normalized, $rating, $problemMatches, $strengthMatches, $matchedKeywords);
        $this->normalizeMatchesBySentiment($sentiment, $problemMatches, $strengthMatches, $matchedKeywords);

        arsort($problemMatches);
        arsort($strengthMatches);

        $problemKey = array_key_first($problemMatches);
        $strengthKey = array_key_first($strengthMatches);
        $problemScore = $problemKey !== null ? (int) ($problemMatches[$problemKey] ?? 0) : 0;
        $strengthScore = $strengthKey !== null ? (int) ($strengthMatches[$strengthKey] ?? 0) : 0;
        $problemEvidence = $this->matchesToProblemEvidence($problemMatches, $matchedKeywords);
        $strengthEvidence = $this->matchesToStrengthEvidence($strengthMatches, $matchedKeywords);
        $isStrength = $sentiment === 'positivo'
            && $strengthKey !== null
            && ($strengthScore >= max(12, $problemScore + 6));

        if ($isStrength) {
            $rule = self::STRENGTH_RULES[$strengthKey];

            return [
                'sentiment' => $sentiment,
                'insight_type' => 'ponto_forte',
                'category_key' => $strengthKey,
                'category' => $rule['label'],
                'problem' => $rule['label'],
                'real_pain' => 'Ponto forte detectado; não é uma dor, é algo que o mercado valoriza.',
                'impact' => (int) ($rule['impact'] ?? 7),
                'severity' => 0,
                'insight' => $rule['insight'],
                'market_learning' => $rule['market_learning'],
                'what_to_do' => $rule['what_to_do'],
                'what_not_to_do' => $rule['what_not_to_do'],
                'opportunity' => $rule['what_to_do'],
                'recommended_action' => $rule['what_to_do'],
                'complexity' => 'baixa',
                'seo_keywords' => $rule['seo'] ?? [],
                'matched_keywords' => $matchedKeywords['strength_'.$strengthKey] ?? [],
                'confidence' => $this->confidenceLabel($rating, count($matchedKeywords['strength_'.$strengthKey] ?? [])),
                'problems' => $problemEvidence,
                'strengths' => $strengthEvidence,
            ];
        }

        $rule = $problemKey ? self::PROBLEM_RULES[$problemKey] : null;

        return [
            'sentiment' => $sentiment,
            'insight_type' => $rule ? 'problema' : 'manual',
            'category_key' => $problemKey ?: 'nao_classificado',
            'category' => $rule['label'] ?? 'Não classificado',
            'problem' => $rule ? $rule['label'] : 'Comentário precisa de leitura manual',
            'real_pain' => $rule['real_pain'] ?? 'Dor real ainda não detectada automaticamente.',
            'impact' => (int) ($rule['impact'] ?? 4),
            'severity' => (int) ($rule['severity'] ?? 4),
            'insight' => $rule['insight'] ?? 'Ainda não há insight automático confiável para este comentário.',
            'market_learning' => $rule['market_learning'] ?? 'Separar para leitura manual e enriquecer as regras se esse padrão se repetir.',
            'what_to_do' => $rule['what_to_do'] ?? 'Ler manualmente, identificar a dor real e cadastrar nova regra se o padrão se repetir.',
            'what_not_to_do' => $rule['what_not_to_do'] ?? 'Não tomar decisão de produto com base em comentário isolado ainda não classificado.',
            'opportunity' => $rule['opportunity'] ?? 'Separar para análise humana e enriquecer as regras depois.',
            'recommended_action' => $rule['recommended_action'] ?? 'Ler manualmente, identificar a dor real e cadastrar nova regra se o padrão se repetir.',
            'complexity' => $rule['complexity'] ?? 'baixa',
            'seo_keywords' => $rule['seo'] ?? [],
            'matched_keywords' => $problemKey ? ($matchedKeywords['problem_'.$problemKey] ?? []) : [],
            'confidence' => $this->confidenceLabel($rating, $problemKey ? count($matchedKeywords['problem_'.$problemKey] ?? []) : 0),
            'problems' => $problemEvidence,
            'strengths' => $strengthEvidence,
        ];
    }

    public function buildReport(int $days = 365, int $limitExamples = 5): array
    {
        $since = now()->subDays(max(1, $days));

        if (! Schema::hasTable('ai_market_comments') || ! Schema::hasTable('ai_market_sources')) {
            return [
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'period_days' => $days,
                'setup_required' => true,
                'setup_message' => 'Execute o SQL database/sql/2026_05_31_ai_product_intelligence.sql antes de importar comentários.',
                'summary' => $this->emptySummary(),
                'top_problems' => [],
                'market_strengths' => [],
                'market_learnings' => [],
                'comment_insights' => [],
                'source_frequency' => [],
                'contradictions' => [],
                'pain_points' => [],
                'competitors' => [],
                'seo_opportunities' => [],
                'recommended_roadmap' => [],
                'anti_copy_alerts' => [],
                'prompt_for_chatgpt' => $this->buildPromptForChatGpt([], [], [], [], [], [], []),
            ];
        }

        $comments = AiMarketComment::query()
            ->with('source')
            ->where('created_at', '>=', $since)
            ->latest('id')
            ->limit(5000)
            ->get();

        $this->refreshCommentClassificationsForReport($comments);

        $byCategory = $this->buildProblemGroups($comments, $limitExamples);
        $strengths = $this->buildStrengthGroups($comments, $limitExamples);
        $sourceFrequency = $this->buildSourceFrequency($comments);
        $marketLearnings = $this->buildMarketLearnings($byCategory, $strengths);
        $contradictions = $this->buildContradictions($byCategory, $strengths);
        $roadmap = $this->buildRoadmap($byCategory);
        $antiCopy = $this->buildAntiCopyAlerts($byCategory, $contradictions);
        $commentInsights = $this->buildCommentInsights($comments, 15);
        $seo = $this->buildSeoOpportunities($byCategory, $strengths);
        $detectedOpportunities = $this->buildDetectedOpportunities($byCategory, $strengths);
        $criticalProblemsTotal = $this->countCriticalProblems($byCategory);

        return [
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'period_days' => $days,
            'summary' => [
                'market_comments_total' => $comments->count(),
                'sources_total' => Schema::hasTable('ai_market_sources') ? AiMarketSource::query()->count() : 0,
                'competitors_total' => $comments->pluck('competitor_name')->filter()->unique()->count(),
                'negative_comments_total' => $comments->where('detected_sentiment', 'negativo')->count(),
                'positive_comments_total' => $comments->where('detected_sentiment', 'positivo')->count(),
                'neutral_comments_total' => $comments->where('detected_sentiment', 'neutro')->count(),
                'mixed_comments_total' => $comments->where('detected_sentiment', 'misto')->count(),
                'critical_problems_total' => $criticalProblemsTotal,
                'opportunities_total' => count($detectedOpportunities),
                'product_health_score' => $this->productHealthScore($comments->count(), $comments->where('detected_sentiment', 'negativo')->count(), $comments->where('detected_sentiment', 'positivo')->count(), $comments->where('detected_sentiment', 'misto')->count(), $criticalProblemsTotal),
                'problem_categories_total' => count($byCategory),
                'strength_categories_total' => count($strengths),
                'market_learnings_total' => count($marketLearnings),
                'contradictions_total' => count($contradictions),
            ],
            'top_problems' => $byCategory,
            'market_strengths' => $strengths,
            'market_learnings' => $marketLearnings,
            'comment_insights' => $commentInsights,
            'source_frequency' => $sourceFrequency,
            'contradictions' => $contradictions,
            'pain_points' => $this->buildPainPoints($byCategory),
            'competitors' => $this->buildCompetitorSummary($comments),
            'seo_opportunities' => $seo,
            'detected_opportunities' => $detectedOpportunities,
            'recommended_roadmap' => $roadmap,
            'anti_copy_alerts' => $antiCopy,
            'prompt_for_chatgpt' => $this->buildPromptForChatGpt($byCategory, $strengths, $marketLearnings, $sourceFrequency, $contradictions, $seo, $roadmap),
        ];
    }

    private function scoreRule(string $normalized, array $keywords, ?array &$localMatches = []): int
    {
        $score = 0;
        $localMatches = [];

        foreach ($keywords as $keyword) {
            $normalizedKeyword = Str::of($keyword)->lower()->ascii()->toString();

            if (! str_contains($normalized, $normalizedKeyword)) {
                continue;
            }

            if ($this->isNegatedKeywordMention($normalized, $normalizedKeyword)) {
                continue;
            }

            $score++;
            $localMatches[] = $keyword;
        }

        return $score;
    }

    private function matchesToProblemEvidence(array $problemMatches, array $matchedKeywords): array
    {
        arsort($problemMatches);

        return collect($problemMatches)
            ->filter(fn (int|float $score): bool => $score >= 12)
            ->map(function (int|float $score, string $key) use ($matchedKeywords): array {
                $rule = self::PROBLEM_RULES[$key] ?? null;

                if (! $rule) {
                    return [];
                }

                return [
                    'key' => $key,
                    'category' => $rule['label'],
                    'score' => (int) $score,
                    'impact' => (int) ($rule['impact'] ?? 4),
                    'severity' => (int) ($rule['severity'] ?? 4),
                    'real_pain' => $rule['real_pain'] ?? 'Dor real não classificada automaticamente.',
                    'insight' => $rule['insight'] ?? 'Insight automático ainda não disponível.',
                    'market_learning' => $rule['market_learning'] ?? 'Aprendizado ainda precisa de análise manual.',
                    'what_to_do' => $rule['what_to_do'] ?? 'Transformar o padrão em melhoria pequena e mensurável.',
                    'what_not_to_do' => $rule['what_not_to_do'] ?? 'Não tomar decisão grande sem mais evidência.',
                    'opportunity' => $rule['opportunity'] ?? 'Analisar manualmente para decidir se vira melhoria.',
                    'recommended_action' => $rule['recommended_action'] ?? 'Ler exemplos reais e transformar em melhoria pequena, mensurável e reversível.',
                    'complexity' => $rule['complexity'] ?? 'baixa',
                    'seo_keywords' => $rule['seo'] ?? [],
                    'matched_keywords' => $matchedKeywords['problem_'.$key] ?? [],
                    'confidence' => $this->confidenceLabel(null, count($matchedKeywords['problem_'.$key] ?? [])),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function matchesToStrengthEvidence(array $strengthMatches, array $matchedKeywords): array
    {
        arsort($strengthMatches);

        return collect($strengthMatches)
            ->filter(fn (int|float $score): bool => $score >= 10)
            ->map(function (int|float $score, string $key) use ($matchedKeywords): array {
                $rule = self::STRENGTH_RULES[$key] ?? null;

                if (! $rule) {
                    return [];
                }

                return [
                    'key' => $key,
                    'category' => $rule['label'],
                    'score' => (int) $score,
                    'impact' => (int) ($rule['impact'] ?? 7),
                    'insight' => $rule['insight'] ?? 'Ponto forte detectado.',
                    'market_learning' => $rule['market_learning'] ?? 'Preservar o que o mercado valoriza.',
                    'what_to_do' => $rule['what_to_do'] ?? 'Preservar esse ponto forte ao evoluir o produto.',
                    'what_not_to_do' => $rule['what_not_to_do'] ?? 'Não perder esse diferencial ao adicionar complexidade.',
                    'seo_keywords' => $rule['seo'] ?? [],
                    'matched_keywords' => $matchedKeywords['strength_'.$key] ?? [],
                    'confidence' => $this->confidenceLabel(null, count($matchedKeywords['strength_'.$key] ?? [])),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function detectSentiment(string $normalized, ?int $rating = null, array $problemMatches = [], array $strengthMatches = [], array $matchedKeywords = []): string
    {
        $positiveScore = 0;
        $negativeScore = 0;

        if ($rating !== null) {
            if ($rating <= 2) {
                $negativeScore += 7;
            } elseif ($rating === 3) {
                $positiveScore += 1;
                $negativeScore += 1;
            } elseif ($rating >= 4) {
                $positiveScore += 7;
            }
        }

        foreach (self::POSITIVE_WORDS as $word) {
            $normalizedWord = Str::of($word)->lower()->ascii()->toString();

            if (! str_contains($normalized, $normalizedWord)) {
                continue;
            }

            if ($this->isNegatedKeywordMention($normalized, $normalizedWord)) {
                $negativeScore += str_contains($normalizedWord, ' ') ? 3 : 1;
                continue;
            }

            $positiveScore += str_contains($normalizedWord, ' ') ? 3 : 1;
        }

        foreach (self::NEGATIVE_WORDS as $word) {
            $normalizedWord = Str::of($word)->lower()->ascii()->toString();

            if (! str_contains($normalized, $normalizedWord)) {
                continue;
            }

            if ($this->isNegatedNegativeMention($normalized, $normalizedWord) || $this->isNegatedKeywordMention($normalized, $normalizedWord)) {
                $positiveScore += 2;
                continue;
            }

            $negativeScore += str_contains($normalizedWord, ' ') ? 4 : 1;
        }

        if (str_contains($normalized, 'o problema e que') || str_contains($normalized, 'o problema é que')) {
            $negativeScore += 5;
        }

        if (str_contains($normalized, 'esse nao e o problema') || str_contains($normalized, 'esse não é o problema') || str_contains($normalized, 'nao e o problema') || str_contains($normalized, 'não é o problema')) {
            $negativeScore = max(0, $negativeScore - 1);
        }

        if (str_contains($normalized, 'nunca tive grandes problemas') || str_contains($normalized, 'sem grandes problemas') || str_contains($normalized, 'no major problems')) {
            $positiveScore += 5;
            $negativeScore = max(0, $negativeScore - 3);
        }

        if (str_contains($normalized, 'melhor escolha') || str_contains($normalized, 'uma das melhores') || str_contains($normalized, 'de longe uma das melhores') || str_contains($normalized, 'one of the best') || str_contains($normalized, 'best choice')) {
            $positiveScore += 5;
        }

        if ((str_contains($normalized, 'atendimento') || str_contains($normalized, 'support') || str_contains($normalized, 'customer service')) && (str_contains($normalized, 'util') || str_contains($normalized, 'helpful') || str_contains($normalized, 'bom') || str_contains($normalized, 'great'))) {
            $positiveScore += 4;
        }

        $problemScore = empty($problemMatches) ? 0 : (int) max($problemMatches);
        $strengthScore = empty($strengthMatches) ? 0 : (int) max($strengthMatches);

        if ($problemScore >= 30 && $problemScore > ($strengthScore + 8)) {
            $negativeScore += 4;
        }

        if ($strengthScore >= 24 && $strengthScore > ($problemScore + 8)) {
            $positiveScore += 4;
        }

        $priceMatches = $matchedKeywords['problem_preco_valor'] ?? [];
        $priceOnlyBecauseOfSubscription = ! empty($priceMatches)
            && empty(array_diff($priceMatches, ['assinatura', 'plano']))
            && empty(array_intersect($priceMatches, ['caro', 'preco', 'custo', 'expensive', 'price', 'pricing', 'cost']));

        if ($priceOnlyBecauseOfSubscription) {
            $negativeScore = max(0, $negativeScore - 2);
        }

        if ((str_contains($normalized, 'nao posso recomendar') || str_contains($normalized, 'nao funciona') || str_contains($normalized, 'inutilizavel')) && $negativeScore >= ($positiveScore + 4)) {
            return 'negativo';
        }

        $hasProblemEvidence = $problemScore >= 18 || ! empty($problemMatches);
        $hasStrengthEvidence = $strengthScore >= 15 || ! empty($strengthMatches);
        $hasMixedConnector = collect(self::MIXED_SENTIMENT_CONNECTORS)
            ->contains(fn (string $connector): bool => str_contains($normalized, Str::of($connector)->lower()->ascii()->toString()));

        if ($hasMixedConnector && $hasProblemEvidence && ($positiveScore >= 1 || $hasStrengthEvidence)) {
            return 'misto';
        }

        if ($hasProblemEvidence && $hasStrengthEvidence && $problemScore >= 14) {
            return 'misto';
        }

        if ($hasProblemEvidence && $positiveScore >= 2 && $problemScore >= 18) {
            return 'misto';
        }

        if ($positiveScore >= 4 && $negativeScore >= 4 && abs($positiveScore - $negativeScore) <= 6) {
            return 'misto';
        }

        if ($hasProblemEvidence && $negativeScore >= 3 && $positiveScore >= 2) {
            return 'misto';
        }

        if ($hasProblemEvidence && $negativeScore > 0 && $positiveScore === 0) {
            return 'negativo';
        }

        if ($problemScore >= 24 && $negativeScore >= $positiveScore) {
            return 'negativo';
        }

        if ($positiveScore >= ($negativeScore + 3) && ! $hasProblemEvidence) {
            return 'positivo';
        }

        if ($positiveScore >= ($negativeScore + 4) && $problemScore < 14) {
            return 'positivo';
        }

        if ($negativeScore > $positiveScore) {
            return 'negativo';
        }

        if ($hasProblemEvidence && $positiveScore === 0) {
            return 'negativo';
        }

        if ($hasProblemEvidence && $positiveScore > 0) {
            return 'misto';
        }

        if ($hasStrengthEvidence && $negativeScore === 0) {
            return 'positivo';
        }

        if ($positiveScore > 0 && $negativeScore === 0 && empty($problemMatches)) {
            return 'positivo';
        }

        return 'neutro';
    }

    private function isNegatedKeywordMention(string $normalized, string $keyword): bool
    {
        $keyword = Str::of($keyword)->lower()->ascii()->toString();
        $patterns = [
            'nao '.$keyword,
            'nao e '.$keyword,
            'nao eh '.$keyword,
            'nao esta '.$keyword,
            'nao fica '.$keyword,
            'nao parece '.$keyword,
            'nao foi '.$keyword,
            'nao tenho '.$keyword,
            'sem '.$keyword,
            'nunca '.$keyword,
            'never '.$keyword,
            'not '.$keyword,
            'not a '.$keyword,
            'not an '.$keyword,
            'no '.$keyword,
            'without '.$keyword,
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($normalized, Str::of($pattern)->lower()->ascii()->toString())) {
                return true;
            }
        }

        return false;
    }

    private function isNegatedNegativeMention(string $normalized, string $negativeWord): bool
    {
        $patterns = [
            'nao tive grandes '.$negativeWord,
            'não tive grandes '.$negativeWord,
            'nunca tive grandes '.$negativeWord,
            'sem grandes '.$negativeWord,
            'nao tive '.$negativeWord,
            'não tive '.$negativeWord,
            'nunca tive '.$negativeWord,
            'sem '.$negativeWord,
            'no major '.$negativeWord,
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($normalized, Str::of($pattern)->lower()->ascii()->toString())) {
                return true;
            }
        }

        return false;
    }

    private function confidenceLabel(?int $rating, int $matchedKeywords): string
    {
        if ($matchedKeywords >= 3 || ($rating !== null && $matchedKeywords >= 2)) {
            return 'alta';
        }

        if ($matchedKeywords >= 1 || $rating !== null) {
            return 'média';
        }

        return 'baixa';
    }

    private function applyContextualBoosts(string $normalized, array &$matches, string $sentiment = 'neutro'): void
    {
        if (str_contains($normalized, 'processo de integracao') || str_contains($normalized, 'integracao deles') || str_contains($normalized, 'primeira vez') || str_contains($normalized, 'primeiros 10 minutos')) {
            $matches['onboarding_complexo'] = ($matches['onboarding_complexo'] ?? 0) + 35;
        }

        if ($sentiment !== 'positivo' && (str_contains($normalized, 'tudo em um') || str_contains($normalized, 'faz tudo') || str_contains($normalized, 'all in one'))) {
            $matches['excesso_funcionalidades'] = ($matches['excesso_funcionalidades'] ?? 0) + 30;
        }

        if (str_contains($normalized, 'antes de fazer algo util') || str_contains($normalized, 'antes mesmo de concluir')) {
            $matches['onboarding_complexo'] = ($matches['onboarding_complexo'] ?? 0) + 40;
        }

        if (str_contains($normalized, 'problemas de desempenho') || str_contains($normalized, 'problema de desempenho') || str_contains($normalized, 'nao funciona') || str_contains($normalized, 'nao funcionava') || str_contains($normalized, 'inutilizavel')) {
            $matches['lentidao_performance'] = ($matches['lentidao_performance'] ?? 0) + 45;
        }

        if (str_contains($normalized, 'nao se integram') || str_contains($normalized, 'nao integra') || str_contains($normalized, 'nao integram') || str_contains($normalized, 'nao se conecta')) {
            $matches['integracoes'] = ($matches['integracoes'] ?? 0) + 35;
        }

        if (str_contains($normalized, 'sistema de tarefas') || str_contains($normalized, 'task system') || str_contains($normalized, 'task management')) {
            $matches['gestao_tarefas_fraca'] = ($matches['gestao_tarefas_fraca'] ?? 0) + 35;
        }
    }

    private function normalizeMatchesBySentiment(string $sentiment, array &$problemMatches, array &$strengthMatches, array $matchedKeywords): void
    {
        if ($sentiment !== 'positivo') {
            return;
        }

        $priceMatches = $matchedKeywords['problem_preco_valor'] ?? [];
        $priceOnlyBecauseOfSubscription = ! empty($priceMatches)
            && empty(array_diff($priceMatches, ['assinatura', 'plano']))
            && empty(array_intersect($priceMatches, ['caro', 'preco', 'custo', 'expensive', 'price', 'pricing', 'cost']));

        if ($priceOnlyBecauseOfSubscription) {
            unset($problemMatches['preco_valor']);
        }

        foreach ($problemMatches as $key => $score) {
            $problemMatches[$key] = (int) floor($score * 0.45);
        }

        if (empty($strengthMatches)) {
            $strengthMatches['satisfacao_geral'] = 30;
        }
    }

    private function refreshCommentClassificationsForReport($comments): void
    {
        $comments->each(function (AiMarketComment $comment): void {
            if (! filled($comment->original_text)) {
                return;
            }

            $classification = $this->classifyText((string) $comment->original_text, $comment->rating);
            $metadata = is_array($comment->metadata) ? $comment->metadata : [];
            $metadata['classification'] = $classification;

            $comment->detected_sentiment = $classification['sentiment'];
            $comment->detected_category = $classification['category'];
            $comment->detected_problem = $classification['problem'];
            $comment->detected_opportunity = $classification['opportunity'];
            $comment->detected_real_pain = $classification['real_pain'];
            $comment->detected_impact = $classification['impact'];
            $comment->recommended_action = $classification['recommended_action'];
            $comment->metadata = $metadata;

            if ($comment->exists && $comment->isDirty()) {
                $comment->saveQuietly();
            }
        });
    }

    private function emptySummary(): array
    {
        return [
            'market_comments_total' => 0,
            'sources_total' => 0,
            'competitors_total' => 0,
            'negative_comments_total' => 0,
            'positive_comments_total' => 0,
            'neutral_comments_total' => 0,
            'mixed_comments_total' => 0,
            'critical_problems_total' => 0,
            'opportunities_total' => 0,
            'product_health_score' => 100,
            'problem_categories_total' => 0,
            'strength_categories_total' => 0,
            'market_learnings_total' => 0,
            'contradictions_total' => 0,
        ];
    }

    private function buildProblemGroups($comments, int $limitExamples): array
    {
        $evidence = collect();

        $comments->each(function (AiMarketComment $comment) use ($evidence): void {
            $problems = data_get($comment->metadata, 'classification.problems', []);

            foreach ($problems as $problem) {
                if (! filled(data_get($problem, 'category'))) {
                    continue;
                }

                $evidence->push([
                    'comment' => $comment,
                    'category' => (string) data_get($problem, 'category'),
                    'problem' => $problem,
                ]);
            }
        });

        return $evidence
            ->groupBy('category')
            ->map(function ($items, string $category) use ($limitExamples): array {
                $firstProblem = (array) data_get($items->first(), 'problem', []);
                $count = $items->count();
                $comments = $items->pluck('comment');
                $negative = $comments->where('detected_sentiment', 'negativo')->count();
                $mixed = $comments->where('detected_sentiment', 'misto')->count();
                $severity = (int) ($firstProblem['severity'] ?? 4);
                $impact = (int) ($firstProblem['impact'] ?? 4);
                $sourceBreakdown = $this->sourceBreakdownFor($comments);
                $evidenceWeight = $negative + $mixed;

                return [
                    'category' => $category,
                    'real_pain' => $firstProblem['real_pain'] ?? 'Dor real não classificada automaticamente.',
                    'insight' => $firstProblem['insight'] ?? 'Insight automático ainda não disponível.',
                    'market_learning' => $firstProblem['market_learning'] ?? 'Aprendizado ainda precisa de análise manual.',
                    'what_to_do' => $firstProblem['what_to_do'] ?? 'Transformar o padrão em melhoria pequena e mensurável.',
                    'what_not_to_do' => $firstProblem['what_not_to_do'] ?? 'Não tomar decisão grande sem mais evidência.',
                    'total' => $count,
                    'negative_total' => $negative,
                    'positive_total' => $comments->where('detected_sentiment', 'positivo')->count(),
                    'neutral_total' => $comments->where('detected_sentiment', 'neutro')->count(),
                    'mixed_total' => $mixed,
                    'impact' => $impact,
                    'severity' => $severity,
                    'priority_score' => ($count * $severity) + ($evidenceWeight * 5) + $impact,
                    'confidence' => $this->confidenceByOccurrences($count, $sourceBreakdown->count()),
                    'source_breakdown' => $sourceBreakdown->values()->all(),
                    'opportunity' => $firstProblem['opportunity'] ?? 'Analisar manualmente para decidir se vira melhoria.',
                    'recommended_action' => $firstProblem['recommended_action'] ?? 'Ler exemplos reais e transformar em melhoria pequena, mensurável e reversível.',
                    'complexity' => $firstProblem['complexity'] ?? 'baixa',
                    'seo_keywords' => collect($items)->pluck('problem.seo_keywords')->flatten()->filter()->unique()->values()->all(),
                    'examples' => $comments->unique('id')->take($limitExamples)->map(fn (AiMarketComment $comment): array => [
                        'competitor' => $comment->competitor_name ?: ($comment->source?->competitor_name ?: 'Não informado'),
                        'source' => $comment->source?->name ?: 'Não informado',
                        'source_type' => $comment->source?->source_type ?: 'manual',
                        'rating' => $comment->rating,
                        'sentiment' => $comment->detected_sentiment ?: 'neutro',
                        'text' => Str::limit($comment->original_text, 380),
                    ])->values()->all(),
                ];
            })
            ->sortByDesc('priority_score')
            ->values()
            ->all();
    }

    private function buildStrengthGroups($comments, int $limitExamples): array
    {
        $evidence = collect();

        $comments->each(function (AiMarketComment $comment) use ($evidence): void {
            $strengths = data_get($comment->metadata, 'classification.strengths', []);

            foreach ($strengths as $strength) {
                if (! filled(data_get($strength, 'category'))) {
                    continue;
                }

                $evidence->push([
                    'comment' => $comment,
                    'category' => (string) data_get($strength, 'category'),
                    'strength' => $strength,
                ]);
            }
        });

        return $evidence
            ->groupBy('category')
            ->map(function ($items, string $category) use ($limitExamples): array {
                $firstStrength = (array) data_get($items->first(), 'strength', []);
                $comments = $items->pluck('comment');
                $count = $items->count();
                $sourceBreakdown = $this->sourceBreakdownFor($comments);

                return [
                    'category' => $category,
                    'total' => $count,
                    'impact' => (int) ($firstStrength['impact'] ?? 5),
                    'confidence' => $this->confidenceByOccurrences($count, $sourceBreakdown->count()),
                    'insight' => $firstStrength['insight'] ?? 'Ponto positivo detectado, mas ainda precisa de leitura manual.',
                    'market_learning' => $firstStrength['market_learning'] ?? 'Entender por que o mercado valoriza esse ponto.',
                    'what_to_do' => $firstStrength['what_to_do'] ?? 'Preservar e usar como referência ao evoluir o produto.',
                    'what_not_to_do' => $firstStrength['what_not_to_do'] ?? 'Não remover sem evidência clara.',
                    'seo_keywords' => collect($items)->pluck('strength.seo_keywords')->flatten()->filter()->unique()->values()->all(),
                    'source_breakdown' => $sourceBreakdown->values()->all(),
                    'examples' => $comments->unique('id')->take($limitExamples)->map(fn (AiMarketComment $comment): array => [
                        'competitor' => $comment->competitor_name ?: ($comment->source?->competitor_name ?: 'Não informado'),
                        'source' => $comment->source?->name ?: 'Não informado',
                        'source_type' => $comment->source?->source_type ?: 'manual',
                        'rating' => $comment->rating,
                        'sentiment' => $comment->detected_sentiment ?: 'neutro',
                        'text' => Str::limit($comment->original_text, 300),
                    ])->values()->all(),
                ];
            })
            ->sortByDesc(fn (array $item): int => ($item['total'] * 10) + ($item['impact'] ?? 0))
            ->values()
            ->all();
    }

    private function sourceBreakdownFor($items)
    {
        return $items
            ->groupBy(fn (AiMarketComment $comment): string => ($comment->source?->source_type ?: 'manual').'|'.($comment->source?->name ?: 'Não informado'))
            ->map(function ($sourceItems, string $key): array {
                [$type, $name] = array_pad(explode('|', $key, 2), 2, 'Não informado');

                return [
                    'source_type' => $type,
                    'source_name' => $name,
                    'total' => $sourceItems->count(),
                    'negative_total' => $sourceItems->where('detected_sentiment', 'negativo')->count(),
                    'positive_total' => $sourceItems->where('detected_sentiment', 'positivo')->count(),
                    'neutral_total' => $sourceItems->where('detected_sentiment', 'neutro')->count(),
                    'mixed_total' => $sourceItems->where('detected_sentiment', 'misto')->count(),
                ];
            })
            ->sortByDesc('total');
    }

    private function confidenceByOccurrences(int $count, int $sources): string
    {
        if ($count >= 25 || ($count >= 10 && $sources >= 3)) {
            return 'alta';
        }

        if ($count >= 5 || $sources >= 2) {
            return 'média';
        }

        return 'baixa';
    }

    private function buildPainPoints(array $byCategory): array
    {
        return collect($byCategory)
            ->map(fn (array $item): array => [
                'pain' => $item['real_pain'] ?? 'Dor real não classificada automaticamente.',
                'category' => $item['category'],
                'total' => $item['total'],
                'priority_score' => $item['priority_score'],
                'confidence' => $item['confidence'] ?? 'baixa',
                'recommended_action' => $item['recommended_action'] ?? $this->actionFor($item['category']),
            ])
            ->sortByDesc('priority_score')
            ->values()
            ->all();
    }

    private function buildCompetitorSummary($comments): array
    {
        return $comments
            ->groupBy(fn (AiMarketComment $comment): string => $comment->competitor_name ?: ($comment->source?->competitor_name ?: 'Não informado'))
            ->map(fn ($items, string $competitor): array => [
                'competitor' => $competitor,
                'total' => $items->count(),
                'negative_total' => $items->where('detected_sentiment', 'negativo')->count(),
                'positive_total' => $items->where('detected_sentiment', 'positivo')->count(),
                'top_categories' => $items
                    ->groupBy('detected_category')
                    ->map->count()
                    ->sortDesc()
                    ->take(5)
                    ->toArray(),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    private function buildSeoOpportunities(array $byCategory, array $strengths): array
    {
        return collect($byCategory)
            ->merge($strengths)
            ->flatMap(function (array $item): array {
                return collect($item['seo_keywords'] ?? [])
                    ->map(fn (string $keyword): array => [
                        'keyword' => $keyword,
                        'source_problem' => $item['category'],
                        'real_pain' => $item['real_pain'] ?? ($item['insight'] ?? null),
                        'priority_score' => $item['priority_score'] ?? (($item['total'] ?? 1) * ($item['impact'] ?? 5)),
                        'suggested_angle' => $this->seoAngleFor($item['category']),
                    ])
                    ->all();
            })
            ->sortByDesc('priority_score')
            ->unique('keyword')
            ->take(40)
            ->values()
            ->all();
    }

    private function buildRoadmap(array $byCategory): array
    {
        $items = [];

        foreach (array_slice($byCategory, 0, 10) as $problem) {
            $items[] = [
                'priority' => $this->isCriticalProblem($problem) ? 'crítica' : ($problem['priority_score'] >= 35 ? 'alta' : 'média'),
                'problem' => $problem['category'],
                'real_pain' => $problem['real_pain'] ?? 'Dor real não classificada automaticamente.',
                'insight' => $problem['insight'] ?? null,
                'market_learning' => $problem['market_learning'] ?? null,
                'why' => "Apareceu em {$problem['total']} comentário(s), com {$problem['negative_total']} negativo(s), confiança {$problem['confidence']}.",
                'recommended_action' => $problem['recommended_action'] ?? $this->actionFor($problem['category']),
                'what_to_do' => $problem['what_to_do'] ?? $problem['recommended_action'] ?? $this->actionFor($problem['category']),
                'what_not_to_do' => $problem['what_not_to_do'] ?? 'Não copiar complexidade sem evidência.',
                'expected_impact' => $this->isCriticalProblem($problem) ? 'alto' : 'médio',
                'complexity' => $problem['complexity'] ?? $this->complexityFor($problem['category']),
                'confidence' => $problem['confidence'] ?? 'baixa',
            ];
        }

        return $items;
    }

    private function buildAntiCopyAlerts(array $byCategory, array $contradictions = []): array
    {
        $alerts = [];

        foreach ($byCategory as $problem) {
            if (in_array($problem['category'], ['Interface confusa / excesso visual', 'Onboarding complexo / muitas decisões iniciais', 'Muitos cliques / fluxo burocrático', 'Excesso de funcionalidades / produto inchado'], true)) {
                $alerts[] = [
                    'alert' => 'Não copiar complexidade dos concorrentes',
                    'evidence' => $problem['category'].' aparece nos comentários analisados com confiança '.$problem['confidence'].'.',
                    'decision' => $problem['what_not_to_do'] ?? 'Antes de adicionar uma funcionalidade, validar se ela reduz esforço ou aumenta complexidade.',
                ];
            }
        }

        foreach ($contradictions as $contradiction) {
            $alerts[] = [
                'alert' => 'Contradição detectada no mercado',
                'evidence' => $contradiction['summary'],
                'decision' => $contradiction['decision'],
            ];
        }

        return collect($alerts)->unique('evidence')->values()->all();
    }

    private function buildSourceFrequency($comments): array
    {
        return $comments
            ->groupBy(fn (AiMarketComment $comment): string => ($comment->source?->source_type ?: 'manual').'|'.($comment->source?->name ?: 'Não informado'))
            ->map(function ($items, string $key): array {
                [$type, $name] = array_pad(explode('|', $key, 2), 2, 'Não informado');

                return [
                    'source_type' => $type,
                    'source_name' => $name,
                    'total' => $items->count(),
                    'negative_total' => $items->where('detected_sentiment', 'negativo')->count(),
                    'positive_total' => $items->where('detected_sentiment', 'positivo')->count(),
                    'neutral_total' => $items->where('detected_sentiment', 'neutro')->count(),
                    'mixed_total' => $items->where('detected_sentiment', 'misto')->count(),
                    'top_categories' => $items->groupBy('detected_category')->map->count()->sortDesc()->take(5)->toArray(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    private function buildMarketLearnings(array $byCategory, array $strengths): array
    {
        return collect($byCategory)
            ->map(fn (array $item): array => [
                'type' => 'problema',
                'title' => $item['category'],
                'learning' => $item['market_learning'],
                'insight' => $item['insight'],
                'what_to_do' => $item['what_to_do'],
                'what_not_to_do' => $item['what_not_to_do'],
                'evidence' => "{$item['total']} ocorrência(s), {$item['negative_total']} negativa(s), {$item['mixed_total']} mista(s), confiança {$item['confidence']}.",
                'priority_score' => $item['priority_score'],
                'confidence' => $item['confidence'],
            ])
            ->merge(collect($strengths)->map(fn (array $item): array => [
                'type' => 'ponto_forte',
                'title' => $item['category'],
                'learning' => $item['market_learning'],
                'insight' => $item['insight'],
                'what_to_do' => $item['what_to_do'],
                'what_not_to_do' => $item['what_not_to_do'],
                'evidence' => "{$item['total']} ocorrência(s) positiva(s), confiança {$item['confidence']}.",
                'priority_score' => ($item['total'] * ($item['impact'] ?? 5)),
                'confidence' => $item['confidence'],
            ]))
            ->sortByDesc('priority_score')
            ->take(20)
            ->values()
            ->all();
    }

    private function buildContradictions(array $byCategory, array $strengths): array
    {
        $problems = collect($byCategory)->keyBy('category');
        $strengthMap = collect($strengths)->keyBy('category');
        $contradictions = [];

        if ($problems->has('Excesso de funcionalidades / produto inchado') && $strengthMap->has('Ponto forte: centralização / tudo em um útil')) {
            $bad = $problems->get('Excesso de funcionalidades / produto inchado');
            $good = $strengthMap->get('Ponto forte: centralização / tudo em um útil');
            $contradictions[] = [
                'title' => 'Centralização é amada e odiada ao mesmo tempo',
                'summary' => "Há {$good['total']} elogio(s) sobre centralização e {$bad['total']} reclamação(ões) sobre produto inchado.",
                'risk' => 'Copiar o all-in-one dos concorrentes pode atrair usuários avançados e afastar iniciantes.',
                'decision' => 'Manter centralização como poder avançado, mas esconder complexidade no primeiro uso.',
                'confidence' => $this->confidenceByOccurrences(($good['total'] ?? 0) + ($bad['total'] ?? 0), 2),
            ];
        }


        if ($problems->has('Lentidão / performance') && ($strengthMap->has('Ponto forte: satisfação geral / confiança') || $strengthMap->has('Ponto forte: centralização / tudo em um útil'))) {
            $performance = $problems->get('Lentidão / performance');
            $good = $strengthMap->get('Ponto forte: satisfação geral / confiança') ?: $strengthMap->get('Ponto forte: centralização / tudo em um útil');
            $contradictions[] = [
                'title' => 'O produto é valorizado quando funciona bem, mas rejeitado quando fica lento',
                'summary' => "Há {$good['total']} evidência(s) de valor/confiança e {$performance['total']} reclamação(ões) ligadas a performance.",
                'risk' => 'Adicionar recursos ou copiar concorrentes sem cuidar da velocidade pode transformar valor percebido em frustração.',
                'decision' => 'Priorizar velocidade percebida, estabilidade e tarefas críticas antes de aumentar complexidade ou adicionar recursos pesados.',
                'confidence' => $this->confidenceByOccurrences(($good['total'] ?? 0) + ($performance['total'] ?? 0), 2),
            ];
        }

        if ($problems->has('Falta de funcionalidades importantes') && $problems->has('Excesso de funcionalidades / produto inchado')) {
            $missing = $problems->get('Falta de funcionalidades importantes');
            $excess = $problems->get('Excesso de funcionalidades / produto inchado');
            $contradictions[] = [
                'title' => 'Alguns querem mais funções, outros querem menos complexidade',
                'summary' => "Há {$missing['total']} pedido(s) de função e {$excess['total']} reclamação(ões) sobre excesso.",
                'risk' => 'Adicionar tudo que pedem pode transformar o produto em uma ferramenta pesada.',
                'decision' => 'Desenvolver apenas funcionalidades que reduzem esforço, prazo ou risco no nicho principal.',
                'confidence' => $this->confidenceByOccurrences(($missing['total'] ?? 0) + ($excess['total'] ?? 0), 2),
            ];
        }

        return $contradictions;
    }

    private function buildCommentInsights($comments, int $limit): array
    {
        return $comments
            ->take($limit)
            ->map(fn (AiMarketComment $comment): array => [
                'competitor' => $comment->competitor_name ?: ($comment->source?->competitor_name ?: 'Não informado'),
                'source' => $comment->source?->name ?: 'Não informado',
                'source_type' => $comment->source?->source_type ?: 'manual',
                'sentiment' => $comment->detected_sentiment ?: 'neutro',
                'category' => $comment->detected_category ?: 'Não classificado',
                'insight_type' => data_get($comment->metadata, 'classification.insight_type', 'problema'),
                'insight' => data_get($comment->metadata, 'classification.insight', 'Insight não disponível para comentário antigo.'),
                'market_learning' => data_get($comment->metadata, 'classification.market_learning', 'Aprendizado não disponível para comentário antigo.'),
                'what_to_do' => data_get($comment->metadata, 'classification.what_to_do', $comment->recommended_action ?: 'Analisar manualmente.'),
                'what_not_to_do' => data_get($comment->metadata, 'classification.what_not_to_do', 'Não tomar decisão grande com base em um comentário isolado.'),
                'confidence' => data_get($comment->metadata, 'classification.confidence', 'baixa'),
                'text' => Str::limit($comment->original_text, 280),
            ])
            ->values()
            ->all();
    }

    private function buildPromptForChatGpt(array $byCategory, array $strengths, array $marketLearnings, array $sourceFrequency, array $contradictions, array $seo, array $roadmap): string
    {
        $summary = [
            'problemas_detectados' => count($byCategory),
            'pontos_fortes_detectados' => count($strengths),
            'aprendizados_gerados' => count($marketLearnings),
            'fontes_analisadas' => count($sourceFrequency),
            'contradicoes_detectadas' => count($contradictions),
            'itens_roadmap' => count($roadmap),
            'oportunidades_detectadas' => $this->countDetectedOpportunities($byCategory, $strengths),
            'problemas_criticos' => $this->countCriticalProblems($byCategory),
            'maior_problema' => $byCategory[0]['category'] ?? null,
            'maior_dor_real' => $byCategory[0]['real_pain'] ?? null,
            'maior_aprendizado' => $marketLearnings[0]['learning'] ?? null,
        ];

        $problemsText = collect($byCategory)->map(function (array $item, int $index): string {
            $sources = collect($item['source_breakdown'] ?? [])->map(fn (array $source): string => "{$source['source_name']} ({$source['total']})")->implode(', ');
            $examples = collect($item['examples'] ?? [])->map(fn (array $example): string => "  - {$example['competitor']} | {$example['source']} | Nota: ".($example['rating'] ?: 'sem nota')."\n    Comentário: {$example['text']}")->implode("\n");
            $keywords = collect($item['seo_keywords'] ?? [])->map(fn (string $keyword): string => "  - {$keyword}")->implode("\n");

            return ($index + 1).". Problema: {$item['category']}\nOcorrências: {$item['total']}\nNegativos: {$item['negative_total']}\nConfiança: {$item['confidence']}\nPrioridade: {$item['priority_score']}\nImpacto: {$item['impact']}\nGravidade: {$item['severity']}\nDor real: {$item['real_pain']}\nInsight: {$item['insight']}\nAprendizado do mercado: {$item['market_learning']}\nO que fazer: {$item['what_to_do']}\nO que NÃO fazer: {$item['what_not_to_do']}\nOportunidade: {$item['opportunity']}\nAção recomendada: {$item['recommended_action']}\nFontes: ".($sources ?: 'não informado')."\nPalavras-chave SEO:\n".($keywords ?: '  - Nenhuma palavra-chave automática')."\nExemplos:\n".($examples ?: '  - Nenhum exemplo disponível');
        })->implode("\n\n-------------------------\n\n");

        $strengthsText = collect($strengths)->map(function (array $item, int $index): string {
            $sources = collect($item['source_breakdown'] ?? [])->map(fn (array $source): string => "{$source['source_name']} ({$source['total']})")->implode(', ');

            return ($index + 1).". Ponto forte: {$item['category']}\nOcorrências: {$item['total']}\nConfiança: {$item['confidence']}\nInsight: {$item['insight']}\nAprendizado do mercado: {$item['market_learning']}\nO que preservar/fazer: {$item['what_to_do']}\nO que NÃO fazer: {$item['what_not_to_do']}\nFontes: ".($sources ?: 'não informado');
        })->implode("\n\n-------------------------\n\n");

        $learningsText = collect($marketLearnings)->map(fn (array $item, int $index): string => ($index + 1).". {$item['title']} ({$item['type']}, confiança {$item['confidence']})\nAprendizado: {$item['learning']}\nInsight: {$item['insight']}\nEvidência: {$item['evidence']}\nFazer: {$item['what_to_do']}\nNão fazer: {$item['what_not_to_do']}")->implode("\n\n");

        $sourcesText = collect($sourceFrequency)->map(function (array $source, int $index): string {
            $categories = collect($source['top_categories'] ?? [])->map(fn ($total, $category): string => "{$category}: {$total}")->implode('; ');

            return ($index + 1).". {$source['source_name']} ({$source['source_type']})\nTotal: {$source['total']} | Negativos: {$source['negative_total']} | Positivos: {$source['positive_total']} | Mistos: {$source['mixed_total']} | Neutros: {$source['neutral_total']}\nPrincipais categorias: ".($categories ?: 'não informado');
        })->implode("\n\n");

        $contradictionsText = collect($contradictions)->map(fn (array $item, int $index): string => ($index + 1).". {$item['title']}\nResumo: {$item['summary']}\nRisco: {$item['risk']}\nDecisão sugerida: {$item['decision']}\nConfiança: {$item['confidence']}")->implode("\n\n");

        $seoText = collect($seo)->map(fn (array $item): string => "- {$item['keyword']} | Origem: {$item['source_problem']} | Ângulo: {$item['suggested_angle']}")->implode("\n");

        $roadmapText = collect($roadmap)->map(fn (array $item, int $index): string => ($index + 1).". Item: {$item['problem']}\nPrioridade: {$item['priority']} | Confiança: {$item['confidence']} | Complexidade: {$item['complexity']}\nDor real: {$item['real_pain']}\nAprendizado: ".($item['market_learning'] ?? 'não informado')."\nMotivo: {$item['why']}\nFazer: {$item['what_to_do']}\nNão fazer: {$item['what_not_to_do']}\nAção recomendada: {$item['recommended_action']}")->implode("\n\n-------------------------\n\n");

        return trim("Analise este relatório do meu SaaS.\n\nCONTEXTO DO PRODUTO:\nMeu produto é um sistema operacional leve para empresas, com foco em controle de prazos, prevenção de multas, execução correta de tarefas críticas, menos cliques, menos complexidade, menos configuração inicial e mais produtividade. Eu ainda não quero instalar IA no sistema; estou usando este relatório interno para receber recomendações.\n\nPAPEL QUE VOCÊ DEVE ASSUMIR:\nAtue como Product Manager, UX Specialist, Growth Specialist, Analista de Mercado e Estrategista de SaaS. Quero recomendações práticas, priorizadas e diretas.\n\nOBJETIVO DA ANÁLISE:\n1. Identificar os problemas mais importantes.\n2. Identificar as dores reais por trás dos comentários.\n3. Identificar pontos fortes que eu devo preservar.\n4. Encontrar contradições nos comentários para evitar decisões erradas.\n5. Encontrar oportunidades de UX, SEO, marketing e posicionamento.\n6. Dizer o que devo melhorar primeiro.\n7. Dizer o que devo ignorar, simplificar, remover ou não implementar.\n8. Gerar um plano de ação priorizado do maior impacto para o menor impacto.\n\n=========================\nRESUMO EXECUTIVO GERADO PELO SISTEMA\n=========================\n".json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n=========================\nPRINCIPAIS PROBLEMAS DETECTADOS - LISTA COMPLETA\n=========================\n".($problemsText ?: 'Nenhum problema detectado ainda.')."\n\n=========================\nPONTOS FORTES DETECTADOS - LISTA COMPLETA\n=========================\n".($strengthsText ?: 'Nenhum ponto forte detectado ainda.')."\n\n=========================\nAPRENDIZADOS DO MERCADO\n=========================\n".($learningsText ?: 'Nenhum aprendizado gerado ainda.')."\n\n=========================\nFREQUÊNCIA POR FONTE\n=========================\n".($sourcesText ?: 'Nenhuma fonte analisada ainda.')."\n\n=========================\nCONTRADIÇÕES DETECTADAS\n=========================\n".($contradictionsText ?: 'Nenhuma contradição detectada ainda.')."\n\n=========================\nOPORTUNIDADES DE SEO DETECTADAS\n=========================\n".($seoText ?: 'Nenhuma oportunidade de SEO detectada ainda.')."\n\n=========================\nROADMAP SUGERIDO - LISTA COMPLETA\n=========================\n".($roadmapText ?: 'Nenhum item de roadmap gerado ainda.')."\n\n=========================\nPERGUNTAS QUE VOCÊ DEVE RESPONDER\n=========================\n1. Qual é o principal problema detectado?\n2. Qual é a principal dor dos usuários?\n3. Qual ponto forte do mercado eu devo preservar no meu SaaS?\n4. Existe alguma contradição nos comentários? Como devo agir?\n5. O que devo corrigir primeiro?\n6. O que devo ignorar?\n7. O que devo remover ou simplificar?\n8. Qual funcionalidade tem maior retorno?\n9. Qual funcionalidade tem menor retorno?\n10. Quais oportunidades de SEO devo atacar primeiro?\n11. Quais oportunidades de marketing existem?\n12. O que os concorrentes estão errando?\n13. Como posso usar esses erros para diferenciar meu SaaS?\n14. Gere um plano de ação priorizado do maior impacto para o menor impacto.");
    }

    private function buildDetectedOpportunities(array $byCategory, array $strengths): array
    {
        return collect($byCategory)
            ->map(fn (array $item): array => [
                'type' => 'problema',
                'category' => $item['category'] ?? 'Não classificado',
                'opportunity' => $item['opportunity'] ?? $item['recommended_action'] ?? 'Transformar o padrão detectado em melhoria prática.',
                'priority_score' => $item['priority_score'] ?? 0,
                'confidence' => $item['confidence'] ?? 'baixa',
            ])
            ->merge(collect($strengths)->map(fn (array $item): array => [
                'type' => 'ponto_forte',
                'category' => $item['category'] ?? 'Ponto forte',
                'opportunity' => $item['what_to_do'] ?? 'Preservar esse ponto forte ao evoluir o produto.',
                'priority_score' => ($item['total'] ?? 1) * ($item['impact'] ?? 5),
                'confidence' => $item['confidence'] ?? 'baixa',
            ]))
            ->filter(fn (array $item): bool => filled($item['opportunity']))
            ->unique(fn (array $item): string => Str::lower($item['type'].'|'.$item['category'].'|'.$item['opportunity']))
            ->sortByDesc('priority_score')
            ->values()
            ->all();
    }

    private function countDetectedOpportunities(array $byCategory, array $strengths): int
    {
        return count($this->buildDetectedOpportunities($byCategory, $strengths));
    }

    private function countCriticalProblems(array $byCategory): int
    {
        return collect($byCategory)
            ->filter(fn (array $problem): bool => $this->isCriticalProblem($problem))
            ->count();
    }

    private function isCriticalProblem(array $problem): bool
    {
        $severity = (int) ($problem['severity'] ?? 0);
        $impact = (int) ($problem['impact'] ?? 0);
        $negative = (int) ($problem['negative_total'] ?? 0);
        $mixed = (int) ($problem['mixed_total'] ?? 0);
        $priority = (int) ($problem['priority_score'] ?? 0);

        return $priority >= 80 || (($negative + $mixed) > 0 && $severity >= 9 && $impact >= 9);
    }

    private function productHealthScore(int $total, int $negative, int $positive, int $mixed, int $criticalProblems): int
    {
        if ($total <= 0) {
            return 100;
        }

        $negativeRate = $negative / max(1, $total);
        $positiveRate = $positive / max(1, $total);
        $mixedRate = $mixed / max(1, $total);

        $score = 78;
        $score += (int) round($positiveRate * 22);
        $score -= (int) round($negativeRate * 34);
        $score -= (int) round($mixedRate * 12);
        $score -= min(25, $criticalProblems * 7);

        return max(0, min(100, $score));
    }

    private function actionFor(string $category): string
    {
        return collect(self::PROBLEM_RULES)->firstWhere('label', $category)['recommended_action']
            ?? 'Analisar exemplos reais e transformar em uma melhoria pequena, mensurável e reversível.';
    }

    private function complexityFor(string $category): string
    {
        return collect(self::PROBLEM_RULES)->firstWhere('label', $category)['complexity'] ?? 'baixa';
    }

    private function seoAngleFor(string $category): string
    {
        return match ($category) {
            'Lentidão / performance', 'Ponto forte: rapidez / leveza' => 'Comparar velocidade, simplicidade e leveza contra ferramentas pesadas.',
            'Muitos cliques / fluxo burocrático' => 'Prometer resolução em menos etapas e menos telas.',
            'Onboarding complexo / muitas decisões iniciais' => 'Prometer começar a trabalhar rápido sem configurar arquitetura complexa.',
            'Prazos / risco / multas' => 'Focar em prevenção de prejuízo, multa e retrabalho.',
            'Excesso de funcionalidades / produto inchado' => 'Prometer foco, simplicidade e menos complexidade que ferramentas all-in-one.',
            'Ponto forte: facilidade de uso' => 'Explorar facilidade, clareza e baixa curva de aprendizado.',
            'Ponto forte: centralização / tudo em um útil' => 'Explorar centralização sem complexidade.',
            default => 'Transformar reclamação ou elogio comum em benefício claro e específico.',
        };
    }
}
