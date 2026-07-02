<x-filament-panels::page>
    @php($grupos = $this->widgetsPorEmpresa())

    <div class="dc-page">
        <div class="dc-hero">
            <div>
                <div class="dc-eyebrow">Plano Business</div>
                <h2 class="dc-title">Dashboard Configurável</h2>
                <p class="dc-subtitle">
                    Acompanhe os indicadores configurados respeitando o tipo de cada widget: card, gráfico ou tabela.
                </p>
            </div>
        </div>

        @if ($grupos->isEmpty())
            <div class="dc-empty-card">
                <div class="dc-empty-icon">▦</div>
                <h3>Nenhum widget ativo configurado</h3>
                <p>Crie um widget para começar a visualizar o dashboard.</p>

                @if (\App\Filament\Resources\DashboardConfiguravel\DashboardWidgetConfiguracaoResource::canCreate())
                    <a class="dc-button" href="{{ \App\Filament\Resources\DashboardConfiguravel\DashboardWidgetConfiguracaoResource::getUrl('create') }}">
                        Criar primeiro widget
                    </a>
                @endif
            </div>
        @else
            <div class="dc-groups">
                @foreach ($grupos as $empresaNome => $widgets)
                    <section class="dc-group">
                        @if (Filament\Facades\Filament::auth()->user()?->isSuperAdmin())
                            <div class="dc-group-header">
                                <h3>{{ $empresaNome }}</h3>
                                <p>Widgets ativos dessa empresa.</p>
                            </div>
                        @endif

                        <div class="dc-grid">
                            @foreach ($widgets as $widget)
                                <article class="dc-widget {{ $this->larguraWidget($widget) }} {{ $this->corWidget($widget) }} {{ $this->classeTipoWidget($widget) }}">
                                    <div class="dc-widget-top">
                                        <div>
                                            <span class="dc-widget-source">{{ $this->labelFonte($widget) }}</span>
                                            <h4>{{ $widget->titulo }}</h4>
                                        </div>

                                        <span class="dc-widget-type">{{ $this->labelTipo($widget) }}</span>
                                    </div>

                                    @switch($widget->tipo)
                                        @case('grafico')
                                            @php($dadosGrafico = $this->dadosGrafico($widget))
                                            @php($maiorValorGrafico = $this->maiorValorGrafico($widget))

                                            @if ($dadosGrafico->isEmpty())
                                                <div class="dc-widget-empty-content">Sem dados para montar o gráfico.</div>
                                            @else
                                                <div class="dc-chart" aria-label="Gráfico do widget {{ $widget->titulo }}">
                                                    @foreach ($dadosGrafico as $linha)
                                                        @php($valorGrafico = (float) ($linha['valor'] ?? 0))
                                                        @php($percentualGrafico = max(4, min(100, ($valorGrafico / $maiorValorGrafico) * 100)))

                                                        <div class="dc-chart-row">
                                                            <div class="dc-chart-label" title="{{ $linha['label'] ?? 'Sem label' }}">
                                                                {{ $linha['label'] ?? 'Sem label' }}
                                                            </div>
                                                            <div class="dc-chart-track">
                                                                <div class="dc-chart-bar" style="width: {{ $percentualGrafico }}%"></div>
                                                            </div>
                                                            <div class="dc-chart-value">{{ number_format($valorGrafico, 0, ',', '.') }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @break

                                        @case('tabela')
                                            @php($dadosTabela = $this->dadosTabela($widget))

                                            @if ($dadosTabela->isEmpty())
                                                <div class="dc-widget-empty-content">Sem registros para exibir na tabela.</div>
                                            @else
                                                <div class="dc-table-wrap">
                                                    <table class="dc-table">
                                                        <thead>
                                                        <tr>
                                                            <th>Título</th>
                                                            <th>Status</th>
                                                            <th>Tipo</th>
                                                            <th>Vencimento</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach ($dadosTabela as $linha)
                                                            <tr>
                                                                <td title="{{ $linha['titulo'] ?? '-' }}">{{ $linha['titulo'] ?? '-' }}</td>
                                                                <td><span class="dc-status-pill">{{ $linha['status'] ?? '-' }}</span></td>
                                                                <td>{{ $linha['tipo'] ?? '-' }}</td>
                                                                <td>{{ $linha['data_vencimento'] ?? '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                            @break

                                        @default
                                            <div class="dc-widget-value">
                                                {{ $this->valorFormatado($widget) }}
                                            </div>
                                    @endswitch

                                    <div class="dc-widget-footer">
                                        <span>Ordem {{ $widget->ordem ?? 1 }}</span>
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
