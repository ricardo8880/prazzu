<x-filament-panels::page>
    @php
        $ready = (bool) ($ready ?? false);
        $summary = $summary ?? [];
        $statusBoard = $statusBoard ?? [];
        $prioridadeResumo = $prioridadeResumo ?? [];
        $atendimentos = $atendimentos ?? [];
        $empresas = $empresas ?? [];
        $clientes = $clientes ?? [];
        $responsaveis = $responsaveis ?? [];
        $statusOptions = $statusOptions ?? [];
        $prioridadeOptions = $prioridadeOptions ?? [];
        $slaOptions = $slaOptions ?? ['todos' => 'Todos'];
        $clientesDaEmpresa = collect($clientes)->when($novoEmpresaId, fn ($c) => $c->where('empresa_id', (int) $novoEmpresaId))->values();
        $idsAtendimentosVisiveis = collect($atendimentos)->pluck('id')->map(fn ($id) => (string) $id)->values();
        $idsAtendimentosSelecionados = collect($atendimentosSelecionados ?? [])->map(fn ($id) => (string) $id)->values();
        $todosAtendimentosVisiveisSelecionados = $idsAtendimentosVisiveis->isNotEmpty() && $idsAtendimentosVisiveis->diff($idsAtendimentosSelecionados)->isEmpty();
        $totalAtendimentosSelecionados = $idsAtendimentosSelecionados->count();
    @endphp

    <div class="at-wrap at-reference-layout" x-data="{ criar: @entangle('createModalAberto').live, detalhe: @entangle('detailModalAberto').live, cadastroCliente: false }" wire:poll.visible.90s="loadData(true)">
        <section class="at-page-head at-reference-head">
            <div class="at-title-block">
                <span class="at-eyebrow">Central de suporte</span>
                <h1>Atendimentos</h1>
                <p>Gerencie e acompanhe todos os atendimentos da sua equipe</p>
            </div>

            <div class="at-head-actions">
                @if($lastRefreshAt)
                    <small class="at-last-sync">Atualizado em {{ $lastRefreshAt }}</small>
                @endif
                <button type="button" class="at-btn ghost" wire:click="sincronizarPortal" wire:loading.attr="disabled" wire:target="sincronizarPortal"><i class="bi bi-arrow-clockwise at-btn-icon" aria-hidden="true"></i> Atualizar</button>
                <button type="button" class="at-btn ghost" @click="cadastroCliente = true"><i class="bi bi-person-plus at-btn-icon" aria-hidden="true"></i> Link do cliente</button>
                <button type="button" class="at-btn" wire:click="abrirCriacao" wire:loading.attr="disabled" wire:target="abrirCriacao"><i class="bi bi-plus-lg at-btn-icon" aria-hidden="true"></i> Novo atendimento</button>
            </div>
        </section>

        @unless($ready)
            <section class="at-alert danger">
                <strong>Módulo aguardando banco de dados.</strong>
                <span>Execute <code>php artisan migrate</code> para aplicar a estrutura oficial da central de atendimentos.</span>
            </section>
        @endunless

        <section class="at-kpis at-kpis-saas at-reference-metrics">
            <button type="button" wire:click="filtrarFilaAtiva" class="at-kpi-button at-kpi-focus">
                <span class="at-kpi-icon danger"><i class="bi bi-inbox-fill" aria-hidden="true"></i></span>
                <strong>{{ $summary['abertos'] ?? 0 }}</strong>
                <span>Abertos</span>
                <small>Precisam de atenção</small>
            </button>
            <button type="button" wire:click="filtrarStatus('aguardando_cliente')" class="at-kpi-button at-kpi-focus warning">
                <span class="at-kpi-icon warning"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
                <strong>{{ $summary['aguardando_cliente'] ?? 0 }}</strong>
                <span>Aguardando cliente</span>
                <small>Resposta pendente</small>
            </button>
            <button type="button" wire:click="filtrarStatus('em_andamento')" class="at-kpi-button at-kpi-focus primary">
                <span class="at-kpi-icon primary"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i></span>
                <strong>{{ $summary['em_andamento'] ?? 0 }}</strong>
                <span>Em andamento</span>
                <small>Atendendo agora</small>
            </button>
            <button type="button" wire:click="filtrarStatus('resolvido')" class="at-kpi-button at-kpi-focus success">
                <span class="at-kpi-icon success"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
                <strong>{{ $summary['resolvidos_hoje'] ?? ($summary['resolvidos'] ?? 0) }}</strong>
                <span>Resolvidos hoje</span>
                <small>Ótimo trabalho!</small>
            </button>
        </section>

        <section class="at-workspace at-reference-workspace">
            <div class="at-main-column at-reference-main">
                <section class="at-filter-bar">
                    <label class="at-search-field">
                        <span>Buscar atendimento</span>
                        <input type="search" wire:model.live.debounce.450ms="search" placeholder="Buscar por cliente, assunto ou protocolo...">
                    </label>

                    <details class="at-filter-drawer">
                        <summary>Filtros <strong>{{ (($statusFilter ?? 'todos') !== 'todos' ? 1 : 0) + (($prioridadeFilter ?? 'todos') !== 'todos' ? 1 : 0) + (($slaFilter ?? 'todos') !== 'todos' ? 1 : 0) + (($responsavelFilter ?? 'todos') !== 'todos' ? 1 : 0) + (!empty($empresaFilter) ? 1 : 0) + (($origemFilter ?? 'todos') !== 'todos' ? 1 : 0) }}</strong></summary>
                        <div class="at-filter-grid">
                            <label><span>Status</span><select wire:model.live="statusFilter"><option value="todos">Todos</option><option value="ativos">Fila ativa</option>@foreach($statusOptions as $key => $meta)<option value="{{ $key }}">{{ $meta['label'] }}</option>@endforeach</select></label>
                            <label><span>Prioridade</span><select wire:model.live="prioridadeFilter"><option value="todos">Todas</option>@foreach($prioridadeOptions as $key => $meta)<option value="{{ $key }}">{{ $meta['label'] }}</option>@endforeach</select></label>
                            <label><span>SLA</span><select wire:model.live="slaFilter">@foreach($slaOptions as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                            <label><span>Responsável</span><select wire:model.live="responsavelFilter"><option value="todos">Todos</option><option value="sem_responsavel">Sem responsável</option>@foreach($responsaveis as $resp)<option value="{{ $resp['id'] }}">{{ $resp['nome'] }}</option>@endforeach</select></label>
                            @if(count($empresas) > 1)
                                <label><span>Empresa</span><select wire:model.live="empresaFilter"><option value="">Todas</option>@foreach($empresas as $empresa)<option value="{{ $empresa['id'] }}">{{ $empresa['nome'] }}</option>@endforeach</select></label>
                            @endif
                            <label><span>Origem</span><select wire:model.live="origemFilter"><option value="todos">Todas</option><option value="manual">Manual</option><option value="portal">Portal</option><option value="whatsapp">WhatsApp</option><option value="email">E-mail</option><option value="telefone">Telefone</option></select></label>
                            <label><span>Ordenar</span><select wire:model.live="sortBy"><option value="recentes">Atualização</option><option value="sla">SLA mais próximo</option><option value="prioridade">Prioridade</option><option value="cliente">Cliente</option></select></label>
                        </div>
                    </details>

                    <button type="button" class="at-clear-link" wire:click="resetarFiltros" wire:loading.attr="disabled" wire:target="resetarFiltros">Limpar filtros</button>
                </section>

                <article class="at-card at-table-card at-queue-card">
                    <header class="at-list-head at-reference-list-head">
                        <div>
                            <h2>Atendimentos recentes</h2>
                            <p>{{ count($atendimentos) }} atendimento(s) na fila. Priorize quem precisa de resposta agora.</p>
                        </div>
                        <div class="at-list-pills" aria-label="Resumo rápido da fila">
                            <span><strong>{{ $summary['abertos'] ?? 0 }}</strong> abertos</span>
                            <span><strong>{{ $summary['em_andamento'] ?? 0 }}</strong> em andamento</span>
                            <span><strong>{{ $summary['aguardando_cliente'] ?? 0 }}</strong> aguardando</span>
                        </div>
                    </header>

                    @if($totalAtendimentosSelecionados > 0)
                        <div class="at-bulk-actions" role="region" aria-label="Ações dos atendimentos selecionados">
                            <div class="at-bulk-actions-info">
                                <i class="bi bi-check2-square" aria-hidden="true"></i>
                                <strong>{{ $totalAtendimentosSelecionados }}</strong>
                                <span>{{ $totalAtendimentosSelecionados === 1 ? 'atendimento selecionado' : 'atendimentos selecionados' }}</span>
                            </div>

                            <button
                                type="button"
                                class="at-bulk-delete-btn"
                                wire:click="apagarAtendimentosSelecionados"
                                wire:loading.attr="disabled"
                                wire:target="apagarAtendimentosSelecionados"
                                wire:confirm="Apagar os atendimentos selecionados? Essa ação não pode ser desfeita."
                            >
                                <i class="bi bi-trash3" aria-hidden="true"></i>
                                Apagar
                            </button>
                        </div>
                    @endif

                    <div class="at-table-wrap at-queue-wrap">
                        <table class="at-table at-queue-table">
                            <colgroup>
                                <col class="at-col-select">
                                <col class="at-col-atendimento">
                                <col class="at-col-cliente">
                                <col class="at-col-prioridade">
                                <col class="at-col-status">
                                <col class="at-col-status">
                                <col class="at-col-atualizacao">
                                <col class="at-col-acoes">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="at-select-col"><input type="checkbox" class="at-fake-checkbox" aria-label="Selecionar todos os atendimentos visíveis" wire:click="alternarSelecaoVisivel" @checked($todosAtendimentosVisiveisSelecionados)></th>
                                    <th>Atendimento</th>
                                    <th>Cliente</th>
                                    <th>Prioridade</th>
                                    <th>Status</th>
                                    <th>Aguardando</th>
                                    <th>Tempo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($atendimentos as $item)
                                    <tr wire:key="atendimento-row-{{ $item['id'] }}" class="at-ticket-row {{ $item['sla_vencido'] ? 'is-late' : '' }} tone-{{ $item['prioridade_tone'] ?? 'neutral' }}">
                                        <td class="at-select-col" data-label=""><input type="checkbox" class="at-fake-checkbox" aria-label="Selecionar atendimento #{{ $item['id'] }}" value="{{ $item['id'] }}" wire:model.live="atendimentosSelecionados"></td>
                                        <td class="at-ticket-cell" data-label="Atendimento">
                                            <div class="at-ticket-content">
                                                <span class="at-ticket-icon {{ $item['sla_vencido'] ? 'danger' : ($item['prioridade_tone'] ?? 'neutral') }}">
                                                    <i class="bi {{ $item['sla_vencido'] ? 'bi-exclamation-lg' : 'bi-chat-dots-fill' }}" aria-hidden="true"></i>
                                                </span>
                                                <div class="at-ticket-main">
                                                    <div class="at-ticket-title-row">
                                                        <strong><span class="at-ticket-id">#{{ $item['id'] }} - </span>{{ $item['titulo'] }}</strong>
                                                    </div>
                                                    <small>{{ \Illuminate\Support\Str::limit($item['descricao'], 92) }}</small>
                                                    <div class="at-ticket-meta-line">
                                                        <span>{{ $item['origem_label'] ?? ucfirst($item['origem'] ?? 'Manual') }}</span>
                                                        @if(!empty($item['portal_solicitacao_id']) || !empty($item['portal_mensagem_id']))
                                                            <span class="at-source-chip">Portal</span>
                                                        @endif
                                                        @if(! $item['responsavel_id'])
                                                            <span class="at-source-chip warning">Sem responsável</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="at-client-cell" data-label="Cliente">
                                            <div class="at-client-content">
                                                <span class="at-client-avatar" title="{{ $item['empresa_nome'] ?? 'Cliente' }}">{{ mb_strtoupper(mb_substr(trim($item['empresa_nome'] ?? 'Cliente'), 0, 1)) }}</span>
                                                <div class="at-client-main">
                                                    <strong>{{ $item['empresa_nome'] }}</strong>
                                                    <small>{{ $item['empresa_email'] ?? $item['cliente_email'] ?? 'Sem e-mail' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Prioridade"><span class="at-badge at-badge-dot {{ $item['prioridade_tone'] }}">{{ $item['prioridade_label'] }}</span></td>
                                        <td data-label="Status"><span class="at-badge at-badge-soft {{ $item['status_tone'] }}">{{ $item['status_label'] }}</span></td>
                                        <td data-label="Aguardando">
                                            <span class="at-badge at-badge-soft {{ $item['aguardando_tone'] ?? 'neutral' }}">{{ $item['aguardando_label'] ?? '-' }}</span>
                                            <div class="at-ticket-meta-line at-ticket-meta-line-spaced">
                                                <span>{{ $item['responsavel_nome'] ?? 'Sem responsável' }}</span>
                                            </div>
                                        </td>
                                        <td class="at-time-cell" data-label="Tempo">
                                            <strong>{{ $item['tempo_aguardando_detalhe'] ?? ($item['updated_at'] ?? '-') }}</strong>
                                            <small class="{{ $item['sla_vencido'] ? 'danger' : '' }}">{{ $item['sla_texto'] }}</small>
                                        </td>
                                        <td class="at-actions-cell" data-label="Ações">
                                            <div class="at-actions at-row-actions">
                                                <button type="button" class="at-open-btn" wire:click="selecionarAtendimento({{ $item['id'] }})">Abrir</button>
                                                <details class="at-more-menu">
                                                    <summary class="at-more-btn" aria-label="Mais ações"><i class="bi bi-three-dots" aria-hidden="true"></i></summary>
                                                    <div class="at-more-menu-panel">
                                                        <button type="button" wire:click="selecionarAtendimento({{ $item['id'] }})"><i class="bi bi-eye" aria-hidden="true"></i> Ver detalhes</button>
                                                        @if(! $item['responsavel_id'])
                                                            <button type="button" wire:click="assumirAtendimento({{ $item['id'] }})"><i class="bi bi-person-check" aria-hidden="true"></i> Assumir</button>
                                                        @endif
                                                        @if(! in_array($item['status'] ?? '', ['resolvido', 'fechado', 'cancelado'], true))
                                                            <button type="button" wire:click="mudarStatusRapido({{ $item['id'] }}, 'aguardando_cliente')"><i class="bi bi-hourglass-split" aria-hidden="true"></i> Aguardar cliente</button>
                                                            <button type="button" wire:click="resolverAtendimento({{ $item['id'] }})"><i class="bi bi-check2-circle" aria-hidden="true"></i> Resolver</button>
                                                        @else
                                                            <button type="button" wire:click="reabrirAtendimento({{ $item['id'] }})"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Reabrir</button>
                                                        @endif
                                                    </div>
                                                </details>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8"><div class="at-empty">Nenhum atendimento encontrado com os filtros atuais.</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>

            <aside class="at-side-panel at-reference-aside">
                <article class="at-side-card at-filter-panel-card">
                    <header class="at-filter-panel-head">
                        <div>
                            <h3>Filtros rápidos</h3>
                        </div>
                        <span class="at-filter-icon"><i class="bi bi-sliders2" aria-hidden="true"></i></span>
                    </header>

                    <details class="at-filter-section" open>
                        <summary class="at-filter-section-title">
                            <span>Status</span>
                            <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                        </summary>
                        <div class="at-filter-section-body">
                            @foreach($statusBoard as $statusItem)
                                <button type="button" class="at-filter-check-row {{ ($statusFilter === $statusItem['key']) ? 'active' : '' }}" wire:click="filtrarStatus('{{ $statusItem['key'] }}')">
                                    <span class="at-check-box"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
                                    <span class="at-dot {{ $statusItem['tone'] }}"></span>
                                    <span>{{ $statusItem['label'] }}</span>
                                    <strong>{{ $statusItem['total'] }}</strong>
                                </button>
                            @endforeach
                        </div>
                    </details>

                    <details class="at-filter-section">
                        <summary class="at-filter-section-title">
                            <span>Prioridade</span>
                            <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                        </summary>
                        <div class="at-filter-section-body">
                            @foreach($prioridadeResumo as $prioridadeItem)
                                <button type="button" class="at-filter-check-row {{ ($prioridadeFilter === $prioridadeItem['key']) ? 'active' : '' }}" wire:click="$set('prioridadeFilter', '{{ $prioridadeItem['key'] }}')">
                                    <span class="at-check-box"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
                                    <span class="at-dot {{ $prioridadeItem['tone'] ?? 'neutral' }}"></span>
                                    <span>{{ $prioridadeItem['label'] }}</span>
                                    <strong>{{ $prioridadeItem['total'] }}</strong>
                                </button>
                            @endforeach
                        </div>
                    </details>

                    <details class="at-filter-section">
                        <summary class="at-filter-section-title">
                            <span>Responsável</span>
                            <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                        </summary>
                        <div class="at-filter-section-body">
                            <label class="at-side-select"><span>Filtrar responsável</span><select wire:model.live="responsavelFilter"><option value="todos">Todos</option><option value="sem_responsavel">Sem responsável</option>@foreach($responsaveis as $resp)<option value="{{ $resp['id'] }}">{{ $resp['nome'] }}</option>@endforeach</select></label>
                        </div>
                    </details>

                    <details class="at-filter-section">
                        <summary class="at-filter-section-title">
                            <span>Aguardando quem</span>
                            <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                        </summary>
                        <div class="at-filter-section-body">
                            <label class="at-side-select">
                                <span>Filtrar espera</span>
                                <select wire:model.live="aguardandoFilter">
                                    <option value="todos">Todos</option>
                                    @foreach(($aguardandoOptions ?? []) as $key => $label)
                                        @if($key !== 'todos')
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </details>

                    <details class="at-filter-section">
                        <summary class="at-filter-section-title">
                            <span>Origem</span>
                            <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                        </summary>
                        <div class="at-filter-section-body">
                            <label class="at-side-select"><span>Canal de entrada</span><select wire:model.live="origemFilter"><option value="todos">Todas</option><option value="manual">Manual</option><option value="portal">Portal</option><option value="whatsapp">WhatsApp</option><option value="email">E-mail</option><option value="telefone">Telefone</option></select></label>
                        </div>
                    </details>

                    <details class="at-filter-section">
                        <summary class="at-filter-section-title">
                            <span>SLA</span>
                            <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                        </summary>
                        <div class="at-filter-section-body">
                            <label class="at-side-select"><span>Prazo</span><select wire:model.live="slaFilter">@foreach($slaOptions as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
                        </div>
                    </details>

                    @if(count($empresas) > 1)
                        <details class="at-filter-section">
                            <summary class="at-filter-section-title">
                                <span>Empresa</span>
                                <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                            </summary>
                            <div class="at-filter-section-body">
                                <label class="at-side-select"><span>Empresa</span><select wire:model.live="empresaFilter"><option value="">Todas</option>@foreach($empresas as $empresa)<option value="{{ $empresa['id'] }}">{{ $empresa['nome'] }}</option>@endforeach</select></label>
                            </div>
                        </details>
                    @endif

                    <button type="button" class="at-clear-filters-wide" wire:click="resetarFiltros" wire:loading.attr="disabled" wire:target="resetarFiltros"><i class="bi bi-x-lg" aria-hidden="true"></i> Limpar todos os filtros</button>
                </article>

                <article class="at-side-card at-tip-card at-reference-tip-card">
                    <strong><i class="bi bi-lightbulb-fill" aria-hidden="true"></i> Dica</strong>
                    <p>Use os filtros para encontrar rapidamente o que precisa.</p>
                </article>
            </aside>
        </section>



        <div class="at-modal" x-show="cadastroCliente" x-cloak>
            <div class="at-modal-card" @click.outside="cadastroCliente = false" x-data="{ copied: false }">
                <header>
                    <div>
                        <h2>Link de cadastro do cliente</h2>
                        <p>Envie este link para o cliente criar o próprio acesso ao Portal do Cliente já vinculado à empresa correta.</p>
                    </div>
                    <button type="button" @click="cadastroCliente = false">×</button>
                </header>

                <div class="at-form-grid">
                    @if(count($empresas) > 1)
                        <label>
                            <span>Empresa do link</span>
                            <select wire:model.live="portalCadastroEmpresaId">
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa['id'] }}">{{ $empresa['nome'] ?? ('Empresa #' . $empresa['id']) }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif

                    <label>
                        <span>Empresa vinculada</span>
                        <input type="text" readonly value="{{ $portalCadastroClienteEmpresaNome ?: 'Nenhuma empresa selecionada' }}">
                    </label>
                </div>

                <label class="at-full">
                    <span>Link para enviar ao cliente</span>
                    <input type="text" readonly x-ref="linkCadastroCliente" value="{{ $portalCadastroClienteLink ?: 'Nenhuma empresa disponível para gerar o link.' }}">
                </label>

                <section class="at-alert at-client-link-help">
                    <strong>Como usar</strong>
                    <span>Copie o link e envie para o cliente. Ao preencher o formulário, o cadastro dele será separado automaticamente pela empresa selecionada acima.</span>
                </section>

                <footer>
                    <button type="button" class="at-btn ghost" @click="cadastroCliente = false">Fechar</button>
                    @if($portalCadastroClienteLink)
                        <button type="button" class="at-btn ghost" wire:click="renovarLinkCadastroClientePortal" wire:loading.attr="disabled" wire:target="renovarLinkCadastroClientePortal">Renovar link</button>
                        <a href="{{ $portalCadastroClienteLink }}" target="_blank" rel="noopener" class="at-btn ghost">Abrir</a>
                        <button type="button" class="at-btn" @click="navigator.clipboard.writeText($refs.linkCadastroCliente.value); copied = true; setTimeout(() => copied = false, 1800)">
                            <i class="bi bi-clipboard-check at-btn-icon" aria-hidden="true"></i>
                            <span x-text="copied ? 'Copiado' : 'Copiar link'"></span>
                        </button>
                    @endif
                </footer>
            </div>
        </div>
        <div class="at-modal" x-show="criar" x-cloak>
            <div class="at-modal-card" @click.outside="criar = false">
                <header><div><h2>Novo atendimento</h2><p>Registre uma demanda interna vinculada ao cliente/empresa.</p></div><button type="button" @click="criar = false">×</button></header>
                <div class="at-form-grid">
                    <label><span>Empresa</span><select wire:model.live="novoEmpresaId"><option value="">Selecione</option>@foreach($empresas as $empresa)<option value="{{ $empresa['id'] }}">{{ $empresa['nome'] }}</option>@endforeach</select></label>
                    <label><span>Cliente CRM</span><select wire:model="novoClienteId"><option value="">Opcional</option>@foreach($clientesDaEmpresa as $cliente)<option value="{{ $cliente['id'] }}">{{ $cliente['nome'] }}</option>@endforeach</select></label>
                    <label><span>Responsável</span><select wire:model="novoResponsavelId"><option value="">Sem responsável</option>@foreach($responsaveis as $resp)<option value="{{ $resp['id'] }}">{{ $resp['nome'] }}</option>@endforeach</select></label>
                    <label><span>Prioridade</span><select wire:model="novoPrioridade">@foreach($prioridadeOptions as $key => $meta)<option value="{{ $key }}">{{ $meta['label'] }} · SLA {{ $meta['sla'] ?? '-' }}h</option>@endforeach</select></label>
                    <label><span>Origem</span><select wire:model="novoOrigem"><option value="manual">Manual</option><option value="portal">Portal</option><option value="whatsapp">WhatsApp</option><option value="email">E-mail</option><option value="telefone">Telefone</option></select></label>
                    <label><span>Canal</span><select wire:model="novoCanal"><option value="interno">Interno</option><option value="portal">Portal</option><option value="whatsapp">WhatsApp</option><option value="email">E-mail</option><option value="telefone">Telefone</option></select></label>
                </div>
                <label class="at-full"><span>Título</span><input type="text" wire:model="novoTitulo" maxlength="180" placeholder="Ex.: Cliente solicitou ajuste no contrato"></label>
                <label class="at-full"><span>Descrição</span><textarea wire:model="novoDescricao" rows="5" placeholder="Descreva o contexto, o que precisa ser feito e qualquer detalhe importante."></textarea></label>
                <footer><button type="button" class="at-btn ghost" @click="criar = false">Cancelar</button><button type="button" class="at-btn" wire:click="criarAtendimento" wire:loading.attr="disabled" wire:target="criarAtendimento">Criar atendimento</button></footer>
            </div>
        </div>



        @include('filament.pages.partials.atendimentos-detail-modal')
        <div class="at-loading-panel" wire:loading.delay.longer wire:target="loadData,sincronizarPortal,criarAtendimento,salvarDetalhe,responderCliente,adicionarInteracao,resolverComResumo,encerrarComMotivo,criarPendenciaDoAtendimento,solicitarDocumentoDoAtendimento">
            Atualizando central de atendimentos...
        </div>
    </div>


    <script>
        (() => {
            let atendimentoSocket = null;
            let atendimentoSocketKey = null;

            function loadSocketIo(url) {
                return new Promise((resolve, reject) => {
                    if (window.io) {
                        resolve(window.io);
                        return;
                    }

                    const src = String(url || '').replace(/\/$/, '') + '/socket.io/socket.io.js';
                    let script = document.getElementById('atendimentos-socket-io-client');

                    if (script) {
                        script.addEventListener('load', () => resolve(window.io), { once: true });
                        script.addEventListener('error', reject, { once: true });
                        return;
                    }

                    script = document.createElement('script');
                    script.id = 'atendimentos-socket-io-client';
                    script.src = src;
                    script.async = true;
                    script.onload = () => resolve(window.io);
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }

            async function emitirMensagemAtendimento(payload) {
                const config = payload?.socket || {};
                const message = payload?.message || {};

                if (!config.enabled || !config.url || !message.id) {
                    return;
                }

                try {
                    await loadSocketIo(config.url);

                    const key = [config.url, config.empresaId, config.room, config.token].join('|');
                    if (!atendimentoSocket || atendimentoSocketKey !== key || !atendimentoSocket.connected) {
                        if (atendimentoSocket) atendimentoSocket.disconnect();

                        atendimentoSocketKey = key;
                        atendimentoSocket = window.io(config.url, {
                            transports: ['websocket', 'polling'],
                            auth: {
                                empresaId: config.empresaId,
                                actor: config.actor || 'suporte',
                                token: config.token || '',
                                signature: config.signature || '',
                                room: config.room || '',
                            },
                        });
                    }

                    const emitNow = () => {
                        message.room = message.room || config.room || '';
                        message.actor = message.actor || config.actor || 'suporte';
                        atendimentoSocket.emit('chat:message:new', message);
                        atendimentoSocket.emit('chat:typing:stop', { actor: config.actor || 'suporte', nome: config.nome || 'Suporte', room: config.room || '' });
                    };

                    if (atendimentoSocket.connected) {
                        emitNow();
                    } else {
                        atendimentoSocket.once('connect', emitNow);
                    }
                } catch (error) {
                    console.warn('Não foi possível emitir a mensagem do atendimento via Socket.IO.', error);
                }
            }


            const atendimentoSessionKeepAliveUrl = '{{ route('admin.session.keepalive') }}';
            let atendimentoSessionKeepAliveTimer = null;

            function manterSessaoAtendimentosAtiva() {
                if (!atendimentoSessionKeepAliveUrl || document.hidden) {
                    return;
                }

                fetch(atendimentoSessionKeepAliveUrl, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                }).catch(() => {});
            }

            function iniciarKeepAliveAtendimentos() {
                if (atendimentoSessionKeepAliveTimer) {
                    clearInterval(atendimentoSessionKeepAliveTimer);
                }

                manterSessaoAtendimentosAtiva();
                atendimentoSessionKeepAliveTimer = setInterval(manterSessaoAtendimentosAtiva, 4 * 60 * 1000);
            }

            document.addEventListener('livewire:init', () => {
                iniciarKeepAliveAtendimentos();

                if (window.Livewire && typeof Livewire.hook === 'function') {
                    Livewire.hook('request', ({ fail }) => {
                        fail(({ status, preventDefault }) => {
                            if (status === 419) {
                                preventDefault();
                                manterSessaoAtendimentosAtiva();
                                setTimeout(() => window.location.reload(), 500);
                            }
                        });
                    });
                }

                Livewire.on('atendimento-chat-message-sent', (event) => {
                    emitirMensagemAtendimento(event?.payload || event?.[0]?.payload || event?.[0] || event);
                });
            });

            document.addEventListener('livewire:navigated', () => {
                if (atendimentoSocket) atendimentoSocket.disconnect();
                atendimentoSocket = null;
                atendimentoSocketKey = null;
            });
        })();
    </script>

</x-filament-panels::page>
