<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php
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
    ?>
<div class="pc-service-shell" x-data="{
        shouldStick: true,
        activeSection: 'conversa',
        quickReplyOpen: false,
        internalNote: localStorage.getItem('portal_cliente_internal_note_<?php echo e($empresaId ?? 0); ?>') || '',
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
            localStorage.setItem('portal_cliente_internal_note_<?php echo e($empresaId ?? 0); ?>', this.internalNote || '');
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
                        <span><?php echo e($empresaNome); ?></span>
                    </div>
                    <button type="button" class="pc-inbox-filter" title="Filtrar atendimentos" aria-label="Filtrar atendimentos">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M4 7h16M7 12h10M10 17h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($empresasLista->count() > 1): ?>
                    <div class="pc-inbox-model-select-wrap">
                        <select class="pc-company-select pc-company-select-model" wire:model.live="empresaSelecionadaId" aria-label="Selecionar empresa">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $empresasLista; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresaOpcao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($empresaOpcao['id']); ?>"><?php echo e($empresaOpcao['nome_fantasia'] ?? $empresaOpcao['razao_social'] ?? 'Empresa #' . $empresaOpcao['id']); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="pc-ticket-list pc-ticket-list-model">
                    <div class="pc-ticket-card pc-ticket-model is-active">
                        <div class="pc-ticket-top">
                            <strong><?php echo e($protocolo); ?></strong>
                        </div>
                        <div class="pc-ticket-meta">
                            <span class="pc-dot <?php echo e($statusClass === 'warn' ? 'warn' : ($atendimentoAberto ? 'ok' : 'muted')); ?>"><?php echo e($statusAtendimento); ?></span>
                        </div>
                        <p><?php echo e($ultimaMensagem['mensagem_texto'] ?? $ultimaMensagem['mensagem'] ?? 'Conversa principal do portal do cliente'); ?></p>
                        <div class="pc-ticket-meta">
                            <span class="pc-ticket-date"><?php echo e($ultimaAtualizacao); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mensagensCliente > 0): ?>
                                <span class="pc-ticket-unread" title="Mensagens do cliente"><?php echo e($mensagensCliente); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $solicitacoesAbertas->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $solicitacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $solicitacaoPrioridade = (string) ($solicitacao['prioridade'] ?? 'media');
                            $solicitacaoPrioridadeLabel = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'urgente' => 'Urgente'][$solicitacaoPrioridade] ?? ucfirst($solicitacaoPrioridade);
                            $solicitacaoStatusLabel = ($solicitacao['status_label'] ?? null) ?: (($solicitacao['status'] ?? null) ? ucfirst((string) $solicitacao['status']) : 'Em andamento');
                            $solicitacaoStatusClass = in_array(strtolower((string) $solicitacaoStatusLabel), ['concluído', 'concluido', 'resolvido', 'finalizado'], true) ? 'muted' : (($solicitacaoPrioridade === 'alta' || $solicitacaoPrioridade === 'urgente') ? 'warn' : 'ok');
                        ?>
                        <div class="pc-ticket-card pc-ticket-model">
                            <div class="pc-ticket-top">
                                <strong><?php echo e($solicitacao['protocolo'] ?? $solicitacao['codigo'] ?? $solicitacao['titulo'] ?? 'Solicitação'); ?></strong>
                            </div>
                            <div class="pc-ticket-meta">
                                <span class="pc-dot <?php echo e($solicitacaoStatusClass); ?>"><?php echo e($solicitacaoStatusLabel); ?></span>
                            </div>
                            <p><?php echo e($solicitacao['descricao'] ?? $solicitacao['titulo'] ?? 'Solicitação em acompanhamento'); ?></p>
                            <div class="pc-ticket-meta">
                                <span class="pc-ticket-date"><?php echo e($solicitacao['created_at_label'] ?? $solicitacao['updated_at_label'] ?? $solicitacaoPrioridadeLabel); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($solicitacao['mensagens_count']) || ! empty($solicitacao['unread_count'])): ?>
                                    <span class="pc-ticket-unread"><?php echo e($solicitacao['unread_count'] ?? $solicitacao['mensagens_count']); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                <details class="pc-ticket-create-compact">
                    <summary class="pc-ticket-create-summary">Novo atendimento</summary>
                    <div class="pc-ticket-create-body">
                        <form class="pc-ticket-form" wire:submit.prevent="criarSolicitacao">
                            <input class="pc-input" type="text" wire:model.defer="solicitacaoTitulo" placeholder="Título da solicitação">
                            <textarea class="pc-textarea" wire:model.defer="solicitacaoDescricao" placeholder="Descreva a necessidade"></textarea>
                            <select class="pc-select" wire:model.defer="solicitacaoPrioridade">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($supportForm['prioridades'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $valor => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($valor); ?>"><?php echo e($label); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <button type="submit" class="pc-btn" wire:loading.attr="disabled" wire:target="criarSolicitacao">Criar atendimento</button>
                        </form>
                    </div>
                </details>

                <div class="pc-inbox-footer-action">
                    <button type="button" class="pc-view-all-btn">Ver todos os atendimentos</button>
                </div>
            </aside>

            <section class="pc-panel pc-main" aria-label="Conversa com o cliente">
                <div class="pc-loading-overlay" wire:loading.flex wire:target="empresaSelecionadaId,finalizarConversa">Atualizando atendimento...</div>

                <header class="pc-header">
                    <div class="pc-title-wrap">
                        <div class="pc-title-line">
                            <h2>Atendimento <?php echo e($protocolo); ?></h2>
                            <span class="pc-badge <?php echo e($statusClass); ?>"><?php echo e($statusAtendimento); ?></span>
                        </div>
                        <div class="pc-subtitle"><?php echo e($suporteOnline ? 'Suporte online' : 'Suporte offline'); ?> • Atualizado <?php echo e($ultimaAtualizacao); ?></div>
                    </div>
                    <div class="pc-actions">
                        <span class="pc-realtime-pill">tempo real</span>
                        <button type="button" class="pc-btn secondary" wire:click="finalizarConversa" wire:loading.attr="disabled" wire:target="finalizarConversa">✓ Marcar como resolvido</button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($portalLink): ?>
                            <a class="pc-btn secondary" href="<?php echo e($portalLink); ?>" target="_blank" rel="noopener">Abrir portal público</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </header>

                <nav class="pc-tabs" aria-label="Seções do atendimento">
                    <button type="button" class="pc-tab" :class="activeSection === 'conversa' ? 'is-active' : ''" :aria-selected="activeSection === 'conversa'" role="tab" x-on:click="setSection('conversa')">Conversa</button>
                    <button type="button" class="pc-tab" :class="activeSection === 'historico' ? 'is-active' : ''" :aria-selected="activeSection === 'historico'" role="tab" x-on:click="setSection('historico')">Histórico</button>
                    <button type="button" class="pc-tab" :class="activeSection === 'anotacoes' ? 'is-active' : ''" :aria-selected="activeSection === 'anotacoes'" role="tab" x-on:click="setSection('anotacoes')">Anotações</button>
                </nav>

                <main class="pc-messages" id="portalClienteChatBody" x-show="activeSection === 'conversa'" x-cloak x-ref="chatBody" x-init="scrollChat(true)" x-on:scroll.passive="watchScroll()">
                    <?php $dataAnteriorMensagem = null; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $mensagensChat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mensagem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
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
                        ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarDivisorData): ?>
                            <div class="pc-date-divider" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'portal-chat-date-'.e($dataMensagem).'-'.e($loop->index).''; ?>wire:key="portal-chat-date-<?php echo e($dataMensagem); ?>-<?php echo e($loop->index); ?>"><?php echo e($dataMensagem); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <article class="pc-message <?php echo e($isCliente ? 'cliente' : 'equipe'); ?>" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'portal-chat-message-'.e($mensagem['id'] ?? $loop->index).''; ?>wire:key="portal-chat-message-<?php echo e($mensagem['id'] ?? $loop->index); ?>" data-message-id="<?php echo e($mensagem['id'] ?? 0); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($isCliente)): ?>
                                <div class="pc-message-avatar" title="<?php echo e($autor); ?>"><?php echo e($iniciais ?: 'EQ'); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="pc-bubble">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCliente): ?>
                                    <div class="pc-message-avatar" title="<?php echo e($autor); ?>"><?php echo e($iniciais ?: 'CL'); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <div class="pc-bubble-content">
                                    <div class="pc-bubble-head">
                                        <span><?php echo e($autor); ?> <?php echo e($isCliente ? '(Cliente)' : '(Suporte)'); ?></span>
                                        <span><?php echo e($mensagem['created_at_label'] ?? ''); ?></span>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($textoMensagem !== ''): ?>
                                        <div class="pc-bubble-text"><?php echo e($textoMensagem); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($mensagem['attachments'])): ?>
                                        <div class="pc-attachments">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $mensagem['attachments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anexo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <a class="pc-attachment" href="<?php echo e($anexo['url']); ?>" target="_blank" rel="noopener" download>
                                                    <span><?php echo e(($anexo['is_image'] ?? false) ? '🖼️' : '📄'); ?></span>
                                                    <span>
                                                        <strong><?php echo e($anexo['nome'] ?? 'Anexo'); ?></strong>
                                                        <span><?php echo e($anexo['size_label'] ?? ($anexo['mime_type'] ?? 'arquivo')); ?></span>
                                                    </span>
                                                    <span>↗</span>
                                                </a>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <div class="pc-message-status-line">
                                        <span><?php echo e($isCliente ? 'Mensagem do cliente' : 'Resposta do suporte'); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isCliente && ! empty($mensagem['id']) && $clienteVisualizouAteId && (int) $mensagem['id'] <= (int) $clienteVisualizouAteId): ?>
                                            <span class="pc-seen-status">✓✓ Visualizado pelo cliente</span>
                                        <?php elseif($isCliente && ! empty($mensagem['id']) && $suporteVisualizouAteId && (int) $mensagem['id'] <= (int) $suporteVisualizouAteId): ?>
                                            <span class="pc-seen-status">✓✓ Visualizado pelo suporte</span>
                                        <?php else: ?>
                                            <span><?php echo e($isCliente ? 'Aguardando leitura' : 'Enviada'); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="pc-empty">
                            Ainda não há mensagens neste atendimento. Envie a primeira resposta para iniciar a conversa com histórico organizado.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </main>

                <section class="pc-section-panel" x-show="activeSection === 'historico'" x-cloak aria-label="Histórico do atendimento">
                    <div class="pc-history-list">
                        <div class="pc-history-summary">
                            <div class="pc-history-summary-card"><span>Protocolo</span><strong><?php echo e($protocolo); ?></strong></div>
                            <div class="pc-history-summary-card"><span>Status atual</span><strong><?php echo e($statusAtendimento); ?></strong></div>
                            <div class="pc-history-summary-card"><span>Atualizado em</span><strong><?php echo e($ultimaAtualizacao); ?></strong></div>
                        </div>

                        <div class="pc-history-timeline">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $timelineAtendimento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
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
                                ?>
                                <div class="pc-history-item is-<?php echo e($corEvento); ?>" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'portal-history-'.e($loop->index).''; ?>wire:key="portal-history-<?php echo e($loop->index); ?>">
                                    <span class="pc-history-icon <?php echo e($corEvento); ?>"><?php echo e($iconeEvento); ?></span>
                                    <div class="pc-history-content">
                                        <div class="pc-history-title-row">
                                            <strong><?php echo e($evento['titulo'] ?? $evento['title'] ?? $evento['acao'] ?? 'Movimentação registrada'); ?></strong>
                                            <span class="pc-history-badge"><?php echo e(ucfirst((string) $tipoEvento)); ?></span>
                                        </div>
                                        <span class="pc-history-description"><?php echo e($evento['descricao'] ?? $evento['description'] ?? 'Evento operacional registrado no atendimento.'); ?></span>
                                    </div>
                                    <span class="pc-history-meta"><?php echo e($evento['created_at_label'] ?? $evento['data_label'] ?? $evento['tempo'] ?? 'Agora'); ?></span>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                            <p>Cliente: <?php echo e($empresaNome); ?></p>
                            <p>Prioridade: <?php echo e($prioridadeLabel); ?></p>
                            <p>Mensagens no atendimento: <?php echo e($mensagensChat->count()); ?></p>
                        </div>
                    </div>
                </section>

                <div class="pc-typing-row" data-cliente-typing style="display: none;">
                    <span class="pc-typing-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                    <span data-cliente-typing-text>Cliente está digitando...</span>
                </div>

                <footer class="pc-chat-composer" x-show="activeSection === 'conversa'" x-cloak>
                    <div class="pc-upload-progress" wire:loading.flex wire:target="portalAnexos"><i></i> Preparando anexos...</div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($portalAnexos): ?>
                        <div class="pc-upload-list">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $portalAnexos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anexoTemporario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <span class="pc-upload-pill">📎 <?php echo e(method_exists($anexoTemporario, 'getClientOriginalName') ? $anexoTemporario->getClientOriginalName() : 'Arquivo anexado'); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <form method="POST" action="javascript:void(0)" enctype="multipart/form-data" data-admin-chat-form data-send-url="<?php echo e(route('admin.portal-cliente.chat.mensagem', ['empresa' => $empresaId])); ?>" onsubmit="event.preventDefault(); window.portalClienteEnviarMensagemSuporte && window.portalClienteEnviarMensagemSuporte(this); return false;">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="empresa" value="<?php echo e($empresaId); ?>">
                        <div class="pc-composer-tabs" role="tablist" aria-label="Modo de mensagem">
                            <button type="button" class="pc-composer-tab is-active" role="tab" aria-selected="true">Responder</button>
                            <button type="button" class="pc-composer-tab" role="tab" aria-selected="false" disabled title="Mensagem interna ficará para o próximo lote funcional">Mensagem interna</button>
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['respostaChat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="pc-error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['portalAnexos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="pc-error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['portalAnexos.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="pc-error"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </form>
                </footer>
            </section>

            <aside class="pc-context" aria-label="Contexto do cliente e atendimento">
                <div class="pc-card">
                    <div class="pc-client-card-head">
                        <div class="pc-avatar"><?php echo e(strtoupper(substr($empresaNome, 0, 2))); ?></div>
                        <div class="pc-client-name">
                            <strong><?php echo e($empresaNome); ?></strong>
                            <span><?php echo e($portalLink ? 'Portal público ativo' : 'Portal interno'); ?></span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($portalLink): ?>
                            <a class="pc-btn secondary" href="<?php echo e($portalLink); ?>" target="_blank" rel="noopener">Ver perfil</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="pc-client-status-row">
                        <span class="pc-health-pill <?php echo e($statusClass); ?>"><?php echo e($statusAtendimento); ?></span>
                        <span class="pc-muted"><?php echo e($suporteOnline ? 'Suporte disponível' : 'Fora do horário'); ?></span>
                    </div>

                    <div class="pc-stats-grid">
                        <div class="pc-stat"><span>Mensagens</span><strong><?php echo e($mensagensChat->count()); ?></strong></div>
                        <div class="pc-stat"><span>Pendências</span><strong><?php echo e($pendenciasCliente->count()); ?></strong></div>
                        <div class="pc-stat"><span>Progresso</span><strong><?php echo e($percent); ?>%</strong></div>
                    </div>
                </div>

                <div class="pc-next-step">
                    <span>Próximo passo</span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($temPendencias): ?>
                        <strong>Resolver as pendências abertas do cliente.</strong>
                        <p>Priorize os itens pendentes antes de encerrar o atendimento.</p>
                    <?php elseif($mensagensCliente > $mensagensEquipe): ?>
                        <strong>Responder a última mensagem do cliente.</strong>
                        <p>O cliente está aguardando uma orientação do suporte.</p>
                    <?php elseif($atendimentoAberto): ?>
                        <strong>Acompanhar a conversa até a confirmação do cliente.</strong>
                        <p>Se a situação já foi resolvida, marque o atendimento como resolvido.</p>
                    <?php else: ?>
                        <strong>Nenhuma ação obrigatória no momento.</strong>
                        <p>Use esta área para iniciar um novo atendimento quando necessário.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Ações rápidas</strong>
                    <div class="pc-action-group">
                        <button type="button" class="pc-action-link primary full" wire:click="$refresh">↻ Atualizar conversa</button>
                        <button type="button" class="pc-action-link" onclick="document.querySelector('.pc-composer-textarea')?.focus()">💬 Responder</button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($portalLink): ?>
                            <a class="pc-action-link" href="<?php echo e($portalLink); ?>" target="_blank" rel="noopener">🔗 Portal</a>
                        <?php else: ?>
                            <button type="button" class="pc-action-link" disabled style="opacity:.55;cursor:not-allowed;">🔗 Portal</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <button type="button" class="pc-action-link danger full" wire:click="finalizarConversa" wire:loading.attr="disabled" wire:target="finalizarConversa">✓ Marcar atendimento como resolvido</button>
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Sobre o atendimento</strong>
                    <div class="pc-info-list">
                        <div class="pc-info-row"><span>Protocolo</span><strong><?php echo e($protocolo); ?></strong></div>
                        <div class="pc-info-row"><span>Prioridade</span><strong><?php echo e($prioridadeLabel); ?></strong></div>
                        <div class="pc-info-row"><span>Canal</span><strong>Portal do Cliente</strong></div>
                        <div class="pc-info-row"><span>Responsável</span><strong><?php echo e($responsavel); ?></strong></div>
                        <div class="pc-info-row"><span>Atualização</span><strong><?php echo e($ultimaAtualizacao); ?></strong></div>
                        <div class="pc-info-row"><span>SLA</span><strong><?php echo e($temPendencias ? 'Acompanhar pendências' : 'Dentro do prazo'); ?></strong></div>
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Pendências do cliente</strong>
                    <div class="pc-info-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pendenciasCliente->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pendencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="pc-info-row">
                                <span><?php echo e($pendencia['action_label'] ?? 'Acompanhar'); ?></span>
                                <strong><?php echo e($pendencia['titulo'] ?? 'Item sem título'); ?></strong>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="pc-empty">Nenhuma pendência do cliente no momento.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Arquivos recentes</strong>
                    <div class="pc-file-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $documentosPublicados->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $documento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $documentoUrl = $documento['url'] ?? $documento['download_url'] ?? null;
                                $documentoNome = $documento['nome'] ?? $documento['titulo'] ?? $documento['name'] ?? 'Documento';
                                $documentoMeta = $documento['size_label'] ?? $documento['created_at_label'] ?? $documento['tipo'] ?? 'Arquivo do portal';
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($documentoUrl): ?>
                                <a class="pc-file-row" href="<?php echo e($documentoUrl); ?>" target="_blank" rel="noopener">
                                    <span>📄</span>
                                    <span><strong><?php echo e($documentoNome); ?></strong><span><?php echo e($documentoMeta); ?></span></span>
                                    <span>↗</span>
                                </a>
                            <?php else: ?>
                                <div class="pc-file-row">
                                    <span>📄</span>
                                    <span><strong><?php echo e($documentoNome); ?></strong><span><?php echo e($documentoMeta); ?></span></span>
                                    <span>—</span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="pc-empty">Nenhum arquivo publicado para este cliente.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Linha do tempo</strong>
                    <div class="pc-mini-timeline">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $timelineAtendimento->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="pc-mini-event">
                                <div>
                                    <strong><?php echo e($evento['titulo'] ?? $evento['title'] ?? $evento['label'] ?? 'Evento do atendimento'); ?></strong>
                                    <span><?php echo e($evento['created_at_label'] ?? $evento['data_label'] ?? $evento['description'] ?? 'Registrado no portal'); ?></span>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="pc-mini-event">
                                <div>
                                    <strong>Atendimento iniciado</strong>
                                    <span><?php echo e($ultimaAtualizacao); ?></span>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="pc-card">
                    <strong class="pc-card-title">Progresso do fluxo</strong>
                    <div class="pc-info-list">
                        <div class="pc-info-row"><span>Documentos</span><strong><?php echo e($documentosPublicados->count()); ?></strong></div>
                        <div class="pc-info-row"><span>Próximas datas</span><strong><?php echo e($entregasCalendario->count()); ?></strong></div>
                        <div class="pc-info-row"><span>Concluído</span><strong><?php echo e($progress['done'] ?? 0); ?> de <?php echo e($progress['total'] ?? 0); ?></strong></div>
                    </div>
                    <div class="pc-progress" style="--progress: <?php echo e($percent); ?>%;"><i></i></div>
                    <p class="pc-muted" style="margin-top:.55rem;"><?php echo e($percent); ?>% do fluxo concluído.</p>
                </div>
            </aside>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($socketIoConfig['url'])): ?>
        <script id="portal-socket-io-client" src="<?php echo e(rtrim($socketIoConfig['url'], '/')); ?>/socket.io/socket.io.js" onload="window.__portalAdminSocketIoScriptLoaded=true" onerror="window.__portalAdminSocketIoScriptError=true"></script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <script>
        (function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>';
            const chat = document.getElementById('portalClienteChatBody');
            const socketConfig = <?php echo json_encode($socketIoConfig ?? [], 15, 512) ?>;
            const supportName = socketConfig?.nome || <?php echo json_encode(auth()->user()?->name ?: 'Suporte', 15, 512) ?>;
            const adminSyncUrl = <?php echo json_encode(route('admin.portal-cliente.chat.mensagens-novas', ['empresa' => $empresaId]), 512) ?>;
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
                const fallbackSendUrl = form.dataset.sendUrl || '<?php echo e(route('admin.portal-cliente.chat.mensagem', ['empresa' => $empresaId])); ?>';
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

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\portal-cliente.blade.php ENDPATH**/ ?>