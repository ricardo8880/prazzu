<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/relatorios-premium.css') }}">

    <div class="rel-premium-page">
        <section class="rel-hero">
            <div>
                <span class="rel-kicker">Relatórios, segurança e validação final</span>
                <h1>Central operacional de relatórios</h1>
                <p>Documentos vencidos, vencendo, aprovações, assinaturas, produtividade e visão por cliente usando dados reais do banco.</p>
            </div>

            <div class="rel-actions">
                <button type="button" wire:click="exportarCsv" wire:loading.attr="disabled" wire:target="exportarCsv">CSV</button>
                <button type="button" wire:click="exportarExcel" wire:loading.attr="disabled" wire:target="exportarExcel">Excel</button>
                <button type="button" wire:click="exportarPdf" wire:loading.attr="disabled" wire:target="exportarPdf">PDF</button>
            </div>
        </section>

        <div class="rel-loading" wire:loading.flex>
            <span></span>
            <strong>Processando relatório...</strong>
        </div>

        <section class="rel-summary-grid">
            @foreach (($resumo ?? []) as $card)
                <article class="rel-stat rel-stat--{{ $card['tone'] ?? 'info' }}">
                    <span>{{ $card['label'] }}</span>
                    <strong>{{ $card['value'] }}</strong>
                    <small>{{ $card['hint'] }}</small>
                </article>
            @endforeach
        </section>

        <section class="rel-tabs" aria-label="Tipos de relatório">
            @foreach (($tipos ?? []) as $tipo => $label)
                <button
                    type="button"
                    wire:click="selecionarRelatorio('{{ $tipo }}')"
                    class="{{ ($tipoAtual ?? '') === $tipo ? 'active' : '' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </section>

        <section class="rel-summary-grid compact">
            @foreach (($cards ?? []) as $card)
                <article class="rel-stat rel-stat--{{ $card['tone'] ?? 'info' }}">
                    <span>{{ $card['label'] }}</span>
                    <strong>{{ $card['value'] }}</strong>
                    <small>{{ $card['hint'] }}</small>
                </article>
            @endforeach
        </section>

        <section class="rel-grid two">
            <article class="rel-card large">
                <header class="rel-card-header">
                    <div>
                        <h2>{{ $tipos[$tipoAtual] ?? 'Relatório operacional' }}</h2>
                        <p>Os itens mais urgentes aparecem primeiro. Use a exportação quando precisar compartilhar ou analisar fora do sistema.</p>
                    </div>
                    <span class="rel-pill">{{ count($linhas ?? []) }} registro(s)</span>
                </header>

                <div class="rel-table-wrap">
                    <table class="rel-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Título</th>
                                <th>Status</th>
                                <th>Responsável</th>
                                <th>Prazo</th>
                                <th>Indicador</th>
                                <th>Prioridade</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($linhas ?? []) as $row)
                                <tr>
                                    <td><strong>{{ $row['cliente'] }}</strong><small>{{ $row['relatorio'] }}</small></td>
                                    <td>{{ $row['titulo'] }}<small>{{ $row['observacao'] }}</small></td>
                                    <td><span class="rel-badge">{{ $row['status'] }}</span></td>
                                    <td>{{ $row['responsavel'] }}</td>
                                    <td>{{ $row['vencimento'] }}<small>{{ $row['dias'] !== '-' ? $row['dias'].' dia(s)' : 'Sem data' }}</small></td>
                                    <td>{{ $row['indicador'] }}</td>
                                    <td><span class="rel-priority rel-priority--{{ str_replace('í', 'i', strtolower($row['prioridade'] ?? 'media')) }}">{{ ucfirst($row['prioridade'] ?? 'media') }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="rel-empty">
                                            <strong>Nenhum registro encontrado</strong>
                                            <p>Esse é um bom sinal para este relatório. Troque o tipo acima ou gere outra visão operacional.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <aside class="rel-side-stack">
                <article class="rel-card">
                    <header class="rel-card-header simple">
                        <h2>Clientes que pedem atenção</h2>
                    </header>
                    <div class="rel-list">
                        @forelse (($clientesCriticos ?? []) as $cliente)
                            <div class="rel-list-row">
                                <div>
                                    <strong>{{ $cliente['cliente'] }}</strong>
                                    <span>{{ $cliente['indicador'] }}</span>
                                </div>
                                <em>{{ $cliente['status'] }}</em>
                            </div>
                        @empty
                            <div class="rel-empty small">Nenhum cliente crítico encontrado agora.</div>
                        @endforelse
                    </div>
                </article>

                <article class="rel-card">
                    <header class="rel-card-header simple">
                        <h2>Segurança revisada</h2>
                        <span class="rel-score">{{ $seguranca['score'] ?? 0 }}%</span>
                    </header>
                    <div class="rel-checklist">
                        @foreach (($seguranca['checks'] ?? []) as $check)
                            <div class="rel-check {{ $check['ok'] ? 'ok' : 'warn' }}">
                                <span>{{ $check['ok'] ? '✓' : '!' }}</span>
                                <div>
                                    <strong>{{ $check['title'] }}</strong>
                                    <p>{{ $check['description'] }}</p>
                                    <small>{{ $check['action'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            </aside>
        </section>

        <section class="rel-card">
            <header class="rel-card-header">
                <div>
                    <h2>Rotina de validação final</h2>
                    <p>Checklist operacional para não deixar página quebrada, ação sem feedback, upload inseguro ou relatório lento.</p>
                </div>
                <span class="rel-score">{{ $validacao['score'] ?? 0 }}%</span>
            </header>

            <div class="rel-validation-grid">
                @foreach (($validacao['items'] ?? []) as $item)
                    <div class="rel-validation {{ $item['ok'] ? 'ok' : 'warn' }}">
                        <strong>{{ $item['title'] }}</strong>
                        <p>{{ $item['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-filament-panels::page>
