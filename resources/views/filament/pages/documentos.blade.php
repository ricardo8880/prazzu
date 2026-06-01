<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/prazzu-fase2-pages.css') }}?v={{ filemtime(public_path('css/prazzu-fase2-pages.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/prazzu-ux-essentials.css') }}?v={{ file_exists(public_path('css/prazzu-ux-essentials.css')) ? filemtime(public_path('css/prazzu-ux-essentials.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/documentos-hub.css') }}?v={{ file_exists(public_path('css/documentos-hub.css')) ? filemtime(public_path('css/documentos-hub.css')) : time() }}">

    @php
        $hub = $hub ?? [];
        $atalhos = $atalhos ?? [];
        $acoesInteligentes = $acoesInteligentes ?? [];
        $indicadoresPrioridade = $indicadoresPrioridade ?? [];
        $hubTone = $hub['tom'] ?? 'muted';
        $integracaoEnterprise = $integracaoEnterprise ?? [];
        $clusterDocumentos = $clusterDocumentos ?? 'visao-geral';
        $clusterAtivo = $clusterAtivo ?? [];
        $clustersDocumentos = $clustersDocumentos ?? [];
        $statusResolucaoOptions = $statusResolucaoOptions ?? [];
        $documentosPorCluster = $documentosPorCluster ?? [];
        $documentosClusterAtivo = $documentosPorCluster[$clusterDocumentos] ?? ($documentos ?? []);
        $prioridadeInteligente = $prioridadeInteligente ?? [];
        $prioridadeDocumento = $prioridadeInteligente['documento'] ?? null;
        $fluxoContinuo = $fluxoContinuo ?? [];
        $fluxoProximo = $fluxoContinuo['proximo'] ?? null;
        $fluxoFeedback = $fluxoContinuo['feedback'] ?? null;
    @endphp

    <div class="prazzu-page prazzu-docs-page documentos-hub-page documentos-cluster-page">
        <div class="prazzu-hero prazzu-hero-docs documentos-hub-hero">
            <div>
                <span class="prazzu-kicker">DOCUMENTOS</span>
                <h2>Hub de Documentos</h2>
                <p>{{ $hub['mensagem'] ?? 'Organize arquivos, vencimentos, liberação para cliente e pendências em uma visão mais operacional.' }}</p>
                <div class="documentos-hub-status {{ $hubTone }}">
                    <strong>{{ $hub['status'] ?? 'Base documental' }}</strong>
                    <span>Próxima ação: {{ $hub['proximaAcao'] ?? 'Revisar fila documental' }}</span>
                </div>
            </div>
            <div class="documentos-hub-score-card {{ $hubTone }}">
                <span>Saúde documental</span>
                <strong>{{ (int) ($hub['score'] ?? 0) }}%</strong>
                <small>{{ number_format($hub['pendentes'] ?? 0, 0, ',', '.') }} pendente(s) • {{ number_format($hub['regularizados'] ?? 0, 0, ',', '.') }} regularizado(s)</small>
            </div>
        </div>


        <section class="documentos-prioridade-inteligente {{ $prioridadeInteligente['tom'] ?? 'success' }}" aria-label="Prioridade inteligente de documentos">
            <div class="documentos-prioridade-inteligente__content">
                <span class="pz-ux-kicker">Prioridade inteligente</span>
                <h2>{{ $prioridadeInteligente['titulo'] ?? 'Base documental sob controle' }}</h2>
                <p>{{ $prioridadeInteligente['mensagem'] ?? 'Nenhum documento crítico foi identificado na fila atual.' }}</p>
                <div class="documentos-prioridade-inteligente__meta">
                    <span><strong>{{ number_format($prioridadeInteligente['criticos'] ?? 0, 0, ',', '.') }}</strong> crítico(s)</span>
                    <span><strong>{{ number_format($prioridadeInteligente['altos'] ?? 0, 0, ',', '.') }}</strong> alta prioridade</span>
                    <span><strong>{{ number_format($prioridadeInteligente['monitorar'] ?? 0, 0, ',', '.') }}</strong> em monitoramento</span>
                </div>
            </div>
            <div class="documentos-prioridade-inteligente__action">
                @if (! empty($prioridadeDocumento))
                    <strong>{{ \Illuminate\Support\Str::limit($prioridadeDocumento['titulo'] ?? 'Documento prioritário', 56) }}</strong>
                    <small>{{ $prioridadeDocumento['prioridade_operacional']['prazo'] ?? 'Prioridade calculada pela fila' }}</small>
                    <x-filament::button
                        type="button"
                        color="danger"
                        icon="heroicon-m-bolt"
                        wire:click="abrirResolucaoDocumento({{ $prioridadeDocumento['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="abrirResolucaoDocumento({{ $prioridadeDocumento['id'] }})"
                    >
                        <span wire:loading.remove wire:target="abrirResolucaoDocumento({{ $prioridadeDocumento['id'] }})">{{ $prioridadeInteligente['acao'] ?? 'Resolver agora' }}</span>
                        <span wire:loading wire:target="abrirResolucaoDocumento({{ $prioridadeDocumento['id'] }})">Abrindo...</span>
                    </x-filament::button>
                    <a href="{{ $prioridadeInteligente['clusterUrl'] ?? '#fila-documentos' }}">Ver fila relacionada</a>
                @else
                    <strong>Nenhuma urgência ativa</strong>
                    <small>Continue usando os clusters para acompanhar vencimentos e novos cadastros.</small>
                    <a href="{{ $prioridadeInteligente['clusterUrl'] ?? '#fila-documentos' }}">Abrir fila de documentos</a>
                @endif
            </div>
        </section>


        <section class="documentos-fluxo-continuo {{ ! empty($fluxoContinuo['ativo']) ? 'ativo' : 'concluido' }}" aria-label="Fluxo contínuo de resolução documental">
            <div class="documentos-fluxo-continuo__content">
                <span class="pz-ux-kicker">Fluxo contínuo</span>
                <h2>{{ ! empty($fluxoContinuo['ativo']) ? 'Modo produtividade pronto para uso' : 'Fila prioritária sob controle' }}</h2>
                <p>{{ $fluxoContinuo['mensagem'] ?? 'Resolva documentos sem sair da página e mantenha o foco no próximo item prioritário.' }}</p>

                @if (! empty($fluxoFeedback))
                    <div class="documentos-fluxo-continuo__feedback {{ $fluxoFeedback['tipo'] ?? 'info' }}">
                        <strong>{{ $fluxoFeedback['titulo'] ?? 'Atualização concluída' }}</strong>
                        <span>{{ $fluxoFeedback['mensagem'] ?? 'A fila foi atualizada com sucesso.' }}</span>
                    </div>
                @endif
            </div>

            <div class="documentos-fluxo-continuo__panel">
                <span>{{ number_format($fluxoContinuo['total'] ?? 0, 0, ',', '.') }} item(ns) prioritário(s)</span>
                @if (! empty($fluxoProximo))
                    <strong>{{ \Illuminate\Support\Str::limit($fluxoProximo['titulo'] ?? 'Próximo documento', 48) }}</strong>
                    <small>{{ $fluxoProximo['prioridade_operacional']['motivo'] ?? 'Próximo item calculado por prioridade.' }}</small>
                    <x-filament::button
                        type="button"
                        color="warning"
                        icon="heroicon-m-forward"
                        wire:click="abrirResolucaoDocumento({{ $fluxoProximo['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="abrirResolucaoDocumento({{ $fluxoProximo['id'] }})"
                    >
                        <span wire:loading.remove wire:target="abrirResolucaoDocumento({{ $fluxoProximo['id'] }})">Resolver próximo</span>
                        <span wire:loading wire:target="abrirResolucaoDocumento({{ $fluxoProximo['id'] }})">Abrindo...</span>
                    </x-filament::button>
                @else
                    <strong>Nenhum próximo item crítico</strong>
                    <small>Continue acompanhando os clusters para novos vencimentos e pendências.</small>
                    <a href="{{ $integracaoEnterprise['url'] ?? \App\Filament\Pages\GestaoDocumentalEnterprise::getUrl() }}">Abrir Enterprise</a>
                @endif
            </div>
        </section>

        <section class="documentos-cluster-context" aria-label="Resumo do cluster ativo de documentos">
            <div class="documentos-cluster-context__header">
                <div>
                    <span class="pz-ux-kicker">Cluster ativo</span>
                    <h2>{{ $clusterAtivo['label'] ?? 'Visão Geral' }}</h2>
                    <p>{{ $clusterAtivo['description'] ?? 'Use as abas superiores do Filament para alternar o contexto sem sair da página.' }}</p>
                </div>
                <div class="documentos-cluster-context__result {{ $clusterAtivo['tone'] ?? 'primary' }}">
                    <strong>{{ number_format($clusterAtivo['count'] ?? 0, 0, ',', '.') }}</strong>
                    <span>{{ $clusterAtivo['hint'] ?? 'itens no contexto' }}</span>
                </div>
            </div>
            <div class="documentos-cluster-insights" aria-label="Indicadores rápidos do cluster ativo">
                <span class="{{ $hubTone }}"><strong>{{ (int) ($hub['score'] ?? 0) }}%</strong> saúde documental</span>
                <span class="danger"><strong>{{ number_format($hub['criticos'] ?? 0, 0, ',', '.') }}</strong> críticos</span>
                <span class="warning"><strong>{{ number_format($resumo['vencem30'] ?? 0, 0, ',', '.') }}</strong> vencem em 30 dias</span>
                <span class="neutral"><strong>Ação</strong> {{ $clusterAtivo['next_action'] ?? 'Revisar fila documental' }}</span>
            </div>
        </section>

        @if ($clusterDocumentos === 'visao-geral')
            <section class="documentos-ux-guide" aria-label="Guia rápido da rotina documental">
                <article>
                    <span>1</span>
                    <div>
                        <strong>Entenda a situação</strong>
                        <small>Use a saúde documental e os indicadores para saber se existe risco imediato.</small>
                    </div>
                </article>
                <article>
                    <span>2</span>
                    <div>
                        <strong>Priorize o que importa</strong>
                        <small>Resolva primeiro vencidos, sem arquivo e prazos próximos.</small>
                    </div>
                </article>
                <article>
                    <span>3</span>
                    <div>
                        <strong>Execute na tela certa</strong>
                        <small>Avance para Enterprise, Validades, Contratos ou Pendências sem procurar no menu.</small>
                    </div>
                </article>
            </section>

            <section class="documentos-hub-actions" aria-label="Atalhos principais de documentos">
                @foreach ($atalhos as $atalho)
                    <a class="documentos-hub-action {{ $atalho['tom'] ?? 'neutral' }}" href="{{ $atalho['url'] }}">
                        <strong>{{ $atalho['label'] }}</strong>
                        <span>{{ $atalho['descricao'] }}</span>
                    </a>
                @endforeach
            </section>

            <section class="documentos-hub-command documentos-hub-command--actions">
                <div>
                    <span class="pz-ux-kicker">Comando rápido</span>
                    <h2>O que merece atenção agora</h2>
                    <p>As ações abaixo mudam conforme os dados reais: vencidos, itens sem arquivo, prazos próximos e gestão completa.</p>
                </div>
                <div class="documentos-smart-actions" aria-label="Ações inteligentes de documentos">
                    @foreach ($acoesInteligentes as $acao)
                        <article class="documentos-smart-action {{ $acao['tom'] ?? 'primary' }}">
                            <span>{{ $acao['prioridade'] }}</span>
                            <strong>{{ $acao['titulo'] }}</strong>
                            <p>{{ $acao['descricao'] }}</p>
                            <a href="{{ $acao['url'] }}">{{ $acao['botao'] }}</a>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="documentos-hub-command documentos-hub-command--metrics">
                <div>
                    <span class="pz-ux-kicker">Leitura rápida</span>
                    <h2>Indicadores para decidir sem procurar informação</h2>
                    <p>Use estes sinais para saber se o problema está em prazo, arquivo ou liberação no portal.</p>
                </div>
                <div class="documentos-hub-command-grid">
                    <article>
                        <span>Críticos</span>
                        <strong>{{ number_format($hub['criticos'] ?? 0, 0, ',', '.') }}</strong>
                        <small>Vencidos ou sem arquivo principal.</small>
                    </article>
                    <article>
                        <span>Arquivados</span>
                        <strong>{{ (int) ($hub['comArquivoPercentual'] ?? 0) }}%</strong>
                        <small>Itens com arquivo anexado.</small>
                    </article>
                    <article>
                        <span>Portal</span>
                        <strong>{{ (int) ($hub['portalPercentual'] ?? 0) }}%</strong>
                        <small>Itens liberados para consulta externa.</small>
                    </article>
                </div>
            </section>
        @elseif ($clusterDocumentos === 'pendencias')
            <section class="documentos-priority-panel" aria-label="Priorização operacional de documentos">
                <div class="documentos-priority-panel__intro">
                    <span class="pz-ux-kicker">Priorização</span>
                    <h2>Fila ordenada por criticidade real</h2>
                    <p>O hub destaca primeiro o que pode gerar risco: vencidos, itens sem arquivo, prazos curtos e prioridades altas.</p>
                </div>
                <div class="documentos-priority-grid">
                    @foreach ($indicadoresPrioridade as $indicador)
                        <article class="documentos-priority-card {{ $indicador['tom'] ?? 'primary' }}">
                            <span>{{ $indicador['label'] }}</span>
                            <strong>{{ number_format($indicador['total'] ?? 0, 0, ',', '.') }}</strong>
                            <small>{{ $indicador['descricao'] }}</small>
                        </article>
                    @endforeach
                </div>
            </section>

            <div class="prazzu-stats-grid">
                <div class="prazzu-stat-card"><span>Total de itens</span><strong>{{ number_format($resumo['total'] ?? 0, 0, ',', '.') }}</strong><small>Documentos cadastrados</small></div>
                <div class="prazzu-stat-card success"><span>Com arquivo</span><strong>{{ number_format($resumo['comArquivo'] ?? 0, 0, ',', '.') }}</strong><small>Prontos para consulta</small></div>
                <div class="prazzu-stat-card warning"><span>Vencem em 30 dias</span><strong>{{ number_format($resumo['vencem30'] ?? 0, 0, ',', '.') }}</strong><small>Exigem acompanhamento</small></div>
                <div class="prazzu-stat-card danger"><span>Vencidos</span><strong>{{ number_format($resumo['vencidos'] ?? 0, 0, ',', '.') }}</strong><small>Regularização necessária</small></div>
            </div>

            <div class="prazzu-work-grid">
                <div class="prazzu-card">
                    <div class="prazzu-card-header">
                        <div><h3>Fila inteligente</h3><p>Priorize documentos sem arquivo, vencidos e liberados no portal.</p></div>
                    </div>
                    <div class="prazzu-mini-grid">
                        <div class="prazzu-mini-card"><span>Sem arquivo</span><strong>{{ number_format($resumo['semArquivo'] ?? 0, 0, ',', '.') }}</strong><p>Itens que precisam de anexo.</p></div>
                        <div class="prazzu-mini-card"><span>No portal</span><strong>{{ number_format($resumo['portal'] ?? 0, 0, ',', '.') }}</strong><p>Liberados para cliente.</p></div>
                    </div>
                </div>
                <div class="prazzu-card">
                    <div class="prazzu-card-header compact"><div><h3>Ações úteis</h3><p>Atalhos para resolver sem sair procurando telas no menu lateral.</p></div></div>
                    <div class="prazzu-actions-list documentos-secondary-actions">
                        <a href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('create') }}">Cadastrar novo documento</a>
                        <a href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}">Abrir lista completa</a>
                        <a href="{{ $integracaoEnterprise['url'] ?? \App\Filament\Pages\GestaoDocumentalEnterprise::getUrl() }}">Gestão documental Enterprise</a>
                        <a href="{{ $integracaoEnterprise['fluxos'][2]['url'] ?? \App\Filament\Pages\Validades::getUrl() }}">Controlar validades</a>
                        <a href="{{ \App\Filament\Pages\Contratos::getUrl() }}">Acompanhar contratos</a>
                        <a href="{{ \App\Filament\Pages\Pendencias::getUrl() }}">Resolver pendências</a>
                    </div>
                </div>
            </div>


            <section class="documentos-cluster-list" aria-label="Documentos pendentes para resolução rápida">
                <div class="documentos-cluster-list__header">
                    <div>
                        <span class="pz-ux-kicker">Resolver sem sair da página</span>
                        <h3>Pendências operacionais</h3>
                        <p>Mostra apenas os itens críticos e de alta prioridade para o usuário focar no que precisa de ação.</p>
                    </div>
                    <strong>{{ number_format(count($documentosPorCluster['pendencias'] ?? []), 0, ',', '.') }} item(ns)</strong>
                </div>
                <div class="documentos-cluster-card-grid">
                    @forelse (($documentosPorCluster['pendencias'] ?? []) as $documento)
                        @php
                            $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                            $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
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
                                @if (! empty($documento['arquivo_url']))
                                    <a href="{{ $documento['arquivo_url'] }}" target="_blank" rel="noopener noreferrer">Arquivo</a>
                                @endif
                                @include('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions])
                            </div>
                        </article>
                    @empty
                        <div class="documentos-cluster-empty"><strong>Nenhuma pendência crítica no momento.</strong><span>Quando houver itens vencidos, sem arquivo ou com prioridade alta, eles aparecerão aqui.</span></div>
                    @endforelse
                </div>
            </section>

        @elseif ($clusterDocumentos === 'vencimentos')
            <section class="documentos-enterprise-sync documentos-enterprise-sync--compact" aria-label="Vencimentos e prazos documentais">
                <div class="documentos-enterprise-sync__content">
                    <span class="pz-ux-kicker">Vencimentos</span>
                    <h2>Prazos críticos sem poluir a página inteira</h2>
                    <p>Este cluster concentra vencidos e próximos prazos. Para operar com filtros avançados, avance para a Enterprise já no fluxo correto.</p>
                    <a href="{{ $integracaoEnterprise['fluxos'][0]['url'] ?? ($integracaoEnterprise['url'] ?? \App\Filament\Pages\GestaoDocumentalEnterprise::getUrl()) }}">Abrir vencidos na Enterprise</a>
                </div>
                <div class="documentos-enterprise-sync__flows">
                    @foreach (($integracaoEnterprise['fluxos'] ?? []) as $fluxo)
                        @if (in_array($fluxo['tom'] ?? '', ['danger', 'warning'], true) || str_contains(strtolower($fluxo['titulo'] ?? ''), 'venc'))
                            <a class="documentos-enterprise-flow {{ $fluxo['tom'] ?? 'primary' }}" href="{{ $fluxo['url'] }}">
                                <span>{{ $fluxo['titulo'] }}</span>
                                <strong>{{ number_format($fluxo['total'] ?? 0, 0, ',', '.') }}</strong>
                                <small>{{ $fluxo['descricao'] }}</small>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>

            <div class="prazzu-stats-grid">
                <div class="prazzu-stat-card danger"><span>Vencidos</span><strong>{{ number_format($resumo['vencidos'] ?? 0, 0, ',', '.') }}</strong><small>Regularização necessária</small></div>
                <div class="prazzu-stat-card warning"><span>Vencem em 30 dias</span><strong>{{ number_format($resumo['vencem30'] ?? 0, 0, ',', '.') }}</strong><small>Exigem acompanhamento</small></div>
                <div class="prazzu-stat-card"><span>Total monitorado</span><strong>{{ number_format(($resumo['vencidos'] ?? 0) + ($resumo['vencem30'] ?? 0), 0, ',', '.') }}</strong><small>Itens no radar de prazo</small></div>
                <div class="prazzu-stat-card success"><span>Com arquivo</span><strong>{{ number_format($resumo['comArquivo'] ?? 0, 0, ',', '.') }}</strong><small>Itens com evidência anexada</small></div>
            </div>


            <section class="documentos-cluster-list" aria-label="Documentos ordenados por vencimento">
                <div class="documentos-cluster-list__header">
                    <div>
                        <span class="pz-ux-kicker">Prazos em ordem</span>
                        <h3>Vencimentos que precisam de acompanhamento</h3>
                        <p>Lista curta com os prazos mais relevantes para evitar que a tela vire uma rolagem longa.</p>
                    </div>
                    <strong>{{ number_format(count($documentosPorCluster['vencimentos'] ?? []), 0, ',', '.') }} item(ns)</strong>
                </div>
                <div class="documentos-cluster-card-grid documentos-cluster-card-grid--timeline">
                    @forelse (($documentosPorCluster['vencimentos'] ?? []) as $documento)
                        @php
                            $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                            $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
                        @endphp
                        <article class="documentos-cluster-card {{ $prioridadeOperacional['tom'] ?? 'success' }}">
                            <div>
                                <span class="documentos-priority-badge {{ $prioridadeOperacional['tom'] ?? 'success' }}">{{ $prioridadeOperacional['prazo'] ?? '-' }}</span>
                                <h4>{{ $documento['titulo'] }}</h4>
                                <p>{{ ! empty($documento['data_vencimento']) ? 'Vencimento em ' . \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : 'Sem data de vencimento cadastrada.' }}</p>
                            </div>
                            <dl>
                                <div><dt>Empresa</dt><dd>{{ $empresa }}</dd></div>
                                <div><dt>Status</dt><dd>{{ ucfirst(str_replace('_', ' ', $documento['status'] ?? '-')) }}</dd></div>
                                <div><dt>Prioridade</dt><dd>{{ $prioridadeOperacional['label'] ?? 'Estável' }}</dd></div>
                            </dl>
                            <div class="documentos-cluster-card__actions">
                                <a href="{{ $documento['enterprise_url'] ?? $documento['edit_url'] }}">Enterprise</a>
                                @include('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions])
                            </div>
                        </article>
                    @empty
                        <div class="documentos-cluster-empty"><strong>Nenhum vencimento no radar.</strong><span>Quando existirem documentos com data de vencimento, eles serão organizados aqui por prazo.</span></div>
                    @endforelse
                </div>
            </section>

        @elseif ($clusterDocumentos === 'enterprise')
            <section class="documentos-enterprise-sync" aria-label="Integração com Gestão Documental Enterprise">
                <div class="documentos-enterprise-sync__content">
                    <span class="pz-ux-kicker">Integração Enterprise</span>
                    <h2>Hub para decidir, Enterprise para operar</h2>
                    <p>{{ $integracaoEnterprise['descricao'] ?? 'Use esta tela para enxergar a prioridade e avance para a Gestão Documental Enterprise quando precisar filtrar, tratar e acompanhar documentos em detalhe.' }}</p>
                    <a href="{{ $integracaoEnterprise['url'] ?? \App\Filament\Pages\GestaoDocumentalEnterprise::getUrl() }}">Abrir Gestão Documental Enterprise</a>
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

            <section class="documentos-hub-actions" aria-label="Atalhos principais de documentos">
                @foreach ($atalhos as $atalho)
                    <a class="documentos-hub-action {{ $atalho['tom'] ?? 'neutral' }}" href="{{ $atalho['url'] }}">
                        <strong>{{ $atalho['label'] }}</strong>
                        <span>{{ $atalho['descricao'] }}</span>
                    </a>
                @endforeach
            </section>
        @else
            <div id="fila-documentos" class="prazzu-card documentos-cluster-table-card">
                <div class="prazzu-card-header">
                    <div><h3>Documentos recentes e próximos vencimentos</h3><p>Clique em qualquer linha para abrir detalhes, arquivo, portal e edição.</p></div>
                </div>
                <div class="prazzu-table-wrap">
                    <table class="prazzu-table prazzu-click-table documentos-premium-table">
                        <thead><tr><th>Documento</th><th>Prioridade</th><th>Empresa</th><th>Tipo</th><th>Status</th><th>Vencimento</th><th>Arquivo</th><th>Portal</th><th>Ações</th><th class="prazzu-modal-head"></th></tr></thead>
                        <tbody>
                            @forelse (($documentosPorCluster['fila'] ?? $documentos) as $documento)
                                @php
                                    $vencido = ! empty($documento['data_vencimento']) && \Carbon\Carbon::parse($documento['data_vencimento'])->isPast() && ! in_array($documento['status'] ?? '', ['concluido', 'concluído', 'finalizado'], true);
                                    $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                                    $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
                                @endphp
                                <tr class="documentos-priority-row {{ $prioridadeOperacional['tom'] ?? 'success' }}">
                                    <td><strong>{{ $documento['titulo'] }}</strong><small>{{ \Illuminate\Support\Str::limit($documento['descricao'] ?? 'Sem descrição cadastrada', 60) }}</small></td>
                                    <td>
                                        <span class="documentos-priority-badge {{ $prioridadeOperacional['tom'] ?? 'success' }}">{{ $prioridadeOperacional['label'] ?? 'Estável' }}</span>
                                        <small class="documentos-priority-reason">{{ $prioridadeOperacional['motivo'] ?? 'Sem sinal crítico.' }}</small>
                                    </td>
                                    <td>{{ $empresa }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $documento['tipo'] ?? '-')) }}</td>
                                    <td><span class="prazzu-badge {{ $vencido ? 'danger' : '' }}">{{ ucfirst(str_replace('_', ' ', $documento['status'] ?? '-')) }}</span></td>
                                    <td><span class="{{ $vencido ? 'prazzu-date-danger' : '' }}">{{ ! empty($documento['data_vencimento']) ? \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : '-' }}</span><small class="documentos-priority-reason">{{ $prioridadeOperacional['prazo'] ?? '-' }}</small></td>
                                    <td><span class="prazzu-pill {{ ! empty($documento['arquivo']) ? 'ok' : 'muted' }}">{{ ! empty($documento['arquivo']) ? 'Com arquivo' : 'Sem arquivo' }}</span></td>
                                    <td><span class="prazzu-pill {{ ! empty($documento['portal_ativo']) ? 'ok' : 'muted' }}">{{ ! empty($documento['portal_ativo']) ? 'Ativo' : 'Inativo' }}</span></td>
                                    <td class="documentos-row-actions documentos-row-actions--filament">
                                        @if (! empty($documento['arquivo_url']))
                                            <a href="{{ $documento['arquivo_url'] }}" target="_blank" rel="noopener noreferrer">Arquivo</a>
                                        @endif
                                        <a href="{{ $documento['enterprise_url'] ?? $documento['edit_url'] }}">Enterprise</a>
                                    </td>
                                    <td class="prazzu-modal-cell documentos-filament-modal-cell">
                                        @include('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions])
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="prazzu-empty"><strong>Nenhum documento encontrado.</strong><br>Cadastre o primeiro documento usando o botão principal acima. Quando houver registros, eles aparecerão aqui com status, vencimento e portal.</td></tr>
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
