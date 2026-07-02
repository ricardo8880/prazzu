<x-filament-panels::page>

    @php($etapas = $this->etapas())
    @php($itensRecentes = $this->itensRecentes())

    <div class="fo-page">
        <section class="fo-hero">
            <div class="fo-hero-content">
                <div class="fo-eyebrow">Fluxo operacional</div>
                <h2 class="fo-title">{{ $record->nome }}</h2>
                <p class="fo-subtitle">
                    {{ $record->descricao ?: 'Fluxo criado para padronizar as etapas operacionais dos itens de controle.' }}
                </p>

                <div class="fo-tags">
                    <span>{{ $this->tipoItemLabel() }}</span>
                    <span>{{ $record->padrao ? 'Fluxo padrão' : 'Fluxo personalizado' }}</span>
                    <span>{{ $record->ativo ? 'Ativo' : 'Inativo' }}</span>
                    @if($this->usuarioEhSuperAdmin())
                        <span>{{ $record->empresa?->razao_social ?? 'Sem empresa' }}</span>
                    @endif
                </div>
            </div>
        </section>

        <section class="fo-metrics">
            <article class="fo-metric-card">
                <span>Etapas cadastradas</span>
                <strong>{{ $etapas->count() }}</strong>
                <small>{{ $this->totalEtapasAtivas() }} etapas ativas</small>
            </article>

            <article class="fo-metric-card fo-metric-card--info">
                <span>Itens vinculados</span>
                <strong>{{ $this->totalItens() }}</strong>
                <small>Itens usando este fluxo</small>
            </article>

            <article class="fo-metric-card fo-metric-card--warning">
                <span>Pendentes</span>
                <strong>{{ $this->itensPendentes() }}</strong>
                <small>Ainda precisam avançar</small>
            </article>

            <article class="fo-metric-card fo-metric-card--success">
                <span>Concluídos</span>
                <strong>{{ $this->itensConcluidos() }}</strong>
                <small>Finalizados nesse fluxo</small>
            </article>
        </section>

        <section class="fo-layout">
            <article class="fo-panel fo-panel--timeline">
                <div class="fo-panel-header">
                    <div>
                        <h3>Etapas do fluxo</h3>
                        <p>Ordem operacional que o cliente consegue entender sem depender apenas da tabela.</p>
                    </div>
                </div>

                @if($etapas->isEmpty())
                    <div class="fo-empty">
                        <strong>Nenhuma etapa cadastrada</strong>
                        <span>Edite o fluxo e adicione as etapas para montar a jornada operacional.</span>
                    </div>
                @else
                    <div class="fo-timeline">
                        @foreach($etapas as $etapa)
                            <div class="fo-step {{ $etapa->ativo ? '' : 'fo-step--inactive' }}">
                                <div class="fo-step-number">{{ $etapa->ordem ?: $loop->iteration }}</div>

                                <div class="fo-step-body">
                                    <div class="fo-step-top">
                                        <h4>{{ $etapa->nome }}</h4>
                                        <span>{{ $etapa->ativo ? 'Ativa' : 'Inativa' }}</span>
                                    </div>

                                    <p>{{ $etapa->descricao ?: 'Sem descrição cadastrada para esta etapa.' }}</p>

                                    <div class="fo-step-meta">
                                        <span>Prazo: {{ $etapa->prazo_horas ? $etapa->prazo_horas . 'h' : 'Não definido' }}</span>
                                        <span>Aprovação: {{ $etapa->exige_aprovacao ? 'Sim' : 'Não' }}</span>
                                        <span>Responsável: {{ $etapa->responsavelPadrao?->nome ?? 'Não definido' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>

            <aside class="fo-panel fo-panel--side">
                <div class="fo-panel-header">
                    <div>
                        <h3>Resumo do fluxo</h3>
                        <p>Informações principais da configuração.</p>
                    </div>
                </div>

                <div class="fo-summary-list">
                    <div>
                        <span>Empresa</span>
                        <strong>{{ $record->empresa?->razao_social ?? '-' }}</strong>
                    </div>
                    <div>
                        <span>Tipo de item</span>
                        <strong>{{ $this->tipoItemLabel() }}</strong>
                    </div>
                    <div>
                        <span>Padrão</span>
                        <strong>{{ $record->padrao ? 'Sim' : 'Não' }}</strong>
                    </div>
                    <div>
                        <span>Status</span>
                        <strong>{{ $record->ativo ? 'Ativo' : 'Inativo' }}</strong>
                    </div>
                    <div>
                        <span>Criado em</span>
                        <strong>{{ optional($record->created_at)->format('d/m/Y H:i') ?: '-' }}</strong>
                    </div>
                    <div>
                        <span>Atualizado em</span>
                        <strong>{{ optional($record->updated_at)->format('d/m/Y H:i') ?: '-' }}</strong>
                    </div>
                </div>
            </aside>
        </section>

        <section class="fo-panel">
            <div class="fo-panel-header">
                <div>
                    <h3>Itens recentes neste fluxo</h3>
                    <p>Últimos itens de controle vinculados a esta configuração operacional.</p>
                </div>
            </div>

            @if($itensRecentes->isEmpty())
                <div class="fo-empty">
                    <strong>Nenhum item vinculado ainda</strong>
                    <span>Quando um item usar este fluxo, ele aparecerá aqui.</span>
                </div>
            @else
                <div class="fo-table-wrap">
                    <table class="fo-table">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Status</th>
                            <th>Responsável</th>
                            <th>Vencimento</th>
                            <th>Atualizado</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($itensRecentes as $item)
                            <tr>
                                <td title="{{ $item->titulo }}">{{ $item->titulo }}</td>
                                <td><span class="fo-status {{ $this->statusClasse($item->status) }}">{{ $this->statusLabel($item->status) }}</span></td>
                                <td>{{ $item->responsavel?->nome ?? '-' }}</td>
                                <td>{{ optional($item->data_vencimento)->format('d/m/Y') ?: '-' }}</td>
                                <td>{{ optional($item->updated_at)->format('d/m/Y H:i') ?: '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
