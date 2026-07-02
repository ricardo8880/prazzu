<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/contabilidade-ux-lote6.css') }}?v={{ file_exists(public_path('css/contabilidade-ux-lote6.css')) ? filemtime(public_path('css/contabilidade-ux-lote6.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/contabilidade-operacao-lote3.css') }}?v={{ file_exists(public_path('css/contabilidade-operacao-lote3.css')) ? filemtime(public_path('css/contabilidade-operacao-lote3.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/trabalho-pages.css') }}?v=20260513-lote5-empty-states">
    <link rel="stylesheet" href="{{ asset('css/tarefas-qa-standard.css') }}?v=20260513-lote7-visual">

    @php
        $dias = $this->getDias();
        $proximos = $this->getProximos();
        $atrasados = $this->getAtrasados();
        $semData = $this->getSemData();
        $resumo = $this->getResumo();
        $selecionado = $this->getItemSelecionado();
    @endphp

    <div class="tp-page">
        <section class="contabilidade-lote3-scope" aria-label="Propósito do Calendário Operacional">
            <div class="contabilidade-lote3-scope__top">
                <div>
                    <span class="contabilidade-lote3-eyebrow"><i class="bi bi-calendar3"></i> Calendário</span>
                    <h2>Mapa visual de vencimentos e agenda operacional</h2>
                    <p>Use para enxergar distribuição no mês e abrir o item certo. Não é a tela para explicar ou resolver pendências; ela direciona para a aba adequada.</p>
                </div>
                <div class="contabilidade-lote3-actions">
                    <a class="contabilidade-lote3-action primary" href="{{ \App\Filament\Pages\SlaPrazos::getUrl() }}"><i class="bi bi-clock-history"></i> SLA e Prazos</a>
                    <a class="contabilidade-lote3-action" href="{{ \App\Filament\Pages\Pendencias::getUrl() }}"><i class="bi bi-list-check"></i> Pendências</a>
                </div>
            </div>
        </section>

        <div class="tp-action-loading" wire:loading.flex wire:target="mesAnterior,proximoMes,voltarParaHoje,abrirItem,fecharModal,alterarStatusSelecionado">
            <span class="tp-spinner"></span>
            <span>Atualizando calendário...</span>
        </div>
        <div class="tp-hero tp-hero-calendar">
            <div>
                <span class="tp-eyebrow">TRABALHO</span>
                <h2>Calendário operacional</h2>
                <p>Visualize vencimentos, agenda da semana e tarefas sem data. Esta tela organiza o tempo; a execução permanece na Central Operacional.</p>
            </div>

            <div class="tp-calendar-actions">
                <x-filament::button color="gray" wire:click="mesAnterior" wire:loading.attr="disabled" wire:target="mesAnterior" icon="heroicon-o-chevron-left">
                    Mês anterior
                </x-filament::button>
                <div class="tp-month">{{ $this->getMesAtual() }}</div>
                <x-filament::button color="gray" wire:click="proximoMes" wire:loading.attr="disabled" wire:target="proximoMes" icon="heroicon-o-chevron-right" icon-position="after">
                    Próximo mês
                </x-filament::button>
                <x-filament::button color="warning" wire:click="voltarParaHoje" wire:loading.attr="disabled" wire:target="voltarParaHoje" icon="heroicon-o-calendar-days">
                    Hoje
                </x-filament::button>
            </div>
        </div>

        <div class="tp-metrics-grid tp-calendar-metrics">
            <div class="tp-metric-card">
                <span>Total no mês</span>
                <strong>{{ $resumo['total'] }}</strong>
                <small>Itens com vencimento no mês aberto.</small>
            </div>
            <div class="tp-metric-card danger">
                <span>Atrasados</span>
                <strong>{{ $resumo['atrasados'] }}</strong>
                <small>Itens pendentes com prazo vencido.</small>
            </div>
            <div class="tp-metric-card warning">
                <span>Hoje</span>
                <strong>{{ $resumo['hoje'] }}</strong>
                <small>Demandas que vencem hoje.</small>
            </div>
            <div class="tp-metric-card success">
                <span>Concluídos</span>
                <strong>{{ $resumo['concluidos'] }}</strong>
                <small>Itens encerrados no mês aberto.</small>
            </div>
        </div>

        <div class="tp-grid tp-grid-calendar-pro">
            <x-filament::section>
                <x-slot name="heading">Agenda mensal</x-slot>
                <x-slot name="description">Clique em um item para abrir ações rápidas antes de ir para o cadastro completo.</x-slot>

                <div class="tp-calendar-grid tp-calendar-grid-pro">
                    @foreach($dias as $dia)
                        <div class="tp-calendar-day @if($dia['hoje']) is-today @endif @if($dia['fora_mes']) is-muted @endif @if($dia['atrasado']) has-late @endif">
                            <div class="tp-calendar-day-head">
                                <strong>{{ $dia['dia'] }}</strong>
                                <span>{{ $dia['semana'] }}</span>
                            </div>

                            <div class="tp-calendar-day-items">
                                @if($dia['total'] > 0)
                                    @php
                                        $primeiroItem = $dia['itens'][0];
                                        $itensOcultos = array_slice($dia['itens'], 1);
                                    @endphp

                                    <button type="button" wire:click="abrirItem({{ $primeiroItem['id'] }})" wire:loading.attr="disabled" wire:target="abrirItem({{ $primeiroItem['id'] }})" class="tp-calendar-card priority-{{ $primeiroItem['prioridade_raw'] }} @if($primeiroItem['atrasado']) is-late @endif">
                                        <span>{{ $primeiroItem['titulo'] }}</span>
                                        <small>{{ $primeiroItem['responsavel'] }} • {{ $primeiroItem['status'] }}</small>
                                    </button>

                                    @if($dia['total'] > 1)
                                        <details class="tp-calendar-more-wrap">
                                            <summary class="tp-more-items">+{{ $dia['total'] - 1 }} item(ns) no dia</summary>

                                            <div class="tp-calendar-more-popover">
                                                <div class="tp-calendar-more-head">
                                                    <strong>{{ $dia['dia'] }} • {{ $dia['semana'] }}</strong>
                                                    <span>{{ $dia['total'] }} item(ns)</span>
                                                </div>

                                                <div class="tp-calendar-more-list">
                                                    @foreach($dia['itens'] as $item)
                                                        <button type="button" wire:click="abrirItem({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="abrirItem({{ $item['id'] }})" class="tp-calendar-mini-card priority-{{ $item['prioridade_raw'] }} @if($item['atrasado']) is-late @endif">
                                                            <strong>{{ $item['titulo'] }}</strong>
                                                            <span>{{ $item['responsavel'] }} • {{ $item['status'] }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </details>
                                    @endif
                                @else
                                    <span class="tp-calendar-empty-day">Sem vencimentos</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>

            <div class="tp-calendar-side">
                <x-filament::section>
                    <x-slot name="heading">Próximos vencimentos</x-slot>

                    <div class="tp-list tp-list-compact">
                        @forelse($proximos as $item)
                            <button type="button" wire:click="abrirItem({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="abrirItem({{ $item['id'] }})" class="tp-list-row tp-list-button @if($item['atrasado']) is-danger @endif">
                                <div class="tp-list-main">
                                    <strong>{{ $item['titulo'] }}</strong>
                                    <span>{{ $item['data_humana'] }} • {{ $item['responsavel'] }}</span>
                                </div>
                                <b>{{ $item['data'] }}</b>
                            </button>
                        @empty
                            <div class="tp-empty tp-empty-small">
                                <strong>Nenhum vencimento futuro</strong>
                                <span>Itens com data de vencimento futura aparecerão aqui automaticamente.</span>
                            </div>
                        @endforelse
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">Atrasados</x-slot>

                    <div class="tp-list tp-list-compact">
                        @forelse($atrasados as $item)
                            <button type="button" wire:click="abrirItem({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="abrirItem({{ $item['id'] }})" class="tp-list-row tp-list-button is-danger">
                                <div class="tp-list-main">
                                    <strong>{{ $item['titulo'] }}</strong>
                                    <span>{{ $item['empresa'] }} • {{ $item['responsavel'] }}</span>
                                </div>
                                <b>{{ $item['data'] }}</b>
                            </button>
                        @empty
                            <div class="tp-empty tp-empty-small tp-empty-positive">
                                <strong>Nenhum atraso encontrado</strong>
                                <span>Ótimo. Não há itens pendentes vencidos para os filtros atuais.</span>
                            </div>
                        @endforelse
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">Sem data definida</x-slot>

                    <div class="tp-list tp-list-compact">
                        @forelse($semData as $item)
                            <button type="button" wire:click="abrirItem({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="abrirItem({{ $item['id'] }})" class="tp-list-row tp-list-button">
                                <div class="tp-list-main">
                                    <strong>{{ $item['titulo'] }}</strong>
                                    <span>{{ $item['tipo'] }} • {{ $item['status'] }}</span>
                                </div>
                            </button>
                        @empty
                            <div class="tp-empty tp-empty-small tp-empty-positive">
                                <strong>Nenhum item sem data</strong>
                                <span>Todos os itens visíveis possuem prazo definido.</span>
                            </div>
                        @endforelse
                    </div>
                </x-filament::section>
            </div>
        </div>

        @if($modalAberto && $selecionado)
            <div class="tp-modal-backdrop" wire:click="fecharModal">
                <div class="tp-modal tp-calendar-modal" wire:click.stop>
                    <div class="tp-modal-header">
                        <div>
                            <span class="tp-eyebrow">{{ $selecionado['tipo'] }} • {{ $selecionado['prioridade'] }}</span>
                            <h3>{{ $selecionado['titulo'] }}</h3>
                            <p>{{ $selecionado['empresa'] }} • {{ $selecionado['responsavel'] }}</p>
                        </div>
                        <button type="button" wire:click="fecharModal" wire:loading.attr="disabled" wire:target="fecharModal" class="tp-modal-close">×</button>
                    </div>

                    <div class="tp-modal-body">
                        <div class="tp-modal-grid">
                            <div class="tp-info-card @if($selecionado['atrasado']) danger @endif">
                                <span>Prazo</span>
                                <strong>{{ $selecionado['data'] }}</strong>
                                <small>{{ $selecionado['data_humana'] }}</small>
                            </div>
                            <div class="tp-info-card">
                                <span>Status</span>
                                <strong>{{ $selecionado['status'] }}</strong>
                                <small>{{ $selecionado['sla'] }}</small>
                            </div>
                            <div class="tp-info-card">
                                <span>Checklist</span>
                                <strong>{{ $selecionado['checklist_resumo'] }}</strong>
                                <small>{{ $selecionado['categoria'] }}</small>
                            </div>
                        </div>

                        <div class="tp-modal-section">
                            <h4>Descrição</h4>
                            <p>{{ $selecionado['descricao'] }}</p>
                            @if($selecionado['observacao'])
                                <p><strong>Observação:</strong> {{ $selecionado['observacao'] }}</p>
                            @endif
                        </div>

                        <div class="tp-modal-section">
                            <h4>Ações rápidas</h4>
                            <div class="tp-action-grid">
                                <x-filament::button size="sm" color="gray" wire:click="alterarStatusSelecionado('pendente')" wire:loading.attr="disabled" wire:target="alterarStatusSelecionado">
                                    Marcar pendente
                                </x-filament::button>
                                <x-filament::button size="sm" color="warning" wire:click="alterarStatusSelecionado('em_andamento')" wire:loading.attr="disabled" wire:target="alterarStatusSelecionado">
                                    Em andamento
                                </x-filament::button>
                                <x-filament::button size="sm" color="success" wire:click="alterarStatusSelecionado('concluido')" wire:loading.attr="disabled" wire:target="alterarStatusSelecionado">
                                    Concluir
                                </x-filament::button>
                            </div>
                        </div>

                        <div class="tp-modal-section">
                            <h4>Checklist</h4>
                            <div class="tp-check-mini-list">
                                @forelse($selecionado['checklists'] as $checklist)
                                    <div class="tp-check-mini @if($checklist['concluido']) done @endif">
                                        <span>{{ $checklist['concluido'] ? '✓' : '•' }}</span>
                                        <p>{{ $checklist['titulo'] }}</p>
                                    </div>
                                @empty
                                    <div class="tp-empty tp-empty-small">
                                        <strong>Checklist vazio</strong>
                                        <span>Abra o cadastro completo ou o Kanban para adicionar etapas neste item.</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="tp-modal-footer">
                        <x-filament::button color="gray" wire:click="fecharModal">
                            Fechar
                        </x-filament::button>
                        <x-filament::button tag="a" href="{{ $selecionado['url'] }}" color="warning" icon="heroicon-o-arrow-top-right-on-square">
                            Abrir cadastro completo
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
