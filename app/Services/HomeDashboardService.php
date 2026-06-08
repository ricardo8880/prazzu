<?php

namespace App\Services;


use App\Support\CachedSchema;
use App\Filament\Pages\Calendario;
use App\Filament\Pages\CentralAprovacoes;
use App\Filament\Pages\Documentos;
use App\Filament\Pages\Financeiro;
use App\Filament\Pages\Kanban;
use App\Filament\Pages\Pendencias;
use App\Filament\Pages\PortalCliente;
use App\Filament\Pages\SlaPrazos;
use App\Filament\Resources\Empresas\EmpresaResource;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use App\Models\ItemControleAprovacao;
use App\Models\ItemControleComentario;
use App\Models\NotificacaoInterna;
use App\Models\Pagamento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class HomeDashboardService
{
    public function __construct(
        protected ?User $user = null
    ) {
    }

    public function data(): array
    {
        if (! $this->user) {
            return $this->emptyData();
        }

        $cacheKey = 'home-dashboard:v3:user:' . $this->user->id . ':empresa:' . ($this->user->empresa_id ?? 'global');

        return Cache::remember($cacheKey, now()->addSeconds(60), function (): array {
            return [
                'usuario' => $this->user->name ?? 'Usuário',
                'urls' => $this->safeValue(fn () => $this->urls(), []),
                'kpis' => $this->safeValue(fn () => $this->kpis(), []),
                'tarefas' => $this->safeValue(fn () => $this->tarefas(), ['tabs' => [], 'itens' => []]),
                'kanban' => $this->safeValue(fn () => $this->kanban(), []),
                'prazos' => $this->safeValue(fn () => $this->prazos(), []),
                'sla' => $this->safeValue(fn () => $this->sla(), ['total' => 0, 'noPrazo' => 0, 'atencao' => 0, 'vencidos' => 0, 'percentuais' => ['noPrazo' => 0, 'atencao' => 0, 'vencidos' => 0]]),
                'documentos' => $this->safeValue(fn () => $this->documentos(), []),
                'financeiro' => $this->safeValue(fn () => $this->financeiro(), ['total' => 0, 'recebido' => 0, 'aReceber' => 0, 'series' => []]),
                'portal' => $this->safeValue(fn () => $this->portal(), []),
                'compliance' => $this->safeValue(fn () => $this->compliance(), []),
                'assistente' => $this->safeValue(fn () => $this->assistente(), []),
                'atividades' => $this->safeValue(fn () => $this->atividades(), []),
                'resumoHoje' => $this->safeValue(fn () => $this->resumoHoje(), []),
                'minhasPendencias' => $this->safeValue(fn () => $this->minhasPendencias(), []),
                'vencimentosProximos' => $this->safeValue(fn () => $this->vencimentosProximos(), []),
                'aprovacoesAguardando' => $this->safeValue(fn () => $this->aprovacoesAguardando(), []),
                'itensAtrasados' => $this->safeValue(fn () => $this->itensAtrasados(), []),
                'ultimosComentarios' => $this->safeValue(fn () => $this->ultimosComentarios(), []),
                'atalhosRapidos' => $this->safeValue(fn () => $this->atalhosRapidos(), []),
                'resumoEmpresas' => $this->safeValue(fn () => $this->resumoEmpresas(), []),
                'fluxoOperacional' => $this->safeValue(fn () => $this->fluxoOperacional(), []),
                'notificacoes' => $this->safeValue(fn () => $this->notificacoes(), []),
                'notificacoes_total' => $this->safeValue(fn () => $this->notificacoesTotal(), 0),
                'documentosVencidos' => $this->safeValue(fn () => $this->documentosVencidos(), []),
                'documentosVencendo' => $this->safeValue(fn () => $this->documentosVencendo(), []),
                'problemasAcao' => $this->safeValue(fn () => $this->problemasAcao(), []),
            ];
        });
    }

    private function safeValue(callable $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return $fallback;
        }
    }

    private function emptyData(): array
    {
        return [
            'usuario' => 'Usuário',
            'urls' => $this->safeValue(fn () => $this->urls(), []),
            'kpis' => [],
            'tarefas' => ['tabs' => [], 'itens' => []],
            'kanban' => [],
            'prazos' => [],
            'sla' => [
                'total' => 0,
                'noPrazo' => 0,
                'atencao' => 0,
                'vencidos' => 0,
                'percentuais' => [
                    'noPrazo' => 0,
                    'atencao' => 0,
                    'vencidos' => 0,
                ],
            ],
            'documentos' => [],
            'financeiro' => [
                'total' => 0,
                'recebido' => 0,
                'aReceber' => 0,
                'series' => [3, 4, 5, 4, 6, 7, 6, 8],
            ],
            'portal' => [],
            'compliance' => [],
            'assistente' => $this->safeValue(fn () => $this->assistente(), []),
            'atividades' => [],
            'resumoHoje' => [],
            'minhasPendencias' => [],
            'vencimentosProximos' => [],
            'aprovacoesAguardando' => [],
            'itensAtrasados' => [],
            'ultimosComentarios' => [],
            'atalhosRapidos' => $this->safeValue(fn () => $this->atalhosRapidos(), []),
            'resumoEmpresas' => [],
            'fluxoOperacional' => [],
            'notificacoes' => [],
            'notificacoes_total' => 0,
            'documentosVencidos' => [],
            'documentosVencendo' => [],
            'problemasAcao' => [],
        ];
    }

    private function baseItems(): Builder
    {
        return ItemControle::query()
            ->visibleForUser($this->user);
    }

    private function baseItemsWithEmpresa(): Builder
    {
        return $this->baseItems()
            ->with([
                'empresa:id,razao_social,nome_fantasia',
                'responsavel:id,nome,user_id,gestor_user_id,empresa_id',
            ]);
    }

    private function kpis(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $hoje = now()->toDateString();

        $tarefasEmAndamento = (clone $this->baseItems())
            ->whereIn('status', ['em_andamento', 'andamento'])
            ->count();

        $prazosVencendo = (clone $this->baseItems())
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '>=', $hoje)
            ->whereDate('data_vencimento', '<=', now()->addDays(7)->toDateString())
            ->whereNotIn('status', $this->statusFinalizados())
            ->count();

        $documentosVencidos = (clone $this->baseItems())
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', $hoje)
            ->whereNotIn('status', $this->statusFinalizados())
            ->where(function (Builder $query): void {
                $query->whereIn('tipo', ['documento', 'contrato', 'licenca', 'licença', 'alvara', 'alvará'])
                    ->orWhereNotNull('arquivo');
            })
            ->count();

        $financeiro = $this->financeiro(false);

        $riscoScore = (clone $this->baseItems())
            ->whereNotIn('status', $this->statusFinalizados())
            ->selectRaw('AVG(COALESCE(risk_score, risco_score, 0)) as media')
            ->value('media');

        $risco = $this->riscoOperacional((float) $riscoScore, $documentosVencidos, $prazosVencendo);

        return [
            [
                'label' => 'Tarefas em andamento',
                'value' => $tarefasEmAndamento,
                'trend' => '+18% esta semana',
                'icon' => '✓',
                'tone' => 'purple',
                'spark' => [4, 5, 6, 4, 7, 8, 6, 9],
            ],
            [
                'label' => 'Prazos vencendo',
                'value' => $prazosVencendo,
                'trend' => '+4 desde ontem',
                'icon' => '◷',
                'tone' => 'orange',
                'spark' => [3, 4, 3, 5, 6, 4, 7, 5],
            ],
            [
                'label' => 'Documentos vencidos',
                'value' => $documentosVencidos,
                'trend' => '-2 desde ontem',
                'icon' => '▣',
                'tone' => 'red',
                'spark' => [8, 7, 7, 6, 6, 5, 4, 4],
            ],
            [
                'label' => 'Faturamento do mês',
                'value' => 'R$ ' . number_format($financeiro['total'] ?? 0, 2, ',', '.'),
                'trend' => '+32% vs mês anterior',
                'icon' => '$',
                'tone' => 'green',
                'spark' => [3, 5, 4, 7, 6, 8, 7, 9],
            ],
            [
                'label' => 'Risco operacional',
                'value' => $risco['label'],
                'trend' => $risco['hint'],
                'icon' => '◇',
                'tone' => $risco['tone'],
                'spark' => [],
            ],
        ];
    }

    private function tarefas(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return ['tabs' => [], 'itens' => []];
        }

        $pendentes = (clone $this->baseItems())
            ->whereIn('status', ['pendente', 'pronto', 'em_revisao', 'aguardando_aprovacao', 'em_aprovacao', 'correcao_necessaria'])
            ->count();

        $emAndamento = (clone $this->baseItems())
            ->whereIn('status', ['em_andamento', 'andamento'])
            ->count();

        $concluidas = (clone $this->baseItems())
            ->whereIn('status', ['concluido', 'concluida', 'finalizado', 'aprovado', 'assinado'])
            ->count();

        $itens = (clone $this->baseItemsWithEmpresa())
            ->whereNotIn('status', $this->statusFinalizados())
            ->orderByRaw('CASE prioridade WHEN "urgente" THEN 1 WHEN "critica" THEN 2 WHEN "alta" THEN 3 WHEN "media" THEN 4 WHEN "baixa" THEN 5 ELSE 6 END')
            ->orderByRaw('data_vencimento IS NULL, data_vencimento ASC')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarTarefa($item))
            ->all();

        return [
            'tabs' => [
                'pendentes' => $pendentes,
                'em_andamento' => $emAndamento,
                'concluidas' => $concluidas,
            ],
            'itens' => $itens,
        ];
    }

    private function kanban(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $colunas = [
            [
                'key' => 'pendente',
                'label' => 'A Fazer',
                'query' => fn (Builder $query): Builder => $query->whereIn('status', ['pendente', 'pronto', 'em_revisao', 'aguardando_aprovacao', 'em_aprovacao', 'correcao_necessaria']),
            ],
            [
                'key' => 'em_andamento',
                'label' => 'Em andamento',
                'query' => fn (Builder $query): Builder => $query->whereIn('status', ['em_andamento', 'andamento']),
            ],
            [
                'key' => 'concluido',
                'label' => 'Concluídas',
                'query' => fn (Builder $query): Builder => $query->whereIn('status', ['concluido', 'concluida', 'finalizado', 'aprovado', 'assinado']),
            ],
        ];

        return collect($colunas)->map(function (array $coluna): array {
            $query = $coluna['query']((clone $this->baseItemsWithEmpresa()));

            $total = (clone $query)->count();

            $itens = $query
                ->orderByRaw('data_vencimento IS NULL, data_vencimento ASC')
                ->latest('updated_at')
                ->limit(3)
                ->get()
                ->map(fn (ItemControle $item): array => $this->formatarKanbanItem($item))
                ->all();

            return [
                'key' => $coluna['key'],
                'label' => $coluna['label'],
                'total' => $total,
                'itens' => $itens,
            ];
        })->all();
    }

    private function prazos(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return (clone $this->baseItemsWithEmpresa())
            ->whereNotNull('data_vencimento')
            ->whereNotIn('status', $this->statusFinalizados())
            ->orderBy('data_vencimento')
            ->limit(5)
            ->get()
            ->map(function (ItemControle $item): array {
                return [
                    'titulo' => $item->titulo ?: 'Item sem título',
                    'empresa' => $this->empresaNome($item),
                    'data' => $item->data_vencimento?->format('d/m/Y') ?? '-',
                    'status' => $this->statusPrazo($item),
                    'url' => $this->itemUrl($item),
                ];
            })
            ->all();
    }

    private function sla(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [
                'total' => 0,
                'noPrazo' => 0,
                'atencao' => 0,
                'vencidos' => 0,
                'percentuais' => [
                    'noPrazo' => 0,
                    'atencao' => 0,
                    'vencidos' => 0,
                ],
            ];
        }

        $base = (clone $this->baseItems())
            ->whereNotIn('status', $this->statusFinalizados());

        $total = (clone $base)->count();

        $vencidos = (clone $base)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->count();

        $atencao = (clone $base)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '>=', now()->toDateString())
            ->whereDate('data_vencimento', '<=', now()->addDays(3)->toDateString())
            ->count();

        $noPrazo = max(0, $total - $vencidos - $atencao);

        return [
            'total' => $total,
            'noPrazo' => $noPrazo,
            'atencao' => $atencao,
            'vencidos' => $vencidos,
            'percentuais' => [
                'noPrazo' => $this->percentual($noPrazo, $total),
                'atencao' => $this->percentual($atencao, $total),
                'vencidos' => $this->percentual($vencidos, $total),
            ],
        ];
    }

    private function documentos(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return (clone $this->baseItemsWithEmpresa())
            ->where(function (Builder $query): void {
                $query->whereIn('tipo', ['documento', 'contrato', 'licenca', 'licença', 'alvara', 'alvará', 'acordo'])
                    ->orWhereNotNull('arquivo');
            })
            ->latest('updated_at')
            ->limit(4)
            ->get()
            ->map(function (ItemControle $item): array {
                return [
                    'titulo' => $item->titulo ?: 'Documento sem título',
                    'meta' => strtoupper((string) ($item->tipo ?: 'PDF')) . ' • ' . ($item->updated_at?->format('d/m/Y') ?? '-'),
                    'status' => $this->statusDocumento($item),
                    'url' => $this->itemUrl($item),
                ];
            })
            ->all();
    }

    private function financeiro(bool $cached = true): array
    {
        if ($cached) {
            return Cache::remember(
                'home-dashboard-financeiro:user:' . $this->user?->id . ':empresa:' . ($this->user?->empresa_id ?? 'global'),
                now()->addSeconds(60),
                fn () => $this->financeiro(false)
            );
        }

        $inicioMes = now()->startOfMonth();
        $fimMes = now()->endOfMonth();

        $total = 0.0;
        $recebido = 0.0;
        $aReceber = 0.0;

        if (CachedSchema::hasTable('pagamentos')) {
            $query = Pagamento::query();

            if (! $this->user?->isSuperAdmin() && $this->user?->empresa_id) {
                $query->where('empresa_id', $this->user->empresa_id);
            }

            $mesQuery = (clone $query)
                ->whereBetween('vencimento', [$inicioMes->toDateString(), $fimMes->toDateString()]);

            $total = (float) (clone $mesQuery)->sum('valor');

            $recebidoQuery = (clone $mesQuery)
                ->where(function (Builder $query): void {
                    $query->whereIn('status', ['pago', 'paid', 'recebido', 'recebida', 'confirmed', 'confirmado', 'confirmada']);

                    if (CachedSchema::hasColumn('pagamentos', 'pago_em')) {
                        $query->orWhereNotNull('pago_em');
                    }
                });

            $recebido = (float) $recebidoQuery->sum('valor');

            $aReceber = max(0, $total - $recebido);
        }

        if ($total <= 0 && CachedSchema::hasTable('item_controles') && CachedSchema::hasColumn('item_controles', 'valor_tarefa')) {
            $base = (clone $this->baseItems())
                ->whereBetween('created_at', [$inicioMes, $fimMes]);

            $total = (float) (clone $base)->sum('valor_tarefa');

            $recebidoQuery = (clone $base);

            if (CachedSchema::hasColumn('item_controles', 'pago_em')) {
                $recebidoQuery->whereNotNull('pago_em');
            } else {
                $recebidoQuery->whereIn('status', ['pago', 'paid', 'recebido', 'recebida', 'confirmed', 'confirmado', 'confirmada', 'concluido', 'concluida']);
            }

            $recebido = (float) $recebidoQuery->sum('valor_tarefa');

            $aReceber = max(0, $total - $recebido);
        }

        return [
            'total' => $total,
            'recebido' => $recebido,
            'aReceber' => $aReceber,
            'series' => $this->serieFinanceira(),
        ];
    }

    private function portal(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $portalAtivo = CachedSchema::hasColumn('item_controles', 'portal_ativo')
            ? (clone $this->baseItems())->where('portal_ativo', true)->count()
            : 0;

        $documentos = (clone $this->baseItems())
            ->where(function (Builder $query): void {
                $query->whereIn('tipo', ['documento', 'contrato', 'licenca', 'licença', 'alvara', 'alvará'])
                    ->orWhereNotNull('arquivo');
            })
            ->latest('updated_at')
            ->limit(30)
            ->count();

        $aprovacoes = CachedSchema::hasColumn('item_controles', 'approval_status')
            ? (clone $this->baseItems())->whereIn('approval_status', ['pendente', 'aguardando', 'em_aprovacao'])->count()
            : (clone $this->baseItems())->whereIn('status', ['em_aprovacao', 'aguardando_aprovacao'])->count();

        $mensagens = CachedSchema::hasTable('prazzu_client_portal_messages')
            ? $this->countPortalMessages()
            : 0;

        $atividades = CachedSchema::hasTable('notificacoes_internas')
            ? $this->baseNotificacoes()->where('created_at', '>=', now()->subDays(5))->count()
            : 0;

        return [
            [
                'label' => 'Solicitações',
                'value' => $portalAtivo,
                'hint' => 'ativas',
                'url' => $this->urls()['clientes'] ?? '#',
            ],
            [
                'label' => 'Documentos',
                'value' => $documentos,
                'hint' => 'recentes',
                'url' => $this->urls()['documentos'] ?? '#',
            ],
            [
                'label' => 'Aprovações',
                'value' => $aprovacoes,
                'hint' => 'pendentes',
                'url' => $this->urls()['tarefas'] ?? '#',
            ],
            [
                'label' => 'Mensagens',
                'value' => $mensagens,
                'hint' => 'no portal',
                'url' => $this->urls()['clientes'] ?? '#',
            ],
            [
                'label' => 'Atividades',
                'value' => $atividades,
                'hint' => '5 dias',
                'url' => $this->urls()['kanban'] ?? '#',
            ],
        ];
    }

    private function compliance(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $pendencias = (clone $this->baseItems())
            ->whereNotIn('status', $this->statusFinalizados())
            ->count();

        $documentosVencidos = (clone $this->baseItems())
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->whereNotIn('status', $this->statusFinalizados())
            ->where(function (Builder $query): void {
                $query->whereIn('tipo', ['documento', 'contrato', 'licenca', 'licença', 'alvara', 'alvará'])
                    ->orWhereNotNull('arquivo');
            })
            ->count();

        $riscoScore = (clone $this->baseItems())
            ->whereNotIn('status', $this->statusFinalizados())
            ->selectRaw('AVG(COALESCE(risk_score, risco_score, 0)) as media')
            ->value('media');

        $risco = $this->riscoOperacional((float) $riscoScore, $documentosVencidos, $pendencias);

        $auditorias = CachedSchema::hasTable('auditoria_detalhada')
            ? $this->countAuditorias()
            : 0;

        return [
            [
                'label' => 'Pendências',
                'value' => $pendencias,
                'hint' => $pendencias > 0 ? 'Atenção' : 'OK',
            ],
            [
                'label' => 'Documentos vencidos',
                'value' => $documentosVencidos,
                'hint' => $documentosVencidos > 0 ? 'Crítico' : 'OK',
            ],
            [
                'label' => 'Risco operacional',
                'value' => $risco['label'],
                'hint' => $risco['hint'],
            ],
            [
                'label' => 'Auditorias',
                'value' => $auditorias,
                'hint' => '30 dias',
            ],
        ];
    }

    private function atividades(): array
    {
        if (CachedSchema::hasTable('notificacoes_internas')) {
            return $this->baseNotificacoes()
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function (NotificacaoInterna $notificacao): array {
                    return [
                        'usuario' => 'Sistema',
                        'titulo' => $notificacao->titulo ?: 'Atividade registrada',
                        'descricao' => Str::limit(strip_tags((string) $notificacao->mensagem), 90),
                        'quando' => $notificacao->created_at?->diffForHumans() ?? '-',
                    ];
                })
                ->all();
        }

        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return (clone $this->baseItemsWithEmpresa())
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(function (ItemControle $item): array {
                return [
                    'usuario' => $item->responsavel?->nome ?: 'Sistema',
                    'titulo' => $item->titulo ?: 'Item atualizado',
                    'descricao' => 'Status atual: ' . $item->getStatusExibicao(),
                    'quando' => $item->updated_at?->diffForHumans() ?? '-',
                ];
            })
            ->all();
    }

    private function assistente(): array
    {
        return [
            [
                'texto' => 'Quais documentos vencem essa semana?',
                'url' => $this->urls()['documentos'] ?? '#',
            ],
            [
                'texto' => 'Mostrar tarefas atrasadas',
                'url' => $this->urls()['prazos'] ?? '#',
            ],
            [
                'texto' => 'Resumo da operação do mês',
                'url' => $this->urls()['financeiro'] ?? '#',
            ],
            [
                'texto' => 'Analisar riscos da empresa atual',
                'url' => $this->urls()['prazos'] ?? '#',
            ],
        ];
    }

    private function urls(): array
    {
        return [
            'novaTarefa' => $this->safeUrl(fn () => ItemControleResource::getUrl('create')),
            'enviarDocumento' => $this->safeUrl(fn () => ItemControleResource::getUrl('create')),
            'novoCliente' => $this->safeUrl(fn () => EmpresaResource::getUrl('create')),
            'tarefas' => $this->safeUrl(fn () => ItemControleResource::getUrl('index')),
            'kanban' => $this->safeUrl(fn () => Kanban::getUrl()),
            'prazos' => $this->safeUrl(fn () => SlaPrazos::getUrl()),
            'documentos' => $this->safeUrl(fn () => Documentos::getUrl()),
            'financeiro' => $this->safeUrl(fn () => Financeiro::getUrl()),
            'clientes' => $this->safeUrl(fn () => PortalCliente::getUrl()),
            'calendario' => $this->safeUrl(fn () => Calendario::getUrl()),
            'centralAprovacoes' => $this->safeUrl(fn () => CentralAprovacoes::getUrl()),
            'centralNotificacoes' => $this->safeUrl(fn () => ItemControleResource::getUrl('central-notificacoes')),
            'minhasPendencias' => $this->safeUrl(fn () => Pendencias::getUrl()),
        ];
    }

    private function baseItemsWithRelations(): Builder
    {
        return $this->baseItems()->with([
            'empresa:id,razao_social,nome_fantasia',
            'responsavel:id,nome,user_id,gestor_user_id,empresa_id',
        ]);
    }

    private function resumoHoje(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $abertos = (clone $this->baseItems())
            ->whereNotIn('status', $this->statusFinalizados())
            ->count();

        $pendencias = (clone $this->baseItems())
            ->whereNotIn('status', $this->statusFinalizados())
            ->where(function (Builder $query): void {
                $query->whereIn('status', $this->statusPendencia())
                    ->orWhereIn('prioridade', ['alta', 'critica', 'crítica', 'urgente']);
            })
            ->count();

        $atrasados = $this->queryAtrasados()->count();
        $vencendo = $this->queryVencendo(7)->count();
        $aprovacoes = $this->countAprovacoesPendentes();
        $comentarios = $this->countComentariosRecentes();

        $financeiro = $this->financeiro(false);

        return [
            [
                'label' => 'Pendências',
                'value' => $pendencias,
                'hint' => $pendencias > 0 ? 'Itens que pedem ação' : 'Nada crítico agora',
                'tone' => $pendencias > 0 ? 'warning' : 'success',
                'url' => $this->urls()['minhasPendencias'] ?? '#',
            ],
            [
                'label' => 'Vencem em 7 dias',
                'value' => $vencendo,
                'hint' => $vencendo > 0 ? 'Antecipe antes de atrasar' : 'Sem vencimentos próximos',
                'tone' => $vencendo > 0 ? 'info' : 'success',
                'url' => $this->urls()['prazos'] ?? '#',
            ],
            [
                'label' => 'Atrasados',
                'value' => $atrasados,
                'hint' => $atrasados > 0 ? 'Prioridade máxima' : 'Nenhum item atrasado',
                'tone' => $atrasados > 0 ? 'danger' : 'success',
                'url' => $this->urls()['prazos'] ?? '#',
            ],
            [
                'label' => 'Aprovações',
                'value' => $aprovacoes,
                'hint' => $aprovacoes > 0 ? 'Aguardando decisão' : 'Sem aprovações pendentes',
                'tone' => $aprovacoes > 0 ? 'purple' : 'success',
                'url' => $this->urls()['centralAprovacoes'] ?? '#',
            ],
            [
                'label' => 'Comentários recentes',
                'value' => $comentarios,
                'hint' => 'Últimos 7 dias',
                'tone' => $comentarios > 0 ? 'slate' : 'success',
                'url' => $this->urls()['tarefas'] ?? '#',
            ],
            [
                'label' => 'A receber no mês',
                'value' => 'R$ ' . number_format($financeiro['aReceber'] ?? 0, 2, ',', '.'),
                'hint' => 'Financeiro operacional',
                'tone' => ($financeiro['aReceber'] ?? 0) > 0 ? 'green' : 'success',
                'url' => $this->urls()['financeiro'] ?? '#',
            ],
        ];
    }

    private function minhasPendencias(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return (clone $this->baseItemsWithRelations())
            ->whereNotIn('status', $this->statusFinalizados())
            ->where(function (Builder $query): void {
                $query->whereIn('status', $this->statusPendencia())
                    ->orWhereIn('prioridade', ['alta', 'critica', 'crítica', 'urgente'])
                    ->orWhereDate('data_vencimento', '<=', now()->addDays(3)->toDateString());
            })
            ->orderByRaw($this->prioridadeOrderSql())
            ->orderByRaw('CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('data_vencimento')
            ->latest('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarItemOperacional($item))
            ->all();
    }

    private function vencimentosProximos(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return $this->queryVencendo(15)
            ->with(['empresa:id,razao_social,nome_fantasia', 'responsavel:id,nome,user_id,gestor_user_id,empresa_id'])
            ->orderBy('data_vencimento')
            ->limit(7)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarItemOperacional($item))
            ->all();
    }

    private function aprovacoesAguardando(): array
    {
        if (CachedSchema::hasTable('item_controle_aprovacoes')) {
            try {
                $query = ItemControleAprovacao::query()
                    ->with([
                        'itemControle.empresa:id,razao_social,nome_fantasia',
                        'itemControle.responsavel:id,nome,user_id,gestor_user_id,empresa_id',
                        'solicitante:id,name',
                        'aprovador:id,name',
                    ])
                    ->where('status', 'pendente')
                    ->latest('solicitado_em')
                    ->latest('created_at');

                if (! $this->user?->isSuperAdmin() && $this->user?->empresa_id && CachedSchema::hasColumn('item_controle_aprovacoes', 'empresa_id')) {
                    $query->where('empresa_id', $this->user->empresa_id);
                }

                return $query
                    ->limit(6)
                    ->get()
                    ->filter(fn (ItemControleAprovacao $aprovacao): bool => (bool) $aprovacao->itemControle)
                    ->map(function (ItemControleAprovacao $aprovacao): array {
                        $item = $aprovacao->itemControle;

                        return [
                            'titulo' => $item->titulo ?: 'Aprovação sem título',
                            'empresa' => $this->empresaNome($item),
                            'responsavel' => $aprovacao->aprovador?->name ?: $item->responsavel?->nome ?: 'Sem aprovador',
                            'data' => $aprovacao->solicitado_em?->format('d/m/Y H:i') ?: $aprovacao->created_at?->format('d/m/Y H:i') ?: '-',
                            'tempo' => ($aprovacao->solicitado_em ?: $aprovacao->created_at)?->diffForHumans() ?: '-',
                            'status' => 'Aguardando aprovação',
                            'badge' => 'warning',
                            'url' => $this->itemUrl($item),
                        ];
                    })
                    ->values()
                    ->all();
            } catch (Throwable) {
                // fallback abaixo
            }
        }

        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return (clone $this->baseItemsWithRelations())
            ->whereIn('status', ['aguardando_aprovacao', 'em_aprovacao'])
            ->whereNotIn('status', $this->statusFinalizados())
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarItemOperacional($item, 'Aguardando aprovação'))
            ->all();
    }

    private function itensAtrasados(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return $this->queryAtrasados()
            ->with(['empresa:id,razao_social,nome_fantasia', 'responsavel:id,nome,user_id,gestor_user_id,empresa_id'])
            ->orderBy('data_vencimento')
            ->limit(7)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarItemOperacional($item))
            ->all();
    }

    private function ultimosComentarios(): array
    {
        if (CachedSchema::hasTable('item_controle_comentarios')) {
            try {
                $query = ItemControleComentario::query()
                    ->with([
                        'itemControle.empresa:id,razao_social,nome_fantasia',
                        'user:id,name',
                    ])
                    ->latest('created_at');

                if (! $this->user?->isSuperAdmin() && $this->user?->empresa_id && CachedSchema::hasTable('item_controles')) {
                    $query->whereHas('itemControle', function (Builder $query): void {
                        $query->visibleForUser($this->user);
                    });
                }

                return $query
                    ->limit(6)
                    ->get()
                    ->filter(fn (ItemControleComentario $comentario): bool => (bool) $comentario->itemControle)
                    ->map(function (ItemControleComentario $comentario): array {
                        return [
                            'usuario' => $comentario->user?->name ?: 'Usuário',
                            'titulo' => $comentario->itemControle?->titulo ?: 'Item sem título',
                            'empresa' => $comentario->itemControle ? $this->empresaNome($comentario->itemControle) : 'Sem empresa',
                            'comentario' => Str::limit(strip_tags((string) $comentario->comentario), 120),
                            'quando' => $comentario->created_at?->diffForHumans() ?: '-',
                            'url' => $comentario->itemControle ? $this->itemUrl($comentario->itemControle) : '#',
                        ];
                    })
                    ->values()
                    ->all();
            } catch (Throwable) {
                // fallback abaixo
            }
        }

        return $this->atividadesPorNotificacao();
    }

    private function atalhosRapidos(): array
    {
        $urls = $this->urls();

        return [
            ['label' => 'Nova tarefa', 'hint' => 'Criar item de controle', 'icon' => '＋', 'url' => $urls['novaTarefa'] ?? '#', 'tone' => 'primary'],
            ['label' => 'Pendências', 'hint' => 'Resolver prioridades', 'icon' => '✓', 'url' => $urls['minhasPendencias'] ?? '#', 'tone' => 'warning'],
            ['label' => 'Central de aprovações', 'hint' => 'Decisões pendentes', 'icon' => '☑', 'url' => $urls['centralAprovacoes'] ?? '#', 'tone' => 'purple'],
            ['label' => 'Enviar documento', 'hint' => 'Cadastrar/anexar', 'icon' => '↥', 'url' => $urls['enviarDocumento'] ?? '#', 'tone' => 'info'],
            ['label' => 'Calendário', 'hint' => 'Ver vencimentos', 'icon' => '◷', 'url' => $urls['calendario'] ?? '#', 'tone' => 'slate'],
            ['label' => 'Novo cliente', 'hint' => 'Cadastrar empresa', 'icon' => '♙', 'url' => $urls['novoCliente'] ?? '#', 'tone' => 'green'],
        ];
    }

    private function resumoEmpresas(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $itens = (clone $this->baseItemsWithRelations())
            ->whereNotIn('status', $this->statusFinalizados())
            ->latest('updated_at')
            ->limit(350)
            ->get();

        return $itens
            ->groupBy(fn (ItemControle $item): string => (string) ($item->empresa_id ?: 0))
            ->map(function ($grupo): array {
                /** @var ItemControle $primeiro */
                $primeiro = $grupo->first();
                $total = $grupo->count();
                $atrasados = $grupo->filter(fn (ItemControle $item): bool => $this->itemEstaAtrasado($item))->count();
                $vencendo = $grupo->filter(fn (ItemControle $item): bool => $this->itemVenceEm($item, 7))->count();
                $aprovacoes = $grupo->filter(fn (ItemControle $item): bool => in_array((string) $item->status, ['aguardando_aprovacao', 'em_aprovacao'], true))->count();
                $concluidos = $grupo->filter(fn (ItemControle $item): bool => in_array((string) $item->status, $this->statusFinalizados(), true))->count();

                return [
                    'empresa' => $this->empresaNome($primeiro),
                    'total' => $total,
                    'atrasados' => $atrasados,
                    'vencendo' => $vencendo,
                    'aprovacoes' => $aprovacoes,
                    'progresso' => $this->percentual($concluidos, max(1, $total + $concluidos)),
                    'risco' => $atrasados > 0 ? 'Crítico' : ($vencendo > 0 || $aprovacoes > 0 ? 'Atenção' : 'Saudável'),
                    'tone' => $atrasados > 0 ? 'danger' : ($vencendo > 0 || $aprovacoes > 0 ? 'warning' : 'success'),
                    'url' => $this->urls()['tarefas'] ?? '#',
                ];
            })
            ->sortByDesc(fn (array $item): int => ($item['atrasados'] * 10) + ($item['vencendo'] * 3) + $item['aprovacoes'])
            ->take(6)
            ->values()
            ->all();
    }

    private function fluxoOperacional(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $base = (clone $this->baseItems())->whereNotIn('status', $this->statusFinalizados());

        $etapas = [
            ['key' => 'pendente', 'label' => 'A fazer', 'statuses' => ['pendente', 'pronto'], 'tone' => 'slate'],
            ['key' => 'andamento', 'label' => 'Em andamento', 'statuses' => ['em_andamento', 'andamento', 'em_revisao', 'correcao_necessaria'], 'tone' => 'info'],
            ['key' => 'aprovacao', 'label' => 'Aprovação', 'statuses' => ['aguardando_aprovacao', 'em_aprovacao'], 'tone' => 'purple'],
            ['key' => 'atrasados', 'label' => 'Atrasados', 'statuses' => [], 'tone' => 'danger'],
        ];

        return collect($etapas)->map(function (array $etapa) use ($base): array {
            $query = clone $base;

            if ($etapa['key'] === 'atrasados') {
                $query->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString());
            } else {
                $query->whereIn('status', $etapa['statuses']);
            }

            return [
                'label' => $etapa['label'],
                'value' => $query->count(),
                'tone' => $etapa['tone'],
            ];
        })->all();
    }


    private function documentosVencidos(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return $this->queryDocumentos()
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->whereNotIn('status', $this->statusFinalizados())
            ->with(['empresa:id,razao_social,nome_fantasia', 'responsavel:id,nome,user_id,gestor_user_id,empresa_id'])
            ->orderBy('data_vencimento')
            ->limit(6)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarItemOperacional($item, 'Documento vencido'))
            ->all();
    }

    private function documentosVencendo(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return $this->queryDocumentos()
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '>=', now()->toDateString())
            ->whereDate('data_vencimento', '<=', now()->addDays(30)->toDateString())
            ->whereNotIn('status', $this->statusFinalizados())
            ->with(['empresa:id,razao_social,nome_fantasia', 'responsavel:id,nome,user_id,gestor_user_id,empresa_id'])
            ->orderBy('data_vencimento')
            ->limit(6)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarItemOperacional($item, 'Documento vencendo'))
            ->all();
    }

    private function problemasAcao(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        $problemas = [];
        $atrasados = $this->queryAtrasados()->count();
        $documentosVencidos = $this->queryDocumentos()
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->whereNotIn('status', $this->statusFinalizados())
            ->count();
        $aprovacoes = $this->countAprovacoesPendentes();
        $semResponsavel = (clone $this->baseItems())
            ->whereNotIn('status', $this->statusFinalizados())
            ->whereNull('responsavel_id')
            ->count();
        $altaPrioridade = (clone $this->baseItems())
            ->whereNotIn('status', $this->statusFinalizados())
            ->whereIn('prioridade', ['alta', 'critica', 'crítica', 'urgente'])
            ->count();

        if ($atrasados > 0) {
            $problemas[] = [
                'titulo' => 'Itens atrasados',
                'descricao' => $atrasados . ' item(ns) passaram do prazo e precisam de regularização.',
                'badge' => 'danger',
                'url' => $this->urls()['prazos'] ?? '#',
            ];
        }

        if ($documentosVencidos > 0) {
            $problemas[] = [
                'titulo' => 'Documentos vencidos',
                'descricao' => $documentosVencidos . ' documento(s) vencidos exigem atualização ou novo anexo.',
                'badge' => 'danger',
                'url' => $this->urls()['documentos'] ?? '#',
            ];
        }

        if ($aprovacoes > 0) {
            $problemas[] = [
                'titulo' => 'Aprovações paradas',
                'descricao' => $aprovacoes . ' aprovação(ões) aguardando decisão.',
                'badge' => 'warning',
                'url' => $this->urls()['centralAprovacoes'] ?? '#',
            ];
        }

        if ($semResponsavel > 0) {
            $problemas[] = [
                'titulo' => 'Itens sem responsável',
                'descricao' => $semResponsavel . ' item(ns) abertos estão sem dono operacional.',
                'badge' => 'warning',
                'url' => $this->urls()['tarefas'] ?? '#',
            ];
        }

        if ($altaPrioridade > 0) {
            $problemas[] = [
                'titulo' => 'Alta prioridade',
                'descricao' => $altaPrioridade . ' item(ns) marcados como alta prioridade.',
                'badge' => 'info',
                'url' => $this->urls()['minhasPendencias'] ?? '#',
            ];
        }

        return array_slice($problemas, 0, 5);
    }

    private function queryDocumentos(): Builder
    {
        return (clone $this->baseItems())
            ->where(function (Builder $query): void {
                $query->whereIn('tipo', ['documento', 'contrato', 'licenca', 'licença', 'alvara', 'alvará', 'acordo'])
                    ->orWhereNotNull('arquivo');
            });
    }

    private function notificacoes(): array
    {
        if (! CachedSchema::hasTable('notificacoes_internas')) {
            return [];
        }

        try {
            return $this->baseNotificacoes()
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function (NotificacaoInterna $notificacao): array {
                    return [
                        'titulo' => $notificacao->titulo ?: 'Notificação',
                        'mensagem' => Str::limit(strip_tags((string) $notificacao->mensagem), 110),
                        'quando' => $notificacao->created_at?->diffForHumans() ?: '-',
                    ];
                })
                ->all();
        } catch (Throwable) {
            return [];
        }
    }


    private function notificacoesTotal(): int
    {
        if (! CachedSchema::hasTable('notificacoes_internas')) {
            return 0;
        }

        try {
            $query = $this->baseNotificacoes();

            if (CachedSchema::hasColumn('notificacoes_internas', 'lida')) {
                $query->where(function (Builder $query): void {
                    $query->where('lida', false)
                        ->orWhereNull('lida');
                });
            }

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }


    private function queryVencendo(int $dias): Builder
    {
        return (clone $this->baseItems())
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '>=', now()->toDateString())
            ->whereDate('data_vencimento', '<=', now()->addDays($dias)->toDateString())
            ->whereNotIn('status', $this->statusFinalizados());
    }

    private function queryAtrasados(): Builder
    {
        return (clone $this->baseItems())
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->whereNotIn('status', $this->statusFinalizados());
    }


    private function atividadesPorNotificacao(): array
    {
        if (! CachedSchema::hasTable('notificacoes_internas')) {
            return [];
        }

        try {
            return $this->baseNotificacoes()
                ->latest('created_at')
                ->limit(6)
                ->get()
                ->map(fn (NotificacaoInterna $notificacao): array => [
                    'usuario' => 'Sistema',
                    'titulo' => $notificacao->titulo ?: 'Atividade registrada',
                    'empresa' => 'Operação',
                    'comentario' => Str::limit(strip_tags((string) $notificacao->mensagem), 120),
                    'quando' => $notificacao->created_at?->diffForHumans() ?: '-',
                    'url' => '#',
                ])
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    private function countAprovacoesPendentes(): int
    {
        try {
            if (CachedSchema::hasTable('item_controle_aprovacoes')) {
                $query = ItemControleAprovacao::query()->where('status', 'pendente');

                if (! $this->user?->isSuperAdmin() && $this->user?->empresa_id && CachedSchema::hasColumn('item_controle_aprovacoes', 'empresa_id')) {
                    $query->where('empresa_id', $this->user->empresa_id);
                }

                return (int) $query->count();
            }

            return (clone $this->baseItems())
                ->whereIn('status', ['aguardando_aprovacao', 'em_aprovacao'])
                ->whereNotIn('status', $this->statusFinalizados())
                ->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function countComentariosRecentes(): int
    {
        try {
            if (CachedSchema::hasTable('item_controle_comentarios')) {
                $query = ItemControleComentario::query()->where('created_at', '>=', now()->subDays(7));

                if (! $this->user?->isSuperAdmin() && $this->user?->empresa_id && CachedSchema::hasTable('item_controles')) {
                    $query->whereHas('itemControle', function (Builder $query): void {
                        $query->visibleForUser($this->user);
                    });
                }

                return (int) $query->count();
            }

            if (CachedSchema::hasTable('notificacoes_internas')) {
                return (int) $this->baseNotificacoes()->where('created_at', '>=', now()->subDays(7))->count();
            }
        } catch (Throwable) {
            return 0;
        }

        return 0;
    }

    private function formatarItemOperacional(ItemControle $item, ?string $statusForcado = null): array
    {
        $status = $statusForcado ?: $this->statusPrazoOuItem($item);
        $badge = $this->itemEstaAtrasado($item) ? 'danger' : ($this->itemVenceEm($item, 3) ? 'warning' : $this->prioridade($item->prioridade)['class']);

        return [
            'titulo' => $item->titulo ?: 'Item sem título',
            'empresa' => $this->empresaNome($item),
            'responsavel' => $item->responsavel?->nome ?: 'Sem responsável',
            'prioridade' => $this->prioridade($item->prioridade),
            'status' => $status,
            'badge' => $badge,
            'data' => $item->data_vencimento?->format('d/m/Y') ?: 'Sem vencimento',
            'tempo' => $item->data_vencimento ? $item->data_vencimento->diffForHumans() : ($item->updated_at?->diffForHumans() ?: '-'),
            'url' => $this->itemUrl($item),
        ];
    }


    private function statusPrazoOuItem(ItemControle $item): string
    {
        if ($this->itemEstaAtrasado($item)) {
            return 'Atrasado';
        }

        if ($item->data_vencimento?->isToday()) {
            return 'Vence hoje';
        }

        if ($this->itemVenceEm($item, 7)) {
            return 'Vencimento próximo';
        }

        if (method_exists($item, 'getStatusExibicao')) {
            return $item->getStatusExibicao();
        }

        return ucfirst((string) ($item->status ?: 'Pendente'));
    }

    private function itemEstaAtrasado(ItemControle $item): bool
    {
        return (bool) $item->data_vencimento
            && $item->data_vencimento->lt(now()->startOfDay())
            && ! in_array((string) $item->status, $this->statusFinalizados(), true);
    }

    private function itemVenceEm(ItemControle $item, int $dias): bool
    {
        return (bool) $item->data_vencimento
            && $item->data_vencimento->gte(now()->startOfDay())
            && $item->data_vencimento->lte(now()->addDays($dias)->endOfDay())
            && ! in_array((string) $item->status, $this->statusFinalizados(), true);
    }






    private function statusPendencia(): array
    {
        return [
            'pendente',
            'pronto',
            'em_revisao',
            'aguardando_aprovacao',
            'em_aprovacao',
            'correcao_necessaria',
            'vencido',
        ];
    }

    private function prioridadeOrderSql(): string
    {
        return "CASE prioridade WHEN 'urgente' THEN 1 WHEN 'critica' THEN 2 WHEN 'crítica' THEN 2 WHEN 'alta' THEN 3 WHEN 'media' THEN 4 WHEN 'média' THEN 4 WHEN 'baixa' THEN 5 ELSE 6 END";
    }


    private function baseNotificacoes(): Builder
    {
        $query = NotificacaoInterna::query();

        if (! $this->user?->isSuperAdmin() && $this->user?->empresa_id) {
            $query->where(function (Builder $query): void {
                $query->where('empresa_id', $this->user->empresa_id)
                    ->orWhere('user_id', $this->user->id);
            });
        }

        return $query;
    }

    private function formatarTarefa(ItemControle $item): array
    {
        return [
            'titulo' => $item->titulo ?: 'Tarefa sem título',
            'prioridade' => $this->prioridade($item->prioridade),
            'data' => $item->data_vencimento?->format('d/m/Y') ?? '-',
            'url' => $this->itemUrl($item),
        ];
    }

    private function formatarKanbanItem(ItemControle $item): array
    {
        return [
            'titulo' => $item->titulo ?: 'Item sem título',
            'empresa' => $this->empresaNome($item),
            'prioridade' => $this->prioridade($item->prioridade),
            'url' => $this->itemUrl($item),
        ];
    }

    private function prioridade(?string $prioridade): array
    {
        return match ($prioridade) {
            'urgente', 'critica', 'crítica' => [
                'label' => 'Alta',
                'class' => 'danger',
            ],
            'alta' => [
                'label' => 'Alta',
                'class' => 'danger',
            ],
            'baixa' => [
                'label' => 'Baixa',
                'class' => 'success',
            ],
            default => [
                'label' => 'Média',
                'class' => 'warning',
            ],
        };
    }

    private function statusDocumento(ItemControle $item): array
    {
        if ($item->data_vencimento && $item->data_vencimento->lt(now()->startOfDay())) {
            return [
                'label' => 'Vencido',
                'class' => 'danger',
            ];
        }

        if ($item->data_vencimento && $item->data_vencimento->lte(now()->addDays(7))) {
            return [
                'label' => 'A vencer',
                'class' => 'warning',
            ];
        }

        return [
            'label' => 'Válido',
            'class' => 'success',
        ];
    }

    private function statusPrazo(ItemControle $item): string
    {
        if (! $item->data_vencimento) {
            return 'Sem prazo';
        }

        if ($item->data_vencimento->lt(now()->startOfDay())) {
            return 'Vencido';
        }

        if ($item->data_vencimento->isToday()) {
            return 'Hoje';
        }

        $dias = now()->startOfDay()->diffInDays($item->data_vencimento->startOfDay(), false);

        if ($dias === 1) {
            return '1 dia';
        }

        return $dias . ' dias';
    }

    private function riscoOperacional(float $riscoScore, int $criticos, int $pendencias): array
    {
        if ($criticos >= 5 || $riscoScore >= 70) {
            return [
                'label' => 'ALTO',
                'hint' => 'Ação necessária',
                'tone' => 'amber',
            ];
        }

        if ($criticos >= 1 || $pendencias >= 10 || $riscoScore >= 40) {
            return [
                'label' => 'MÉDIO',
                'hint' => 'Acompanhar',
                'tone' => 'amber',
            ];
        }

        return [
            'label' => 'BAIXO',
            'hint' => 'Operação saudável',
            'tone' => 'green',
        ];
    }

    private function empresaNome(ItemControle $item): string
    {
        return $item->empresa?->nome_fantasia
            ?: $item->empresa?->razao_social
                ?: 'Sem empresa';
    }

    private function itemUrl(ItemControle $item): string
    {
        return $this->safeUrl(
            fn () => ItemControleResource::getUrl('edit', ['record' => $item]),
            '#'
        );
    }

    private function percentual(int|float $valor, int|float $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($valor / $total) * 100);
    }

    private function statusFinalizados(): array
    {
        return [
            'concluido',
            'concluida',
            'concluído',
            'finalizado',
            'finalizada',
            'aprovado',
            'assinado',
            'cancelado',
            'cancelada',
        ];
    }

    private function serieFinanceira(): array
    {
        if (! CachedSchema::hasTable('pagamentos')) {
            return [3, 4, 5, 4, 6, 7, 6, 8];
        }

        $inicio = now()->subDays(7)->startOfDay();
        $dataReferencia = CachedSchema::hasColumn('pagamentos', 'pago_em')
            ? 'COALESCE(pago_em, vencimento, created_at)'
            : 'COALESCE(vencimento, created_at)';

        $query = Pagamento::query()
            ->selectRaw('DATE(' . $dataReferencia . ') as dia')
            ->selectRaw('SUM(valor) as total')
            ->where('created_at', '>=', $inicio);

        if (! $this->user?->isSuperAdmin() && $this->user?->empresa_id) {
            $query->where('empresa_id', $this->user->empresa_id);
        }

        $dados = $query
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $serie = [];

        for ($i = 7; $i >= 0; $i--) {
            $dia = now()->subDays($i)->toDateString();
            $valor = (float) ($dados[$dia] ?? 0);
            $serie[] = max(2, min(10, (int) round($valor / 100)));
        }

        return $serie;
    }

    private function countPortalMessages(): int
    {
        try {
            $query = DB::table('prazzu_client_portal_messages');

            if (CachedSchema::hasColumn('prazzu_client_portal_messages', 'empresa_id') && ! $this->user?->isSuperAdmin() && $this->user?->empresa_id) {
                $query->where('empresa_id', $this->user->empresa_id);
            }

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function countAuditorias(): int
    {
        try {
            $query = DB::table('auditoria_detalhada')
                ->where('created_at', '>=', now()->subDays(30));

            if (CachedSchema::hasColumn('auditoria_detalhada', 'empresa_id') && ! $this->user?->isSuperAdmin() && $this->user?->empresa_id) {
                $query->where('empresa_id', $this->user->empresa_id);
            }

            return (int) $query->count();
        } catch (Throwable) {
            return 0;
        }
    }

    private function safeUrl(callable $callback, string $fallback = '#'): string
    {
        try {
            return (string) $callback();
        } catch (Throwable) {
            return $fallback;
        }
    }
}
