<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    @php
        $colunas = $this->getColunas();
        $itemSelecionado = $this->getItemSelecionado();
        $totalItensKanban = collect($colunas)->sum('total');
    @endphp

    <div class="tp-page">
        <section class="contabilidade-lote3-scope" aria-label="Propósito do Kanban">
            <div class="contabilidade-lote3-scope__top">
                <div>
                    <span class="contabilidade-lote3-eyebrow"><i class="bi bi-columns-gap"></i> Kanban</span>
                    <h2>Visualização de fluxo por status</h2>
                    <p>Kanban serve para movimentar e entender o fluxo. A análise de prazo fica em SLA; decisões ficam em Aprovações; resolução detalhada fica em Pendências.</p>
                </div>
                <div class="contabilidade-lote3-actions">
                    <a class="contabilidade-lote3-action primary" href="{{ \App\Filament\Pages\Pendencias::getUrl() }}"><i class="bi bi-list-check"></i> Abrir Pendências</a>
                    <a class="contabilidade-lote3-action" href="{{ \App\Filament\Pages\CentroOperacional::getUrl() }}"><i class="bi bi-command"></i> Mesa</a>
                </div>
            </div>
        </section>

        <div class="tp-action-loading" wire:loading.flex wire:target="abrirItem,fecharItem,atualizarStatus,moverItemKanban,alternarChecklist,adicionarChecklist,adicionarComentario">
            <span class="tp-spinner"></span>
            <span>Processando alteração...</span>
        </div>
        <div class="tp-hero">
            <div>
                <span class="tp-eyebrow">TRABALHO</span>
                <h2>Kanban Operacional</h2>
                <p>Quadro de execução com modal de detalhes, checklist rápido, comentários e mudança de status sem precisar sair da tela.</p>
            </div>

            <div class="tp-actions">
                <a href="{{ $this->getUrlNovaTarefa() }}" class="tp-btn">Novo item</a>
            </div>
        </div>

        <div class="tp-kanban-toolbar">
            <div>
                <strong>Fluxo rápido</strong>
                <span>Arraste os cards entre colunas para mudar o status. Clique no card para abrir o painel do item.</span>
            </div>
            <div class="tp-kanban-legend">
                <span><i class="tp-dot tp-dot-danger"></i> Vencido</span>
                <span><i class="tp-dot tp-dot-warning"></i> Checklist pendente</span>
                <span><i class="tp-dot tp-dot-success"></i> Concluído</span>
            </div>
        </div>

        @if($totalItensKanban === 0)
            <div class="tp-empty tp-empty-large tp-empty-actionable">
                <div class="tp-empty-icon">▦</div>
                <strong>Nenhum item no Kanban</strong>
                <p>Assim que uma tarefa for criada na Central Operacional, ela aparecerá aqui separada por status para acompanhamento visual.</p>
                <a href="{{ $this->getUrlNovaTarefa() }}" class="tp-empty-link">Criar primeiro item</a>
            </div>
        @endif

        <div class="tp-kanban">
            @foreach($colunas as $coluna)
                <div class="tp-kanban-column tp-kanban-{{ $coluna['status'] }}" data-kanban-column="{{ $coluna['status'] }}">
                    <div class="tp-kanban-header">
                        <div>
                            <strong>{{ $coluna['label'] }}</strong>
                            <small>{{ $coluna['total'] }} item(ns)</small>
                        </div>
                        <span>{{ $coluna['total'] }}</span>
                    </div>

                    <div class="tp-kanban-cards" data-kanban-list="{{ $coluna['status'] }}" data-kanban-accepts-drop="{{ $coluna['status'] === 'vencido' ? '0' : '1' }}">
                        @forelse($coluna['itens'] as $item)
                            <article role="button" tabindex="0" aria-label="Abrir item {{ $item['titulo'] }}" data-kanban-card="{{ $item['id'] }}" data-kanban-open="{{ $item['id'] }}" class="tp-kanban-card @if($item['vencido']) is-late @endif">
                                <div class="tp-kanban-card-top">
                                    <strong>{{ $item['titulo'] }}</strong>
                                    @if($item['vencido'])
                                        <span class="tp-mini-badge tp-mini-danger">Vencido</span>
                                    @elseif(($item['status_normalized'] ?? $item['status_raw']) === 'concluido')
                                        <span class="tp-mini-badge tp-mini-success">Concluído</span>
                                    @else
                                        <span class="tp-mini-badge">{{ $item['prioridade'] ?: 'Normal' }}</span>
                                    @endif
                                </div>

                                <span>{{ $item['tipo'] }} • {{ $item['empresa'] }}</span>

                                @if($item['descricao'])
                                    <small>{{ $item['descricao'] }}</small>
                                @endif

                                <div class="tp-kanban-progress">
                                    <div>
                                        <small>Checklist</small>
                                        <b>{{ $item['checklists_concluidos'] }}/{{ $item['checklists_total'] }}</b>
                                    </div>
                                    <div class="tp-progress"><i style="width: {{ $item['checklists_percentual'] }}%"></i></div>
                                </div>

                                <div class="tp-kanban-foot">
                                    <em>{{ $item['responsavel'] }}</em>
                                    <b>{{ $item['vencimento'] }}</b>
                                </div>

                                <div class="tp-kanban-meta">
                                    <span>{{ $item['comentarios'] }} comentário(s)</span>
                                    <span>Ver painel</span>
                                </div>
                            </article>
                        @empty
                            <div class="tp-empty tp-empty-column">
                                <strong>Coluna sem itens</strong>
                                <span>Arraste um card para cá ou altere o status pelo painel do item.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if($itemSelecionado)
        <div class="tp-modal-backdrop" wire:key="kanban-modal-{{ $itemSelecionado['id'] }}">
            <div class="tp-modal tp-kanban-modal">
                <div class="tp-modal-head tp-kanban-modal-head">
                    <div class="tp-kanban-modal-title">
                        <span class="tp-eyebrow-dark">ITEM DO KANBAN</span>
                        <h3>{{ $itemSelecionado['titulo'] }}</h3>
                        <p>{{ $itemSelecionado['empresa'] }} • {{ $itemSelecionado['responsavel'] }}</p>
                    </div>
                    <div class="tp-kanban-modal-head-actions">
                        <span class="tp-badge @if($itemSelecionado['vencido'] || ($itemSelecionado['proxima_acao']['tom'] ?? '') === 'danger') tp-badge-danger @endif">{{ $itemSelecionado['status'] }}</span>
                        <button type="button" wire:click="fecharItem" wire:loading.attr="disabled" wire:target="fecharItem" class="tp-modal-close">×</button>
                    </div>
                </div>

                <div class="tp-kanban-focus @if(($itemSelecionado['proxima_acao']['tom'] ?? '') === 'danger') is-danger @elseif(($itemSelecionado['proxima_acao']['tom'] ?? '') === 'warning') is-warning @else is-success @endif">
                    <div>
                        <span>Próxima ação recomendada</span>
                        <strong>{{ $itemSelecionado['proxima_acao']['titulo'] }}</strong>
                        <p>{{ $itemSelecionado['proxima_acao']['descricao'] }}</p>
                    </div>
                    <div class="tp-kanban-focus-status">
                        <small>Prazo</small>
                        <b>{{ $itemSelecionado['vencimento'] }}</b>
                    </div>
                </div>

                <div class="tp-kanban-modal-summary">
                    <div>
                        <span>Cliente</span>
                        <strong>{{ $itemSelecionado['empresa'] }}</strong>
                    </div>
                    <div>
                        <span>Responsável</span>
                        <strong>{{ $itemSelecionado['responsavel'] }}</strong>
                    </div>
                    <div>
                        <span>Tipo / Categoria</span>
                        <strong>{{ $itemSelecionado['tipo'] }} @if($itemSelecionado['categoria'] !== '-') • {{ $itemSelecionado['categoria'] }} @endif</strong>
                    </div>
                    <div>
                        <span>Prioridade</span>
                        <strong>{{ $itemSelecionado['prioridade'] ?: '-' }}</strong>
                    </div>
                </div>

                <div class="tp-modal-grid tp-kanban-modal-grid">
                    <div class="tp-modal-main">
                        @if(! empty($itemSelecionado['alertas_operacionais']))
                            <div class="tp-detail-card tp-kanban-alerts-card">
                                <div class="tp-detail-title">
                                    <strong>Pontos de atenção</strong>
                                    <span>{{ count($itemSelecionado['alertas_operacionais']) }}</span>
                                </div>
                                <div class="tp-kanban-alerts">
                                    @foreach($itemSelecionado['alertas_operacionais'] as $alerta)
                                        <div class="tp-kanban-alert is-{{ $alerta['tom'] }}">
                                            <i>{{ $alerta['tom'] === 'danger' ? '!' : ($alerta['tom'] === 'warning' ? '•' : 'i') }}</i>
                                            <span>{{ $alerta['texto'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="tp-detail-card">
                            <div class="tp-detail-title">
                                <strong>Resumo operacional</strong>
                                <span class="tp-mini-badge">{{ $itemSelecionado['status'] }}</span>
                            </div>

                            <div class="tp-detail-text">
                                {!! nl2br(e($itemSelecionado['descricao_completa'] ?? 'Sem descrição cadastrada. Use os comentários para registrar o contexto do atendimento.')) !!}
                            </div>

                            @if(! empty($itemSelecionado['tags']))
                                <div class="tp-kanban-tags">
                                    @foreach($itemSelecionado['tags'] as $tag)
                                        <span>{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if($itemSelecionado['observacao'])
                                <div class="tp-note">
                                    <strong>Observação interna</strong>
                                    <span>{!! nl2br(e($itemSelecionado['observacao'])) !!}</span>
                                </div>
                            @endif
                        </div>

                        <div class="tp-detail-card">
                            <div class="tp-detail-title">
                                <strong>Checklist de execução</strong>
                                <span>{{ $itemSelecionado['checklists_concluidos'] }}/{{ $itemSelecionado['checklists_total'] }}</span>
                            </div>

                            <div class="tp-progress tp-progress-large"><i style="width: {{ $itemSelecionado['checklists_percentual'] }}%"></i></div>

                            <div class="tp-checklist-box">
                                @forelse($itemSelecionado['checklists'] as $checklist)
                                    <button type="button" wire:click="alternarChecklist({{ $checklist['id'] }})" wire:loading.attr="disabled" wire:target="alternarChecklist({{ $checklist['id'] }})" class="tp-checkline @if($checklist['concluido']) is-done @endif">
                                        <i>{{ $checklist['concluido'] ? '✓' : '○' }}</i>
                                        <span>{{ $checklist['titulo'] }}</span>
                                        @if($checklist['concluido_em'])
                                            <small>{{ $checklist['concluido_em'] }}</small>
                                        @endif
                                    </button>
                                @empty
                                    <div class="tp-empty tp-empty-small">
                                        <strong>Checklist ainda vazio</strong>
                                        <span>Adicione etapas simples para a equipe saber exatamente o que falta fazer.</span>
                                    </div>
                                @endforelse
                            </div>

                            <form wire:submit.prevent="adicionarChecklist" class="tp-inline-form">
                                <input type="text" wire:model.defer="novoChecklistTitulo" wire:loading.attr="disabled" wire:target="adicionarChecklist" placeholder="Ex.: Conferir guia, validar documento, avisar cliente">
                                <button type="submit" wire:loading.attr="disabled" wire:target="adicionarChecklist">
                                    <span wire:loading.remove wire:target="adicionarChecklist">Adicionar etapa</span>
                                    <span wire:loading.inline-flex wire:target="adicionarChecklist" class="tp-inline-loading"><i class="tp-spinner"></i> Salvando</span>
                                </button>
                            </form>
                        </div>

                        <div class="tp-kanban-split">
                            <div class="tp-detail-card">
                                <div class="tp-detail-title">
                                    <strong>Documentos</strong>
                                    <span>{{ $itemSelecionado['anexos'] }} arquivo(s)</span>
                                </div>
                                <div class="tp-mini-list">
                                    @forelse($itemSelecionado['anexos_lista'] as $anexo)
                                        <div class="tp-mini-row">
                                            <i>📎</i>
                                            <div>
                                                <strong>{{ $anexo['nome'] }}</strong>
                                                <span>{{ $anexo['data'] }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="tp-empty tp-empty-small">
                                            <strong>Nenhum anexo neste item</strong>
                                            <span>Se o processo depende de arquivo, registre isso antes de concluir.</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="tp-detail-card">
                                <div class="tp-detail-title">
                                    <strong>Dependências</strong>
                                    <span>{{ $itemSelecionado['dependencias'] + $itemSelecionado['bloqueios'] }}</span>
                                </div>
                                <div class="tp-mini-list">
                                    @forelse($itemSelecionado['dependencias_lista'] as $dependencia)
                                        <div class="tp-mini-row">
                                            <i>↳</i>
                                            <div>
                                                <strong>{{ $dependencia['titulo'] }}</strong>
                                                <span>{{ $dependencia['status'] }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        @if(empty($itemSelecionado['bloqueios_lista']))
                                            <div class="tp-empty tp-empty-small">
                                                <strong>Sem dependências cadastradas</strong>
                                                <span>O item pode avançar sem aguardar outro processo.</span>
                                            </div>
                                        @endif
                                    @endforelse

                                    @foreach($itemSelecionado['bloqueios_lista'] as $bloqueio)
                                        <div class="tp-mini-row is-warning">
                                            <i>!</i>
                                            <div>
                                                <strong>Impacta: {{ $bloqueio['titulo'] }}</strong>
                                                <span>{{ $bloqueio['status'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="tp-detail-card">
                            <div class="tp-detail-title">
                                <strong>Comentários e histórico rápido</strong>
                                <span>{{ $itemSelecionado['comentarios'] }}</span>
                            </div>

                            <div class="tp-comments-box">
                                @forelse($itemSelecionado['comentarios_lista'] as $comentario)
                                    <div class="tp-comment">
                                        <strong>{{ $comentario['autor'] }}</strong>
                                        <p>{{ $comentario['comentario'] }}</p>
                                        <small>{{ $comentario['data'] }}</small>
                                    </div>
                                @empty
                                    <div class="tp-empty tp-empty-small">
                                        <strong>Sem comentários</strong>
                                        <span>Registre decisões, contato com cliente ou motivo de bloqueio.</span>
                                    </div>
                                @endforelse
                            </div>

                            <form wire:submit.prevent="adicionarComentario" class="tp-comment-form">
                                <textarea wire:model.defer="novoComentario" wire:loading.attr="disabled" wire:target="adicionarComentario" rows="3" placeholder="Ex.: Cliente avisado, documento conferido, aguardando retorno..."></textarea>
                                <button type="submit" wire:loading.attr="disabled" wire:target="adicionarComentario">
                                    <span wire:loading.remove wire:target="adicionarComentario">Registrar comentário</span>
                                    <span wire:loading.inline-flex wire:target="adicionarComentario" class="tp-inline-loading"><i class="tp-spinner"></i> Salvando</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <aside class="tp-modal-side tp-kanban-side">
                        <div class="tp-detail-card">
                            <strong class="tp-side-title">Mover status</strong>
                            <p class="tp-side-help">Use quando a etapa realmente mudou. Se existir bloqueio, registre comentário antes de concluir.</p>
                            <div class="tp-action-stack">
                                <button type="button" wire:click="atualizarStatus({{ $itemSelecionado['id'] }}, 'pendente')" wire:loading.attr="disabled" wire:target="atualizarStatus({{ $itemSelecionado['id'] }}, 'pendente')">Marcar como pendente</button>
                                <button type="button" wire:click="atualizarStatus({{ $itemSelecionado['id'] }}, 'em_andamento')" wire:loading.attr="disabled" wire:target="atualizarStatus({{ $itemSelecionado['id'] }}, 'em_andamento')">Mover para andamento</button>
                                <button type="button" wire:click="atualizarStatus({{ $itemSelecionado['id'] }}, 'concluido')" wire:loading.attr="disabled" wire:target="atualizarStatus({{ $itemSelecionado['id'] }}, 'concluido')">Concluir item</button>
                            </div>
                        </div>

                        <div class="tp-detail-card tp-info-list">
                            <strong class="tp-side-title">Controle do processo</strong>
                            <p><span>Vencimento</span><b>{{ $itemSelecionado['vencimento'] }}</b></p>
                            <p><span>SLA</span><b>{{ $itemSelecionado['sla'] }}</b></p>
                            <p><span>Status SLA</span><b>{{ $itemSelecionado['sla_status'] }}</b></p>
                            <p><span>Urgência</span><b>{{ $itemSelecionado['urgencia'] }}</b></p>
                            <p><span>Risco</span><b>{{ $itemSelecionado['risco_score'] ?: '-' }}</b></p>
                            <p><span>Bloqueado</span><b>{{ $itemSelecionado['bloqueado'] ? 'Sim' : 'Não' }}</b></p>
                        </div>

                        <div class="tp-detail-card tp-info-list">
                            <strong class="tp-side-title">Cliente e documentação</strong>
                            <p><span>Documento</span><b>{{ $itemSelecionado['document_status'] }}</b></p>
                            <p><span>Portal</span><b>{{ $itemSelecionado['portal_status'] }}</b></p>
                            <p><span>Mensagens portal</span><b>{{ $itemSelecionado['mensagens_portal'] }}</b></p>
                            <p><span>Versões</span><b>{{ $itemSelecionado['versoes'] }}</b></p>
                            <p><span>Aprovação</span><b>{{ $itemSelecionado['approval_required'] ? $itemSelecionado['approval_status'] : 'Não exige' }}</b></p>
                        </div>

                        <div class="tp-detail-card tp-info-list">
                            <strong class="tp-side-title">Tempo e auditoria</strong>
                            <p><span>Estimado</span><b>{{ $itemSelecionado['tempo_estimado'] }}</b></p>
                            <p><span>Real</span><b>{{ $itemSelecionado['tempo_real'] }}</b></p>
                            <p><span>Criado em</span><b>{{ $itemSelecionado['data_criacao'] }}</b></p>
                            <p><span>Atualizado em</span><b>{{ $itemSelecionado['data_atualizacao'] }}</b></p>
                        </div>

                        <a href="{{ $itemSelecionado['url'] }}" class="tp-open-record">Abrir cadastro completo</a>
                    </aside>
                </div>
            </div>
        </div>
    @endif

    <script>
        (function () {
            const KANBAN_SELECTOR = '[data-kanban-list]';
            const CARD_SELECTOR = '[data-kanban-card]';
            const OPEN_SELECTOR = '[data-kanban-open]';

            window.PrazzuKanban = window.PrazzuKanban || {
                instances: [],
                dragStartedAt: 0,
                lastMovedItemId: null,
            };

            function getLivewireComponent() {
                try {
                    return @this;
                } catch (error) {
                    return null;
                }
            }

            function destruirSortablesAntigos() {
                (window.PrazzuKanban.instances || []).forEach((instance) => {
                    try {
                        instance.destroy();
                    } catch (error) {
                        // Mantém a página utilizável mesmo se uma instância já tiver sido destruída pelo Livewire.
                    }
                });

                window.PrazzuKanban.instances = [];

                document.querySelectorAll(KANBAN_SELECTOR).forEach((lista) => {
                    delete lista.dataset.sortableAtivo;
                });
            }

            function abrirCardPeloClique(event) {
                const card = event.target.closest(OPEN_SELECTOR);

                if (! card) {
                    return;
                }

                const agora = Date.now();
                const acabouDeArrastar = agora - (window.PrazzuKanban.dragStartedAt || 0) < 350;

                if (acabouDeArrastar) {
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }

                const itemId = Number(card.dataset.kanbanOpen || 0);
                const livewire = getLivewireComponent();

                if (itemId && livewire) {
                    livewire.call('abrirItem', itemId);
                }
            }

            function abrirCardPeloTeclado(event) {
                if (! ['Enter', ' '].includes(event.key)) {
                    return;
                }

                const card = event.target.closest(OPEN_SELECTOR);

                if (! card) {
                    return;
                }

                event.preventDefault();
                const itemId = Number(card.dataset.kanbanOpen || 0);
                const livewire = getLivewireComponent();

                if (itemId && livewire) {
                    livewire.call('abrirItem', itemId);
                }
            }

            function moverCard(event) {
                const card = event.item;
                const origem = event.from;
                const destino = event.to;
                const itemId = Number(card?.dataset?.kanbanCard || 0);
                const novoStatus = destino?.dataset?.kanbanList;
                const statusAnterior = origem?.dataset?.kanbanList;

                window.PrazzuKanban.dragStartedAt = Date.now();

                if (! itemId || ! novoStatus || ! statusAnterior) {
                    return;
                }

                if (novoStatus === statusAnterior) {
                    return;
                }

                if (novoStatus === 'vencido') {
                    // "Vencido" é uma coluna calculada pela data de vencimento, não um status manual.
                    // Reverte imediatamente para não passar a sensação de que o sistema ignorou o usuário.
                    origem.insertBefore(card, origem.children[event.oldIndex] || null);
                    window.dispatchEvent(new CustomEvent('prazzu-kanban-invalid-drop'));
                    return;
                }

                const livewire = getLivewireComponent();

                if (! livewire) {
                    origem.insertBefore(card, origem.children[event.oldIndex] || null);
                    return;
                }

                card.classList.add('is-saving');
                window.PrazzuKanban.lastMovedItemId = itemId;

                livewire.call('moverItemKanban', itemId, novoStatus)
                    .catch(() => {
                        origem.insertBefore(card, origem.children[event.oldIndex] || null);
                    })
                    .finally(() => {
                        card.classList.remove('is-saving');
                    });
            }

            function iniciarKanbanArrastarSoltar() {
                if (typeof Sortable === 'undefined') {
                    return;
                }

                destruirSortablesAntigos();

                document.querySelectorAll(KANBAN_SELECTOR).forEach((lista) => {
                    lista.dataset.sortableAtivo = '1';

                    const instance = new Sortable(lista, {
                        group: {
                            name: 'prazzu-kanban',
                            pull: true,
                            put: function (to) {
                                return to.el?.dataset?.kanbanAcceptsDrop !== '0';
                            },
                        },
                        animation: 180,
                        draggable: CARD_SELECTOR,
                        ghostClass: 'tp-kanban-card-ghost',
                        chosenClass: 'tp-kanban-card-chosen',
                        dragClass: 'tp-kanban-card-drag',
                        fallbackOnBody: true,
                        forceFallback: false,
                        fallbackTolerance: 5,
                        touchStartThreshold: 5,
                        emptyInsertThreshold: 80,
                        swapThreshold: 0.65,
                        invertSwap: true,
                        filter: '.tp-empty',
                        preventOnFilter: false,
                        onStart: function () {
                            document.body.classList.add('tp-kanban-is-dragging');
                        },
                        onAdd: moverCard,
                        onEnd: function () {
                            document.body.classList.remove('tp-kanban-is-dragging');
                            window.PrazzuKanban.dragStartedAt = Date.now();
                        },
                    });

                    window.PrazzuKanban.instances.push(instance);
                });
            }

            if (! window.PrazzuKanban.listenersRegistered) {
                window.PrazzuKanban.listenersRegistered = true;

                document.addEventListener('click', abrirCardPeloClique, true);
                document.addEventListener('keydown', abrirCardPeloTeclado, true);
                document.addEventListener('DOMContentLoaded', iniciarKanbanArrastarSoltar);
                document.addEventListener('livewire:navigated', iniciarKanbanArrastarSoltar);
                document.addEventListener('livewire:initialized', iniciarKanbanArrastarSoltar);

                window.addEventListener('prazzu-kanban-invalid-drop', function () {
                    if (window.FilamentNotification) {
                        new window.FilamentNotification()
                            .title('A coluna Vencido é automática')
                            .body('Para aparecer como vencido, o item precisa estar com prazo expirado. Use Pendente, Em andamento ou Concluído.')
                            .warning()
                            .send();
                    }
                });

                if (window.Livewire?.hook) {
                    window.Livewire.hook('morph.updated', () => {
                        window.requestAnimationFrame(iniciarKanbanArrastarSoltar);
                    });
                }
            }

            window.requestAnimationFrame(iniciarKanbanArrastarSoltar);
        })();
    </script>
</x-filament-panels::page>
