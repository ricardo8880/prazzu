<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/centro-operacional.css') }}?v={{ file_exists(public_path('css/centro-operacional.css')) ? filemtime(public_path('css/centro-operacional.css')) : time() }}">

    @php
        $cards = $data['cards'] ?? [];
        $sections = $data['sections'] ?? [];
        $workload = $data['workload'] ?? [];
        $bloqueados = $data['bloqueados'] ?? [];
        $missingColumns = $data['missing_columns'] ?? [];
    @endphp

    <div class="co-page">
        <section class="co-hero">
            <div>
                <span>Centro Operacional</span>
                <h1>Prioridade clara em 3 segundos</h1>
                <p>Vermelho primeiro, amarelo em seguida, verde monitorado. Cada card mostra dono, prazo, tempo parado e ação direta sem dados estáticos.</p>
            </div>
            <div class="co-guide">
                <strong>Guia rápido</strong>
                <p>Se aparecer algo no quadro vermelho, pare tudo e resolva primeiro. Aprovações e cobranças podem ser tratadas direto daqui.</p>
            </div>
        </section>

        @if (! empty($missingColumns))
            <section class="co-warning-box">
                <strong>SQL recomendado ainda não aplicado</strong>
                <p>O painel está funcionando com fallback nos campos atuais, mas para ativar 100% dos campos enterprise aplique o arquivo SQL incluído no ZIP. Campos pendentes: {{ implode(', ', $missingColumns) }}.</p>
            </section>
        @endif

        <section class="co-stats">
            @foreach ($cards as $card)
                <article class="co-stat {{ $card['tone'] ?? 'info' }}">
                    <span>{{ $card['label'] ?? '-' }}</span>
                    <strong>{{ number_format((int) ($card['value'] ?? 0), 0, ',', '.') }}</strong>
                    <small>{{ $card['hint'] ?? '' }}</small>
                </article>
            @endforeach
        </section>

        <div class="co-grid">
            @foreach ($sections as $section)
                <section class="co-panel">
                    <header>
                        <div>
                            <h2>{{ $section['title'] ?? 'Seção' }}</h2>
                            <p>{{ $section['description'] ?? '' }}</p>
                        </div>
                    </header>

                    <div class="co-list">
                        @forelse (($section['items'] ?? []) as $item)
                            <article class="co-task {{ $item['tone'] ?? 'info' }}">
                                <div class="co-task-main">
                                    <div>
                                        <h3>{{ $item['title'] }}</h3>
                                        <div class="co-meta">
                                            <span>{{ $item['status'] }}</span>
                                            <span>Urgência: {{ $item['urgency'] }}</span>
                                            @if(!empty($item['blocked']))<span>Bloqueado</span>@endif
                                        </div>
                                    </div>
                                    <strong>{{ $item['stopped_for'] ?? '-' }}</strong>
                                </div>

                                <p>{{ $item['description'] }}</p>

                                <div class="co-foot">
                                    <small>{{ $item['responsavel'] }} • {{ $item['empresa'] }} @if($item['due']) • Vence {{ $item['due'] }} @endif @if($item['value']) • {{ $item['value'] }} @endif</small>
                                    <div class="co-actions">
                                        @if(in_array($item['status'], ['Aguardando Aprovação'], true))
                                            <button type="button" wire:click="aprovar({{ $item['id'] }})">Aprovar</button>
                                            <button type="button" class="danger" wire:click="enviarParaCorrecao({{ $item['id'] }})">Corrigir</button>
                                        @endif

                                        @if(str_contains(($section['title'] ?? ''), 'Cobrança'))
                                            <button type="button" wire:click="marcarFaturado({{ $item['id'] }})">Faturado</button>
                                            <button type="button" wire:click="marcarPago({{ $item['id'] }})">Pago</button>
                                        @endif

                                        <a href="{{ $item['url'] }}">Abrir</a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="co-empty">
                                <strong>{{ $section['empty'] ?? 'Nada encontrado.' }}</strong>
                                <p>Quando existir algo nessa fila, aparecerá aqui automaticamente.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>

        <div class="co-bottom-grid">
            <section class="co-panel">
                <header>
                    <div>
                        <h2>📊 Caminho Crítico / Workload</h2>
                        <p>Quem tem mais tarefas acumuladas para redistribuir carga.</p>
                    </div>
                </header>

                <div class="co-bars">
                    @forelse ($workload as $row)
                        <div class="co-bar-row">
                            <div>
                                <strong>{{ $row['name'] }}</strong>
                                <span>{{ $row['total'] }} tarefa(s)</span>
                            </div>
                            <div class="co-bar"><span style="width: {{ $row['percent'] }}%"></span></div>
                        </div>
                    @empty
                        <div class="co-empty"><strong>Nenhuma carga pendente.</strong></div>
                    @endforelse
                </div>
            </section>

            <section class="co-panel">
                <header>
                    <div>
                        <h2>⛔ Bloqueados</h2>
                        <p>Dependências externas ou internas que travaram a execução.</p>
                    </div>
                </header>

                <div class="co-list compact">
                    @forelse ($bloqueados as $item)
                        <article class="co-task warning">
                            <div class="co-task-main">
                                <div>
                                    <h3>{{ $item['title'] }}</h3>
                                    <div class="co-meta"><span>{{ $item['responsavel'] }}</span><span>{{ $item['status'] }}</span></div>
                                </div>
                                <a href="{{ $item['url'] }}">Abrir</a>
                            </div>
                        </article>
                    @empty
                        <div class="co-empty"><strong>Nenhum bloqueio ativo.</strong></div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
