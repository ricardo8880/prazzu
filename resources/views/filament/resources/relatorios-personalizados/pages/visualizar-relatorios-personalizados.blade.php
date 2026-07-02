<x-filament-panels::page>
    @php($grupos = $this->relatoriosPorEmpresa())

    <div class="rp-page">
        <div class="rp-hero">
            <div>
                <div class="rp-eyebrow">Plano Business</div>
                <h2 class="rp-title">Relatórios Personalizados</h2>
                <p class="rp-subtitle">
                    Visualize seus relatórios como painéis: métricas, gráfico por status e prévia dos registros conforme as colunas configuradas.
                </p>
            </div>
        </div>

        @if ($grupos->isEmpty())
            <div class="rp-empty-card">
                <div class="rp-empty-icon">▤</div>
                <h3>Nenhum relatório ativo configurado</h3>
                <p>Crie um relatório com colunas e filtros para visualizar os dados aqui.</p>

                @if (\App\Filament\Resources\RelatoriosPersonalizados\RelatorioPersonalizadoResource::canCreate())
                    <a class="rp-button" href="{{ \App\Filament\Resources\RelatoriosPersonalizados\RelatorioPersonalizadoResource::getUrl('create') }}">
                        Criar primeiro relatório
                    </a>
                @endif
            </div>
        @else
            <div class="rp-groups">
                @foreach ($grupos as $empresaNome => $relatorios)
                    <section class="rp-group">
                        @if (Filament\Facades\Filament::auth()->user()?->isSuperAdmin())
                            <div class="rp-group-header">
                                <h3>{{ $empresaNome }}</h3>
                                <p>Relatórios ativos dessa empresa.</p>
                            </div>
                        @endif

                        <div class="rp-grid">
                            @foreach ($relatorios as $relatorio)
                                @php($metricas = $this->metricas($relatorio))
                                @php($dadosGrafico = $this->dadosGrafico($relatorio))
                                @php($maiorValorGrafico = $this->maiorValorGrafico($relatorio))
                                @php($dadosTabela = $this->dadosTabela($relatorio))

                                <article class="rp-report {{ $this->classeRelatorio($relatorio) }}">
                                    <div class="rp-report-top">
                                        <div>
                                            <span class="rp-report-source">{{ $this->labelFonte($relatorio) }}</span>
                                            <h4>{{ $relatorio->nome }}</h4>
                                            @if ($relatorio->descricao)
                                                <p>{{ $relatorio->descricao }}</p>
                                            @endif
                                        </div>

                                        <span class="rp-report-type">{{ $this->labelFormato($relatorio) }}</span>
                                    </div>

                                    <div class="rp-metrics">
                                        <div class="rp-metric">
                                            <span>Total</span>
                                            <strong>{{ number_format((int) ($metricas['total'] ?? 0), 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="rp-metric">
                                            <span>Pendentes</span>
                                            <strong>{{ number_format((int) ($metricas['pendentes'] ?? 0), 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="rp-metric">
                                            <span>Vencidos</span>
                                            <strong>{{ number_format((int) ($metricas['vencidos'] ?? 0), 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="rp-metric">
                                            <span>Concluídos</span>
                                            <strong>{{ number_format((int) ($metricas['concluidos'] ?? 0), 0, ',', '.') }}</strong>
                                        </div>
                                    </div>

                                    <div class="rp-content-grid">
                                        <div class="rp-panel">
                                            <div class="rp-panel-title">Distribuição por status</div>

                                            @if ($dadosGrafico->isEmpty())
                                                <div class="rp-empty-content">Sem dados para montar o gráfico.</div>
                                            @else
                                                <div class="rp-chart" aria-label="Gráfico do relatório {{ $relatorio->nome }}">
                                                    @foreach ($dadosGrafico as $linha)
                                                        @php($valorGrafico = (float) ($linha['valor'] ?? 0))
                                                        @php($percentualGrafico = max(4, min(100, ($valorGrafico / $maiorValorGrafico) * 100)))

                                                        <div class="rp-chart-row">
                                                            <div class="rp-chart-label" title="{{ $linha['label'] ?? 'Sem label' }}">
                                                                {{ $linha['label'] ?? 'Sem label' }}
                                                            </div>
                                                            <div class="rp-chart-track">
                                                                <div class="rp-chart-bar" style="width: {{ $percentualGrafico }}%"></div>
                                                            </div>
                                                            <div class="rp-chart-value">{{ number_format($valorGrafico, 0, ',', '.') }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <div class="rp-panel">
                                            <div class="rp-panel-title">Prévia do relatório</div>

                                            @if ($dadosTabela->isEmpty())
                                                <div class="rp-empty-content">Sem registros para exibir.</div>
                                            @else
                                                <div class="rp-table-wrap">
                                                    <table class="rp-table">
                                                        <thead>
                                                        <tr>
                                                            @foreach (array_keys($dadosTabela->first()) as $coluna)
                                                                <th>{{ $coluna }}</th>
                                                            @endforeach
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach ($dadosTabela as $linha)
                                                            <tr>
                                                                @foreach ($linha as $valor)
                                                                    <td title="{{ $valor }}">{{ $valor }}</td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="rp-report-footer">
                                        <span>{{ $relatorio->colunas_count ?? $relatorio->colunas->count() }} colunas</span>
                                        <span>{{ $relatorio->filtros_count ?? $relatorio->filtros->count() }} filtros</span>
                                        <span>{{ $empresaNome }}</span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
