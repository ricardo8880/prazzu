<x-filament-panels::page>
    @php
        $percent = (int) ($progress['percent'] ?? 0);
        $empresaNome = $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? 'Empresa não selecionada';
        $empresasLista = collect($empresas ?? []);
        $mensagensChat = collect($chat ?? []);
        $solicitacoesAbertas = collect($supportQueue ?? []);
        $pendenciasCliente = collect($pendingActions ?? []);
        $documentosPublicados = collect($documents ?? []);
        $entregasCalendario = collect($calendar ?? []);
        $timelineAtendimento = collect($timeline ?? []);
        $ultimaMensagem = $mensagensChat->last();
        $ultimaAtualizacao = $ultimaMensagem['created_at_label'] ?? ($solicitacoesAbertas->first()['created_at_label'] ?? 'Sem atualização recente');
        $mensagensCliente = $mensagensChat->where('css_class', 'cliente')->count();
        $mensagensEquipe = $mensagensChat->where('css_class', 'equipe')->count();
        $atendimentoAberto = $mensagensChat->where('conversa_status', 'aberta')->count() > 0 || $solicitacoesAbertas->count() > 0;
        $statusAtendimento = $atendimentoAberto ? ($mensagensCliente > $mensagensEquipe ? 'Aguardando suporte' : 'Em andamento') : 'Sem atendimento aberto';
        $statusClass = $atendimentoAberto ? ($mensagensCliente > $mensagensEquipe ? 'warn' : 'ok') : 'muted';
        $protocolo = 'PC-' . str_pad((string) ($empresaId ?? 0), 5, '0', STR_PAD_LEFT) . '-' . now()->format('Y');
        $responsavel = auth()->user()?->name ?? 'Equipe de suporte';
        $suporteOnline = now()->isWeekday() && now()->hour >= 8 && now()->hour <= 18;
        $canalAtivo = $solicitacoesAbertas->first();
        $prioridadeAtual = $canalAtivo['prioridade'] ?? ($pendenciasCliente->where('is_late', true)->count() ? 'alta' : 'media');
        $prioridadeLabel = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'urgente' => 'Urgente'][$prioridadeAtual] ?? ucfirst((string) $prioridadeAtual);
        $temPendencias = $pendenciasCliente->count() > 0;
        $temSolicitacoes = $solicitacoesAbertas->count() > 0;
        $portalLink = $portalLink ?? null;
        $supportForm = $supportForm ?? ['prioridades' => ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'urgente' => 'Urgente']];
        $clienteVisualizouAteId = $clienteVisualizouAteId ?? ($this->clienteVisualizouAteId ?? null);
        $suporteVisualizouAteId = $suporteVisualizouAteId ?? ($this->suporteVisualizouAteId ?? null);
        $clienteDigitando = $clienteDigitando ?? ($this->clienteDigitando ?? false);
        $clienteDigitandoNome = $clienteDigitandoNome ?? ($this->clienteDigitandoNome ?? null);
    @endphp
<div class="pc-service-shell" x-data="{
        shouldStick: true,
        activeSection: 'conversa',
        quickReplyOpen: false,
        internalNote: localStorage.getItem('portal_cliente_internal_note_{{ $empresaId ?? 0 }}') || '',
        quickReplies: [
            'Olá! Tudo bem? Vou te ajudar com isso. Pode me enviar mais detalhes ou um print da mensagem que aparece?',
            'Entendi. Já estou verificando por aqui e retorno assim que concluir a análise.',
            'Pode testar novamente, por favor? Fiz o ajuste necessário e vou acompanhar por aqui.',
            'Obrigado pelo retorno. Vou registrar essa informação no atendimento e seguir com a tratativa.',
            'Conseguimos resolver sua solicitação. Posso marcar o atendimento como resolvido?'
        ],
        setSection(section) {
            this.activeSection = section;
            this.quickReplyOpen = false;
            if (section === 'conversa') this.scrollChat(true);
        },
        applyQuickReply(text) {
            this.quickReplyOpen = false;
            this.$wire.set('respostaChat', text);
            this.$nextTick(() => {
                const textarea = this.$refs.replyTextarea;
                if (!textarea) return;
                textarea.value = text;
                textarea.focus();
                this.grow(textarea);
            });
        },
        saveInternalNote() {
            localStorage.setItem('portal_cliente_internal_note_{{ $empresaId ?? 0 }}', this.internalNote || '');
        },
        scrollChat(force = false) {
            this.$nextTick(() => {
                const el = this.$refs.chatBody;
                if (!el) return;
                const nearBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 140;
                if (force || this.shouldStick || nearBottom) {
                    el.scrollTop = el.scrollHeight;
                }
            });
        },
        watchScroll() {
            const el = this.$refs.chatBody;
            if (!el) return;
            this.shouldStick = el.scrollHeight - el.scrollTop - el.clientHeight < 170;
        },
        grow(el) {
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 128) + 'px';
        }
    }" x-init="scrollChat(true); setTimeout(() => scrollChat(true), 120); setTimeout(() => scrollChat(true), 450)" x-on:livewire:navigated.window="scrollChat(true)" x-on:livewire:updated.window="scrollChat()" x-on:livewire:update.window="scrollChat()" x-on:message.processed.window="scrollChat()">
        <div class="pc-workspace">
            <aside class="pc-panel pc-inbox" aria-label="Atendimentos do portal">
                <div class="pc-inbox-model-head">
                    <div class="pc-inbox-model-title">
                        <strong>Meus atendimentos</strong>
                        <span>{{ $empresaNome }}</span>
                    </div>
                    <button type="button" class="pc-inbox-filter" title="Filtrar atendimentos" aria-label="Filtrar atendimentos">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 7h16M7 12h10M10 17h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                @if ($empresasLista->count() > 1)
                    <div class="pc-inbox-model-select-wrap">
                        <select class="pc-company-select pc-company-select-model" wire:model.live="empresaSelecionadaId" aria-label="Selecionar empresa">
                            @foreach ($empresasLista as $empresaOpcao)
                                <option value="{{ $empresaOpcao['id'] }}">{{ $empresaOpcao['nome_fantasia'] ?? $empresaOpcao['razao_social'] ?? 'Empresa #' . $empresaOpcao['id'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="pc-ticket-list pc-ticket-list-model">
                    <div class="pc-ticket-card pc-ticket-model is-active">
                        <div class="pc-ticket-top">
                            <strong>{{ $protocolo }}</strong>
                        </div>
                        <div class="pc-ticket-meta">
                            <span class="pc-dot {{ $statusClass === 'warn' ? 'warn' : ($atendimentoAberto ? 'ok' : 'muted') }}">{{ $statusAtendimento }}</span>
                        </div>
                        <p>{{ $ultimaMensagem['mensagem_texto'] ?? $ultimaMensagem['mensagem'] ?? 'Conversa principal do portal do cliente' }}</p>
                        <div class="pc-ticket-meta">
                            <span class="pc-ticket-date">{{ $ultimaAtualizacao }}</span>
                            @if ($mensagensCliente > 0)
                                <span class="pc-ticket-unread" title="Mensagens do cliente">{{ $mensagensCliente }}</span>
                            @endif
                        </div>
                    </div>

                    @foreach ($solicitacoesAbertas->take(4) as $solicitacao)
                        @php
                            $solicitacaoPrioridade = (string) ($solicitacao['prioridade'] ?? 'media');
                            $solicitacaoPrioridadeLabel = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'urgente' => 'Urgente'][$solicitacaoPrioridade] ?? ucfirst($solicitacaoPrioridade);
                            $solicitacaoStatusLabel = ($solicitacao['status_label'] ?? null) ?: (($solicitacao['status'] ?? null) ? ucfirst((string) $solicitacao['status']) : 'Em andamento');
                            $solicitacaoStatusClass = in_array(strtolower((string) $solicitacaoStatusLabel), ['concluído', 'concluido', 'resolvido', 'finalizado'], true) ? 'muted' : (($solicitacaoPrioridade === 'alta' || $solicitacaoPrioridade === 'urgente') ? 'warn' : 'ok');
                        @endphp
                        <div class="pc-ticket-card pc-ticket-model">
                            <div class="pc-ticket-top">
                                <strong>{{ $solicitacao['protocolo'] ?? $solicitacao['codigo'] ?? $solicitacao['titulo'] ?? 'Solicitação' }}</strong>
                            </div>
                            <div class="pc-ticket-meta">
                                <span class="pc-dot {{ $solicitacaoStatusClass }}">{{ $solicitacaoStatusLabel }}</span>
                            </div>
                            <p>{{ $solicitacao['descricao'] ?? $solicitacao['titulo'] ?? 'Solicitação em acompanhamento' }}</p>
                            <div class="pc-ticket-meta">
                                <span class="pc-ticket-date">{{ $solicitacao['created_at_label'] ?? $solicitacao['updated_at_label'] ?? $solicitacaoPrioridadeLabel }}</span>
                                @if (! empty($solicitacao['mensagens_count']) || ! empty($solicitacao['unread_count']))
                                    <span class="pc-ticket-unread">{{ $solicitacao['unread_count'] ?? $solicitacao['mensagens_count'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <details class="pc-ticket-create-compact">
                    <summary class="pc-ticket-create-summary">Novo atendimento</summary>
                    <div class="pc-ticket-create-body">
                        <form class="pc-ticket-form" wire:submit.prevent="criarSolicitacao">
                            <input class="pc-input" type="text" wire:model.defer="solicitacaoTitulo" placeholder="Título da solicitação">
                            <textarea class="pc-textarea" wire:model.defer="solicitacaoDescricao" placeholder="Descreva a necessidade"></textarea>
                            <select class="pc-select" wire:model.defer="solicitacaoPrioridade">
                                @foreach (($supportForm['prioridades'] ?? []) as $valor => $label)
                                    <option value="{{ $valor }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="pc-btn" wire:loading.attr="disabled" wire:target="criarSolicitacao">Criar atendimento</button>
                        </form>
                    </div>
                </details>

                <div class="pc-inbox-footer-action">
                    <a href="{{ \App\Filament\Pages\Atendimentos::getUrl() }}" class="pc-view-all-btn">Ver todos os atendimentos</a>
                </div>
            </aside>

            <section class="pc-panel pc-main" aria-label="Conversa com o cliente">
                <div class="pc-loading-overlay" wire:loading.flex wire:target="empresaSelecionadaId,finalizarConversa">Atualizando atendimento...</div>

                <header class="pc-header">
                    <div class="pc-title-wrap">
                        <div class="pc-title-line">
                            <h2>Atendimento {{ $protocolo }}</h2>
                            <span class="pc-badge {{ $statusClass }}">{{ $statusAtendimento }}</span>
                        </div>
                        <div class="pc-subtitle">{{ $suporteOnline ? 'Suporte online' : 'Suporte offline' }} • Atualizado {{ $ultimaAtualizacao }}</div>
                    </div>
                    <div class="pc-actions">
                        <span class="pc-realtime-pill">tempo real</span>
                        <button type="button" class="pc-btn secondary" wire:click="finalizarConversa" wire:loading.attr="disabled" wire:target="finalizarConversa">✓ Marcar como resolvido</button>
                        @if ($portalLink)
                            <a class="pc-btn secondary" href="{{ $portalLink }}" target="_blank" rel="noopener">Abrir portal público</a>
                        @endif
                    </div>
                </header>

                <nav class="pc-tabs" aria-label="Seções do atendimento">
                    <button type="button" class="pc-tab" :class="activeSection === 'conversa' ? 'is-active' : ''" :aria-selected="activeSection === 'conversa'" role="tab" x-on:click="setSection('conversa')">Conversa</button>
                    <button type="button" class="pc-tab" :class="activeSection === 'historico' ? 'is-active' : ''" :aria-selected="activeSection === 'historico'" role="tab" x-on:click="setSection('historico')">Histórico</button>
                    <button type="button" class="pc-tab" :class="activeSection === 'anotacoes' ? 'is-active' : ''" :aria-selected="activeSection === 'anotacoes'" role="tab" x-on:click="setSection('anotacoes')">Anotações</button>
                </nav>

                <main class="pc-messages" id="portalClienteChatBody" x-show="activeSection === 'conversa'" x-cloak x-ref="chatBody" x-init="scrollChat(true)" x-on:scroll.passive="watchScroll()">
                    @php $dataAnteriorMensagem = null; @endphp
                    @forelse ($mensagensChat as $mensagem)
                        @php
                            $isCliente = ($mensagem['css_class'] ?? '') === 'cliente';
                            $autor = $mensagem['nome'] ?? ($isCliente ? 'Cliente' : 'Equipe');
                            $iniciais = strtoupper(substr((string) $autor, 0, 2));
                            $textoMensagem = trim((string) ($mensagem['mensagem_texto'] ?? $mensagem['mensagem'] ?? ''));
                            $dataMensagem = $mensagem['date_label'] ?? $mensagem['data_label'] ?? null;

                            if (! $dataMensagem && ! empty($mensagem['created_at'])) {
                                try {
                                    $dataMensagem = \Illuminate\Support\Carbon::parse($mensagem['created_at'])->format('d/m/Y');
                                } catch (\Throwable $e) {
                                    $dataMensagem = null;
                                }
                            }

                            $mostrarDivisorData = $dataMensagem && $dataMensagem !== $dataAnteriorMensagem;
                            if ($dataMensagem) {
                                $dataAnteriorMensagem = $dataMensagem;
                            }
                        @endphp

                        @if ($mostrarDivisorData)
                            <div class="pc-date-divider" wire:key="portal-chat-date-{{ $dataMensagem }}-{{ $loop->index }}">{{ $dataMensagem }}</div>
                        @endif

                        <article class="pc-message {{ $isCliente ? 'cliente' : 'equipe' }}" wire:key="portal-chat-message-{{ $mensagem['id'] ?? $loop->index }}" data-message-id="{{ $mensagem['id'] ?? 0 }}">
                            @unless ($isCliente)
                                <div class="pc-message-avatar" title="{{ $autor }}">{{ $iniciais ?: 'EQ' }}</div>
                            @endunless

                            <div class="pc-bubble">
                                @if ($isCliente)
                                    <div class="pc-message-avatar" title="{{ $autor }}">{{ $iniciais ?: 'CL' }}</div>
                                @endif

                                <div class="pc-bubble-content">
                                    <div class="pc-bubble-head">
                                        <span>{{ $autor }} {{ $isCliente ? '(Cliente)' : '(Suporte)' }}</span>
                                        <span>{{ $mensagem['created_at_label'] ?? '' }}</span>
                                    </div>
                                    @if ($textoMensagem !== '')
                                        <div class="pc-bubble-text">{{ $textoMensagem }}</div>
                                    @endif

                                    @if (! empty($mensagem['attachments']))
                                        <div class="pc-attachments">
                                            @foreach ($mensagem['attachments'] as $anexo)
                                                <a class="pc-attachment" href="{{ $anexo['url'] }}" target="_blank" rel="noopener" download>
                                                    <span>{{ ($anexo['is_image'] ?? false) ? '🖼️' : '📄' }}</span>
                                                    <span>
                                                        <strong>{{ $anexo['nome'] ?? 'Anexo' }}</strong>
                                                        <span>{{ $anexo['size_label'] ?? ($anexo['mime_type'] ?? 'arquivo') }}</span>
                                                    </span>
                                                    <span>↗</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="pc-message-status-line">
                                        <span>{{ $isCliente ? 'Mensagem do cliente' : 'Resposta do suporte' }}</span>
                                        @if (! $isCliente && ! empty($mensagem['id']) && $clienteVisualizouAteId && (int) $mensagem['id'] <= (int) $clienteVisualizouAteId)
                                            <span class="pc-seen-status">✓✓ Visualizado pelo cliente</span>
                                        @elseif ($isCliente && ! empty($mensagem['id']) && $suporteVisualizouAteId && (int) $mensagem['id'] <= (int) $suporteVisualizouAteId)
                                            <span class="pc-seen-status">✓✓ Visualizado pelo suporte</span>
                                        @else
                                            <span>{{ $isCliente ? 'Aguardando leitura' : 'Enviada' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="pc-empty">
                            Ainda não há mensagens neste atendimento. Envie a primeira resposta para iniciar a conversa com histórico organizado.
                        </div>
                    @endforelse
                </main>

                <section class="pc-section-panel" x-show="activeSection === 'historico'" x-cloak aria-label="Histórico do atendimento">
                    <div class="pc-history-list">
                        <div class="pc-history-summary">
                            <div class="pc-history-summary-card"><span>Protocolo</span><strong>{{ $protocolo }}</strong></div>
                            <div class="pc-history-summary-card"><span>Status atual</span><strong>{{ $statusAtendimento }}</strong></div>
                            <div class="pc-history-summary-card"><span>Atualizado em</span><strong>{{ $ultimaAtualizacao }}</strong></div>
                        </div>

                        <div class="pc-history-timeline">
                            @forelse ($timelineAtendimento as $evento)
                                @php
                                    $tipoEvento = $evento['tipo'] ?? $evento['type'] ?? 'info';
                                    $iconeEvento = $evento['icone'] ?? $evento['icon'] ?? match ($tipoEvento) {
                                        'status' => '✓',
                                        'prazo', 'sla' => '⏱',
                                        'pendencia' => '!',
                                        'documento' => '📎',
                                        'atendimento' => '💬',
                                        default => '•',
                                    };
                                    $corEvento = $evento['cor'] ?? $evento['color'] ?? match ($tipoEvento) {
                                        'status', 'documento' => 'ok',
                                        'prazo', 'sla' => 'warn',
                                        'pendencia' => 'danger',
                                        default => 'muted',
                                    };
                                @endphp
                                <div class="pc-history-item is-{{ $corEvento }}" wire:key="portal-history-{{ $loop->index }}">
                                    <span class="pc-history-icon {{ $corEvento }}">{{ $iconeEvento }}</span>
                                    <div class="pc-history-content">
                                        <div class="pc-history-title-row">
                                            <strong>{{ $evento['titulo'] ?? $evento['title'] ?? $evento['acao'] ?? 'Movimentação registrada' }}</strong>
                                            <span class="pc-history-badge">{{ ucfirst((string) $tipoEvento) }}</span>
                                        </div>
                                        <span class="pc-history-description">{{ $evento['descricao'] ?? $evento['description'] ?? 'Evento operacional registrado no atendimento.' }}</span>
                                    </div>
                                    <span class="pc-history-meta">{{ $evento['created_at_label'] ?? $evento['data_label'] ?? $evento['tempo'] ?? 'Agora' }}</span>
                                </div>
                            @empty
                                <div class="pc-history-item pc-history-empty is-muted">
                                    <span class="pc-history-icon muted">•</span>
                                    <div class="pc-history-content">
                                        <div class="pc-history-title-row">
                                            <strong>Histórico operacional ainda vazio</strong>
                                            <span class="pc-history-badge">Sistema</span>
                                        </div>
                                        <span class="pc-history-description">Assim que houver mudança de status, prioridade, prazo, documento ou ação relevante, ela aparecerá aqui sem misturar com o chat.</span>
                                    </div>
                                    <span class="pc-history-meta">—</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section class="pc-section-panel" x-show="activeSection === 'anotacoes'" x-cloak aria-label="Anotações internas do atendimento">
                    <div class="pc-notes-list">
                        <div class="pc-note-card">
                            <strong>Anotações internas</strong>
                            <span>Use este campo para observações rápidas do suporte. A anotação fica salva neste navegador e não aparece para o cliente.</span>
                            <textarea class="pc-note-textarea" x-model="internalNote" x-on:input.debounce.350ms="saveInternalNote()" placeholder="Ex.: cliente informou erro ao acessar, validar cache e retorno por e-mail..."></textarea>
                        </div>
                        <div class="pc-note-card">
                            <strong>Resumo operacional</strong>
                            <p>Cliente: {{ $empresaNome }}</p>
                            <p>Prioridade: {{ $prioridadeLabel }}</p>
                            <p>Mensagens no atendimento: {{ $mensagensChat->count() }}</p>
                        </div>
                    </div>
                </section>

                <div class="pc-typing-row" data-cliente-typing style="display: none;">
                    <span class="pc-typing-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                    <span data-cliente-typing-text>Cliente está digitando...</span>
                </div>

                <footer class="pc-chat-composer" x-show="activeSection === 'conversa'" x-cloak>
                    <div class="pc-upload-progress" wire:loading.flex wire:target="portalAnexos"><i></i> Preparando anexos...</div>

                    @if ($portalAnexos)
                        <div class="pc-upload-list">
                            @foreach ($portalAnexos as $anexoTemporario)
                                <span class="pc-upload-pill">📎 {{ method_exists($anexoTemporario, 'getClientOriginalName') ? $anexoTemporario->getClientOriginalName() : 'Arquivo anexado' }}</span>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="javascript:void(0)" enctype="multipart/form-data" data-admin-chat-form data-send-url="{{ route('admin.portal-cliente.chat.mensagem', ['empresa' => $empresaId]) }}" onsubmit="event.preventDefault(); window.portalClienteEnviarMensagemSuporte && window.portalClienteEnviarMensagemSuporte(this); return false;">
                        @csrf
                        <input type="hidden" name="empresa" value="{{ $empresaId }}">
                        <div class="pc-composer-tabs" role="tablist" aria-label="Modo de mensagem">
                            <button type="button" class="pc-composer-tab is-active" role="tab" aria-selected="true">Responder</button>
                        </div>
                        <div class="pc-composer-box">
                            <textarea class="pc-composer-textarea" x-ref="replyTextarea" name="mensagem" data-admin-chat-textarea placeholder="Digite sua mensagem..." aria-label="Mensagem de resposta para o cliente" x-on:input="grow($event.target); window.portalClienteAvisarSuporteDigitando && window.portalClienteAvisarSuporteDigitando($event.target.value)" x-on:keydown.enter="if (!$event.shiftKey && !$event.isComposing) { $event.preventDefault(); const form = $event.target.closest('form'); if (form && $event.target.value.trim().length > 0 && window.portalClienteEnviarMensagemSuporte) window.portalClienteEnviarMensagemSuporte(form); }"></textarea>
                            <div class="pc-composer-row">
                                <div class="pc-composer-tools">
                                    <label class="pc-file-trigger pc-icon-btn" title="Anexar arquivo">
                                        📎 Anexar
                                        <input type="file" name="portalAnexos[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv" aria-label="Anexar arquivos na conversa">
                                    </label>
                                    <button type="button" class="pc-icon-btn" :class="quickReplyOpen ? 'is-active' : ''" title="Inserir resposta rápida" x-on:click="quickReplyOpen = !quickReplyOpen">▱ Resposta rápida</button>
                                    <div class="pc-quick-replies-panel" x-show="quickReplyOpen" x-cloak x-on:click.outside="quickReplyOpen = false">
                                        <template x-for="reply in quickReplies" :key="reply">
                                            <button type="button" class="pc-quick-reply-option" x-text="reply" x-on:click="applyQuickReply(reply)"></button>
                                        </template>
                                    </div>
                                </div>
                                <button type="button" class="pc-btn pc-btn-send-split" wire:loading.attr="disabled" wire:target="responderChat,portalAnexos" data-admin-chat-submit>
                                    <span wire:loading.remove wire:target="responderChat" data-send-label>Enviar</span>
                                    <span class="pc-send-loading" wire:loading.inline-flex wire:target="responderChat" data-send-loading><i></i> Enviando</span>
                                </button>
                            </div>
                        </div>
                        <div class="pc-composer-helper">
                            <span>Enter envia • Shift + Enter quebra linha</span>
                            <span wire:dirty wire:target="respostaChat">digitando...</span>
                        </div>
                        @error('respostaChat') <div class="pc-error">{{ $message }}</div> @enderror
                        @error('portalAnexos') <div class="pc-error">{{ $message }}</div> @enderror
                        @error('portalAnexos.*') <div class="pc-error">{{ $message }}</div> @enderror
                    </form>
                </footer>
            </section>

            <aside class="pc-context" aria-label="Contexto do cliente e atendimento">
                <div class="pc-card">
                    <div class="pc-client-card-head">
                        <div class="pc-avatar">{{ strtoupper(substr($empresaNome, 0, 2)) }}</div>
                        <div class="pc-client-name">
                            <strong>{{ $empresaNome }}</strong>
                            <span>{{ $portalLink ? 'Portal público ativo' : 'Portal interno' }}</span>
                        </div>
                        @if ($portalLink)
                            <a class="pc-btn secondary" href="{{ $portalLink }}" target="_blank" rel="noopener">Ver perfil</a>
                        @endif
                    </div>

                    <div class="pc-client-status-row">
                        <span class="pc-health-pill {{ $statusClass }}">{{ $statusAtendimento }}</span>
                        <span class="pc-muted">{{ $suporteOnline ? 'Suporte disponível' : 'Fora do horário' }}</span>
                    </div>

                    <div class="pc-stats-grid">
                        <div class="pc-stat"><span>Mensagens</span><strong>{{ $mensagensChat->count() }}</strong></div>
                        <div class="pc-stat"><span>Pendências</span><strong>{{ $pendenciasCliente->count() }}</strong></div>
                        <div class="pc-stat"><span>Progresso</span><strong>{{ $percent }}%</strong></div>
                    </div>
                </div>

                <div class="pc-next-step">
                    <span>Próximo passo</span>
                    @if ($temPendencias)
                        <strong>Resolver as pendências abertas do cliente.</strong>
                        <p>Priorize os itens pendentes antes de encerrar o atendimento.</p>
                    @elseif ($mensagensCliente > $mensagensEquipe)
                        <strong>Responder a última mensagem do cliente.</strong>
                        <p>O cliente está aguardando uma orientação do suporte.</p>
                    @elseif ($atendimentoAberto)
                        <strong>Acompanhar a conversa até a confirmação do cliente.</strong>
                        <p>Se a situação já foi resolvida, marque o atendimento como resolvido.</p>
                    @else
                        <strong>Nenhuma ação obrigatória no momento.</strong>
                        <p>Use esta área para iniciar um novo atendimento quando necessário.</p>
                    @endif
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Ações rápidas</strong>
                    <div class="pc-action-group">
                        <button type="button" class="pc-action-link primary full" wire:click="$refresh">↻ Atualizar conversa</button>
                        <button type="button" class="pc-action-link" onclick="document.querySelector('.pc-composer-textarea')?.focus()">💬 Responder</button>
                        @if ($portalLink)
                            <a class="pc-action-link" href="{{ $portalLink }}" target="_blank" rel="noopener">🔗 Portal</a>
                        @else
                            <button type="button" class="pc-action-link" disabled style="opacity:.55;cursor:not-allowed;">🔗 Portal</button>
                        @endif
                        <button type="button" class="pc-action-link danger full" wire:click="finalizarConversa" wire:loading.attr="disabled" wire:target="finalizarConversa">✓ Marcar atendimento como resolvido</button>
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Sobre o atendimento</strong>
                    <div class="pc-info-list">
                        <div class="pc-info-row"><span>Protocolo</span><strong>{{ $protocolo }}</strong></div>
                        <div class="pc-info-row"><span>Prioridade</span><strong>{{ $prioridadeLabel }}</strong></div>
                        <div class="pc-info-row"><span>Canal</span><strong>Portal do Cliente</strong></div>
                        <div class="pc-info-row"><span>Responsável</span><strong>{{ $responsavel }}</strong></div>
                        <div class="pc-info-row"><span>Atualização</span><strong>{{ $ultimaAtualizacao }}</strong></div>
                        <div class="pc-info-row"><span>SLA</span><strong>{{ $temPendencias ? 'Acompanhar pendências' : 'Dentro do prazo' }}</strong></div>
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Pendências do cliente</strong>
                    <div class="pc-info-list">
                        @forelse ($pendenciasCliente->take(5) as $pendencia)
                            <div class="pc-info-row">
                                <span>{{ $pendencia['action_label'] ?? 'Acompanhar' }}</span>
                                <strong>{{ $pendencia['titulo'] ?? 'Item sem título' }}</strong>
                            </div>
                        @empty
                            <div class="pc-empty">Nenhuma pendência do cliente no momento.</div>
                        @endforelse
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Arquivos recentes</strong>
                    <div class="pc-file-list">
                        @forelse ($documentosPublicados->take(4) as $documento)
                            @php
                                $documentoUrl = $documento['url'] ?? $documento['download_url'] ?? null;
                                $documentoNome = $documento['nome'] ?? $documento['titulo'] ?? $documento['name'] ?? 'Documento';
                                $documentoMeta = $documento['size_label'] ?? $documento['created_at_label'] ?? $documento['tipo'] ?? 'Arquivo do portal';
                            @endphp
                            @if ($documentoUrl)
                                <a class="pc-file-row" href="{{ $documentoUrl }}" target="_blank" rel="noopener">
                                    <span>📄</span>
                                    <span><strong>{{ $documentoNome }}</strong><span>{{ $documentoMeta }}</span></span>
                                    <span>↗</span>
                                </a>
                            @else
                                <div class="pc-file-row">
                                    <span>📄</span>
                                    <span><strong>{{ $documentoNome }}</strong><span>{{ $documentoMeta }}</span></span>
                                    <span>—</span>
                                </div>
                            @endif
                        @empty
                            <div class="pc-empty">Nenhum arquivo publicado para este cliente.</div>
                        @endforelse
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Linha do tempo</strong>
                    <div class="pc-mini-timeline">
                        @forelse ($timelineAtendimento->take(4) as $evento)
                            <div class="pc-mini-event">
                                <div>
                                    <strong>{{ $evento['titulo'] ?? $evento['title'] ?? $evento['label'] ?? 'Evento do atendimento' }}</strong>
                                    <span>{{ $evento['created_at_label'] ?? $evento['data_label'] ?? $evento['description'] ?? 'Registrado no portal' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="pc-mini-event">
                                <div>
                                    <strong>Atendimento iniciado</strong>
                                    <span>{{ $ultimaAtualizacao }}</span>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Progresso do fluxo</strong>
                    <div class="pc-info-list">
                        <div class="pc-info-row"><span>Documentos</span><strong>{{ $documentosPublicados->count() }}</strong></div>
                        <div class="pc-info-row"><span>Próximas datas</span><strong>{{ $entregasCalendario->count() }}</strong></div>
                        <div class="pc-info-row"><span>Concluído</span><strong>{{ $progress['done'] ?? 0 }} de {{ $progress['total'] ?? 0 }}</strong></div>
                    </div>
                    <div class="pc-progress" style="--progress: {{ $percent }}%;"><i></i></div>
                    <p class="pc-muted" style="margin-top:.55rem;">{{ $percent }}% do fluxo concluído.</p>
                </div>
            </aside>
        </div>
    </div>

    @if(! empty($socketIoConfig['url']))
        <script id="portal-socket-io-client" src="{{ rtrim($socketIoConfig['url'], '/') }}/socket.io/socket.io.js" onload="window.__portalAdminSocketIoScriptLoaded=true" onerror="window.__portalAdminSocketIoScriptError=true"></script>
    @endif

    <script>
        (function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const chat = document.getElementById('portalClienteChatBody');
            const socketConfig = @json($socketIoConfig ?? []);
            const supportName = socketConfig?.nome || @json(auth()->user()?->name ?: 'Suporte');
            const adminSyncUrl = @json(route('admin.portal-cliente.chat.mensagens-novas', ['empresa' => $empresaId]));
            let socket = null;
            let sendingBusy = false;
            let adminOfflineSyncTimer = null;
            let adminOfflineSyncInFlight = false;
            const typingState = { active: false, timer: null, stopTimer: null };

            function adminDebug(step, payload) {
                return;
            }

            function escapeHtml(value) {
                return String(value || '').replace(/[&<>"']/g, function (char) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
                });
            }

            function initials(author, fallback) {
                const clean = String(author || '').trim();
                return (clean ? clean.slice(0, 2) : fallback).toUpperCase();
            }

            function normalizeMessage(payload) {
                const origem = String(payload?.origem || payload?.actor || payload?.class || '').toLowerCase();
                const isCliente = origem === 'cliente' || origem === 'portal_cliente' || origem === 'client' || payload?.class === 'cliente';
                return {
                    id: Number(payload?.id || payload?.message_id || 0),
                    class: isCliente ? 'cliente' : 'equipe',
                    author: payload?.author || payload?.nome || (isCliente ? 'Cliente' : 'Equipe'),
                    text: payload?.text || payload?.mensagem_texto || payload?.mensagem || '',
                    time: payload?.time || payload?.created_at_label || payload?.created_at || 'agora',
                    attachments: Array.isArray(payload?.attachments) ? payload.attachments : [],
                };
            }

            function renderAttachment(anexo) {
                const url = escapeHtml(anexo.url || '#');
                const name = escapeHtml(anexo.name || anexo.nome || 'Anexo');
                const size = escapeHtml(anexo.size_label || anexo.size || anexo.mime_type || 'arquivo');
                return '<a class="pc-attachment" href="' + url + '" target="_blank" rel="noopener" download>'
                    + '<span>' + (anexo.is_image ? '🖼️' : '📄') + '</span>'
                    + '<span><strong>' + name + '</strong><span>' + size + '</span></span><span>↗</span></a>';
            }

            function renderMessage(payload, optimistic) {
                const msg = normalizeMessage(payload);
                const isCliente = msg.class === 'cliente';
                const attach = msg.attachments.length ? '<div class="pc-attachments">' + msg.attachments.map(renderAttachment).join('') + '</div>' : '';
                const rowId = msg.id > 0 ? msg.id : ('tmp-' + Date.now());
                return '<article class="pc-message ' + (isCliente ? 'cliente' : 'equipe') + (optimistic ? ' is-sending' : '') + '" data-message-id="' + rowId + '">'
                    + (!isCliente ? '<div class="pc-message-avatar" title="' + escapeHtml(msg.author) + '">' + initials(msg.author, 'EQ') + '</div>' : '')
                    + '<div class="pc-bubble">'
                    + (isCliente ? '<div class="pc-message-avatar" title="' + escapeHtml(msg.author) + '">' + initials(msg.author, 'CL') + '</div>' : '')
                    + '<div class="pc-bubble-content"><div class="pc-bubble-head"><span>' + escapeHtml(msg.author) + ' ' + (isCliente ? '(Cliente)' : '(Suporte)') + '</span><span>' + escapeHtml(msg.time) + '</span></div>'
                    + (msg.text ? '<div class="pc-bubble-text">' + escapeHtml(msg.text) + '</div>' : '')
                    + attach
                    + '<div class="pc-message-status-line"><span>' + (isCliente ? 'Mensagem do cliente' : 'Resposta do suporte') + '</span><span data-seen-status>' + (optimistic ? 'Enviando...' : (isCliente ? 'Aguardando leitura' : 'Enviada')) + '</span></div>'
                    + '</div></div></article>';
            }

            function appendMessage(payload, optimistic) {
                if (!chat) return null;
                const msg = normalizeMessage(payload);
                if (msg.id > 0 && chat.querySelector('[data-message-id="' + msg.id + '"]')) return chat.querySelector('[data-message-id="' + msg.id + '"]');
                const empty = chat.querySelector('.pc-empty');
                if (empty) empty.remove();
                chat.insertAdjacentHTML('beforeend', renderMessage(msg, optimistic));
                chat.scrollTop = chat.scrollHeight;
                return chat.lastElementChild;
            }

            function replaceOptimistic(row, payload) {
                if (!row) return;
                const wrapper = document.createElement('div');
                wrapper.innerHTML = renderMessage(payload, false).trim();
                row.replaceWith(wrapper.firstElementChild);
                chat.scrollTop = chat.scrollHeight;
            }


            function latestAdminChatMessageId() {
                if (!chat) return 0;
                return Array.from(chat.querySelectorAll('[data-message-id]')).reduce(function (max, row) {
                    const id = Number(row.dataset.messageId || 0);
                    return Number.isFinite(id) && id > max ? id : max;
                }, 0);
            }

            async function syncAdminMessages(reason) {
                if (!adminSyncUrl || !window.fetch || adminOfflineSyncInFlight) return;
                const forceSync = ['manual', 'socket_connect', 'socket_connected'].includes(String(reason || ''));
                if (!forceSync && socket && socket.connected) {
                    stopAdminOfflineSync();
                    return;
                }
                adminOfflineSyncInFlight = true;
                const afterId = latestAdminChatMessageId();
                const url = new URL(adminSyncUrl, window.location.origin);
                url.searchParams.set('after_id', String(afterId));

                try {
                    const response = await fetch(url.toString(), {
                        method: 'GET',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    const data = await response.json().catch(function () { return null; });
                    const messages = Array.isArray(data?.messages) ? data.messages : [];
                    if (messages.length > 0) {
                        adminDebug('socket_admin_sync_response', { after_id: afterId, quantidade: messages.length, reason: reason || 'manual' });
                    }
                    messages.forEach(function (message) { appendMessage(message, false); });
                } catch (error) {
                    adminDebug('socket_admin_sync_error', { erro: String(error && error.message ? error.message : error), reason: reason || 'manual' });
                } finally {
                    adminOfflineSyncInFlight = false;
                }
            }

            function startAdminOfflineSync(reason) {
                if (socket && socket.connected) return;
                if (window.__portalAdminOfflineSyncTimer) {
                    adminOfflineSyncTimer = window.__portalAdminOfflineSyncTimer;
                    return;
                }
                window.setTimeout(function () { syncAdminMessages(reason || 'socket_offline'); }, 500);
                adminOfflineSyncTimer = window.setInterval(function () { syncAdminMessages(reason || 'socket_offline'); }, 10000);
                window.__portalAdminOfflineSyncTimer = adminOfflineSyncTimer;
            }

            function stopAdminOfflineSync() {
                const timer = adminOfflineSyncTimer || window.__portalAdminOfflineSyncTimer;
                if (!timer) return;
                window.clearInterval(timer);
                adminOfflineSyncTimer = null;
                window.__portalAdminOfflineSyncTimer = null;
            }

            function setSending(form, sending) {
                sendingBusy = Boolean(sending);
                const button = form?.querySelector('[data-admin-chat-submit]');
                if (button) button.disabled = Boolean(sending);
            }

            function updateClientTyping(active, name) {
                const box = document.querySelector('[data-cliente-typing]');
                const text = document.querySelector('[data-cliente-typing-text]');
                if (!box) return;
                box.style.display = active ? 'flex' : 'none';
                if (text) text.textContent = (name || 'Cliente') + ' está digitando...';
            }


            async function persistAdminSeen(messageId) {
                const id = Number(messageId || 0);
                if (!id || !socketConfig?.seenUrl || !window.fetch) return;

                try {
                    await fetch(socketConfig.seenUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ empresa: socketConfig.empresaId, message_id: id }),
                    });
                } catch (error) {
                    adminDebug('socket_admin_seen_persist_error', { message_id: id, erro: String(error && error.message ? error.message : error) });
                }
            }

            function emitSupportTyping(text) {
                if (!socket || !socket.connected) return;
                const hasText = String(text || '').trim() !== '';
                if (hasText && !typingState.active) {
                    typingState.active = true;
                    socket.emit('chat:typing:start', { actor: socketConfig.actor || 'suporte', nome: supportName, room: socketConfig.room || '' });
                }
                window.clearTimeout(typingState.stopTimer);
                typingState.stopTimer = window.setTimeout(function () {
                    typingState.active = false;
                    socket.emit('chat:typing:stop', { actor: socketConfig.actor || 'suporte', nome: supportName, room: socketConfig.room || '' });
                }, hasText ? 1200 : 0);
            }

            function loadSocketIoClient() {
                return new Promise(function (resolve) {
                    if (window.io) {
                        resolve(true);
                        return;
                    }

                    if (!socketConfig?.url) {
                        resolve(false);
                        return;
                    }

                    const src = String(socketConfig.url).replace(/\/$/, '') + '/socket.io/socket.io.js';
                    let script = document.getElementById('portal-socket-io-client')
                        || Array.from(document.scripts).find(function (item) { return item.src === src; });

                    const done = function (ok) {
                        resolve(Boolean(ok && window.io));
                    };

                    if (!script) {
                        script = document.createElement('script');
                        script.id = 'portal-socket-io-client';
                        script.src = src;
                        script.async = false;
                        document.head.appendChild(script);
                    }

                    if (window.__portalAdminSocketIoScriptLoaded || window.io) {
                        done(true);
                        return;
                    }

                    if (window.__portalAdminSocketIoScriptError) {
                        done(false);
                        return;
                    }

                    script.addEventListener('load', function () {
                        window.__portalAdminSocketIoScriptLoaded = true;
                        done(true);
                    }, { once: true });

                    script.addEventListener('error', function () {
                        window.__portalAdminSocketIoScriptError = true;
                        done(false);
                    }, { once: true });

                    window.setTimeout(function () {
                        done(Boolean(window.io));
                    }, 2500);
                });
            }

            async function initializeAdminSocket() {
                if (!socketConfig?.enabled || !socketConfig?.url) {
                    startAdminOfflineSync('socket_disabled');
                    return;
                }

                const loaded = await loadSocketIoClient();
                if (!loaded || !window.io) {
                    adminDebug('socket_admin_connect_error', {
                        erro: window.__portalAdminSocketIoScriptError ? 'socket_io_client_load_error' : 'socket_io_client_missing',
                        url: socketConfig.url,
                    });
                    startAdminOfflineSync('socket_client_missing');
                    return;
                }

                connectSocket();
            }

            function connectSocket() {
                if (!socketConfig?.enabled || !socketConfig?.url) {
                    startAdminOfflineSync('socket_disabled');
                    return;
                }

                if (!window.io) {
                    adminDebug('socket_admin_connect_error', { erro: 'socket_io_client_missing', url: socketConfig.url });
                    startAdminOfflineSync('socket_client_missing');
                    return;
                }

                socket = window.io(socketConfig.url, {
                    transports: ['websocket', 'polling'],
                    withCredentials: true,
                    auth: {
                        empresaId: socketConfig.empresaId,
                        actor: socketConfig.actor || 'suporte',
                        token: socketConfig.token || '',
                        signature: socketConfig.signature || '',
                        room: socketConfig.room || '',
                    },
                });

                socket.on('chat:connected', function (payload) {
                    adminDebug('socket_admin_connected', { socket_id: socket.id, payload });
                    stopAdminOfflineSync();
                    syncAdminMessages('socket_connected');
                });

                socket.on('chat:message:new', function (payload) {
                    const msg = normalizeMessage(payload);
                    adminDebug('socket_admin_message_received', { message_id: msg.id, origem: payload?.origem || null, actor: payload?.actor || null, class: msg.class });
                    appendMessage(msg, false);
                    if (msg.class === 'cliente' && msg.id > 0) {
                        socket.emit('chat:seen', { message_id: msg.id, room: socketConfig.room || '', at: new Date().toISOString() });
                        persistAdminSeen(msg.id);
                    }
                });

                socket.on('chat:typing:start', function (payload) {
                    if (payload?.actor === 'suporte') return;
                    updateClientTyping(true, payload?.nome || 'Cliente');
                    window.clearTimeout(window.__clienteTypingTimer);
                    window.__clienteTypingTimer = window.setTimeout(function () { updateClientTyping(false); }, 8000);
                });

                socket.on('chat:typing:stop', function (payload) {
                    if (payload?.actor === 'suporte') return;
                    updateClientTyping(false);
                });

                socket.on('connect', function () {
                    adminDebug('socket_admin_connected', { socket_id: socket.id, transport: socket.io?.engine?.transport?.name || null });
                    stopAdminOfflineSync();
                    const lastCliente = Array.from(document.querySelectorAll('#portalClienteChatBody .pc-message.cliente[data-message-id]'))
                        .reduce(function (max, row) { const id = Number(row.dataset.messageId || 0); return id > max ? id : max; }, 0);
                    if (lastCliente > 0) {
                        socket.emit('chat:seen', { message_id: lastCliente, room: socketConfig.room || '', at: new Date().toISOString() });
                        persistAdminSeen(lastCliente);
                    }
                    syncAdminMessages('socket_connect');
                });

                socket.on('connect_error', function (error) {
                    adminDebug('socket_admin_connect_error', { erro: String(error && error.message ? error.message : error) });
                    startAdminOfflineSync('socket_connect_error');
                });

                socket.on('disconnect', function (reason) {
                    adminDebug('socket_admin_connect_error', { erro: 'disconnect: ' + String(reason || '') });
                    startAdminOfflineSync('socket_disconnect');
                });
            }

            initializeAdminSocket();

            async function enviarMensagemSuporte(form, event) {
                if (event) event.preventDefault();
                if (!form || sendingBusy || !window.fetch || !window.FormData) return false;

                const textarea = form.querySelector('[data-admin-chat-textarea]');
                const message = textarea?.value.trim() || '';
                const files = form.querySelector('input[type="file"]')?.files || [];
                if (!message && files.length === 0) {
                    textarea?.focus();
                    return false;
                }

                const optimistic = appendMessage({ origem: 'interno', nome: supportName, mensagem: message, attachments: files.length ? [{ nome: files.length + ' anexo(s)', size_label: 'enviando' }] : [] }, true);
                const payload = new FormData(form);
                payload.set('mensagem', message);

                textarea.value = '';
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                setSending(form, true);

                try {
                    const response = await fetch(form.dataset.sendUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
                        body: payload,
                        credentials: 'same-origin',
                    });
                    const data = await response.json().catch(function () { return null; });
                    if (!response.ok || !data || data.ok === false) throw new Error(data?.message || 'Falha ao enviar');

                    adminDebug('chat_ajax_success', { message_id: Number(data?.message_id || data?.chat_message?.id || 0) });
                    if (data.chat_message) {
                        replaceOptimistic(optimistic, data.chat_message);
                        if (socket && socket.connected) {
                            adminDebug('socket_admin_emit_start', { message_id: Number(data.chat_message.id || data.chat_message.message_id || 0) });
                            data.chat_message.room = data.chat_message.room || socketConfig.room || '';
                            data.chat_message.actor = data.chat_message.actor || socketConfig.actor || 'suporte';
                            data.chat_message.class = data.chat_message.class || 'equipe';
                            socket.emit('chat:message:new', data.chat_message, function (ack) {
                                adminDebug('socket_admin_emit_ack', { message_id: Number(data.chat_message.id || data.chat_message.message_id || 0), ack });
                            });
                            socket.emit('chat:typing:stop', { actor: socketConfig.actor || 'suporte', nome: supportName, room: socketConfig.room || '' });
                        } else {
                            startAdminOfflineSync('after_send_socket_offline');
                        }
                    } else if (data.message_id) {
                        optimistic?.classList.remove('is-sending');
                        optimistic.dataset.messageId = String(data.message_id);
                    }
                    form.querySelector('input[type="file"]') && (form.querySelector('input[type="file"]').value = '');
                } catch (error) {
                    adminDebug('chat_ajax_error', { erro: String(error && error.message ? error.message : error) });
                    optimistic?.classList.remove('is-sending');
                    optimistic?.classList.add('is-failed');
                    const status = optimistic?.querySelector('[data-seen-status]');
                    if (status) status.textContent = 'Falha ao enviar';
                    textarea.value = message;
                    textarea.focus();
                } finally {
                    setSending(form, false);
                }

                return false;
            }

            window.portalClienteEnviarMensagemSuporte = enviarMensagemSuporte;

            document.addEventListener('submit', function (event) {
                const form = event.target?.closest?.('[data-admin-chat-form]');
                if (!form) return;
                event.preventDefault();
                event.stopPropagation();
                enviarMensagemSuporte(form, event);
                return false;
            }, true);

            document.querySelectorAll('[data-admin-chat-form]').forEach(function (form) {
                const fallbackSendUrl = form.dataset.sendUrl || '{{ route('admin.portal-cliente.chat.mensagem', ['empresa' => $empresaId]) }}';
                form.dataset.sendUrl = fallbackSendUrl;
                form.setAttribute('action', 'javascript:void(0)');
                form.setAttribute('method', 'POST');
                form.removeAttribute('target');

                const textarea = form.querySelector('[data-admin-chat-textarea]');
                const button = form.querySelector('[data-admin-chat-submit]');

                textarea?.addEventListener('input', function () {
                    emitSupportTyping(textarea.value);
                });

                button?.addEventListener('click', function (event) {
                    event.preventDefault();
                    enviarMensagemSuporte(form, event);
                });
            });

            window.portalClienteAvisarSuporteDigitando = emitSupportTyping;
            document.addEventListener('livewire:navigated', function () { if (socket) socket.disconnect(); });
        })();
    </script>

</x-filament-panels::page>
