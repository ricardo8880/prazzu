<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/trabalho-pages.css') }}?v=20260504-drop-sla">

    @php
        $resumo = $this->getResumo();
        $itens = $this->getItensCriticos();
        $selecionado = $this->getItemSelecionado();
    @endphp

    <div class="tp-page">
        <div class="tp-hero">
            <div>
                <span class="tp-eyebrow">TRABALHO</span>
                <h2>SLA e Prazos</h2>
                <p>Controle visual de SLA, limites de atendimento e itens críticos com prazo vencendo.</p>
            </div>
        </div>

        <div class="tp-metrics tp-metrics-4">
            <div class="tp-card"><span>Com SLA</span><strong>{{ $resumo['com_sla'] }}</strong><small>itens monitorados</small></div>
            <div class="tp-card"><span>Em andamento</span><strong>{{ $resumo['em_andamento'] }}</strong><small>dentro do fluxo</small></div>
            <div class="tp-card tp-danger"><span>Vencidos</span><strong>{{ $resumo['vencidos'] }}</strong><small>fora do prazo</small></div>
            <div class="tp-card tp-success"><span>Concluídos</span><strong>{{ $resumo['concluidos'] }}</strong><small>SLA encerrado</small></div>
        </div>

        <x-filament::section>
            <x-slot name="heading">Fila crítica de SLA</x-slot>
            <x-slot name="description">Clique em uma linha para abrir o resumo do item, acompanhar o SLA e acessar o cadastro completo.</x-slot>

            <div class="tp-table-wrap">
                <table class="tp-table tp-sla-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Status</th>
                            <th>SLA</th>
                            <th>Limite</th>
                            <th>Responsável</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($itens as $item)
                            <tr wire:click="abrirItem({{ $item['id'] }})" class="tp-table-clickable">
                                <td><button type="button" class="tp-table-link-button">{{ $item['titulo'] }}</button><small>{{ $item['empresa'] }}</small></td>
                                <td><span class="tp-badge @if($item['status'] === 'Vencido') tp-badge-danger @endif">{{ $item['status'] }}</span></td>
                                <td>{{ $item['sla'] }}</td>
                                <td>{{ $item['limite'] }}</td>
                                <td>{{ $item['responsavel'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="tp-empty">Nenhum item com SLA em aberto.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        @if($modalAberto && $selecionado)
            <div class="tp-modal-backdrop" wire:click="fecharModal">
                <div class="tp-modal tp-sla-modal" wire:click.stop>
                    <div class="tp-modal-header">
                        <div>
                            <span class="tp-eyebrow">{{ $selecionado['tipo'] }} • {{ $selecionado['prioridade'] }}</span>
                            <h3>{{ $selecionado['titulo'] }}</h3>
                            <p>{{ $selecionado['empresa'] }} • {{ $selecionado['responsavel'] }}</p>
                        </div>
                        <button type="button" wire:click="fecharModal" class="tp-modal-close">×</button>
                    </div>

                    <div class="tp-modal-body">
                        <div class="tp-modal-grid">
                            <div class="tp-info-card @if($selecionado['vencido']) danger @endif">
                                <span>Status do SLA</span>
                                <strong>{{ $selecionado['status'] }}</strong>
                                <small>{{ $selecionado['tempo_restante'] }}</small>
                            </div>
                            <div class="tp-info-card">
                                <span>Limite</span>
                                <strong>{{ $selecionado['limite'] }}</strong>
                                <small>SLA contratado: {{ $selecionado['sla'] }}</small>
                            </div>
                            <div class="tp-info-card">
                                <span>Checklist</span>
                                <strong>{{ $selecionado['checklists_concluidos'] }}/{{ $selecionado['checklists_total'] }}</strong>
                                <small>{{ $selecionado['checklists_percentual'] }}% concluído</small>
                            </div>
                        </div>

                        <div class="tp-detail-card">
                            <div class="tp-detail-title">
                                <strong>Andamento do checklist</strong>
                                <span>{{ $selecionado['checklists_percentual'] }}%</span>
                            </div>
                            <div class="tp-progress tp-progress-large"><i style="width: {{ $selecionado['checklists_percentual'] }}%"></i></div>

                            <div class="tp-check-mini-list">
                                @forelse($selecionado['checklists'] as $checklist)
                                    <div class="tp-check-mini @if($checklist['concluido']) done @endif">
                                        <span>{{ $checklist['concluido'] ? '✓' : '•' }}</span>
                                        <p>{{ $checklist['titulo'] }}</p>
                                    </div>
                                @empty
                                    <div class="tp-empty">Nenhum checklist cadastrado nesse item.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="tp-sla-modal-columns">
                            <div class="tp-detail-card">
                                <div class="tp-detail-title">
                                    <strong>Detalhes principais</strong>
                                </div>
                                <div class="tp-info-list">
                                    <p><span>Categoria</span><b>{{ $selecionado['categoria'] }}</b></p>
                                    <p><span>Status interno</span><b>{{ $selecionado['sla_status'] }}</b></p>
                                    <p><span>Vencimento</span><b>{{ $selecionado['data_vencimento'] }}</b></p>
                                    <p><span>Concluído em</span><b>{{ $selecionado['sla_concluido_em'] }}</b></p>
                                    <p><span>Criado em</span><b>{{ $selecionado['criado_em'] }}</b></p>
                                    <p><span>Atualizado em</span><b>{{ $selecionado['atualizado_em'] }}</b></p>
                                </div>
                            </div>

                            <div class="tp-detail-card">
                                <div class="tp-detail-title">
                                    <strong>Descrição e observação</strong>
                                </div>
                                <div class="tp-text-block">
                                    <p>{!! nl2br(e($selecionado['descricao'])) !!}</p>
                                    @if($selecionado['observacao'])
                                        <p><strong>Observação:</strong> {!! nl2br(e($selecionado['observacao'])) !!}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tp-modal-footer">
                        <x-filament::button color="gray" wire:click="fecharModal">
                            Fechar
                        </x-filament::button>
                        <x-filament::button tag="a" href="{{ $selecionado['url'] }}" color="warning" icon="heroicon-o-arrow-top-right-on-square">
                            Ir para o produto
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
