<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/trabalho-pages.css') }}">

    @php
        $resumo = $this->getResumo();
        $projetos = $this->getProjetos();
        $recentes = $this->getRecentes();
        $projetoSelecionado = $this->getProjetoSelecionado();
    @endphp

    <div class="tp-page tp-projects-page">
        <div class="tp-hero">
            <div>
                <span class="tp-eyebrow">TRABALHO</span>
                <h2>Projetos</h2>
                <p>Hub operacional dos projetos, com progresso, prazos, responsáveis, checklist, comentários, riscos e ações rápidas.</p>
            </div>

            <div class="tp-actions">
                <a href="{{ $this->getUrlNovaTarefa() }}" class="tp-btn">Novo item</a>
                <a href="{{ $this->getUrlTarefas() }}" class="tp-btn-secondary">Cadastro completo</a>
            </div>
        </div>

        <div class="tp-metrics tp-metrics-5">
            <div class="tp-card">
                <span>Total</span>
                <strong>{{ $resumo['total'] }}</strong>
                <small>itens nos filtros</small>
            </div>

            <div class="tp-card">
                <span>Ativos</span>
                <strong>{{ $resumo['ativos'] }}</strong>
                <small>em execução</small>
            </div>

            <div class="tp-card tp-danger">
                <span>Atrasados</span>
                <strong>{{ $resumo['atrasados'] }}</strong>
                <small>exigem atenção</small>
            </div>

            <div class="tp-card">
                <span>Hoje</span>
                <strong>{{ $resumo['hoje'] }}</strong>
                <small>vencem hoje</small>
            </div>

            <div class="tp-card tp-success">
                <span>Concluídos</span>
                <strong>{{ $resumo['concluidos'] }}</strong>
                <small>finalizados</small>
            </div>
        </div>

        <div class="tp-project-filter-bar">
            <div class="tp-filter-field tp-filter-search">
                <label>Buscar</label>
                <input type="search" wire:model.live.debounce.400ms="busca" placeholder="Projeto, item, cliente ou responsável">
            </div>

            <div class="tp-filter-field">
                <label>Status</label>
                <select wire:model.live="filtroStatus">
                    <option value="todos">Todos</option>
                    <option value="pendente">Pendente</option>
                    <option value="em_andamento">Em andamento</option>
                    <option value="concluidos">Concluídos</option>
                    <option value="atrasados">Atrasados</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>

            <div class="tp-filter-field">
                <label>Prioridade</label>
                <select wire:model.live="filtroPrioridade">
                    <option value="todos">Todas</option>
                    <option value="baixa">Baixa</option>
                    <option value="media">Média</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>

            <button type="button" class="tp-filter-clear" wire:click="limparFiltros">Limpar filtros</button>
        </div>

        <div class="tp-project-board">
            @forelse($projetos as $projeto)
                <button type="button" class="tp-project-card" wire:click="abrirProjeto('{{ addslashes($projeto['tipo']) }}')">
                    <div class="tp-project-card-head">
                        <div>
                            <span>Projeto / modalidade</span>
                            <strong>{{ $projeto['nome'] }}</strong>
                        </div>
                        <em>{{ $projeto['percentual'] }}%</em>
                    </div>

                    <div class="tp-progress">
                        <i style="width: {{ $projeto['percentual'] }}%"></i>
                    </div>

                    <div class="tp-project-kpis">
                        <div>
                            <b>{{ $projeto['total'] }}</b>
                            <span>itens</span>
                        </div>

                        <div>
                            <b>{{ $projeto['ativos'] }}</b>
                            <span>ativos</span>
                        </div>

                        <div class="{{ $projeto['atrasados'] > 0 ? 'is-danger' : '' }}">
                            <b>{{ $projeto['atrasados'] }}</b>
                            <span>atrasos</span>
                        </div>

                        <div>
                            <b>{{ $projeto['checklist_percentual'] }}%</b>
                            <span>checklist</span>
                        </div>
                    </div>

                    <div class="tp-project-info-line">
                        <span>Próxima entrega</span>
                        <strong>{{ $projeto['proxima_entrega'] }}</strong>
                    </div>

                    <div class="tp-project-info-line">
                        <span>Responsáveis</span>
                        <strong>{{ $projeto['responsaveis'] }}</strong>
                    </div>

                    <div class="tp-project-info-line">
                        <span>Clientes</span>
                        <strong>{{ $projeto['empresas'] }}</strong>
                    </div>

                    <div class="tp-project-mini-list">
                        @foreach($projeto['itens'] as $item)
                            <div class="tp-project-mini-item {{ $item['atrasado'] ? 'is-late' : '' }}">
                                <span>{{ $item['titulo'] }}</span>
                                <b>{{ $item['vencimento'] }}</b>
                            </div>
                        @endforeach
                    </div>

                    <div class="tp-project-card-footer">
                        <span>{{ $projeto['comentarios'] }} comentários</span>
                        <strong>Abrir painel</strong>
                    </div>
                </button>
            @empty
                <div class="tp-empty tp-empty-large">Nenhum projeto encontrado com os filtros atuais.</div>
            @endforelse
        </div>

        <x-filament::section>
            <x-slot name="heading">Últimos itens movimentados</x-slot>

            <div class="tp-list">
                @forelse($recentes as $item)
                    <button type="button" class="tp-list-row tp-list-button" wire:click="abrirProjeto('{{ addslashes($item['status_original'] === '' ? 'sem_tipo' : strtolower(str_replace(' ', '_', $item['tipo']))) }}')">
                        <div class="tp-list-main">
                            <strong>{{ $item['titulo'] }}</strong>
                            <span>{{ $item['tipo'] }} • {{ $item['empresa'] }} • {{ $item['responsavel'] }}</span>
                        </div>

                        <div class="tp-list-side">
                            <span class="tp-badge {{ $item['atrasado'] ? 'tp-badge-danger' : '' }}">{{ $item['status'] }}</span>
                            <b>{{ $item['vencimento'] }}</b>
                        </div>
                    </button>
                @empty
                    <div class="tp-empty">Nenhum item recente encontrado.</div>
                @endforelse
            </div>
        </x-filament::section>

        @if($projetoSelecionado)
            <div class="tp-modal-backdrop" wire:click.self="fecharProjeto">
                <div class="tp-project-modal">
                    <div class="tp-project-modal-head">
                        <div>
                            <span>PAINEL DO PROJETO</span>
                            <h3>{{ $projetoSelecionado['nome'] }}</h3>
                            <p>{{ $projetoSelecionado['total'] }} itens • {{ $projetoSelecionado['concluidos'] }} concluídos • {{ $projetoSelecionado['atrasados'] }} atrasados</p>
                        </div>

                        <button type="button" wire:click="fecharProjeto">×</button>
                    </div>

                    <div class="tp-project-modal-progress">
                        <strong>{{ $projetoSelecionado['percentual'] }}%</strong>
                        <div class="tp-progress">
                            <i style="width: {{ $projetoSelecionado['percentual'] }}%"></i>
                        </div>
                    </div>

                    <div class="tp-project-modal-grid">
                        <div class="tp-project-modal-items">
                            @foreach($projetoSelecionado['itens'] as $item)
                                <button type="button" class="tp-project-task {{ $item['id'] === ($projetoSelecionado['item_selecionado']['id'] ?? null) ? 'is-active' : '' }} {{ $item['atrasado'] ? 'is-late' : '' }}" wire:click="selecionarItem({{ $item['id'] }})">
                                    <strong>{{ $item['titulo'] }}</strong>
                                    <span>{{ $item['empresa'] }} • {{ $item['responsavel'] }}</span>
                                    <small>{{ $item['status'] }} • {{ $item['vencimento'] }}</small>
                                </button>
                            @endforeach
                        </div>

                        @if($projetoSelecionado['item_selecionado'])
                            @php($item = $projetoSelecionado['item_selecionado'])

                            <div class="tp-project-detail">
                                <div class="tp-project-detail-head">
                                    <div>
                                        <span class="tp-badge {{ $item['atrasado'] ? 'tp-badge-danger' : '' }}">{{ $item['status'] }}</span>
                                        <h4>{{ $item['titulo'] }}</h4>
                                        <p>{{ $item['empresa'] }} • {{ $item['responsavel'] }}</p>
                                    </div>

                                    <a href="{{ $item['url'] }}" class="tp-btn-dark">Abrir cadastro completo</a>
                                </div>

                                <div class="tp-project-detail-grid">
                                    <div>
                                        <span>Prioridade</span>
                                        <strong>{{ $item['prioridade'] }}</strong>
                                    </div>

                                    <div>
                                        <span>Vencimento</span>
                                        <strong>{{ $item['vencimento'] }}</strong>
                                    </div>

                                    <div>
                                        <span>Conclusão</span>
                                        <strong>{{ $item['data_conclusao'] }}</strong>
                                    </div>

                                    <div>
                                        <span>Checklist</span>
                                        <strong>{{ $item['checklist_percentual'] }}%</strong>
                                    </div>
                                </div>

                                <div class="tp-project-description">
                                    <strong>Descrição</strong>
                                    <p>{{ $item['descricao_completa'] }}</p>

                                    <strong>Observação</strong>
                                    <p>{{ $item['observacao'] }}</p>
                                </div>

                                <div class="tp-project-actions-row">
                                    <button type="button" wire:click="alterarStatusItem({{ $item['id'] }}, 'pendente')">Pendente</button>
                                    <button type="button" wire:click="alterarStatusItem({{ $item['id'] }}, 'em_andamento')">Em andamento</button>
                                    <button type="button" wire:click="alterarStatusItem({{ $item['id'] }}, 'concluido')">Concluir</button>
                                </div>

                                <div class="tp-project-detail-columns">
                                    <div class="tp-project-box">
                                        <div class="tp-project-box-head">
                                            <strong>Checklist</strong>
                                            <span>{{ $item['checklist_concluidos'] }}/{{ $item['checklist_total'] }}</span>
                                        </div>

                                        <div class="tp-project-checks">
                                            @forelse($item['checklists'] as $checklist)
                                                <button type="button" class="tp-project-check {{ $checklist['concluido'] ? 'is-done' : '' }}" wire:click="alternarChecklist({{ $checklist['id'] }})">
                                                    <i>{{ $checklist['concluido'] ? '✓' : '' }}</i>
                                                    <span>{{ $checklist['titulo'] }}</span>
                                                </button>
                                            @empty
                                                <div class="tp-empty">Nenhuma etapa cadastrada.</div>
                                            @endforelse
                                        </div>

                                        <div class="tp-inline-form">
                                            <input type="text" wire:model.defer="novaEtapa" placeholder="Nova etapa do checklist">
                                            <button type="button" wire:click="adicionarEtapa">Adicionar</button>
                                        </div>
                                    </div>

                                    <div class="tp-project-box">
                                        <div class="tp-project-box-head">
                                            <strong>Comentários</strong>
                                            <span>{{ $item['comentarios_total'] }}</span>
                                        </div>

                                        <div class="tp-project-comments">
                                            @forelse($item['comentarios'] as $comentario)
                                                <div>
                                                    <strong>{{ $comentario['autor'] }}</strong>
                                                    <p>{{ $comentario['comentario'] }}</p>
                                                    <small>{{ $comentario['data'] }}</small>
                                                </div>
                                            @empty
                                                <div class="tp-empty">Nenhum comentário nesse item.</div>
                                            @endforelse
                                        </div>

                                        <div class="tp-inline-form tp-inline-form-column">
                                            <textarea wire:model.defer="novoComentario" placeholder="Escreva um comentário rápido"></textarea>
                                            <button type="button" wire:click="adicionarComentario">Comentar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>