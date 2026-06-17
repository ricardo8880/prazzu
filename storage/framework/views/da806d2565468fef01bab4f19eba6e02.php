        <div class="at-modal" x-show="detalhe" x-cloak>
            <div class="at-modal-card wide at-ticket-modal-shell" @click.outside="$wire.fecharDetalhe()">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAtendimento): ?>
                    <?php
                        $clienteInicial = mb_strtoupper(mb_substr(trim($selectedAtendimento['empresa_nome'] ?? 'Cliente'), 0, 1));
                        $responsavelNome = $selectedAtendimento['responsavel_nome'] ?? 'Sem responsável';
                        $responsavelInicial = mb_strtoupper(mb_substr(trim($responsavelNome ?: 'S'), 0, 1));
                        $clienteEmail = $selectedAtendimento['empresa_email'] ?? $selectedAtendimento['cliente_email'] ?? 'Sem e-mail';
                        $statusAtual = $selectedAtendimento['status'] ?? \App\Support\AtendimentoStatus::ABERTO;
                        $statusFechado = \App\Support\AtendimentoStatus::isClosed((string) $statusAtual);
                        $anexosDoAtendimento = collect($timeline)
                            ->flatMap(fn ($log) => collect($log['anexos'] ?? [])->map(fn ($anexo) => array_merge($anexo, ['log_id' => $log['id'] ?? 0, 'log_data' => $log['created_at'] ?? '-'])))
                            ->values();
                        $primeiroLogCliente = collect($timeline)->first(fn ($log) => in_array(($log['origem'] ?? ''), ['cliente', 'portal', 'publico'], true));
                        $eventosOperacionais = collect($timeline)->reverse()->values();
                        $temResponsavel = ! empty($selectedAtendimento['responsavel_id']);
                        $temCanalResposta = ! empty($selectedAtendimento['portal_solicitacao_id'])
                            || ! empty($selectedAtendimento['portal_mensagem_id'])
                            || (trim((string) $clienteEmail) !== '' && $clienteEmail !== 'Sem e-mail');
                        $proximaAcao = match (true) {
                            $statusFechado => ['tone' => 'neutral', 'icon' => 'bi-arrow-counterclockwise', 'titulo' => 'Atendimento finalizado', 'texto' => 'Reabra somente se precisar continuar a conversa ou registrar uma nova ação.'],
                            ! $temResponsavel => ['tone' => 'warning', 'icon' => 'bi-person-check', 'titulo' => 'Definir responsável', 'texto' => 'Assuma ou atribua este atendimento antes de resolver, encerrar ou solicitar documentos.'],
                            ! $temCanalResposta => ['tone' => 'danger', 'icon' => 'bi-exclamation-triangle', 'titulo' => 'Cliente sem canal de resposta', 'texto' => 'Cadastre e-mail ou vínculo de portal antes de enviar uma resposta ao cliente.'],
                            ! empty($selectedAtendimento['sla_vencido']) => ['tone' => 'danger', 'icon' => 'bi-alarm', 'titulo' => 'SLA vencido', 'texto' => 'Priorize este atendimento e registre a ação tomada para manter rastreabilidade.'],
                            $statusAtual === \App\Support\AtendimentoStatus::AGUARDANDO_CLIENTE => ['tone' => 'warning', 'icon' => 'bi-hourglass-split', 'titulo' => 'Aguardar cliente', 'texto' => 'Acompanhe o retorno do cliente; se necessário, envie reforço ou solicite documento.'],
                            $statusAtual === \App\Support\AtendimentoStatus::ABERTO => ['tone' => 'primary', 'icon' => 'bi-play-circle', 'titulo' => 'Iniciar atendimento', 'texto' => 'Marque como em andamento, responda o cliente ou atribua um responsável.'],
                            default => ['tone' => 'primary', 'icon' => 'bi-chat-dots', 'titulo' => 'Continuar atendimento', 'texto' => 'Responda, solicite documento, crie pendência ou resolva quando concluir.'],
                        };
                    ?>

                    <header class="at-ticket-modal-head">
                        <div class="at-ticket-modal-title">
                            <span class="at-ticket-modal-title-icon"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i></span>
                            <div>
                                <h2>
                                    Atendimento #<?php echo e($selectedAtendimento['id']); ?> - <?php echo e($selectedAtendimento['titulo']); ?>

                                    <span class="at-badge at-badge-soft <?php echo e($selectedAtendimento['status_tone'] ?? 'info'); ?>"><?php echo e($selectedAtendimento['status_label'] ?? 'Aberto'); ?></span>
                                    <span class="at-badge at-badge-soft <?php echo e($selectedAtendimento['prioridade_tone'] ?? 'info'); ?>"><?php echo e($selectedAtendimento['prioridade_label'] ?? 'Média'); ?></span>
                                </h2>
                                <p>Criado em <?php echo e($selectedAtendimento['created_at'] ?? '-'); ?></p>
                            </div>
                        </div>

                        <div class="at-ticket-modal-actions">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $statusFechado): ?>
                                <details class="at-ticket-control">
                                    <summary><i class="bi bi-record-circle" aria-hidden="true"></i> Alterar status <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                    <div class="at-ticket-control-panel">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusAtual !== \App\Support\AtendimentoStatus::EM_ANDAMENTO): ?>
                                            <button type="button" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'em_andamento')"><span class="at-dot primary"></span> Em andamento</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusAtual !== \App\Support\AtendimentoStatus::AGUARDANDO_CLIENTE): ?>
                                            <button type="button" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'aguardando_cliente')"><span class="at-dot warning"></span> Aguardando cliente</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusAtual !== \App\Support\AtendimentoStatus::RESOLVIDO): ?>
                                            <button type="button" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'resolvido')"><span class="at-dot success"></span> Resolvido</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </details>

                                <details class="at-ticket-control">
                                    <summary><i class="bi bi-person-plus" aria-hidden="true"></i> Atribuir a... <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                    <div class="at-ticket-control-panel">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $responsaveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <button type="button" wire:click="atribuirResponsavelDetalhe(<?php echo e($resp['id']); ?>)"><i class="bi bi-person" aria-hidden="true"></i> <?php echo e($resp['nome']); ?></button>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($responsaveis)): ?>
                                            <button type="button" disabled><i class="bi bi-info-circle" aria-hidden="true"></i> Nenhum responsável disponível</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </details>
                            <?php else: ?>
                                <button type="button" class="at-ticket-header-action" wire:click="reabrirAtendimento(<?php echo e($selectedAtendimento['id']); ?>)"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Reabrir</button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <details class="at-ticket-control at-ticket-more-control">
                                <summary class="at-ticket-icon-summary" title="Mais opções"><i class="bi bi-three-dots" aria-hidden="true"></i></summary>
                                <div class="at-ticket-control-panel at-ticket-more-panel">
                                    <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText('#<?php echo e($selectedAtendimento['id']); ?>')"><i class="bi bi-clipboard" aria-hidden="true"></i> Copiar protocolo</button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $statusFechado && ! $selectedAtendimento['responsavel_id']): ?>
                                        <button type="button" wire:click="assumirAtendimento(<?php echo e($selectedAtendimento['id']); ?>)"><i class="bi bi-person-check" aria-hidden="true"></i> Assumir atendimento</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $statusFechado): ?>
                                        <button type="button" wire:click="criarPendenciaDoAtendimento"><i class="bi bi-clipboard-plus" aria-hidden="true"></i> Criar pendência</button>
                                        <button type="button" wire:click="solicitarDocumentoDoAtendimento"><i class="bi bi-file-earmark-plus" aria-hidden="true"></i> Solicitar documento</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </details>
                            <button type="button" class="at-ticket-icon-btn" title="Fechar" wire:click="fecharDetalhe"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                        </div>
                    </header>

                    <div class="at-ticket-modal-body">
                        <aside class="at-ticket-left">
                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Detalhes do atendimento</header>
                                <div class="at-ticket-detail-list">
                                    <div class="at-ticket-detail-row"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><span>Protocolo</span><strong>#<?php echo e($selectedAtendimento['id']); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-geo-alt" aria-hidden="true"></i><span>Origem</span><strong><?php echo e($selectedAtendimento['origem_label'] ?? ucfirst($selectedAtendimento['origem'] ?? 'Manual')); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-list-ul" aria-hidden="true"></i><span>Categoria</span><strong><?php echo e($selectedAtendimento['canal_label'] ?? ucfirst($selectedAtendimento['canal'] ?? 'Interno')); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-envelope" aria-hidden="true"></i><span>Assunto</span><strong><?php echo e($selectedAtendimento['titulo'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-stars" aria-hidden="true"></i><span>Prioridade</span><strong><?php echo e($selectedAtendimento['prioridade_label'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-arrow-repeat" aria-hidden="true"></i><span>Status</span><strong><?php echo e($selectedAtendimento['status_label'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-hourglass-split" aria-hidden="true"></i><span>Aguardando</span><strong><?php echo e($selectedAtendimento['aguardando_label'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-stopwatch" aria-hidden="true"></i><span>Tempo aguardando</span><strong><?php echo e($selectedAtendimento['tempo_aguardando_detalhe'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-clock" aria-hidden="true"></i><span>SLA</span><strong class="<?php echo e(!empty($selectedAtendimento['sla_vencido']) ? 'at-ticket-sla-danger' : 'at-ticket-sla-ok'); ?>"><?php echo e(!empty($selectedAtendimento['sla_vencido']) ? $selectedAtendimento['sla_texto'] : 'Dentro do prazo'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-calendar2" aria-hidden="true"></i><span>Criado em</span><strong><?php echo e($selectedAtendimento['created_at'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-clock-history" aria-hidden="true"></i><span>Atualizado em</span><strong><?php echo e($selectedAtendimento['updated_at'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-lightning-charge" aria-hidden="true"></i><span>Primeira resposta</span><strong><?php echo e($selectedAtendimento['primeira_resposta_em'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-check2-square" aria-hidden="true"></i><span>Resolução</span><strong><?php echo e($selectedAtendimento['resolvido_em'] ?? '-'); ?></strong></div>
                                </div>
                            </section>

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header"><span>Histórico operacional</span><small><?php echo e($eventosOperacionais->count()); ?></small></header>
                                <div class="at-ticket-operational-timeline">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $eventosOperacionais->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div class="at-ticket-op-event tone-<?php echo e($evento['operacional_tone'] ?? 'neutral'); ?>">
                                            <span class="at-ticket-op-icon"><i class="bi <?php echo e($evento['operacional_icon'] ?? 'bi-activity'); ?>" aria-hidden="true"></i></span>
                                            <div class="at-ticket-op-body">
                                                <strong><?php echo e($evento['operacional_titulo'] ?? ($evento['tipo_label'] ?? 'Registro')); ?></strong>
                                                <small><?php echo e($evento['created_at'] ?? '-'); ?> · <?php echo e($evento['operacional_actor'] ?? ($evento['usuario'] ?? 'Sistema')); ?></small>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim((string) ($evento['operacional_detalhe'] ?? '')) !== ''): ?>
                                                    <p><?php echo e($evento['operacional_detalhe']); ?></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div class="at-empty">Nenhum evento operacional registrado.</div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </section>

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header"><span>Anexos</span><small><?php echo e($anexosDoAtendimento->count()); ?></small></header>
                                <div class="at-ticket-attachments">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $anexosDoAtendimento->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anexo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div class="at-ticket-file-row">
                                            <span><i class="bi bi-file-earmark" aria-hidden="true"></i></span>
                                            <div>
                                                <strong><?php echo e($anexo['nome_original'] ?? 'Anexo'); ?></strong>
                                                <small><?php echo e($anexo['tamanho_label'] ?? 'Arquivo'); ?> · <?php echo e($anexo['log_data'] ?? '-'); ?></small>
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($anexo['log_id']) && !empty($anexo['hash'])): ?>
                                                <button type="button" class="at-ticket-download-btn" wire:click="baixarAnexoHistorico(<?php echo e((int) $anexo['log_id']); ?>, '<?php echo e($anexo['hash']); ?>')" title="Baixar anexo"><i class="bi bi-download" aria-hidden="true"></i></button>
                                            <?php else: ?>
                                                <span></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div class="at-empty">Nenhum anexo neste atendimento.</div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </section>
                        </aside>

                        <main class="at-ticket-center">
                            <header class="at-ticket-conversation-head">
                                <span>Conversa</span>
                                <span class="at-ticket-order-select">Mais antigos primeiro</span>
                            </header>

                            <div class="at-ticket-chat-scroll">
                                <div class="at-ticket-chat-stream">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($primeiroLogCliente === null && trim((string) ($selectedAtendimento['descricao'] ?? '')) !== ''): ?>
                                        <article class="at-ticket-message client">
                                            <span class="at-ticket-chat-avatar client"><?php echo e($clienteInicial); ?></span>
                                            <div class="at-ticket-message-card">
                                                <strong><?php echo e($selectedAtendimento['empresa_nome'] ?? 'Cliente'); ?> <span>(Cliente)</span></strong>
                                                <p><?php echo e($selectedAtendimento['descricao']); ?></p>
                                            </div>
                                            <span class="at-ticket-message-time"><?php echo e($selectedAtendimento['created_at'] ?? '-'); ?></span>
                                        </article>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php
                                            $origemLog = $log['origem'] ?? 'sistema';
                                            $isCliente = in_array($origemLog, ['cliente', 'portal', 'publico'], true);
                                            $isSuporte = in_array($origemLog, ['suporte', 'interno'], true);
                                            $tipoCard = $isCliente ? 'client' : ($isSuporte ? 'support' : 'system');
                                            $inicialLog = $isCliente ? $clienteInicial : ($isSuporte ? $responsavelInicial : 'S');
                                        ?>
                                        <article <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'ticket-chat-'.e($log['id'] ?? md5(($log['created_at'] ?? '') . ($log['mensagem'] ?? ''))).''; ?>wire:key="ticket-chat-<?php echo e($log['id'] ?? md5(($log['created_at'] ?? '') . ($log['mensagem'] ?? ''))); ?>" class="at-ticket-message <?php echo e($tipoCard); ?>">
                                            <span class="at-ticket-chat-avatar <?php echo e($tipoCard); ?>"><?php echo e($inicialLog); ?></span>
                                            <div class="at-ticket-message-card">
                                                <strong><?php echo e($log['usuario'] ?? ($isCliente ? ($selectedAtendimento['empresa_nome'] ?? 'Cliente') : 'Suporte')); ?> <span><?php echo e($isCliente ? '(Cliente)' : ($isSuporte ? '(Suporte)' : '(Sistema)')); ?></span></strong>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim((string) ($log['mensagem'] ?? '')) !== ''): ?>
                                                    <p><?php echo e($log['mensagem']); ?></p>
                                                <?php else: ?>
                                                    <p><?php echo e($log['tipo_label'] ?? 'Registro do sistema'); ?></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($log['anexos'])): ?>
                                                    <div class="at-ticket-message-files">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $log['anexos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anexo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                            <button type="button" class="at-ticket-attachment-btn" wire:click="baixarAnexoHistorico(<?php echo e($log['id']); ?>, '<?php echo e($anexo['hash']); ?>')">
                                                                <i class="bi bi-file-earmark" aria-hidden="true"></i> <?php echo e($anexo['nome_original']); ?> <i class="bi bi-download" aria-hidden="true"></i>
                                                            </button>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            <span class="at-ticket-message-time"><?php echo e($log['created_at'] ?? '-'); ?> <i class="bi bi-check2-all" aria-hidden="true"></i></span>
                                        </article>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div class="at-empty">Nenhuma interação registrada. A descrição inicial aparece como contexto do atendimento.</div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $statusFechado && $temCanalResposta): ?>
                                <section class="at-ticket-reply-box">
                                    <textarea wire:model="novaRespostaCliente" placeholder="Digite sua resposta..."></textarea>
                                    <div class="at-ticket-reply-actions">
                                        <div class="at-ticket-reply-left">
                                            <label class="at-ticket-upload-control" title="Anexar arquivo">
                                                <i class="bi bi-paperclip" aria-hidden="true"></i>
                                                <input type="file" wire:model="anexoRespostaCliente" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,image/jpeg,image/png,image/webp,application/pdf">
                                            </label>
                                            <select class="at-ticket-quick-select" wire:change="$set('novaRespostaCliente', $event.target.value)">
                                                <option value="">Respostas rápidas</option>
                                                <option value="Olá! Recebemos sua mensagem e já estamos analisando o problema. Em breve retorno com mais informações.">Recebemos sua mensagem</option>
                                                <option value="Identificamos o ponto informado. Pode nos confirmar mais alguns dados para avançarmos com a solução?">Solicitar confirmação</option>
                                                <option value="Conseguimos resolver a pendência. Por favor, valide novamente e nos avise se precisar de algo mais.">Informar resolução</option>
                                                <option value="Para avançarmos, precisamos que envie o documento solicitado pelo portal do cliente.">Solicitar documento pelo portal</option>
                                                <option value="Este atendimento será encerrado porque a solicitação foi concluída. Caso precise, basta abrir um novo chamado pelo portal.">Avisar encerramento</option>
                                            </select>
                                        </div>
                                        <div class="at-ticket-reply-right">
                                            <button type="button" class="at-ticket-send-btn" wire:click="responderCliente" wire:loading.attr="disabled" wire:target="responderCliente,anexoRespostaCliente">
                                                <i class="bi bi-send" aria-hidden="true"></i>
                                                <span wire:loading.remove wire:target="responderCliente,anexoRespostaCliente">Enviar resposta</span>
                                                <span wire:loading wire:target="responderCliente,anexoRespostaCliente">Enviando...</span>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="at-ticket-reply-hint" wire:loading.remove wire:target="anexoRespostaCliente">Pressione o botão para enviar ao portal do cliente</small>
                                    <small class="at-ticket-reply-hint" wire:loading wire:target="anexoRespostaCliente">Carregando anexo...</small>
                                </section>
                            <?php elseif(! $statusFechado && ! $temCanalResposta): ?>
                                <div class="at-ticket-finalized danger"><strong>Cliente sem canal de resposta.</strong><br>Cadastre e-mail ou vínculo de portal antes de enviar mensagem.</div>
                            <?php else: ?>
                                <div class="at-ticket-finalized">Atendimento finalizado. Reabra para responder ao cliente.</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </main>

                        <aside class="at-ticket-right">
                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Cliente</header>
                                <div class="at-ticket-person-card">
                                    <span class="at-ticket-person-avatar"><?php echo e($clienteInicial); ?></span>
                                    <div>
                                        <strong><?php echo e($selectedAtendimento['empresa_nome'] ?? 'Cliente'); ?></strong>
                                        <small><?php echo e($clienteEmail); ?></small>
                                    </div>
                                </div>
                                <div class="at-ticket-mini-info">
                                    <span>Empresa ID</span><strong>#<?php echo e($selectedAtendimento['empresa_id'] ?? '-'); ?></strong>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($selectedAtendimento['crm_cliente_id'])): ?>
                                        <span>Cliente CRM</span><strong>#<?php echo e($selectedAtendimento['crm_cliente_id']); ?></strong>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </section>

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Responsável</header>
                                <div class="at-ticket-person-card">
                                    <span class="at-ticket-person-avatar support"><?php echo e($responsavelInicial); ?></span>
                                    <div>
                                        <strong><?php echo e($responsavelNome); ?></strong>
                                        <small><?php echo e($selectedAtendimento['responsavel_email'] ?? 'Sem e-mail'); ?></small>
                                    </div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $selectedAtendimento['responsavel_id']): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $statusFechado): ?>
                                        <button type="button" class="at-ticket-side-btn" wire:click="assumirAtendimento(<?php echo e($selectedAtendimento['id']); ?>)"><i class="bi bi-person-check" aria-hidden="true"></i> Assumir atendimento</button>
                                    <?php else: ?>
                                        <div class="at-ticket-hint">Atendimento fechado sem responsável.</div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $statusFechado): ?>
                                        <div class="at-ticket-hint">Para trocar o responsável, use o menu <strong>Atribuir a...</strong> no topo.</div>
                                    <?php else: ?>
                                        <div class="at-ticket-hint">Atendimento fechado. Reabra para alterar o responsável.</div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </section>

                            <section class="at-ticket-panel at-ticket-next-panel tone-<?php echo e($proximaAcao['tone']); ?>">
                                <header class="at-ticket-panel-header">Próxima ação recomendada</header>
                                <div class="at-ticket-next-action">
                                    <span><i class="bi <?php echo e($proximaAcao['icon']); ?>" aria-hidden="true"></i></span>
                                    <div>
                                        <strong><?php echo e($proximaAcao['titulo']); ?></strong>
                                        <p><?php echo e($proximaAcao['texto']); ?></p>
                                    </div>
                                </div>
                            </section>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $statusFechado): ?>
                                <section class="at-ticket-panel">
                                    <header class="at-ticket-panel-header">Encerramento com motivo</header>
                                    <div class="at-ticket-detail-list">
                                        <label class="at-ticket-form-row">
                                            <span>Motivo</span>
                                            <select wire:model="motivoEncerramento">
                                                <option value="duvida_resolvida">Dúvida resolvida</option>
                                                <option value="documento_recebido">Documento recebido</option>
                                                <option value="pendencia_concluida">Pendência concluída</option>
                                                <option value="erro_corrigido">Erro corrigido</option>
                                                <option value="solicitacao_cancelada">Solicitação cancelada</option>
                                                <option value="outro">Outro motivo</option>
                                            </select>
                                        </label>
                                        <label class="at-ticket-form-row">
                                            <span>Observação opcional</span>
                                            <textarea rows="3" wire:model.defer="observacaoEncerramento" placeholder="Ex.: cliente confirmou recebimento, documento validado, pendência concluída..."></textarea>
                                        </label>
                                        <button type="button" class="at-ticket-quick-btn danger" wire:click="encerrarComMotivo" wire:loading.attr="disabled" wire:target="encerrarComMotivo"><span class="at-dot danger"></span> Encerrar atendimento</button>
                                    </div>
                                </section>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $statusFechado): ?>
                                <section class="at-ticket-panel">
                                    <header class="at-ticket-panel-header">Ações rápidas</header>
                                    <div class="at-ticket-quick-list">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $selectedAtendimento['responsavel_id']): ?>
                                            <button type="button" class="at-ticket-quick-btn" wire:click="assumirAtendimento(<?php echo e($selectedAtendimento['id']); ?>)"><i class="bi bi-person-check" aria-hidden="true"></i> Assumir atendimento</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusAtual !== \App\Support\AtendimentoStatus::EM_ANDAMENTO): ?>
                                            <button type="button" class="at-ticket-quick-btn" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'em_andamento')"><span class="at-dot primary"></span> Marcar como em andamento</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusAtual !== \App\Support\AtendimentoStatus::AGUARDANDO_CLIENTE): ?>
                                            <button type="button" class="at-ticket-quick-btn" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'aguardando_cliente')"><span class="at-dot warning"></span> Marcar como aguardando cliente</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusAtual !== \App\Support\AtendimentoStatus::RESOLVIDO): ?>
                                            <button type="button" class="at-ticket-quick-btn" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'resolvido')"><span class="at-dot success"></span> Marcar como resolvido</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <button type="button" class="at-ticket-quick-btn" wire:click="criarPendenciaDoAtendimento" wire:loading.attr="disabled" wire:target="criarPendenciaDoAtendimento"><i class="bi bi-clipboard-plus" aria-hidden="true"></i> Criar pendência interna</button>
                                        <button type="button" class="at-ticket-quick-btn" wire:click="solicitarDocumentoDoAtendimento" wire:loading.attr="disabled" wire:target="solicitarDocumentoDoAtendimento"><i class="bi bi-file-earmark-plus" aria-hidden="true"></i> Solicitar documento no portal</button>
                                    </div>
                                </section>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Histórico de status</header>
                                <div class="at-ticket-status-list">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusKey => $statusMeta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div class="at-ticket-status-item">
                                            <span class="at-ticket-status-circle <?php echo e(($selectedAtendimento['status'] ?? '') === $statusKey ? ($statusMeta['tone'] ?? 'info') : ''); ?>"></span>
                                            <span><?php echo e($statusMeta['label']); ?></span>
                                            <small><?php echo e(($selectedAtendimento['status'] ?? '') === $statusKey ? ($selectedAtendimento['updated_at'] ?? '-') : '-'); ?></small>
                                        </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            </section>
                        </aside>
                    </div>
                <?php else: ?>
                    <header class="at-ticket-modal-head">
                        <div class="at-ticket-modal-title">
                            <span class="at-ticket-modal-title-icon"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i></span>
                            <div><h2>Carregando atendimento...</h2><p>Aguarde enquanto os dados são preparados.</p></div>
                        </div>
                        <button type="button" class="at-ticket-icon-btn" title="Fechar" wire:click="fecharDetalhe"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                    </header>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/partials/atendimentos-detail-modal.blade.php ENDPATH**/ ?>