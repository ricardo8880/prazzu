<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/prazzu-fase2-pages.css') }}?v={{ filemtime(public_path('css/prazzu-fase2-pages.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/prazzu-ux-essentials.css') }}?v={{ file_exists(public_path('css/prazzu-ux-essentials.css')) ? filemtime(public_path('css/prazzu-ux-essentials.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/documentos-hub.css') }}?v={{ file_exists(public_path('css/documentos-hub.css')) ? filemtime(public_path('css/documentos-hub.css')) : time() }}">

    @php
        $hub = $hub ?? [];
        $atalhos = $atalhos ?? [];
        $acoesInteligentes = $acoesInteligentes ?? [];
        $acoesRapidasDocumentais = $acoesRapidasDocumentais ?? [];
        $indicadoresPrioridade = $indicadoresPrioridade ?? [];
        $saudeDocumental = $saudeDocumental ?? [];
        $inteligenciaDocumental = $inteligenciaDocumental ?? [];
        $hubTone = $hub['tom'] ?? 'muted';
        $integracaoEnterprise = $integracaoEnterprise ?? [];
        $clusterDocumentos = $clusterDocumentos ?? 'visao-geral';
        $clusterAtivo = $clusterAtivo ?? [];
        $statusResolucaoOptions = $statusResolucaoOptions ?? [];
        $documentosPorCluster = $documentosPorCluster ?? [];
        $documentosClusterAtivo = $documentosPorCluster[$clusterDocumentos] ?? ($documentos ?? []);
        $principalFoco = $inteligenciaDocumental['principalFoco'] ?? null;
    @endphp

    <div class="prazzu-page prazzu-docs-page documentos-hub-page documentos-cluster-page">
        <div class="prazzu-hero prazzu-hero-docs documentos-hub-hero">
            <div>
                <span class="prazzu-kicker">DOCUMENTOS</span>
                <h2>Saúde Documental</h2>
                <p>{{ $hub['mensagem'] ?? 'Veja se há documentos faltando, vencidos ou próximos do vencimento e saiba por onde começar.' }}</p>
                <div class="documentos-hub-status {{ $hubTone }}">
                    <strong>{{ $hub['status'] ?? 'Base documental' }}</strong>
                    <span>{{ $hub['proximaAcao'] ?? 'Ver o que precisa de atenção' }}</span>
                </div>
            </div>
            <div class="documentos-hub-score-card {{ $hubTone }}">
                <span>Saúde documental</span>
                <strong>{{ (int) ($hub['score'] ?? 0) }}%</strong>
                <small>{{ number_format($hub['pendentes'] ?? 0, 0, ',', '.') }} pendente(s) • {{ number_format($hub['regularizados'] ?? 0, 0, ',', '.') }} regularizado(s)</small>
            </div>
        </div>

        @if ($clusterDocumentos === 'visao-geral')
            <section class="documentos-hub-command documentos-hub-command--metrics" aria-label="Indicadores essenciais de saúde documental">
                <div>
                    <span class="pz-ux-kicker">Resumo</span>
                    <h2>Situação dos documentos</h2>
                    <p>Veja rapidamente se existem documentos vencidos, faltando arquivo ou próximos do vencimento.</p>
                </div>
                <div class="documentos-hub-command-grid">
                    <article>
                        <span>Clientes monitorados</span>
                        <strong>{{ number_format($saudeDocumental['clientesMonitorados'] ?? 0, 0, ',', '.') }}</strong>
                        <small>Clientes com documentos na base.</small>
                    </article>
                    <article class="danger">
                        <span>Críticos</span>
                        <strong>{{ number_format($hub['criticos'] ?? 0, 0, ',', '.') }}</strong>
                        <small>Vencidos ou sem arquivo principal.</small>
                    </article>
                    <article class="warning">
                        <span>Vencem em 30 dias</span>
                        <strong>{{ number_format($resumo['vencem30'] ?? 0, 0, ',', '.') }}</strong>
                        <small>Prazos que exigem acompanhamento.</small>
                    </article>
                    <article>
                        <span>Com arquivo</span>
                        <strong>{{ (int) ($hub['comArquivoPercentual'] ?? 0) }}%</strong>
                        <small>Documentos com arquivo anexado.</small>
                    </article>
                </div>
            </section>

            @if ($principalFoco)
                <section class="documentos-enterprise-sync documentos-enterprise-sync--compact" aria-label="Próxima ação documental">
                    <div class="documentos-enterprise-sync__content">
                        <span class="pz-ux-kicker">Próxima ação</span>
                        <h2>{{ $principalFoco['titulo'] }}</h2>
                        <p>{{ $principalFoco['descricao'] }}</p>
                        <a href="{{ $principalFoco['url'] }}">{{ $principalFoco['botao'] }}</a>
                    </div>
                    <div class="documentos-enterprise-sync__flows">
                        <div class="documentos-enterprise-flow {{ $inteligenciaDocumental['tom'] ?? 'primary' }}">
                            <span>Risco documental</span>
                            <strong>{{ (int) ($inteligenciaDocumental['scoreRisco'] ?? 0) }}%</strong>
                            <small>{{ $inteligenciaDocumental['recomendacao'] ?? 'Manter acompanhamento documental.' }}</small>
                        </div>
                    </div>
                </section>
            @endif

            <section class="documentos-cluster-list" aria-label="Clientes com atenção documental">
                <div class="documentos-cluster-list__header">
                    <div>
                        <span class="pz-ux-kicker">Clientes com pendência</span>
                        <h3>Clientes que precisam de revisão</h3>
                        <p>Priorize os clientes com documentos vencidos, sem arquivo ou próximos do vencimento.</p>
                    </div>
                    <strong>{{ number_format($saudeDocumental['clientesComProblema'] ?? 0, 0, ',', '.') }} cliente(s)</strong>
                </div>
                <div class="documentos-cluster-card-grid">
                    @forelse (($saudeDocumental['principaisClientes'] ?? []) as $cliente)
                        <article class="documentos-cluster-card {{ $cliente['tom'] ?? 'success' }}">
                            <div>
                                <span class="documentos-priority-badge {{ $cliente['tom'] ?? 'success' }}">{{ (int) ($cliente['score'] ?? 0) }}% saudável</span>
                                <h4>{{ $cliente['nome'] }}</h4>
                                <p>{{ $cliente['motivo'] }}</p>
                            </div>
                            <dl>
                                <div><dt>Total</dt><dd>{{ number_format($cliente['total'] ?? 0, 0, ',', '.') }}</dd></div>
                                <div><dt>Problemas</dt><dd>{{ number_format($cliente['problemas'] ?? 0, 0, ',', '.') }}</dd></div>
                                <div><dt>Críticos</dt><dd>{{ number_format($cliente['criticos'] ?? 0, 0, ',', '.') }}</dd></div>
                            </dl>
                            <div class="documentos-cluster-card__actions">
                                <a href="{{ $cliente['url'] }}">Ver documentos do cliente</a>
                            </div>
                        </article>
                    @empty
                        <div class="documentos-cluster-empty"><strong>Nenhum cliente no radar documental.</strong><span>Quando houver vencidos, sem arquivo ou prazos próximos, eles aparecerão aqui.</span></div>
                    @endforelse
                </div>
            </section>
        @elseif ($clusterDocumentos === 'pendencias')
            <section class="documentos-priority-panel" aria-label="Irregularidades documentais">
                <div class="documentos-priority-panel__intro">
                    <span class="pz-ux-kicker">Irregularidades</span>
                    <h2>Documentos que comprometem a saúde da base</h2>
                    <p>Recorte curto de vencidos, sem arquivo e itens críticos.</p>
                </div>
                <div class="documentos-priority-grid">
                    @foreach (array_slice($indicadoresPrioridade, 0, 3) as $indicador)
                        <article class="documentos-priority-card {{ $indicador['tom'] ?? 'primary' }}">
                            <span>{{ $indicador['label'] }}</span>
                            <strong>{{ number_format($indicador['total'] ?? 0, 0, ',', '.') }}</strong>
                            <small>{{ $indicador['descricao'] }}</small>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="documentos-cluster-list" aria-label="Documentos com irregularidade">
                <div class="documentos-cluster-list__header">
                    <div>
                        <span class="pz-ux-kicker">Lista curta</span>
                        <h3>Irregularidades documentais</h3>
                        <p>Itens que precisam de correção documental.</p>
                    </div>
                    <strong>{{ number_format(count($documentosPorCluster['pendencias'] ?? []), 0, ',', '.') }} item(ns)</strong>
                </div>
                <div class="documentos-cluster-card-grid">
                    @forelse (($documentosPorCluster['pendencias'] ?? []) as $documento)
                        @php
                            $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                            $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
                            $acaoRapida = $documento['acao_rapida'] ?? null;
                        @endphp
                        <article class="documentos-cluster-card {{ $prioridadeOperacional['tom'] ?? 'success' }}">
                            <div>
                                <span class="documentos-priority-badge {{ $prioridadeOperacional['tom'] ?? 'success' }}">{{ $prioridadeOperacional['label'] ?? 'Estável' }}</span>
                                <h4>{{ $documento['titulo'] }}</h4>
                                <p>{{ $prioridadeOperacional['motivo'] ?? 'Sem sinal crítico.' }}</p>
                            </div>
                            <dl>
                                <div><dt>Empresa</dt><dd>{{ $empresa }}</dd></div>
                                <div><dt>Prazo</dt><dd>{{ $prioridadeOperacional['prazo'] ?? '-' }}</dd></div>
                                <div><dt>Arquivo</dt><dd>{{ ! empty($documento['arquivo']) ? 'Com arquivo' : 'Sem arquivo' }}</dd></div>
                            </dl>
                            <div class="documentos-cluster-card__actions">
                                @include('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions])
                            </div>
                        </article>
                    @empty
                        <div class="documentos-cluster-empty"><strong>Nenhuma irregularidade documental.</strong><span>Documentos críticos aparecerão aqui quando existirem.</span></div>
                    @endforelse
                </div>
            </section>
        @elseif ($clusterDocumentos === 'vencimentos')
            <section class="documentos-cluster-list" aria-label="Vencimentos documentais">
                <div class="documentos-cluster-list__header">
                    <div>
                        <span class="pz-ux-kicker">Prazos</span>
                        <h3>Vencimentos no radar</h3>
                        <p>Documentos que merecem acompanhamento antes de vencer.</p>
                    </div>
                    <strong>{{ number_format(count($documentosPorCluster['vencimentos'] ?? []), 0, ',', '.') }} item(ns)</strong>
                </div>
                <div class="documentos-cluster-card-grid documentos-cluster-card-grid--timeline">
                    @forelse (($documentosPorCluster['vencimentos'] ?? []) as $documento)
                        @php
                            $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                            $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
                            $acaoRapida = $documento['acao_rapida'] ?? null;
                        @endphp
                        <article class="documentos-cluster-card {{ $prioridadeOperacional['tom'] ?? 'success' }}">
                            <div>
                                <span class="documentos-priority-badge {{ $prioridadeOperacional['tom'] ?? 'success' }}">{{ $prioridadeOperacional['prazo'] ?? '-' }}</span>
                                <h4>{{ $documento['titulo'] }}</h4>
                                <p>{{ ! empty($documento['data_vencimento']) ? 'Vencimento em ' . \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : 'Sem vencimento cadastrado.' }}</p>
                            </div>
                            <dl>
                                <div><dt>Empresa</dt><dd>{{ $empresa }}</dd></div>
                                <div><dt>Status</dt><dd>{{ ucfirst(str_replace('_', ' ', $documento['status'] ?? '-')) }}</dd></div>
                                <div><dt>Prioridade</dt><dd>{{ $prioridadeOperacional['label'] ?? 'Estável' }}</dd></div>
                            </dl>
                            <div class="documentos-cluster-card__actions">
                                @include('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions])
                            </div>
                        </article>
                    @empty
                        <div class="documentos-cluster-empty"><strong>Nenhum vencimento no radar.</strong><span>Quando houver documentos com vencimento, eles aparecerão aqui por prazo.</span></div>
                    @endforelse
                </div>
            </section>
        @elseif ($clusterDocumentos === 'enterprise')
            <section class="documentos-enterprise-sync" aria-label="Integração com Gestão Documental Enterprise">
                <div class="documentos-enterprise-sync__content">
                    <span class="pz-ux-kicker">Detalhes</span>
                    <h2>Ver documentos em detalhe</h2>
                    <p>{{ $integracaoEnterprise['descricao'] ?? 'Abra a visão detalhada quando precisar filtrar, revisar ou corrigir documentos específicos.' }}</p>
                    <a href="{{ $integracaoEnterprise['url'] ?? \App\Filament\Pages\GestaoDocumentalEnterprise::getUrl() }}">Abrir detalhes dos documentos</a>
                </div>
                <div class="documentos-enterprise-sync__flows">
                    @foreach (($integracaoEnterprise['fluxos'] ?? []) as $fluxo)
                        <a class="documentos-enterprise-flow {{ $fluxo['tom'] ?? 'primary' }}" href="{{ $fluxo['url'] }}">
                            <span>{{ $fluxo['titulo'] }}</span>
                            <strong>{{ number_format($fluxo['total'] ?? 0, 0, ',', '.') }}</strong>
                            <small>{{ $fluxo['descricao'] }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        @else
            <div id="fila-documentos" class="prazzu-card documentos-cluster-table-card">
                <div class="prazzu-card-header">
                    <div><h3>Documentos para revisar</h3><p>Lista objetiva para consultar documentos e corrigir pendências.</p></div>
                </div>
                <div class="prazzu-table-wrap">
                    <table class="prazzu-table prazzu-click-table documentos-premium-table">
                        <thead><tr><th>Documento</th><th>Prioridade</th><th>Empresa</th><th>Status</th><th>Vencimento</th><th>Arquivo</th><th>Portal</th><th>Ações</th></tr></thead>
                        <tbody>
                            @forelse (($documentosPorCluster['fila'] ?? $documentosClusterAtivo) as $documento)
                                @php
                                    $vencido = ! empty($documento['data_vencimento']) && \Carbon\Carbon::parse($documento['data_vencimento'])->isPast() && ! in_array($documento['status'] ?? '', ['concluido', 'concluído', 'finalizado'], true);
                                    $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                                    $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
                                    $acaoRapida = $documento['acao_rapida'] ?? null;
                                @endphp
                                <tr class="documentos-priority-row {{ $prioridadeOperacional['tom'] ?? 'success' }}">
                                    <td><strong>{{ $documento['titulo'] }}</strong><small>{{ \Illuminate\Support\Str::limit($documento['descricao'] ?? 'Sem descrição cadastrada', 60) }}</small></td>
                                    <td><span class="documentos-priority-badge {{ $prioridadeOperacional['tom'] ?? 'success' }}">{{ $prioridadeOperacional['label'] ?? 'Estável' }}</span></td>
                                    <td>{{ $empresa }}</td>
                                    <td><span class="prazzu-badge {{ $vencido ? 'danger' : '' }}">{{ ucfirst(str_replace('_', ' ', $documento['status'] ?? '-')) }}</span></td>
                                    <td><span class="{{ $vencido ? 'prazzu-date-danger' : '' }}">{{ ! empty($documento['data_vencimento']) ? \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : '-' }}</span></td>
                                    <td><span class="prazzu-pill {{ ! empty($documento['arquivo']) ? 'ok' : 'muted' }}">{{ ! empty($documento['arquivo']) ? 'Com arquivo' : 'Sem arquivo' }}</span></td>
                                    <td><span class="prazzu-pill {{ ! empty($documento['portal_ativo']) ? 'ok' : 'muted' }}">{{ ! empty($documento['portal_ativo']) ? 'Ativo' : 'Inativo' }}</span></td>
                                    <td class="documentos-row-actions documentos-row-actions--filament">
                                        @include('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions])
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="prazzu-empty"><strong>Nenhum documento encontrado.</strong><br>Cadastre o primeiro documento para começar a medir a saúde documental.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($documentoResolucaoEmEdicao)
            @include('filament.pages.partials.documento-resolver-dialog', [
                'documento' => $documentoResolucaoEmEdicao,
                'statusResolucaoOptions' => $statusResolucaoOptions,
            ])
        @endif
    </div>
</x-filament-panels::page>
