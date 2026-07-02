<?php

namespace App\Services;


use App\Support\CachedSchema;
use App\Filament\Resources\DashboardConfiguravel\DashboardWidgetConfiguracaoResource;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\Comentario;
use App\Models\DashboardWidgetConfiguracao;
use App\Models\ItemControle;
use App\Models\ItemControleComentario;
use App\Models\Responsavel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class RelatoriosDashboardService
{
    private const STATUS_FINALIZADOS = ['concluido', 'aprovado', 'cancelado'];
    private const STATUS_CONCLUIDOS_FINANCEIRO = ['concluido', 'aprovado', 'assinado'];
    private const STATUS_APROVACAO = ['aguardando_aprovacao', 'em_aprovacao'];

    public function dashboards(?User $user, ?string $perfil = null): array
    {
        $perfilAtual = $this->normalizarPerfil($perfil, $user);
        $base = $this->baseQuery($user);

        $vencidosQuery = (clone $base)
            ->whereNotIn('status', self::STATUS_FINALIZADOS)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', now()->toDateString());

        $aprovacoes = (clone $base)
            ->whereIn('status', self::STATUS_APROVACAO)
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderByRaw($this->hasColumn('status_operacional_at') ? 'COALESCE(status_operacional_at, updated_at) asc' : 'updated_at asc')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->itemPayload($item, 'warning'))
            ->values()
            ->toArray();

        $vencidos = (clone $vencidosQuery)
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderBy('data_vencimento')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->itemPayload($item, 'danger'))
            ->values()
            ->toArray();

        $vencendo = $this->vencendoProximos($user);
        $semResponsavel = $this->semResponsavel($user);
        $bloqueados = $this->bloqueados($user);
        $comentarios = $this->comentariosAtribuidos($user);
        $financeiro = $this->financeiro($user);
        $carga = $this->cargaPorResponsavel($user);
        $status = $this->statusDistribuicao($user);
        $timeTracking = $this->timeTracking($user);
        $milestones = $this->milestones($user);
        $cliente = $this->clienteResumo($user);
        $auditoria = $this->auditoriaResumo($user);

        $totais = [
            'vencidos' => (clone $vencidosQuery)->count(),
            'vencendo' => $vencendo['total'],
            'aprovacoes' => (clone $base)->whereIn('status', self::STATUS_APROVACAO)->count(),
            'comentarios' => count($comentarios),
            'bloqueados' => $bloqueados['total'],
            'financeiro_valor' => $financeiro['total_valor'],
            'financeiro_qtd' => count($financeiro['items']),
            'sem_responsavel' => $semResponsavel['total'],
            'cliente_pendencias' => $cliente['pendencias'],
            'auditoria_eventos' => $auditoria['eventos_30_dias'],
            'acoes_sensiveis' => $auditoria['acoes_sensiveis'],
        ];

        $perfilConfig = $this->perfilConfig($perfilAtual);

        return [
            'header' => [
                'title' => $perfilConfig['title'],
                'subtitle' => $perfilConfig['subtitle'],
                'me_mode' => $this->meMode($user),
                'perfil_atual' => $perfilAtual,
                'perfil_label' => $perfilConfig['label'],
            ],
            'profiles' => $this->perfisDisponiveis(),
            'cards' => $this->cardsPorPerfil($perfilAtual, $totais),
            'sections' => [
                'aprovacoes' => $aprovacoes,
                'vencidos' => $vencidos,
                'vencendo' => $vencendo['items'],
                'comentarios' => $comentarios,
                'financeiro' => $financeiro['items'],
                'bloqueados' => $bloqueados['items'],
                'sem_responsavel' => $semResponsavel['items'],
                'cliente' => $cliente['items'],
                'auditoria' => $auditoria['items'],
            ],
            'layout' => $this->layoutPorPerfil($perfilAtual),
            'charts' => [
                'carga' => $carga,
                'status' => $status,
                'time_tracking' => $timeTracking,
                'auditoria_por_usuario' => $auditoria['por_usuario'],
                'financeiro_por_empresa' => $financeiro['por_empresa'],
                'cliente_por_status' => $cliente['por_status'],
            ],
            'milestones' => $milestones,
            'actions' => [
                'nova_demanda' => $this->resourceUrl(ItemControleResource::class, 'create'),
                'tarefas' => $this->resourceUrl(ItemControleResource::class, 'index'),
                'configurar' => $this->resourceUrl(DashboardWidgetConfiguracaoResource::class, 'gerenciar')
                    ?: $this->resourceUrl(DashboardWidgetConfiguracaoResource::class, 'index'),
            ],
            'missing_columns' => $this->missingRecommendedColumns(),
        ];
    }

    protected function perfisDisponiveis(): array
    {
        return [
            'gestor' => ['label' => 'Gestor', 'icon' => '📊', 'hint' => 'Riscos, gargalos e responsáveis'],
            'operacional' => ['label' => 'Operacional', 'icon' => '✅', 'hint' => 'O que executar agora'],
            'financeiro' => ['label' => 'Financeiro', 'icon' => '💰', 'hint' => 'Cobrança e valor parado'],
            'cliente' => ['label' => 'Cliente', 'icon' => '👥', 'hint' => 'Pendências e entregas por empresa'],
            'auditoria' => ['label' => 'Auditoria/Compliance', 'icon' => '🛡️', 'hint' => 'Rastro, atraso e ações sensíveis'],
        ];
    }

    protected function normalizarPerfil(?string $perfil, ?User $user): string
    {
        $perfil = str((string) $perfil)->lower()->slug('_')->toString();

        if (array_key_exists($perfil, $this->perfisDisponiveis())) {
            return $perfil;
        }

        if ($user?->isUser()) {
            return 'operacional';
        }

        if ($user?->isGestor() || $user?->isAdmin() || $user?->isSuperAdmin()) {
            return 'gestor';
        }

        return 'operacional';
    }

    protected function perfilConfig(string $perfil): array
    {
        return match ($perfil) {
            'operacional' => [
                'label' => 'Operacional',
                'title' => 'Dashboard Operacional',
                'subtitle' => 'Fila prática para o usuário executar primeiro vencidos, aprovações, bloqueios e comentários atribuídos.',
            ],
            'financeiro' => [
                'label' => 'Financeiro',
                'title' => 'Dashboard Financeiro',
                'subtitle' => 'Visão de valor parado, itens concluídos sem faturamento, cobrança por empresa e risco de atraso.',
            ],
            'cliente' => [
                'label' => 'Cliente',
                'title' => 'Dashboard Cliente',
                'subtitle' => 'Resumo por empresa com pendências, entregas próximas, status e itens que precisam de retorno do cliente.',
            ],
            'auditoria' => [
                'label' => 'Auditoria/Compliance',
                'title' => 'Dashboard de Auditoria e Compliance',
                'subtitle' => 'Trilha operacional com eventos recentes, atrasos, alterações sensíveis e usuários mais ativos.',
            ],
            default => [
                'label' => 'Gestor',
                'title' => 'Dashboard do Gestor',
                'subtitle' => 'Painel executivo para priorizar riscos, destravar gargalos, redistribuir carga e acompanhar entregas.',
            ],
        };
    }

    protected function cardsPorPerfil(string $perfil, array $totais): array
    {
        return match ($perfil) {
            'operacional' => [
                ['label' => 'Vencidos', 'value' => $totais['vencidos'], 'hint' => 'Resolva antes de iniciar novas tarefas.', 'tone' => $totais['vencidos'] > 0 ? 'danger' : 'success', 'giant' => true],
                ['label' => 'Vencem em 7 dias', 'value' => $totais['vencendo'], 'hint' => 'Próximas entregas da fila.', 'tone' => $totais['vencendo'] > 0 ? 'warning' : 'success'],
                ['label' => 'Aprovações', 'value' => $totais['aprovacoes'], 'hint' => 'Decisões aguardando ação.', 'tone' => $totais['aprovacoes'] > 0 ? 'warning' : 'success'],
                ['label' => 'Comentários', 'value' => $totais['comentarios'], 'hint' => 'Menções e respostas pendentes.', 'tone' => $totais['comentarios'] > 0 ? 'warning' : 'success'],
                ['label' => 'Bloqueados', 'value' => $totais['bloqueados'], 'hint' => 'Itens travados por dependência.', 'tone' => $totais['bloqueados'] > 0 ? 'danger' : 'success'],
            ],
            'financeiro' => [
                ['label' => 'Valor em aberto', 'value' => $this->money($totais['financeiro_valor']), 'hint' => 'Concluído, mas ainda sem faturar/pagar.', 'tone' => $totais['financeiro_valor'] > 0 ? 'warning' : 'success', 'giant' => true],
                ['label' => 'Itens para cobrar', 'value' => $totais['financeiro_qtd'], 'hint' => 'Itens financeiros exigindo ação.', 'tone' => $totais['financeiro_qtd'] > 0 ? 'warning' : 'success'],
                ['label' => 'Vencidos', 'value' => $totais['vencidos'], 'hint' => 'Risco operacional que pode afetar cobrança.', 'tone' => $totais['vencidos'] > 0 ? 'danger' : 'success'],
                ['label' => 'Aprovações', 'value' => $totais['aprovacoes'], 'hint' => 'Podem liberar faturamento.', 'tone' => $totais['aprovacoes'] > 0 ? 'warning' : 'success'],
                ['label' => 'Bloqueados', 'value' => $totais['bloqueados'], 'hint' => 'Entregas paradas antes da cobrança.', 'tone' => $totais['bloqueados'] > 0 ? 'danger' : 'success'],
            ],
            'cliente' => [
                ['label' => 'Pendências do cliente', 'value' => $totais['cliente_pendencias'], 'hint' => 'Itens aguardando retorno, portal ou cliente.', 'tone' => $totais['cliente_pendencias'] > 0 ? 'warning' : 'success', 'giant' => true],
                ['label' => 'Vencem em 7 dias', 'value' => $totais['vencendo'], 'hint' => 'Entregas próximas por empresa.', 'tone' => $totais['vencendo'] > 0 ? 'warning' : 'success'],
                ['label' => 'Vencidos', 'value' => $totais['vencidos'], 'hint' => 'Atrasos visíveis ao relacionamento.', 'tone' => $totais['vencidos'] > 0 ? 'danger' : 'success'],
                ['label' => 'Sem responsável', 'value' => $totais['sem_responsavel'], 'hint' => 'Risco de ninguém assumir o cliente.', 'tone' => $totais['sem_responsavel'] > 0 ? 'danger' : 'success'],
                ['label' => 'Comentários', 'value' => $totais['comentarios'], 'hint' => 'Interações recentes atribuídas.', 'tone' => $totais['comentarios'] > 0 ? 'warning' : 'success'],
            ],
            'auditoria' => [
                ['label' => 'Eventos em 30 dias', 'value' => $totais['auditoria_eventos'], 'hint' => 'Registros reais da trilha de auditoria.', 'tone' => $totais['auditoria_eventos'] > 0 ? 'info' : 'success', 'giant' => true],
                ['label' => 'Ações sensíveis', 'value' => $totais['acoes_sensiveis'], 'hint' => 'Exclusões, reprovações ou mudanças críticas.', 'tone' => $totais['acoes_sensiveis'] > 0 ? 'danger' : 'success'],
                ['label' => 'Vencidos', 'value' => $totais['vencidos'], 'hint' => 'Risco de conformidade por atraso.', 'tone' => $totais['vencidos'] > 0 ? 'danger' : 'success'],
                ['label' => 'Sem responsável', 'value' => $totais['sem_responsavel'], 'hint' => 'Itens sem dono operacional.', 'tone' => $totais['sem_responsavel'] > 0 ? 'danger' : 'success'],
                ['label' => 'Bloqueados', 'value' => $totais['bloqueados'], 'hint' => 'Dependências que impedem execução.', 'tone' => $totais['bloqueados'] > 0 ? 'warning' : 'success'],
            ],
            default => [
                ['label' => 'Vencidos', 'value' => $totais['vencidos'], 'hint' => 'Risco imediato da operação.', 'tone' => $totais['vencidos'] > 0 ? 'danger' : 'success', 'giant' => true],
                ['label' => 'Aprovações', 'value' => $totais['aprovacoes'], 'hint' => 'Decisões esperando gestor.', 'tone' => $totais['aprovacoes'] > 0 ? 'warning' : 'success'],
                ['label' => 'Sem responsável', 'value' => $totais['sem_responsavel'], 'hint' => 'Itens sem dono definido.', 'tone' => $totais['sem_responsavel'] > 0 ? 'danger' : 'success'],
                ['label' => 'Valor em aberto', 'value' => $this->money($totais['financeiro_valor']), 'hint' => 'Dinheiro parado no fluxo.', 'tone' => $totais['financeiro_valor'] > 0 ? 'warning' : 'success'],
                ['label' => 'Bloqueados', 'value' => $totais['bloqueados'], 'hint' => 'Gargalos que precisam de decisão.', 'tone' => $totais['bloqueados'] > 0 ? 'danger' : 'success'],
            ],
        };
    }

    protected function layoutPorPerfil(string $perfil): array
    {
        return match ($perfil) {
            'operacional' => [
                ['type' => 'tasks', 'key' => 'vencidos', 'title' => '🚨 Resolver agora', 'subtitle' => 'Itens atrasados que precisam sair da fila.', 'badge' => 'Prioridade máxima'],
                ['type' => 'tasks', 'key' => 'vencendo', 'title' => '📅 Próximos vencimentos', 'subtitle' => 'O que precisa ser antecipado nesta semana.', 'badge' => '7 dias'],
                ['type' => 'tasks', 'key' => 'aprovacoes', 'title' => '👉 Aprovar ou revisar', 'subtitle' => 'Itens parados aguardando decisão.', 'badge' => 'Ação'],
                ['type' => 'comments', 'key' => 'comentarios', 'title' => '💬 Comentários atribuídos', 'subtitle' => 'Menções e pedidos de resposta.', 'badge' => 'Responder'],
            ],
            'financeiro' => [
                ['type' => 'tasks', 'key' => 'financeiro', 'title' => '💰 Pendente de cobrança', 'subtitle' => 'Itens concluídos/aprovados ainda sem faturamento ou pagamento.', 'badge' => 'Cobrar'],
                ['type' => 'chart', 'key' => 'financeiro_por_empresa', 'title' => 'Valor em aberto por empresa', 'subtitle' => 'Onde existe mais dinheiro parado.', 'badge' => 'Financeiro'],
                ['type' => 'tasks', 'key' => 'aprovacoes', 'title' => 'Aprovações que liberam cobrança', 'subtitle' => 'Pendências que podem destravar faturamento.', 'badge' => 'Liberar'],
                ['type' => 'tasks', 'key' => 'bloqueados', 'title' => 'Bloqueios financeiros indiretos', 'subtitle' => 'Entregas travadas antes da etapa de cobrança.', 'badge' => 'Risco'],
            ],
            'cliente' => [
                ['type' => 'tasks', 'key' => 'cliente', 'title' => '👥 Pendências do cliente', 'subtitle' => 'Itens que mencionam cliente, portal, retorno ou documentos externos.', 'badge' => 'Relacionamento'],
                ['type' => 'tasks', 'key' => 'vencendo', 'title' => 'Entregas próximas por cliente', 'subtitle' => 'Prazos que precisam de comunicação preventiva.', 'badge' => 'Preventivo'],
                ['type' => 'chart', 'key' => 'cliente_por_status', 'title' => 'Status da carteira', 'subtitle' => 'Distribuição dos itens visíveis por situação.', 'badge' => 'Carteira'],
                ['type' => 'tasks', 'key' => 'sem_responsavel', 'title' => 'Clientes sem dono operacional', 'subtitle' => 'Itens sem responsável geram perda de acompanhamento.', 'badge' => 'Dono'],
            ],
            'auditoria' => [
                ['type' => 'audit', 'key' => 'auditoria', 'title' => '🛡️ Eventos recentes de auditoria', 'subtitle' => 'Últimas alterações reais registradas no sistema.', 'badge' => 'Trilha'],
                ['type' => 'chart', 'key' => 'auditoria_por_usuario', 'title' => 'Atividade por usuário', 'subtitle' => 'Usuários com mais eventos no período.', 'badge' => 'Usuários'],
                ['type' => 'tasks', 'key' => 'vencidos', 'title' => 'Riscos de compliance vencidos', 'subtitle' => 'Itens atrasados que exigem justificativa.', 'badge' => 'Risco'],
                ['type' => 'tasks', 'key' => 'sem_responsavel', 'title' => 'Itens sem responsável', 'subtitle' => 'Falhas de governança operacional.', 'badge' => 'Governança'],
            ],
            default => [
                ['type' => 'tasks', 'key' => 'vencidos', 'title' => '🚨 Riscos críticos', 'subtitle' => 'Itens vencidos que mais afetam a operação.', 'badge' => 'Gestão'],
                ['type' => 'tasks', 'key' => 'aprovacoes', 'title' => '👉 Aprovações pendentes', 'subtitle' => 'Decisões que dependem do gestor.', 'badge' => 'Decisão'],
                ['type' => 'chart', 'key' => 'carga', 'title' => 'Carga por responsável', 'subtitle' => 'Redistribua trabalho quando houver acúmulo.', 'badge' => 'Workload'],
                ['type' => 'chart', 'key' => 'status', 'title' => 'Gargalo por status', 'subtitle' => 'Onde o fluxo está travando.', 'badge' => 'Fluxo'],
                ['type' => 'tasks', 'key' => 'sem_responsavel', 'title' => 'Sem responsável', 'subtitle' => 'Itens sem dono operacional.', 'badge' => 'Governança'],
                ['type' => 'tasks', 'key' => 'bloqueados', 'title' => 'Bloqueados', 'subtitle' => 'Dependências que exigem intervenção.', 'badge' => 'Destravar'],
            ],
        };
    }

    public function configuravel(?User $user): array
    {
        $widgets = DashboardWidgetConfiguracao::query()
            ->visibleForUser($user)
            ->where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('id')
            ->limit(18)
            ->get();

        $configService = app(DashboardConfiguravelService::class);

        return [
            'title' => 'Dashboard Configurável',
            'subtitle' => 'Monte a tela por função: aprovação, cobrança, vencidos, bloqueios, carga por responsável e gargalos de status.',
            'widgets' => $widgets->map(function (DashboardWidgetConfiguracao $widget) use ($configService): array {
                $tipo = (string) $widget->tipo;

                return [
                    'id' => $widget->id,
                    'titulo' => $widget->titulo,
                    'tipo' => $tipo,
                    'fonte' => $this->labelFonte((string) $widget->fonte),
                    'largura' => $widget->largura,
                    'valor' => $tipo === 'card' ? $configService->valor($widget) : null,
                    'tabela' => $tipo === 'tabela' ? $configService->dadosTabela($widget)->toArray() : [],
                    'grafico' => $tipo === 'grafico' ? $configService->dadosGrafico($widget)->toArray() : [],
                    'edit_url' => $this->resourceUrl(DashboardWidgetConfiguracaoResource::class, 'edit', ['record' => $widget]),
                ];
            })->values()->toArray(),
            'actions' => [
                'create' => $this->resourceUrl(DashboardWidgetConfiguracaoResource::class, 'create'),
                'manage' => $this->resourceUrl(DashboardWidgetConfiguracaoResource::class, 'gerenciar')
                    ?: $this->resourceUrl(DashboardWidgetConfiguracaoResource::class, 'index'),
                'dashboards' => $this->resourceUrl(DashboardWidgetConfiguracaoResource::class, 'visualizar')
                    ?: $this->resourceUrl(DashboardWidgetConfiguracaoResource::class, 'index'),
            ],
            'fontes' => $this->fontesDisponiveis(),
        ];
    }

    protected function baseQuery(?User $user): Builder
    {
        return ItemControle::query()
            ->visibleForUser($user)
            ->select($this->safeSelect());
    }

    protected function safeSelect(): array
    {
        $columns = [
            'id',
            'titulo',
            'descricao',
            'tipo',
            'status',
            'prioridade',
            'data_vencimento',
            'data_conclusao',
            'empresa_id',
            'responsavel_id',
            'contrato_valor',
            'contrato_status',
            'created_at',
            'updated_at',
        ];

        foreach ([
                     'urgencia',
                     'valor_tarefa',
                     'bloqueado',
                     'faturado_em',
                     'pago_em',
                     'status_operacional_at',
                     'estimated_minutes',
                     'actual_minutes',
                     'blocked_by_dependency',
                     'bloqueado_por_dependencia',
                 ] as $column) {
            if ($this->hasColumn($column)) {
                $columns[] = $column;
            }
        }

        return array_values(array_unique($columns));
    }

    protected function vencendoProximos(?User $user): array
    {
        $query = $this->baseQuery($user)
            ->whereNotIn('status', self::STATUS_FINALIZADOS)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '>=', now()->toDateString())
            ->whereDate('data_vencimento', '<=', now()->addDays(7)->toDateString());

        $total = (clone $query)->count();

        $items = $query
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderBy('data_vencimento')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->itemPayload($item, 'warning'))
            ->values()
            ->toArray();

        return ['total' => $total, 'items' => $items];
    }

    protected function semResponsavel(?User $user): array
    {
        $query = $this->baseQuery($user)
            ->whereNull('responsavel_id')
            ->whereNotIn('status', self::STATUS_FINALIZADOS);

        $total = (clone $query)->count();

        $items = $query
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderByRaw('CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('data_vencimento')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->itemPayload($item, 'danger'))
            ->values()
            ->toArray();

        return ['total' => $total, 'items' => $items];
    }

    protected function clienteResumo(?User $user): array
    {
        $query = $this->baseQuery($user)
            ->whereNotIn('status', self::STATUS_FINALIZADOS)
            ->where(function (Builder $builder): void {
                $builder->where('tipo', 'like', '%cliente%')
                    ->orWhere('titulo', 'like', '%cliente%')
                    ->orWhere('descricao', 'like', '%cliente%')
                    ->orWhere('titulo', 'like', '%portal%')
                    ->orWhere('descricao', 'like', '%portal%')
                    ->orWhere('titulo', 'like', '%retorno%')
                    ->orWhere('descricao', 'like', '%retorno%')
                    ->orWhere('titulo', 'like', '%documento%')
                    ->orWhere('descricao', 'like', '%documento%');

                if ($this->hasColumn('portal_ativo')) {
                    $builder->orWhere('portal_ativo', true);
                }
            });

        $total = (clone $query)->count();

        $items = $query
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderByRaw('CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('data_vencimento')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->itemPayload($item, 'info'))
            ->values()
            ->toArray();

        return [
            'pendencias' => $total,
            'items' => $items,
            'por_status' => $this->statusDistribuicao($user),
        ];
    }

    protected function auditoriaResumo(?User $user): array
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            return [
                'eventos_30_dias' => 0,
                'acoes_sensiveis' => 0,
                'items' => [],
                'por_usuario' => [],
            ];
        }

        $base = DB::table('auditoria_detalhada')
            ->where('created_at', '>=', now()->subDays(30));

        if ($user && ! $user->isSuperAdmin()) {
            $base->where('empresa_id', $user->empresa_id ?: 0);
        }

        $eventos = (clone $base)->count();

        $acoesSensiveis = (clone $base)
            ->where(function ($builder): void {
                $builder->where('evento', 'like', '%delete%')
                    ->orWhere('evento', 'like', '%excl%')
                    ->orWhere('evento', 'like', '%reprov%')
                    ->orWhere('campo', 'like', '%status%')
                    ->orWhere('campo', 'like', '%valor%')
                    ->orWhere('campo', 'like', '%responsavel%');
            })
            ->count();

        $userIds = (clone $base)->whereNotNull('user_id')->pluck('user_id')->unique()->values();
        $users = User::query()->whereIn('id', $userIds)->pluck('name', 'id');

        $items = (clone $base)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($row): array => [
                'id' => $row->id,
                'title' => $this->auditTitle($row),
                'description' => $this->auditDescription($row),
                'user' => $users[$row->user_id] ?? 'Sistema',
                'when' => Carbon::parse($row->created_at)->diffForHumans(),
                'tone' => $this->auditTone((string) ($row->evento ?? ''), (string) ($row->campo ?? '')),
            ])
            ->values()
            ->toArray();

        $porUsuarioRows = (clone $base)
            ->select(['user_id', DB::raw('COUNT(id) as total')])
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $max = max(1, (int) $porUsuarioRows->max('total'));
        $porUsuario = $porUsuarioRows
            ->map(fn ($row): array => [
                'label' => $users[$row->user_id] ?? 'Sistema',
                'value' => (int) $row->total,
                'percent' => (int) round((((int) $row->total) / $max) * 100),
            ])
            ->values()
            ->toArray();

        return [
            'eventos_30_dias' => $eventos,
            'acoes_sensiveis' => $acoesSensiveis,
            'items' => $items,
            'por_usuario' => $porUsuario,
        ];
    }

    protected function financeiro(?User $user): array
    {
        $valorColumn = $this->hasColumn('valor_tarefa') ? 'valor_tarefa' : 'contrato_valor';

        $query = $this->baseQuery($user)
            ->whereIn('status', self::STATUS_CONCLUIDOS_FINANCEIRO);

        if ($this->hasColumn('faturado_em')) {
            $query->whereNull('faturado_em');
        } elseif ($this->hasColumn('contrato_status')) {
            $query->where(function (Builder $builder): void {
                $builder->whereNull('contrato_status')
                    ->orWhereNotIn('contrato_status', ['faturado', 'pago']);
            });
        }

        if ($this->hasColumn('pago_em')) {
            $query->whereNull('pago_em');
        }

        $totalValor = (clone $query)->sum($valorColumn);

        $items = $query
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderByDesc('data_conclusao')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->itemPayload($item, 'success'))
            ->values()
            ->toArray();

        return [
            'total_valor' => (float) $totalValor,
            'items' => $items,
            'por_empresa' => $this->financeiroPorEmpresa($user, $valorColumn),
        ];
    }

    protected function financeiroPorEmpresa(?User $user, string $valorColumn): array
    {
        $query = DB::table('item_controles')
            ->select([
                'empresa_id',
                DB::raw('SUM(COALESCE(' . $valorColumn . ', 0)) as total_valor'),
                DB::raw('COUNT(id) as total_itens'),
            ])
            ->whereIn('status', self::STATUS_CONCLUIDOS_FINANCEIRO)
            ->groupBy('empresa_id')
            ->orderByDesc('total_valor')
            ->limit(8);

        if ($this->hasColumn('faturado_em')) {
            $query->whereNull('faturado_em');
        } elseif ($this->hasColumn('contrato_status')) {
            $query->where(function ($builder): void {
                $builder->whereNull('contrato_status')
                    ->orWhereNotIn('contrato_status', ['faturado', 'pago']);
            });
        }

        if ($this->hasColumn('pago_em')) {
            $query->whereNull('pago_em');
        }

        $this->applyUserVisibilityToTable($query, $user);

        $rows = $query->get();
        $empresas = DB::table('empresas')
            ->whereIn('id', $rows->pluck('empresa_id')->filter()->values())
            ->pluck('razao_social', 'id');
        $max = max(1, (float) $rows->max('total_valor'));

        return $rows->map(fn ($row): array => [
            'label' => $empresas[$row->empresa_id] ?? 'Sem empresa',
            'value' => $this->money((float) $row->total_valor),
            'raw' => (float) $row->total_valor,
            'hint' => ((int) $row->total_itens) . ' item(ns)',
            'percent' => (int) round((((float) $row->total_valor) / $max) * 100),
        ])->values()->toArray();
    }

    protected function bloqueados(?User $user): array
    {
        $columns = $this->blockColumns();

        if (empty($columns)) {
            return ['total' => 0, 'items' => []];
        }

        $query = $this->baseQuery($user)
            ->where(function (Builder $builder) use ($columns): void {
                foreach ($columns as $column) {
                    $builder->orWhere($column, true);
                }
            });

        $total = (clone $query)->count();

        $items = $query
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->itemPayload($item, 'warning'))
            ->values()
            ->toArray();

        return ['total' => $total, 'items' => $items];
    }

    protected function cargaPorResponsavel(?User $user): array
    {
        $query = DB::table('item_controles')
            ->select([
                'responsavel_id',
                DB::raw('COUNT(id) as total'),
            ])
            ->whereNotNull('responsavel_id')
            ->whereNotIn('status', self::STATUS_FINALIZADOS)
            ->groupBy('responsavel_id')
            ->orderByDesc('total')
            ->limit(8);

        $this->applyUserVisibilityToTable($query, $user);

        $rows = $query->get();

        $names = Responsavel::query()
            ->whereIn('id', $rows->pluck('responsavel_id')->filter()->values())
            ->pluck('nome', 'id');

        $max = max(1, (int) $rows->max('total'));

        return $rows->map(fn ($row): array => [
            'label' => $names[$row->responsavel_id] ?? 'Sem responsável',
            'value' => (int) $row->total,
            'percent' => (int) round((((int) $row->total) / $max) * 100),
        ])->values()->toArray();
    }

    protected function statusDistribuicao(?User $user): array
    {
        $query = DB::table('item_controles')
            ->select([
                DB::raw("COALESCE(status, 'sem_status') as status_label"),
                DB::raw('COUNT(id) as total'),
            ])
            ->groupBy(DB::raw("COALESCE(status, 'sem_status')"))
            ->orderByDesc('total')
            ->limit(8);

        $this->applyUserVisibilityToTable($query, $user);

        $rows = $query->get();
        $total = max(1, (int) $rows->sum('total'));

        return $rows->map(fn ($row): array => [
            'label' => $this->statusLabel((string) $row->status_label),
            'value' => (int) $row->total,
            'percent' => (int) round((((int) $row->total) / $total) * 100),
            'tone' => $this->toneForStatus((string) $row->status_label),
        ])->values()->toArray();
    }

    protected function timeTracking(?User $user): array
    {
        if (! $this->hasColumn('estimated_minutes') || ! $this->hasColumn('actual_minutes')) {
            return [];
        }

        $query = DB::table('item_controles')
            ->select([
                'responsavel_id',
                DB::raw('SUM(COALESCE(estimated_minutes, 0)) as estimado'),
                DB::raw('SUM(COALESCE(actual_minutes, 0)) as tempo_real'),
            ])
            ->whereNotNull('responsavel_id')
            ->groupBy('responsavel_id')
            ->orderByDesc('tempo_real')
            ->limit(8);

        $this->applyUserVisibilityToTable($query, $user);

        $rows = $query->get();

        $names = Responsavel::query()
            ->whereIn('id', $rows->pluck('responsavel_id')->filter()->values())
            ->pluck('nome', 'id');

        return $rows->map(fn ($row): array => [
            'label' => $names[$row->responsavel_id] ?? 'Sem responsável',
            'estimado' => round(((int) $row->estimado) / 60, 1),
            'real' => round(((int) $row->tempo_real) / 60, 1),
        ])->values()->toArray();
    }

    protected function milestones(?User $user): array
    {
        return $this->baseQuery($user)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '>=', now()->toDateString())
            ->whereDate('data_vencimento', '<=', now()->addDays(45)->toDateString())
            ->where(function (Builder $builder): void {
                $builder->whereIn('tipo', ['milestone', 'marco', 'entrega'])
                    ->orWhere('titulo', 'like', '%milestone%')
                    ->orWhere('titulo', 'like', '%marco%')
                    ->orWhere('titulo', 'like', '%entrega%');
            })
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderBy('data_vencimento')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->itemPayload($item, 'info'))
            ->values()
            ->toArray();
    }

    protected function comentariosAtribuidos(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $tokens = array_values(array_filter(array_unique([
            '@' . trim((string) $user->name),
            '@' . trim((string) $user->email),
            $user->responsavel?->nome ? '@' . trim((string) $user->responsavel->nome) : null,
        ])));

        if (empty($tokens)) {
            return [];
        }

        $items = collect();

        if (CachedSchema::hasTable('item_controle_comentarios')) {
            $items = $items->merge($this->comentarioQuery(ItemControleComentario::query(), $user, $tokens)->get());
        }

        if (CachedSchema::hasTable('comentarios')) {
            $items = $items->merge($this->comentarioQuery(Comentario::query(), $user, $tokens)->get());
        }

        return $items
            ->sortByDesc('created_at')
            ->take(8)
            ->map(function ($comentario): array {
                $item = $comentario->itemControle;

                return [
                    'id' => $comentario->id,
                    'title' => $item?->titulo ?: 'Comentário atribuído',
                    'description' => str((string) $comentario->comentario)->limit(140)->toString(),
                    'author' => $comentario->user?->name ?: 'Usuário',
                    'created_at' => $comentario->created_at?->diffForHumans() ?: '-',
                    'url' => $item ? $this->resourceUrl(ItemControleResource::class, 'edit', ['record' => $item]) : null,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function comentarioQuery(Builder $query, ?User $user, array $tokens): Builder
    {
        return $query
            ->with(['user:id,name,email', 'itemControle' => fn ($builder) => $builder->select($this->safeSelect())])
            ->whereHas('itemControle', fn (Builder $builder): Builder => $builder->visibleForUser($user))
            ->where(function (Builder $builder) use ($tokens): void {
                foreach ($tokens as $token) {
                    $builder->orWhere('comentario', 'like', '%' . $token . '%');
                }
            })
            ->latest('created_at')
            ->limit(8);
    }

    protected function itemPayload(ItemControle $item, string $tone): array
    {
        $value = $this->moneyValue($item);

        return [
            'id' => $item->id,
            'title' => $item->titulo ?: 'Sem título',
            'description' => filled($item->descricao) ? str($item->descricao)->limit(130)->toString() : 'Sem descrição cadastrada.',
            'status' => $this->statusLabel((string) $item->status),
            'status_raw' => (string) $item->status,
            'tone' => $this->itemTone($item, $tone),
            'urgency' => $this->urgencyLabel($item),
            'responsavel' => $item->responsavel?->nome ?: 'Sem responsável',
            'empresa' => $item->empresa?->razao_social ?: 'Sem empresa',
            'due' => $item->data_vencimento?->format('d/m/Y'),
            'stopped_for' => $this->stoppedFor($item),
            'value' => $value > 0 ? $this->money($value) : null,
            'blocked' => $this->isBlocked($item),
            'url' => $this->resourceUrl(ItemControleResource::class, 'edit', ['record' => $item]),
        ];
    }

    protected function applyUserVisibilityToTable($query, ?User $user): void
    {
        if (! $user) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        if (! $user->empresa_id) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($user->isAdminEmpresa() || $user->isGestor()) {
            $query->where('empresa_id', $user->empresa_id);
            return;
        }

        if ($user->isUser()) {
            $query->where('responsavel_id', $user->responsavel?->id ?: 0);
        }
    }

    protected function blockColumns(): array
    {
        return array_values(array_filter([
            'bloqueado',
            'blocked_by_dependency',
            'bloqueado_por_dependencia',
        ], fn (string $column): bool => $this->hasColumn($column)));
    }

    protected function isBlocked(ItemControle $item): bool
    {
        foreach ($this->blockColumns() as $column) {
            if ((bool) ($item->{$column} ?? false)) {
                return true;
            }
        }

        return false;
    }

    protected function moneyValue(ItemControle $item): float
    {
        if ($this->hasColumn('valor_tarefa') && filled($item->valor_tarefa)) {
            return (float) $item->valor_tarefa;
        }

        return (float) ($item->contrato_valor ?? 0);
    }

    protected function stoppedFor(ItemControle $item): string
    {
        $date = $this->hasColumn('status_operacional_at') && $item->status_operacional_at
            ? Carbon::parse($item->status_operacional_at)
            : $item->updated_at;

        return $date ? $date->diffForHumans(now(), ['parts' => 2, 'short' => true]) : '-';
    }

    protected function urgencyLabel(ItemControle $item): string
    {
        $value = $this->hasColumn('urgencia') && filled($item->urgencia)
            ? $item->urgencia
            : $item->prioridade;

        return match ((string) $value) {
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'critica', 'urgente' => 'Crítica',
            default => 'Média',
        };
    }

    protected function itemTone(ItemControle $item, string $default): string
    {
        if ($this->isBlocked($item)) {
            return 'warning';
        }

        if ($item->data_vencimento && $item->data_vencimento->isPast() && ! in_array((string) $item->status, self::STATUS_FINALIZADOS, true)) {
            return 'danger';
        }

        return $this->toneForStatus((string) $item->status) ?: $default;
    }

    protected function toneForStatus(string $status): string
    {
        return match ($status) {
            'concluido', 'aprovado', 'assinado' => 'success',
            'correcao_necessaria', 'reprovado', 'vencido' => 'danger',
            'aguardando_aprovacao', 'em_aprovacao', 'em_revisao', 'pronto' => 'warning',
            default => 'info',
        };
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'pendente' => 'Pendente',
            'pronto' => 'Pronto',
            'em_revisao' => 'Em Revisão',
            'aguardando_aprovacao', 'em_aprovacao' => 'Aguardando Aprovação',
            'correcao_necessaria', 'reprovado' => 'Correção Necessária',
            'em_andamento' => 'Em andamento',
            'aprovado' => 'Aprovado',
            'assinado' => 'Assinado',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'vencido' => 'Vencido',
            'sem_status' => 'Sem status',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    protected function labelFonte(string $fonte): string
    {
        return $this->fontesDisponiveis()[$fonte] ?? ucfirst(str_replace('_', ' ', $fonte));
    }

    public function fontesDisponiveis(): array
    {
        return [
            'itens_abertos' => 'Itens abertos',
            'itens_vencidos' => 'Itens vencidos',
            'vencendo_hoje' => 'Vencendo hoje',
            'aprovacoes_pendentes' => 'Aprovações pendentes',
            'comentarios_atribuidos' => 'Comentários atribuídos',
            'bloqueados' => 'Tarefas bloqueadas',
            'valor_em_aberto' => 'Valor em aberto',
            'pendente_cobranca' => 'Pendente de cobrança',
            'carga_responsavel' => 'Carga por responsável',
            'status_gargalo' => 'Gargalo por status',
            'sla_vencido' => 'SLA vencido',
            'contratos_ativos' => 'Contratos ativos',
            'total_itens' => 'Total de itens',
        ];
    }

    protected function auditTitle(object $row): string
    {
        $evento = filled($row->evento ?? null) ? $row->evento : 'Evento registrado';
        $campo = filled($row->campo ?? null) ? ' • ' . $row->campo : '';

        return str($evento . $campo)->replace('_', ' ')->title()->toString();
    }

    protected function auditDescription(object $row): string
    {
        $anterior = filled($row->valor_anterior ?? null) ? (string) $row->valor_anterior : 'vazio';
        $novo = filled($row->valor_novo ?? null) ? (string) $row->valor_novo : 'vazio';

        if (($row->campo ?? null) || ($row->valor_anterior ?? null) || ($row->valor_novo ?? null)) {
            return str('Antes: ' . $anterior . ' → Depois: ' . $novo)->limit(160)->toString();
        }

        return 'Registro de auditoria sem comparação de campo disponível.';
    }

    protected function auditTone(string $evento, string $campo): string
    {
        $texto = str($evento . ' ' . $campo)->lower()->toString();

        if (str_contains($texto, 'delete') || str_contains($texto, 'excl') || str_contains($texto, 'reprov') || str_contains($texto, 'valor')) {
            return 'danger';
        }

        if (str_contains($texto, 'status') || str_contains($texto, 'responsavel')) {
            return 'warning';
        }

        return 'info';
    }

    protected function money(float|int $value): string
    {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }

    protected function meMode(?User $user): bool
    {
        return $user?->isUser() === true;
    }

    protected function missingRecommendedColumns(): array
    {
        return array_values(array_filter([
            'urgencia',
            'valor_tarefa',
            'bloqueado',
            'faturado_em',
            'pago_em',
            'status_operacional_at',
            'estimated_minutes',
            'actual_minutes',
        ], fn (string $column): bool => ! $this->hasColumn($column)));
    }

    protected function hasColumn(string $column): bool
    {
        static $cache = [];

        return $cache[$column] ??= CachedSchema::hasColumn('item_controles', $column);
    }

    protected function resourceUrl(string $resource, string $page = 'index', array $parameters = []): ?string
    {
        try {
            return $resource::getUrl($page, $parameters);
        } catch (Throwable) {
            return null;
        }
    }

    protected function pageUrl(string $page): ?string
    {
        try {
            return $page::getUrl();
        } catch (Throwable) {
            return null;
        }
    }
}
