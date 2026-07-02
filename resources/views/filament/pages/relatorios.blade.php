<x-filament-panels::page>
    <div class="rel-premium-page">
        <section class="rel-hero">
            <div>
                <span class="rel-kicker"><i class="bi bi-bar-chart-line"></i> Relatórios Operacionais</span>
                <h1>Análise consolidada, não execução</h1>
                <p>Esta tela concentra indicadores, comparativos e exportações. Pendências, documentos e aprovações continuam sendo resolvidos nas abas operacionais corretas.</p>
            </div>

            <div class="rel-actions">
                <button type="button" wire:click="exportarCsv" wire:loading.attr="disabled" wire:target="exportarCsv"><i class="bi bi-filetype-csv"></i> CSV</button>
                <button type="button" wire:click="exportarExcel" wire:loading.attr="disabled" wire:target="exportarExcel"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</button>
                <button type="button" wire:click="exportarPdf" wire:loading.attr="disabled" wire:target="exportarPdf"><i class="bi bi-filetype-pdf"></i> PDF</button>
            </div>
        </section>

        <div class="rel-loading" wire:loading.flex>
            <span></span>
            <strong>Processando relatório...</strong>
        </div>

        <section class="rel-purpose-grid" aria-label="Propósito da seção">
            <article class="rel-purpose-card">
                <i class="bi bi-eye"></i>
                <div>
                    <strong>Ver</strong>
                    <span>Resumo consolidado por cliente, prazo, prioridade, status e responsável.</span>
                </div>
            </article>
            <article class="rel-purpose-card">
                <i class="bi bi-funnel"></i>
                <div>
                    <strong>Analisar</strong>
                    <span>Comparar gargalos, risco de atraso, produtividade e tendência sem alterar registros.</span>
                </div>
            </article>
            <article class="rel-purpose-card">
                <i class="bi bi-box-arrow-down"></i>
                <div>
                    <strong>Exportar</strong>
                    <span>Gerar CSV, Excel ou PDF para reunião, auditoria, controle interno ou prestação de contas.</span>
                </div>
            </article>
        </section>

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
                        <p>Os itens mais urgentes aparecem primeiro. Para resolver um item, use a aba dona do fluxo: Pendências, Documentos, Aprovações ou SLA.</p>
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
                        <h2>Clientes com maior impacto</h2>
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
                        <h2>Qualidade da base</h2>
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
                    <h2>Checklist de leitura do relatório</h2>
                    <p>Use este bloco para validar se a leitura do relatório está confiável antes de exportar ou apresentar os dados.</p>
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
