<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/contabilidade-ux-lote6.css') }}?v={{ file_exists(public_path('css/contabilidade-ux-lote6.css')) ? filemtime(public_path('css/contabilidade-ux-lote6.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/contabilidade-operacao-lote3.css') }}?v={{ file_exists(public_path('css/contabilidade-operacao-lote3.css')) ? filemtime(public_path('css/contabilidade-operacao-lote3.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/central-aprovacoes.css') }}?v={{ file_exists(public_path('css/central-aprovacoes.css')) ? filemtime(public_path('css/central-aprovacoes.css')) : time() }}">

    <div class="ca-page">
        <section class="contabilidade-lote3-scope" aria-label="Propósito da aba Aprovações">
            <div class="contabilidade-lote3-scope__top">
                <div>
                    <span class="contabilidade-lote3-eyebrow"><i class="bi bi-check2-square"></i> Aprovações</span>
                    <h2>Central exclusiva para decisões e rastreabilidade</h2>
                    <p>Aqui ficam aprovar, reprovar, pedir ajuste e acompanhar histórico de decisão. Pendências continuam em Pendências; documentos continuam em Documentos.</p>
                </div>
                <div class="contabilidade-lote3-actions">
                    <a class="contabilidade-lote3-action primary" href="#fila-aprovacoes"><i class="bi bi-inbox"></i> Ver fila</a>
                    <a class="contabilidade-lote3-action" href="{{ \App\Filament\Pages\Pendencias::getUrl() }}"><i class="bi bi-list-check"></i> Pendências</a>
                    <a class="contabilidade-lote3-action" href="{{ \App\Filament\Pages\Auditoria::getUrl() }}"><i class="bi bi-shield-check"></i> Auditoria</a>
                </div>
            </div>
            <div class="contabilidade-lote3-rules">
                <div class="contabilidade-lote3-rule"><strong><i class="bi bi-bullseye"></i> Propósito</strong><span>Tomar decisões com contexto suficiente e registro claro.</span></div>
                <div class="contabilidade-lote3-rule"><strong><i class="bi bi-box-arrow-up-right"></i> Não duplicar</strong><span>Não explicar resolução de pendências nem gestão documental nesta tela.</span></div>
                <div class="contabilidade-lote3-rule"><strong><i class="bi bi-shield-lock"></i> UX segura</strong><span>Decisão só depois de revisar contexto, motivo e impacto.</span></div>
            </div>
        </section>

        <section class="ca-hero {{ $diagnostico['tom'] ?? 'info' }}">
            <div>
                <span>GOVERNANÇA / APROVAÇÕES</span>
                <h1>{{ $diagnostico['titulo'] ?? 'Central de Aprovações' }}</h1>
                <p>{{ $diagnostico['descricao'] ?? 'Fila diária para aprovar, reprovar com comentário e acompanhar decisões com rastreabilidade.' }}</p>
                <strong>{{ $diagnostico['acao'] ?? 'Revise a fila priorizada' }}</strong>
            </div>

            <div class="ca-hero-actions">
                @foreach ($atalhos as $atalho)
                    <a class="{{ ! empty($atalho['primary']) ? 'primary' : '' }}" href="{{ $atalho['url'] }}">{{ $atalho['label'] }}</a>
                @endforeach
            </div>
        </section>

        @if (! $temTabelaAprovacoes)
            <section class="ca-empty ca-empty-main">
                <strong>Central pronta, mas a tabela item_controle_aprovacoes não existe no banco.</strong>
                <p>Crie a estrutura de aprovações do projeto para que a fila seja carregada automaticamente com dados reais.</p>
            </section>
        @endif

        <section class="ca-stats">
            <article><span>Total</span><strong>{{ number_format($resumo['total'] ?? 0, 0, ',', '.') }}</strong><small>Solicitações no seu escopo</small></article>
            <article class="warning"><span>Pendentes</span><strong>{{ number_format($resumo['pendentes'] ?? 0, 0, ',', '.') }}</strong><small>Aguardando decisão</small></article>
            <article class="danger"><span>Atrasadas</span><strong>{{ number_format($resumo['atrasadas'] ?? 0, 0, ',', '.') }}</strong><small>Devem ser tratadas primeiro</small></article>
            <article class="critical"><span>Críticas</span><strong>{{ number_format($resumo['criticas'] ?? 0, 0, ',', '.') }}</strong><small>Alta prioridade aberta</small></article>
            <article class="success"><span>Resolvidas</span><strong>{{ number_format(($resumo['aprovadas'] ?? 0) + ($resumo['reprovadas'] ?? 0), 0, ',', '.') }}</strong><small>{{ $resumo['taxaResolucao'] ?? 0 }}% da fila total</small></article>
            <article><span>Tempo médio</span><strong>{{ $resumo['tempoMedio'] ?? '0h' }}</strong><small>Resposta das decisões</small></article>
        </section>

        <section class="ca-focus-panel">
            <div class="ca-section-header">
                <div>
                    <span class="ca-kicker">Prioridade operacional</span>
                    <h2>O que precisa da sua atenção agora</h2>
                    <p>Itens atrasados e críticos aparecem aqui para o usuário decidir sem perder tempo analisando toda a fila.</p>
                </div>
                <strong>{{ count($atencaoAgora) }} prioridade(s)</strong>
            </div>

            <div class="ca-focus-grid">
                @forelse ($atencaoAgora as $item)
                    @include('filament.pages.partials.central-aprovacoes-card', ['item' => $item, 'compacto' => false, 'destaque' => true])
                @empty
                    <div class="ca-empty ca-empty-main"><strong>Nenhum alerta urgente.</strong><p>Não há aprovação atrasada ou crítica no seu escopo atual.</p></div>
                @endforelse
            </div>
        </section>

        <section class="ca-toolbar">
            <div>
                <label>Buscar</label>
                <input type="search" wire:model.live.debounce.400ms="busca" placeholder="Item, empresa ou descrição">
            </div>
            <div>
                <label>Status</label>
                <select wire:model.live="statusFiltro">
                    <option value="todos">Todos</option>
                    <option value="pendente">Pendentes</option>
                    <option value="aprovado">Aprovadas</option>
                    <option value="reprovado">Ajuste solicitado</option>
                </select>
            </div>
            <div>
                <label>Prioridade</label>
                <select wire:model.live="prioridadeFiltro">
                    <option value="todas">Todas</option>
                    <option value="critica">Crítica</option>
                    <option value="crítica">Crítica acentuada</option>
                    <option value="alta">Alta</option>
                    <option value="media">Média</option>
                    <option value="baixa">Baixa</option>
                </select>
            </div>
            <button type="button" wire:click="limparFiltros">Limpar filtros</button>
        </section>

        <section class="ca-layout">
            <div class="ca-main-card">
                <div class="ca-section-header">
                    <div>
                        <span class="ca-kicker">Fila principal</span>
                        <h2>Fila priorizada</h2>
                        <p>Ordenada por atraso, prioridade, vencimento e tempo aguardando. Todas as ações validam permissão antes de decidir.</p>
                    </div>
                    <span>{{ count($fila) }} item(ns)</span>
                </div>

                <div class="ca-list">
                    @forelse ($fila as $item)
                        @include('filament.pages.partials.central-aprovacoes-card', ['item' => $item, 'compacto' => false, 'destaque' => false])
                    @empty
                        <div class="ca-empty ca-empty-main"><strong>Nenhuma aprovação encontrada.</strong><p>Ajuste os filtros ou cadastre uma solicitação para alimentar a central.</p></div>
                    @endforelse
                </div>
            </div>

            <aside class="ca-side">
                <section class="ca-main-card">
                    <div class="ca-section-header compact"><div><span class="ca-kicker">Visão rápida</span><h2>Kanban resumido</h2><p>Amostra das aprovações por status.</p></div></div>
                    <div class="ca-mini-kanban">
                        @foreach ($kanban as $coluna)
                            <article class="{{ $coluna['tom'] }}">
                                <header><strong>{{ $coluna['titulo'] }}</strong><span>{{ count($coluna['items']) }}</span></header>
                                <p>{{ $coluna['descricao'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="ca-main-card">
                    <div class="ca-section-header compact"><div><span class="ca-kicker">Gargalos</span><h2>Por responsável</h2><p>Pendências abertas por pessoa.</p></div></div>
                    <div class="ca-ranking">
                        @forelse ($responsaveis as $responsavel)
                            <div><span>{{ $responsavel['nome'] }}</span><strong>{{ $responsavel['total'] }}</strong></div>
                        @empty
                            <div class="ca-empty"><strong>Sem gargalo.</strong><p>Nenhuma aprovação pendente por responsável.</p></div>
                        @endforelse
                    </div>
                </section>

                <section class="ca-main-card">
                    <div class="ca-section-header compact"><div><span class="ca-kicker">Rastreabilidade</span><h2>Histórico</h2><p>Últimas respostas registradas.</p></div></div>
                    <div class="ca-mini-timeline">
                        @forelse ($historico as $item)
                            <article class="{{ $item['tom'] }}">
                                <strong>{{ $item['titulo'] }}</strong>
                                <span>{{ $item['status_label'] }} • {{ $item['respondido_em'] }} • {{ $item['aprovador'] }}</span>
                            </article>
                        @empty
                            <div class="ca-empty"><strong>Nenhuma decisão ainda.</strong><p>Ao aprovar ou solicitar ajuste, o histórico aparece aqui.</p></div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </section>
    </div>

    @if ($detalhesEmVisualizacao)
        <div class="ca-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="ca-detalhes-titulo">
            <div class="ca-modal-card ca-modal-card-wide ca-detail-modal">
                <div class="ca-modal-head">
                    <div>
                        <span class="ca-kicker">Detalhes da aprovação</span>
                        <h2 id="ca-detalhes-titulo">{{ $detalhesEmVisualizacao['titulo'] }}</h2>
                        <p>Revise contexto, prazos, risco e histórico sem sair da Central de Aprovações.</p>
                    </div>
                    <button type="button" class="ca-icon-button" wire:click="fecharDetalhesItem" aria-label="Fechar detalhes">×</button>
                </div>

                <div class="ca-modal-warning {{ $detalhesEmVisualizacao['tom'] }}">
                    <strong>{{ $detalhesEmVisualizacao['status_label'] }}</strong>
                    <p>{{ $detalhesEmVisualizacao['decisao_alerta'] }}</p>
                </div>

                <div class="ca-detail-grid">
                    <div><span>Empresa</span><strong>{{ $detalhesEmVisualizacao['empresa'] }}</strong></div>
                    <div><span>Tipo</span><strong>{{ $detalhesEmVisualizacao['tipo'] }}</strong></div>
                    <div><span>Prioridade</span><strong>{{ $detalhesEmVisualizacao['prioridade'] }}</strong></div>
                    <div><span>Vencimento</span><strong class="{{ $detalhesEmVisualizacao['atrasado'] ? 'ca-text-danger' : '' }}">{{ $detalhesEmVisualizacao['vencimento'] }}</strong></div>
                    <div><span>Status do item</span><strong>{{ $detalhesEmVisualizacao['item_status'] }}</strong></div>
                    <div><span>Status da aprovação</span><strong>{{ $detalhesEmVisualizacao['approval_status'] }}</strong></div>
                    <div><span>Documento</span><strong>{{ $detalhesEmVisualizacao['document_status'] }}</strong></div>
                    <div><span>Assinatura</span><strong>{{ $detalhesEmVisualizacao['signature_status'] }}</strong></div>
                </div>

                <div class="ca-detail-columns">
                    <section>
                        <h3>Descrição do item</h3>
                        <p>{{ $detalhesEmVisualizacao['descricao_completa'] }}</p>
                    </section>
                    <section>
                        <h3>Observação operacional</h3>
                        <p>{{ $detalhesEmVisualizacao['item_observacao'] }}</p>
                    </section>
                    <section>
                        <h3>Pedido de aprovação</h3>
                        <p>{{ $detalhesEmVisualizacao['observacao'] }}</p>
                    </section>
                    @if (! empty($detalhesEmVisualizacao['resposta']))
                        <section>
                            <h3>Última resposta</h3>
                            <p>{{ $detalhesEmVisualizacao['resposta'] }}</p>
                        </section>
                    @endif
                </div>

                <div class="ca-detail-grid ca-detail-grid-compact">
                    <div><span>Responsável</span><strong>{{ $detalhesEmVisualizacao['responsavel'] }}</strong></div>
                    <div><span>Solicitante</span><strong>{{ $detalhesEmVisualizacao['solicitante'] }}</strong></div>
                    <div><span>Aprovador</span><strong>{{ $detalhesEmVisualizacao['aprovador'] }}</strong></div>
                    <div><span>Aguardando</span><strong>{{ $detalhesEmVisualizacao['idade'] }}</strong></div>
                    <div><span>Solicitado em</span><strong>{{ $detalhesEmVisualizacao['solicitado_em'] }}</strong></div>
                    <div><span>Respondido em</span><strong>{{ $detalhesEmVisualizacao['respondido_em'] }}</strong></div>
                    <div><span>Criado em</span><strong>{{ $detalhesEmVisualizacao['criado_em'] }}</strong></div>
                    <div><span>Atualizado em</span><strong>{{ $detalhesEmVisualizacao['atualizado_em'] }}</strong></div>
                </div>

                @if ($detalhesEmVisualizacao['risk_probability'] || $detalhesEmVisualizacao['risk_impact'] || $detalhesEmVisualizacao['risk_score'])
                    <div class="ca-risk-strip">
                        <div><span>Probabilidade</span><strong>{{ $detalhesEmVisualizacao['risk_probability'] ?? '-' }}</strong></div>
                        <div><span>Impacto</span><strong>{{ $detalhesEmVisualizacao['risk_impact'] ?? '-' }}</strong></div>
                        <div><span>Score de risco</span><strong>{{ $detalhesEmVisualizacao['risk_score'] ?? '-' }}</strong></div>
                    </div>
                @endif

                <div class="ca-decision-checklist">
                    <strong>Checklist antes da decisão</strong>
                    <ul>
                        @foreach ($detalhesEmVisualizacao['decisao_checklist'] as $check)
                            <li>{{ $check }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="ca-modal-actions ca-modal-actions-split">
                    <button type="button" class="ghost" wire:click="fecharDetalhesItem" wire:loading.attr="disabled">Fechar</button>
                    <div>
                        @if (! empty($detalhesEmVisualizacao['url']))
                            <a class="ca-secondary-link" href="{{ $detalhesEmVisualizacao['url'] }}">Abrir cadastro completo</a>
                        @endif
                        @if ($detalhesEmVisualizacao['status'] === 'pendente')
                            <button type="button" class="reject" wire:click="abrirReprovacao({{ $detalhesEmVisualizacao['id'] }})" wire:loading.attr="disabled">Solicitar ajuste</button>
                            <button type="button" class="approve" wire:click="abrirConfirmacaoAprovacao({{ $detalhesEmVisualizacao['id'] }})" wire:loading.attr="disabled">Aprovar com revisão</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($aprovacaoEmConfirmacao)
        <div class="ca-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="ca-aprovar-titulo">
            <div class="ca-modal-card ca-modal-card-wide">
                <div class="ca-section-header compact">
                    <div>
                        <span class="ca-kicker">Confirmação obrigatória</span>
                        <h2 id="ca-aprovar-titulo">Aprovar com revisão final?</h2>
                        <p>Antes de confirmar, confira os dados críticos abaixo. A decisão será registrada no histórico e sincronizada com o item de controle.</p>
                    </div>
                </div>

                <div class="ca-modal-warning {{ $aprovacaoEmConfirmacao['tom'] }}">
                    <strong>Antes de aprovar</strong>
                    <p>{{ $aprovacaoEmConfirmacao['decisao_alerta'] }}</p>
                </div>

                <div class="ca-modal-summary ca-modal-summary-strong">
                    <strong>{{ $aprovacaoEmConfirmacao['titulo'] }}</strong>
                    <span>{{ $aprovacaoEmConfirmacao['empresa'] }} • {{ $aprovacaoEmConfirmacao['prioridade'] }} • Vence: {{ $aprovacaoEmConfirmacao['vencimento'] }}</span>
                    <p>{{ $aprovacaoEmConfirmacao['descricao_completa'] }}</p>
                </div>

                <div class="ca-decision-grid">
                    <div><span>Responsável</span><strong>{{ $aprovacaoEmConfirmacao['responsavel'] }}</strong></div>
                    <div><span>Solicitante</span><strong>{{ $aprovacaoEmConfirmacao['solicitante'] }}</strong></div>
                    <div><span>Aguardando</span><strong>{{ $aprovacaoEmConfirmacao['idade'] }}</strong></div>
                    <div><span>Status atual</span><strong>{{ $aprovacaoEmConfirmacao['status_label'] }}</strong></div>
                </div>

                <div class="ca-decision-checklist">
                    <strong>Checklist de decisão</strong>
                    <ul>
                        @foreach ($aprovacaoEmConfirmacao['decisao_checklist'] as $check)
                            <li>{{ $check }}</li>
                        @endforeach
                    </ul>
                </div>

                <label class="ca-confirm-check">
                    <input type="checkbox" wire:model.live="aprovacaoRevisada">
                    <span>Confirmei os dados acima e estou ciente de que esta aprovação altera o status do item.</span>
                </label>

                <div class="ca-modal-actions">
                    <button type="button" class="ghost" wire:click="cancelarConfirmacaoAprovacao" wire:loading.attr="disabled">Cancelar</button>
                    <button type="button" class="approve" wire:click="confirmarAprovacao" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="confirmarAprovacao">Confirmar aprovação</span>
                        <span wire:loading wire:target="confirmarAprovacao">Registrando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($reprovacaoEmEdicao)
        <div class="ca-modal-backdrop" role="dialog" aria-modal="true">
            <div class="ca-modal-card">
                <div class="ca-section-header compact">
                    <div>
                        <span class="ca-kicker">Ajuste necessário</span>
                        <h2>Reprovar aprovação</h2>
                        <p>Informe o motivo para orientar a correção e manter histórico claro da decisão.</p>
                    </div>
                </div>

                <div class="ca-modal-warning danger">
                    <strong>Oriente a correção</strong>
                    <p>A reprovação deve explicar exatamente o que precisa ser ajustado para evitar retrabalho e manter a rastreabilidade da decisão.</p>
                </div>

                <div class="ca-modal-summary ca-modal-summary-strong">
                    <strong>{{ $reprovacaoEmEdicao['titulo'] }}</strong>
                    <span>{{ $reprovacaoEmEdicao['empresa'] }} • {{ $reprovacaoEmEdicao['prioridade'] }} • Responsável: {{ $reprovacaoEmEdicao['responsavel'] }}</span>
                    <p>{{ $reprovacaoEmEdicao['descricao_completa'] }}</p>
                </div>

                <div class="ca-decision-grid">
                    <div><span>Solicitante</span><strong>{{ $reprovacaoEmEdicao['solicitante'] }}</strong></div>
                    <div><span>Aguardando</span><strong>{{ $reprovacaoEmEdicao['idade'] }}</strong></div>
                    <div><span>Vencimento</span><strong>{{ $reprovacaoEmEdicao['vencimento'] }}</strong></div>
                    <div><span>Status atual</span><strong>{{ $reprovacaoEmEdicao['status_label'] }}</strong></div>
                </div>

                <label class="ca-modal-label" for="motivo-reprovacao">Comentário obrigatório</label>
                <textarea id="motivo-reprovacao" wire:model.defer="motivoReprovacao" rows="5" placeholder="Ex.: Documento sem assinatura do responsável ou anexo incorreto. Informe uma orientação clara para correção."></textarea>

                <div class="ca-modal-actions">
                    <button type="button" class="ghost" wire:click="cancelarReprovacao" wire:loading.attr="disabled">Cancelar</button>
                    <button type="button" class="reject" wire:click="reprovarComComentario" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="reprovarComComentario">Confirmar reprovação</span>
                        <span wire:loading wire:target="reprovarComComentario">Registrando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
