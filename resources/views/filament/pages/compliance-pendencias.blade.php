<x-filament-panels::page>
    @php
        $items = collect($data['items'] ?? []);
        $dashboardStats = $data['dashboardStats'] ?? [];
        $progress = $data['progress'] ?? [];
        $activeCluster = $data['activeCluster'] ?? [];
        $workflowLinks = $data['workflowLinks'] ?? [];
        $emptyState = $data['emptyState'] ?? [];

        $vencidas = $items->where('is_late', true)->values();
        $criticas = $items->whereIn('prioridade_operacional_tone', ['danger', 'warning'])->values();
        $aprovacao = $items->where('status', 'em_aprovacao')->values();
        $bloqueadas = $items->where('bloqueado_operacional', true)->values();
        $semResponsavel = $items->where('sem_responsavel', true)->values();
        $filaRecomendada = $items->take(8)->values();
        $proximos = $items->filter(fn ($item) => ($item['is_due_today'] ?? false) || ($item['is_due_soon'] ?? false))->values();

        $activeTone = $activeCluster['tone'] ?? 'info';
        $totalAfter = $data['totalAfterFilters'] ?? $items->count();
        $totalBefore = $data['totalBeforeFilters'] ?? $items->count();
    @endphp

    <div class="pendencias-clean-page">
        <section class="pendencias-clean-hero {{ $activeTone }}">
            <div class="pendencias-clean-hero-copy">
                <span class="pendencias-clean-kicker"><i class="bi bi-list-check"></i> Central de Pendências</span>
                <h1>O que precisa ser resolvido agora?</h1>
                <p>Fila operacional para priorizar, abrir detalhes, registrar decisão, acompanhar SLA, remover bloqueios e concluir pendências sem sair do fluxo.</p>
            </div>
            <div class="pendencias-clean-hero-actions">
                <a href="#fila-priorizada" class="primary"><i class="bi bi-arrow-down-circle"></i> Ir para fila</a>
                @if(! empty($workflowLinks['todas_tarefas']))
                    <a href="{{ $workflowLinks['todas_tarefas'] }}"><i class="bi bi-table"></i> Tabela completa</a>
                @endif
            </div>
        </section>

        @if($lastActionFeedback)
            <div class="pendencias-clean-feedback {{ $lastActionFeedback['tone'] ?? 'info' }}" role="status" aria-live="polite">
                <strong>{{ $lastActionFeedback['message'] }}</strong>
                <span>{{ $lastActionFeedback['time'] ?? now()->format('H:i') }}</span>
            </div>
        @endif

        <div class="pendencias-clean-loading" wire:loading.delay.flex wire:target="aplicarFiltroPendencias,limparFiltrosPendencias,abrirPendencia,concluirPendenciaSelecionada,solicitarAprovacaoPendenciaSelecionada,aprovarPendenciaSelecionada,reprovarPendenciaSelecionada,iniciarSlaPendenciaSelecionada,atualizarSlaPendenciaSelecionada,finalizarSlaPendenciaSelecionada">
            <i class="pz-ux-spinner"></i> Atualizando pendências...
        </div>

        <section class="pendencias-clean-stats" aria-label="Resumo da fila">
            @foreach($dashboardStats as $stat)
                <article class="pendencias-clean-stat {{ $stat['tone'] ?? 'info' }}">
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small>{{ $stat['hint'] }}</small>
                </article>
            @endforeach
        </section>

        <section class="pendencias-clean-control" id="controle-pendencias">
            <div class="pendencias-clean-active-cluster {{ $activeTone }}">
                <div>
                    <span>Filtro ativo</span>
                    <h2>{{ $activeCluster['label'] ?? 'Minhas Pendências' }}</h2>
                    <p>{{ $activeCluster['next_action'] ?? 'Revise a fila filtrada e execute a próxima ação recomendada.' }}</p>
                </div>
                <strong>{{ $totalAfter }}</strong>
                <small>de {{ $totalBefore }} no total</small>
            </div>

            <div class="pendencias-clean-search">
                <label>
                    <span>Buscar na fila</span>
                    <input type="search" wire:model.live.debounce.350ms="buscaPendencias" placeholder="Título, cliente, responsável, status, prioridade ou SLA">
                </label>
                @if($data['hasActiveFilters'] ?? false)
                    <button type="button" wire:click="limparFiltrosPendencias" wire:loading.attr="disabled" wire:target="limparFiltrosPendencias">
                        Limpar e voltar para minhas
                    </button>
                @endif
            </div>
        </section>

        <section class="pendencias-clean-progress" aria-label="Progresso da fila atual">
            <div>
                <span>Controle da fila atual</span>
                <strong>{{ $progress['percentual_controle'] ?? 0 }}%</strong>
                <p>{{ $progress['mensagem'] ?? 'Acompanhe a evolução da fila conforme resolve os itens prioritários.' }}</p>
            </div>
            <div class="pendencias-clean-meter"><span style="width: {{ $progress['percentual_controle'] ?? 0 }}%"></span></div>
            <ul>
                <li><b>{{ $progress['no_controle'] ?? 0 }}</b> no controle</li>
                <li><b>{{ $progress['criticas'] ?? 0 }}</b> críticas</li>
                <li><b>{{ $progress['total'] ?? 0 }}</b> no filtro</li>
            </ul>
        </section>

        <section class="pendencias-clean-grid">
            <article class="pendencias-clean-card" id="fila-priorizada">
                <header>
                    <div>
                        <span class="pendencias-clean-kicker">Fila priorizada</span>
                        <h2>Próximas ações recomendadas</h2>
                        <p>Os primeiros itens são os que mais afetam prazo, responsável, bloqueio, SLA ou aprovação.</p>
                    </div>
                    <span class="pendencias-clean-pill info">{{ $filaRecomendada->count() }} itens</span>
                </header>

                <div class="pendencias-clean-task-list">
                    @forelse($filaRecomendada as $item)
                        @php
                            $tone = $item['prioridade_operacional_tone'] ?? ($item['is_late'] ? 'danger' : 'ok');
                            $status = ucfirst(str_replace('_', ' ', $item['status'] ?? 'pendente'));
                        @endphp
                        <article class="pendencias-clean-task {{ $tone }}">
                            <div class="pendencias-clean-task-main">
                                <span class="pendencias-clean-priority {{ $tone }}">{{ $item['prioridade_operacional_label'] ?? 'No controle' }}</span>
                                <h3>{{ $item['titulo'] }}</h3>
                                <p>{{ \Illuminate\Support\Str::limit($item['descricao'] ?: 'Sem descrição cadastrada.', 140) }}</p>
                                <div class="pendencias-clean-tags">
                                    <span><i class="bi bi-building"></i> {{ $item['empresa'] }}</span>
                                    <span><i class="bi bi-person"></i> {{ $item['responsavel'] }}</span>
                                    <span><i class="bi bi-calendar-event"></i> {{ $item['vencimento'] }}</span>
                                    <span><i class="bi bi-flag"></i> {{ $status }}</span>
                                </div>
                                @if(! empty($item['workflow_next_action']))
                                    <div class="pendencias-clean-next {{ $item['workflow_stage_tone'] ?? 'info' }}">{{ $item['workflow_next_action'] }}</div>
                                @elseif(! empty($item['prioridade_operacional_message']))
                                    <div class="pendencias-clean-next {{ $tone }}">{{ $item['prioridade_operacional_message'] }}</div>
                                @endif
                            </div>
                            <button type="button" wire:click="abrirPendencia({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="abrirPendencia({{ $item['id'] }})">
                                <i class="bi bi-eye"></i> Ver detalhes
                            </button>
                        </article>
                    @empty
                        <div class="pendencias-clean-empty">
                            <strong>{{ $emptyState['title'] ?? 'Nenhuma pendência encontrada.' }}</strong>
                            <span>{{ $emptyState['message'] ?? 'Troque o filtro ou limpe a busca para ampliar os resultados.' }}</span>
                            @if(! empty($emptyState['action_label']) && ! empty($emptyState['action']))
                                @if($emptyState['action'] === 'limparFiltrosPendencias')
                                    <button type="button" wire:click="limparFiltrosPendencias">{{ $emptyState['action_label'] }}</button>
                                @else
                                    <a href="{{ $emptyState['action'] }}">{{ $emptyState['action_label'] }}</a>
                                @endif
                            @endif
                        </div>
                    @endforelse
                </div>
            </article>

            <aside class="pendencias-clean-side">
                <article class="pendencias-clean-card compact danger">
                    <header><div><h2>Tratar agora</h2><p>Itens vencidos, bloqueados ou sem dono.</p></div></header>
                    <div class="pendencias-clean-mini-list">
                        <div><span>Vencidas</span><strong>{{ $vencidas->count() }}</strong></div>
                        <div><span>Bloqueadas</span><strong>{{ $bloqueadas->count() }}</strong></div>
                        <div><span>Sem responsável</span><strong>{{ $semResponsavel->count() }}</strong></div>
                    </div>
                </article>

                <article class="pendencias-clean-card compact warning">
                    <header><div><h2>Próximos prazos</h2><p>Itens que merecem acompanhamento antes de virar atraso.</p></div></header>
                    <div class="pendencias-clean-mini-list">
                        <div><span>Hoje/próximos dias</span><strong>{{ $proximos->count() }}</strong></div>
                        <div><span>Críticas</span><strong>{{ $criticas->count() }}</strong></div>
                        <div><span>Aprovações</span><strong>{{ $aprovacao->count() }}</strong></div>
                    </div>
                </article>

                <article class="pendencias-clean-card compact info">
                    <header><div><h2>Como usar</h2><p>Fluxo recomendado para não se perder.</p></div></header>
                    <ol class="pendencias-clean-steps">
                        <li>Abra primeiro os itens vermelhos.</li>
                        <li>Remova bloqueios ou solicite documentos.</li>
                        <li>Registre aprovação, reprovação ou conclusão.</li>
                        <li>Use SLA quando o prazo precisar ser monitorado.</li>
                    </ol>
                </article>
            </aside>
        </section>

        <section class="pendencias-clean-card" id="lista-pendencias">
            <header>
                <div>
                    <span class="pendencias-clean-kicker">Lista detalhada</span>
                    <h2>Todas as pendências do filtro atual</h2>
                    <p>Visão de conferência. Para agir, abra o detalhe do item.</p>
                </div>
                <span class="pendencias-clean-pill info">{{ $items->count() }} exibidas</span>
            </header>
            <div class="pendencias-clean-table-wrap">
                <table class="pendencias-clean-table">
                    <thead>
                        <tr>
                            <th>Pendência</th>
                            <th>Cliente</th>
                            <th>Dono</th>
                            <th>Status</th>
                            <th>Prazo</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php $tone = $item['prioridade_operacional_tone'] ?? ($item['is_late'] ? 'danger' : 'ok'); @endphp
                            <tr class="{{ $tone }}">
                                <td><strong>{{ $item['titulo'] }}</strong><small>{{ $item['prioridade_operacional_label'] ?? ucfirst($item['prioridade'] ?? 'Média') }}</small></td>
                                <td>{{ $item['empresa'] }}</td>
                                <td>{{ $item['responsavel'] }}</td>
                                <td><span class="pendencias-clean-pill {{ $tone }}">{{ ucfirst(str_replace('_', ' ', $item['status'])) }}</span></td>
                                <td>{{ $item['vencimento'] }}</td>
                                <td><button type="button" wire:click="abrirPendencia({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="abrirPendencia({{ $item['id'] }})"><i class="bi bi-eye"></i> Abrir</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="pendencias-clean-empty"><strong>{{ $emptyState['title'] ?? 'Nenhuma pendência encontrada.' }}</strong><span>{{ $emptyState['message'] ?? 'Ajuste o filtro para ampliar a visão.' }}</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if($pendenciaSelecionada)
            <div class="pendencias-clean-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="pendencia-modal-title" wire:key="pendencia-modal-{{ $pendenciaSelecionada['id'] }}">
                <div class="pendencias-clean-modal">
                    <header class="pendencias-clean-modal-header {{ $pendenciaSelecionada['prioridade_operacional_tone'] }}">
                        <div>
                            <span class="pendencias-clean-kicker">Pendência #{{ $pendenciaSelecionada['id'] }}</span>
                            <h2 id="pendencia-modal-title">{{ $pendenciaSelecionada['titulo'] }}</h2>
                            <p>{{ $pendenciaSelecionada['empresa'] }} · {{ $pendenciaSelecionada['responsavel'] }}</p>
                        </div>
                        <button type="button" wire:click="fecharPendencia" aria-label="Fechar">×</button>
                    </header>

                    <div class="pendencias-clean-modal-body">
                        <section class="pendencias-clean-modal-section priority {{ $pendenciaSelecionada['prioridade_operacional_tone'] }}">
                            <span>Próxima ação recomendada</span>
                            <strong>{{ $pendenciaSelecionada['prioridade_operacional_label'] }}</strong>
                            <p>{{ $pendenciaSelecionada['prioridade_operacional_message'] }}</p>
                        </section>

                        <section class="pendencias-clean-modal-grid">
                            <div><span>Status</span><strong>{{ $pendenciaSelecionada['status'] }}</strong></div>
                            <div><span>Prioridade</span><strong>{{ $pendenciaSelecionada['prioridade'] }}</strong></div>
                            <div><span>Prazo</span><strong>{{ $pendenciaSelecionada['vencimento'] }}</strong><small>{{ $pendenciaSelecionada['prazo'] }}</small></div>
                            <div><span>Aprovação</span><strong>{{ $pendenciaSelecionada['approval_status'] }}</strong></div>
                            <div><span>SLA</span><strong>{{ $pendenciaSelecionada['sla_resumo'] }}</strong><small>{{ $pendenciaSelecionada['sla_tempo'] }}</small></div>
                            <div><span>Bloqueio</span><strong>{{ $pendenciaSelecionada['bloqueado'] ? 'Existe bloqueio' : 'Sem bloqueio' }}</strong><small>{{ $pendenciaSelecionada['bloqueio_resumo'] }}</small></div>
                            <div><span>Documento</span><strong>{{ $pendenciaSelecionada['document_status'] }}</strong></div>
                            <div><span>Portal</span><strong>{{ $pendenciaSelecionada['portal_status'] }}</strong></div>
                        </section>

                        <section class="pendencias-clean-modal-section">
                            <h3>Descrição</h3>
                            <p>{{ $pendenciaSelecionada['descricao'] }}</p>
                        </section>

                        <section class="pendencias-clean-modal-split">
                            <div class="pendencias-clean-modal-section">
                                <h3>Contexto operacional</h3>
                                <dl>
                                    <div><dt>Tipo/Categoria</dt><dd>{{ $pendenciaSelecionada['tipo'] }}</dd></div>
                                    <div><dt>Risco</dt><dd>{{ $pendenciaSelecionada['risco'] }}</dd></div>
                                    <div><dt>Tempo estimado</dt><dd>{{ $pendenciaSelecionada['tempo_estimado'] }}</dd></div>
                                    <div><dt>Tempo real</dt><dd>{{ $pendenciaSelecionada['tempo_real'] }}</dd></div>
                                    <div><dt>Última atualização</dt><dd>{{ $pendenciaSelecionada['updated_at'] ?? 'Não informado' }}</dd></div>
                                </dl>
                            </div>

                            <div class="pendencias-clean-modal-section">
                                <h3>Cliente / Portal</h3>
                                <dl>
                                    <div><dt>Cliente</dt><dd>{{ $pendenciaSelecionada['portal_cliente'] ?? 'Não informado' }}</dd></div>
                                    <div><dt>E-mail</dt><dd>{{ $pendenciaSelecionada['portal_email'] ?? 'Não informado' }}</dd></div>
                                    <div><dt>Última interação</dt><dd>{{ $pendenciaSelecionada['ultima_interacao_cliente'] ?? 'Sem interação registrada' }}</dd></div>
                                    <div><dt>Assinatura</dt><dd>{{ $pendenciaSelecionada['signature_status'] }}</dd></div>
                                    <div><dt>Arquivo</dt><dd>{{ $pendenciaSelecionada['arquivo'] ? 'Arquivo vinculado' : 'Sem arquivo vinculado' }}</dd></div>
                                </dl>
                            </div>
                        </section>

                        @if($pendenciaSelecionada['bloqueado'])
                            <section class="pendencias-clean-alert danger"><strong>Atenção:</strong> {{ $pendenciaSelecionada['bloqueio_resumo'] }}. Não conclua sem resolver o impedimento.</section>
                        @endif

                        @if(! empty($pendenciaSelecionada['dependencias']))
                            <section class="pendencias-clean-modal-section">
                                <h3>Dependências</h3>
                                <div class="pendencias-clean-dependencies">
                                    @foreach($pendenciaSelecionada['dependencias'] as $dependencia)
                                        <div class="{{ $dependencia['resolvida'] ? 'ok' : ($dependencia['bloqueante'] ? 'danger' : 'warning') }}">
                                            <strong>{{ $dependencia['titulo'] }}</strong>
                                            <span>{{ $dependencia['status'] }}{{ $dependencia['bloqueante'] ? ' · bloqueante' : '' }}</span>
                                            @if($dependencia['observacao'])<small>{{ $dependencia['observacao'] }}</small>@endif
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endif

                        @if(! empty($pendenciaSelecionada['observacao']))
                            <section class="pendencias-clean-modal-section">
                                <h3>Observação cadastrada</h3>
                                <p>{{ $pendenciaSelecionada['observacao'] }}</p>
                            </section>
                        @endif

                        <label class="pendencias-clean-note">
                            <span>Observação da decisão</span>
                            <textarea rows="3" wire:model.defer="observacaoAcao" placeholder="Obrigatório para reprovar. Útil para aprovação, solicitação de aprovação ou atualização do histórico."></textarea>
                            @error('observacaoAcao')<small>{{ $message }}</small>@enderror
                        </label>
                    </div>

                    <footer class="pendencias-clean-modal-actions">
                        <a href="{{ $pendenciaSelecionada['edit_url'] }}" class="secondary"><i class="bi bi-pencil-square"></i> Abrir em Tarefas Operacionais</a>
                        @if($pendenciaSelecionada['can_iniciar_sla'])<button type="button" class="info" wire:click="iniciarSlaPendenciaSelecionada" wire:confirm="Deseja iniciar o SLA desta pendência?" wire:loading.attr="disabled">Iniciar SLA</button>@endif
                        @if($pendenciaSelecionada['can_atualizar_sla'])<button type="button" class="warning" wire:click="atualizarSlaPendenciaSelecionada" wire:loading.attr="disabled">Atualizar SLA</button>@endif
                        @if($pendenciaSelecionada['can_finalizar_sla'])<button type="button" class="success" wire:click="finalizarSlaPendenciaSelecionada" wire:confirm="Deseja finalizar o SLA desta pendência?" wire:loading.attr="disabled">Finalizar SLA</button>@endif
                        @if($pendenciaSelecionada['can_solicitar_aprovacao'])<button type="button" class="warning" wire:click="solicitarAprovacaoPendenciaSelecionada" wire:loading.attr="disabled">Solicitar aprovação</button>@endif
                        @if($pendenciaSelecionada['can_aprovar'])<button type="button" class="success" wire:click="aprovarPendenciaSelecionada" wire:loading.attr="disabled">Aprovar</button><button type="button" class="danger" wire:click="reprovarPendenciaSelecionada" wire:loading.attr="disabled">Reprovar</button>@endif
                        @if($pendenciaSelecionada['can_concluir'])<button type="button" class="primary" wire:click="concluirPendenciaSelecionada" wire:confirm="Deseja concluir esta pendência?" wire:loading.attr="disabled">Concluir</button>@endif
                    </footer>
                </div>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('pendencias-lote8-feedback', () => {
                const page = document.querySelector('.pendencias-clean-page');
                if (! page) return;
                page.classList.add('pendencias-clean-just-updated');
                window.setTimeout(() => page.classList.remove('pendencias-clean-just-updated'), 1200);
            });
        });
    </script>
</x-filament-panels::page>
