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

                    <div class="tp-kanban-cards" data-kanban-list="{{ $coluna['status'] }}">
                        @forelse($coluna['itens'] as $item)
                            <button type="button" wire:click="abrirItem({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="abrirItem({{ $item['id'] }})" data-kanban-card="{{ $item['id'] }}" draggable="true" class="tp-kanban-card @if($item['vencido']) is-late @endif">
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
                            </button>
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
            <div class="tp-modal">
                <div class="tp-modal-head">
                    <div>
                        <span class="tp-eyebrow-dark">ITEM DO KANBAN</span>
                        <h3>{{ $itemSelecionado['titulo'] }}</h3>
                        <p>{{ $itemSelecionado['empresa'] }} • {{ $itemSelecionado['responsavel'] }}</p>
                    </div>
                    <button type="button" wire:click="fecharItem" wire:loading.attr="disabled" wire:target="fecharItem" class="tp-modal-close">×</button>
                </div>

                <div class="tp-modal-grid">
                    <div class="tp-modal-main">
                        <div class="tp-detail-card">
                            <div class="tp-detail-title">
                                <strong>Resumo</strong>
                                <span class="tp-badge @if($itemSelecionado['vencido']) tp-badge-danger @endif">{{ $itemSelecionado['status'] }}</span>
                            </div>

                            <div class="tp-detail-text">
                                {!! nl2br(e($itemSelecionado['descricao_completa'] ?? 'Sem descrição cadastrada.')) !!}
                            </div>

                            @if($itemSelecionado['observacao'])
                                <div class="tp-note">
                                    <strong>Observação</strong>
                                    <span>{!! nl2br(e($itemSelecionado['observacao'])) !!}</span>
                                </div>
                            @endif
                        </div>

                        <div class="tp-detail-card">
                            <div class="tp-detail-title">
                                <strong>Checklist rápido</strong>
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
                                        <span>Adicione a primeira etapa no campo abaixo para acompanhar a execução.</span>
                                    </div>
                                @endforelse
                            </div>

                            <form wire:submit.prevent="adicionarChecklist" class="tp-inline-form">
                                <input type="text" wire:model.defer="novoChecklistTitulo" wire:loading.attr="disabled" wire:target="adicionarChecklist" placeholder="Nova etapa do checklist">
                                <button type="submit" wire:loading.attr="disabled" wire:target="adicionarChecklist">
                                    <span wire:loading.remove wire:target="adicionarChecklist">Adicionar</span>
                                    <span wire:loading.inline-flex wire:target="adicionarChecklist" class="tp-inline-loading"><i class="tp-spinner"></i> Salvando</span>
                                </button>
                            </form>
                        </div>

                        <div class="tp-detail-card">
                            <div class="tp-detail-title">
                                <strong>Comentários</strong>
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
                                        <span>Registre uma observação para manter o histórico claro para a equipe.</span>
                                    </div>
                                @endforelse
                            </div>

                            <form wire:submit.prevent="adicionarComentario" class="tp-comment-form">
                                <textarea wire:model.defer="novoComentario" wire:loading.attr="disabled" wire:target="adicionarComentario" rows="3" placeholder="Escreva um comentário rápido"></textarea>
                                <button type="submit" wire:loading.attr="disabled" wire:target="adicionarComentario">
                                    <span wire:loading.remove wire:target="adicionarComentario">Comentar</span>
                                    <span wire:loading.inline-flex wire:target="adicionarComentario" class="tp-inline-loading"><i class="tp-spinner"></i> Salvando</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <aside class="tp-modal-side">
                        <div class="tp-detail-card">
                            <strong class="tp-side-title">Ações rápidas</strong>
                            <div class="tp-action-stack">
                                <button type="button" wire:click="atualizarStatus({{ $itemSelecionado['id'] }}, 'pendente')" wire:loading.attr="disabled" wire:target="atualizarStatus({{ $itemSelecionado['id'] }}, 'pendente')">Marcar como pendente</button>
                                <button type="button" wire:click="atualizarStatus({{ $itemSelecionado['id'] }}, 'em_andamento')" wire:loading.attr="disabled" wire:target="atualizarStatus({{ $itemSelecionado['id'] }}, 'em_andamento')">Mover para andamento</button>
                                <button type="button" wire:click="atualizarStatus({{ $itemSelecionado['id'] }}, 'concluido')" wire:loading.attr="disabled" wire:target="atualizarStatus({{ $itemSelecionado['id'] }}, 'concluido')">Concluir item</button>
                            </div>
                        </div>

                        <div class="tp-detail-card tp-info-list">
                            <strong class="tp-side-title">Informações</strong>
                            <p><span>Tipo</span><b>{{ $itemSelecionado['tipo'] }}</b></p>
                            <p><span>Prioridade</span><b>{{ $itemSelecionado['prioridade'] ?: '-' }}</b></p>
                            <p><span>Vencimento</span><b>{{ $itemSelecionado['vencimento'] }}</b></p>
                            <p><span>SLA</span><b>{{ $itemSelecionado['sla'] }}</b></p>
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
        document.addEventListener('livewire:navigated', iniciarKanbanArrastarSoltar);
        document.addEventListener('livewire:initialized', iniciarKanbanArrastarSoltar);
        document.addEventListener('DOMContentLoaded', iniciarKanbanArrastarSoltar);

        let tpKanbanUltimoPonto = { x: 0, y: 0 };

        document.addEventListener('dragover', function (event) {
            tpKanbanUltimoPonto = { x: event.clientX, y: event.clientY };
        }, true);

        document.addEventListener('mousemove', function (event) {
            if (document.body.classList.contains('tp-kanban-is-dragging')) {
                tpKanbanUltimoPonto = { x: event.clientX, y: event.clientY };
            }
        }, true);

        function iniciarKanbanArrastarSoltar() {
            if (typeof Sortable === 'undefined') {
                return;
            }

            document.querySelectorAll('[data-kanban-list]').forEach((lista) => {
                if (lista.dataset.sortableAtivo === '1') {
                    return;
                }

                lista.dataset.sortableAtivo = '1';

                new Sortable(lista, {
                    group: 'prazzu-kanban',
                    animation: 150,
                    draggable: '[data-kanban-card]',
                    ghostClass: 'tp-kanban-card-ghost',
                    chosenClass: 'tp-kanban-card-chosen',
                    dragClass: 'tp-kanban-card-drag',
                    forceFallback: true,
                    fallbackOnBody: true,
                    fallbackTolerance: 1,
                    touchStartThreshold: 1,
                    swapThreshold: 0.18,
                    invertedSwapThreshold: 0.85,
                    emptyInsertThreshold: 140,
                    filter: '.tp-empty',
                    onStart: function () {
                        document.body.classList.add('tp-kanban-is-dragging');
                    },
                    onMove: function (event, originalEvent) {
                        if (originalEvent) {
                            tpKanbanUltimoPonto = { x: originalEvent.clientX, y: originalEvent.clientY };
                        }

                        return true;
                    },
                    onEnd: function (event) {
                        document.body.classList.remove('tp-kanban-is-dragging');

                        const card = event.item;
                        const itemId = Number(card.dataset.kanbanCard || 0);

                        let destino = event.to;
                        const elementoNoPonto = document.elementFromPoint(tpKanbanUltimoPonto.x, tpKanbanUltimoPonto.y);
                        const colunaNoPonto = elementoNoPonto ? elementoNoPonto.closest('[data-kanban-list]') : null;

                        if (colunaNoPonto) {
                            destino = colunaNoPonto;

                            if (card.parentElement !== destino) {
                                destino.appendChild(card);
                            }
                        }

                        const novoStatus = destino.dataset.kanbanList;

                        if (! itemId || ! novoStatus) {
                            return;
                        }

                        if (window.Livewire && @this) {
                            @this.call('moverItemKanban', itemId, novoStatus);
                        }
                    },
                });
            });
        }
    </script>
</x-filament-panels::page>
