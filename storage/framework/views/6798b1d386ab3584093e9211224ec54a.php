<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal do Cliente</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>?v=lote3">

</head>
<body class="portal-cliente-public">
<?php
    $portalClienteLogado = auth('portal_cliente')->user();
    $portalEmpresaId = (int) ($empresaId ?? ($empresa['id'] ?? 0));
    $portalClienteEmpresaId = (int) ($portalClienteLogado->empresa_id ?? 0);
    $portalClienteIdentificado = $portalClienteLogado && $portalEmpresaId > 0 && $portalClienteEmpresaId === $portalEmpresaId;

    $clientePublicoNome = old('nome', $portalClienteIdentificado ? ($portalClienteLogado->nome ?? 'Cliente do portal') : ($empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? 'Cliente do portal'));
    $clientePublicoEmail = old('email', $portalClienteIdentificado ? ($portalClienteLogado->email ?? '') : ($empresa['email'] ?? ''));
    $percent = (int) ($progress['percent'] ?? 0);
    $empresaNome = $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? 'Cliente';
    $atrasadosCount = (int) ($statusSummary['atrasados'] ?? 0);
    $solicitacoesAbertas = collect($supportQueue ?? [])->values();
    $ticketsCliente = collect($clienteTickets ?? $tickets ?? $supportQueue ?? [])->values();
    $ticketsCount = $ticketsCliente->count();
    // Mantém os badges de Pendências sincronizados com a quantidade real de tickets exibidos.
    // Isso evita voltar a mostrar total fixo/antigo nos ícones e na aba Pendências.
    $pendenciasCount = $ticketsCount;
    $ticketSelecionadoId = (int) ($selectedAtendimentoId ?? 0);
    $ticketSelecionado = $ticketsCliente->first(fn ($ticket) => (int) ($ticket['id'] ?? 0) === $ticketSelecionadoId);
    $ticketSelecionadoLabel = $ticketSelecionado['ticket_label'] ?? ($ticketSelecionadoId > 0 ? '#ATD-' . str_pad((string) $ticketSelecionadoId, 5, '0', STR_PAD_LEFT) : 'Atendimento');
    $chatMensagens = collect($chat ?? [])->values();
    $timelineItens = collect($timeline ?? [])->take(6)->values();
    $ultimaMensagem = $chatMensagens->last();
    $abertura = $timelineItens->first()['created_at_label'] ?? $ultimaMensagem['created_at_label'] ?? 'Ainda não informado';
    $atualizacao = $ultimaMensagem['created_at_label'] ?? $timelineItens->last()['created_at_label'] ?? 'Ainda não informado';
    $protocolo = '#ATD-' . now()->format('Y') . '-' . str_pad((string) (($empresaId ?? 0) ?: 1), 6, '0', STR_PAD_LEFT);
    $statusLabel = $pendenciasCount > 0 ? 'Aguardando você' : ($chatMensagens->isNotEmpty() ? 'Em andamento' : 'Aberto');
    $statusClasse = $pendenciasCount > 0 ? 'warn' : 'ok';
    $hasPendencias = $pendenciasCount > 0;
    $acaoPrincipalLabel = $hasPendencias ? 'Resolver pendências agora' : 'Falar com a equipe';
    $acaoPrincipalDestino = $hasPendencias ? 'pendencias' : 'chat';
    $statusAtendimentoDescricao = $hasPendencias
        ? 'O atendimento está aguardando sua resposta. Resolva as pendências abaixo para liberar a próxima etapa.'
        : 'O atendimento está em acompanhamento. Você pode enviar dúvidas ou documentos pelo chat quando precisar.';
    $responsavel = 'Equipe de Suporte';
    $iniciaisEmpresa = collect(preg_split('/\s+/', trim($empresaNome)) ?: [])->filter()->take(2)->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))->implode('') ?: 'CL';
    $iniciaisCliente = collect(preg_split('/\s+/', trim((string) $clientePublicoNome)) ?: [])->filter()->take(2)->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))->implode('') ?: $iniciaisEmpresa;
    $iniciaisAutor = function (array $mensagem): string {
        $nome = trim((string) ($mensagem['nome'] ?? $mensagem['autor_label'] ?? ''));
        $partes = preg_split('/\s+/', $nome) ?: [];
        $iniciais = collect($partes)->filter()->take(2)->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))->implode('');
        return $iniciais !== '' ? $iniciais : (($mensagem['origem'] ?? 'cliente') === 'interno' ? 'EQ' : 'CL');
    };
    $mensagemComLinks = function (?string $texto): \Illuminate\Support\HtmlString {
        $seguro = e((string) $texto);
        $seguro = preg_replace('/(https?:\/\/[^\s<]+)/i', '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', $seguro) ?? $seguro;
        return new \Illuminate\Support\HtmlString($seguro);
    };
    $formatarTamanho = function (?int $bytes): string {
        if (! $bytes) return 'Arquivo';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        return max(1, (int) ceil($bytes / 1024)) . ' KB';
    };
?>

<div class="portal-app">
    <header class="portal-topbar">
        <div class="brand">
            <div class="brand-mark" aria-hidden="true"></div>
            <div class="brand-divider" aria-hidden="true"></div>
            <div>
                <h1>Portal do Cliente</h1>
                <p><?php echo e(\Illuminate\Support\Str::limit($empresaNome, 34)); ?> · acompanhe seus atendimentos com segurança.</p>
            </div>
        </div>
        <div class="top-actions">
            <div class="profile" aria-label="Cliente identificado">
                <div class="profile-avatar"><?php echo e($iniciaisCliente); ?></div>
                <div class="profile-copy">
                    <strong class="profile-name"><?php echo e(\Illuminate\Support\Str::limit($clientePublicoNome ?: 'Cliente', 28)); ?></strong>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($clientePublicoEmail)): ?>
                        <span class="profile-email"><?php echo e(\Illuminate\Support\Str::limit($clientePublicoEmail, 34)); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth('portal_cliente')->check() && \Illuminate\Support\Facades\Route::has('portal.cliente.logout')): ?>
                <form method="POST" action="<?php echo e(route('portal.cliente.logout')); ?>" class="logout-form">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="logout-button">Sair</button>
                </form>
            <?php else: ?>
                <a href="<?php echo e(url('/')); ?>" class="logout-button">Sair</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </header>

    <div class="portal-body">
        <nav class="side-nav" aria-label="Navegação lateral">
            <button type="button" class="side-link active" data-portal-cluster="chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8M8 13h5"/></svg>
                <span>Atendimento</span>
            </button>
            <button type="button" class="side-link" data-portal-cluster="pendencias">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciasCount > 0): ?><span class="side-badge"><?php echo e($pendenciasCount); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5h6"/><path d="M9 12l2 2 4-4"/><path d="M5 7a2 2 0 0 1 2-2h1a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2h1a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2z"/></svg>
                <span>Pendências</span>
            </button>
            <div class="side-separator"></div>
            <button type="button" class="side-link" data-portal-cluster="status">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 20h18"/><path d="M6 16V9"/><path d="M12 16V4"/><path d="M18 16v-6"/><path d="m15 7 3-3 3 3"/></svg>
                <span>Status</span>
            </button>
        </nav>

        <div class="portal-content">
            <div class="portal-cluster" aria-label="Navegação do atendimento">
                <button type="button" class="portal-cluster-button is-active" data-portal-cluster="chat">Atendimento</button>
                <button type="button" class="portal-cluster-button" data-portal-cluster="pendencias">Pendências <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciasCount > 0): ?><span class="portal-cluster-badge"><?php echo e($pendenciasCount); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></button>
                <button type="button" class="portal-cluster-button" data-portal-cluster="status">Status</button>
                <button type="button" class="portal-cluster-button" data-portal-cluster="historico">Histórico do chat</button>
            </div>

            <main class="workspace is-single-view" data-portal-workspace>
            <section class="panel panel-pad action-banner <?php echo e($hasPendencias ? 'is-urgent' : ''); ?>" data-portal-section="chat" aria-label="Próxima ação recomendada">
                <div class="action-banner-header">
                    <div class="action-banner-copy">
                        <span class="action-banner-kicker">Próximo passo recomendado</span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasPendencias): ?>
                            <strong>Você tem <?php echo e($pendenciasCount); ?> <?php echo e($pendenciasCount === 1 ? 'pendência aguardando sua ação' : 'pendências aguardando sua ação'); ?>.</strong>
                            <span>Comece pela primeira pendência destacada. Depois de responder, a equipe consegue continuar o atendimento sem atrasos.</span>
                        <?php else: ?>
                            <strong>Seu atendimento está em acompanhamento.</strong>
                            <span>Não há pendências abertas agora. Use o chat para tirar dúvidas, enviar documentos ou acompanhar as atualizações da equipe.</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <button type="button" class="outline-button" data-portal-cluster="<?php echo e($acaoPrincipalDestino); ?>"><?php echo e($acaoPrincipalLabel); ?> <span>→</span></button>
                </div>
                <button type="button" class="mobile-summary-toggle" data-mobile-summary-toggle aria-expanded="false">Ver orientação rápida <span>⌄</span></button>
                <div class="journey-steps" data-mobile-summary-content aria-label="Fluxo recomendado do atendimento">
                    <div class="journey-step <?php echo e($hasPendencias ? 'is-current' : ''); ?>">
                        <span class="journey-step-number">1</span>
                        <div><strong>Resolva pendências</strong><span><?php echo e($hasPendencias ? 'Ação necessária agora' : 'Nada pendente no momento'); ?></span></div>
                    </div>
                    <div class="journey-step <?php echo e(! $hasPendencias ? 'is-current' : ''); ?>">
                        <span class="journey-step-number">2</span>
                        <div><strong>Fale pelo chat</strong><span>Envie dúvidas e documentos</span></div>
                    </div>
                    <div class="journey-step">
                        <span class="journey-step-number">3</span>
                        <div><strong>Acompanhe o status</strong><span>Veja protocolo e atualização</span></div>
                    </div>
                </div>
            </section>
            <section class="panel panel-pad portal-section-hidden" id="pendencias" data-portal-section="pendencias">
                <div class="panel-title-row">
                    <h2>Meus tickets</h2>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticketsCount > 0): ?><span class="count-dot"><?php echo e($ticketsCount); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <p class="panel-sub">Acompanhe cada problema separado em um ticket. Para outro assunto, abra um novo problema e ele aparecerá para o suporte na aba Atendimentos.</p>

                <div class="ticket-actions">
                    <details class="new-ticket-box" <?php echo e($errors->has('solicitacao') || $errors->has('titulo') || $errors->has('descricao') ? 'open' : ''); ?>>
                        <summary><span>+ Novo problema</span><span>Abrir ticket</span></summary>
                        <form method="POST" action="<?php echo e(route('portal.cliente.solicitacoes.store', $token)); ?>" class="new-ticket-form js-feedback-form" data-processing="Abrindo ticket...">
                            <?php echo csrf_field(); ?>
                            <div class="hp-field" aria-hidden="true"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                            <input type="text" name="titulo" value="<?php echo e(old('titulo')); ?>" placeholder="Título do problema" required maxlength="255">
                            <textarea name="descricao" placeholder="Explique o que aconteceu e o que precisa de ajuda" required maxlength="5000"><?php echo e(old('descricao')); ?></textarea>
                            <select name="prioridade" required>
                                <option value="baixa" <?php if(old('prioridade') === 'baixa'): echo 'selected'; endif; ?>>Baixa</option>
                                <option value="media" <?php if(old('prioridade', 'media') === 'media'): echo 'selected'; endif; ?>>Média</option>
                                <option value="alta" <?php if(old('prioridade') === 'alta'): echo 'selected'; endif; ?>>Alta</option>
                                <option value="urgente" <?php if(old('prioridade') === 'urgente'): echo 'selected'; endif; ?>>Urgente</option>
                            </select>
                            <button class="btn" type="submit">Criar ticket</button>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['solicitacao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['titulo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['descricao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['prioridade'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="field-error" data-form-feedback style="display:none"></div>
                        </form>
                    </details>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasPendencias): ?>
                        <div class="pending-guidance"><strong>Pendências:</strong> alguns tickets podem estar aguardando resposta, documento ou confirmação sua.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="pending-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ticketsCliente; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $cardClass = ! empty($ticket['is_done']) ? 'info' : ($index === 1 ? 'warn' : '');
                            $prioridade = mb_strtoupper((string) ($ticket['prioridade'] ?? 'MÉDIA'));
                            $statusTicket = $ticket['status_label'] ?? $ticket['status'] ?? 'Aberto';
                            $ticketLabel = $ticket['ticket_label'] ?? ('#ATD-' . str_pad((string) ($ticket['id'] ?? 0), 5, '0', STR_PAD_LEFT));
                        ?>
                        <article class="pending-card ticket-card <?php echo e($cardClass); ?> <?php echo e($index === 0 ? 'is-primary' : ''); ?> <?php echo e($index > 4 ? 'is-extra-hidden' : ''); ?>" data-pending-card>
                            <div class="pending-head">
                                <span class="pending-icon" aria-hidden="true">#</span>
                                <h3 class="pending-title"><?php echo e($ticket['titulo'] ?? 'Ticket de atendimento'); ?></h3>
                                <span class="priority"><?php echo e($prioridade); ?></span>
                            </div>
                            <p class="pending-text"><?php echo e(\Illuminate\Support\Str::limit($ticket['descricao'] ?? 'Conversa aberta com a equipe de suporte.', 150)); ?></p>
                            <div class="ticket-meta">
                                <span class="ticket-chip"><?php echo e($ticketLabel); ?></span>
                                <span class="ticket-chip <?php echo e(! empty($ticket['is_done']) ? 'done' : 'open'); ?>"><?php echo e($statusTicket); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($ticket['created_at_label'])): ?><span class="ticket-chip">Aberto em <?php echo e($ticket['created_at_label']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($ticket['updated_at_label'])): ?><span class="ticket-chip">Atualizado em <?php echo e($ticket['updated_at_label']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <a href="<?php echo e(route('portal.cliente.show', ['token' => $token, 'ticket' => (int) ($ticket['id'] ?? 0)])); ?>#chat" class="outline-button" style="margin-top:14px; text-decoration:none" data-open-ticket-chat="<?php echo e((int) ($ticket['id'] ?? 0)); ?>">Abrir conversa <span>→</span></a>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <article class="pending-card info">
                            <div class="pending-head">
                                <span class="pending-icon" aria-hidden="true">✓</span>
                                <h3 class="pending-title">Nenhum ticket aberto ainda</h3>
                            </div>
                            <p class="pending-text">Use o botão <strong>+ Novo problema</strong> para abrir seu primeiro ticket com a equipe.</p>
                            <span class="deadline">Tudo certo por enquanto.</span>
                        </article>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticketsCliente->count() > 5): ?>
                        <button type="button" class="all-link" data-toggle-all-pendings data-total-pendings="<?php echo e($ticketsCliente->count()); ?>">Ver todos os <?php echo e($ticketsCliente->count()); ?> tickets <span>→</span></button>
                    <?php else: ?>
                        <button type="button" class="all-link" data-portal-cluster="chat">Falar com a equipe <span>→</span></button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <section class="panel chat-panel" id="chat" data-portal-section="chat historico">
                <header class="chat-header">
                    <div class="chat-title">
                        <h2>Chat com a equipe</h2>
                        <span class="online-badge"><?php echo e($ticketSelecionadoId > 0 ? $ticketSelecionadoLabel : 'Resposta pelo portal'); ?></span>
                    </div>
                    <div class="chat-tools">
                        <span class="chat-safe-label">Canal seguro</span>
                    </div>
                </header>

                <div class="chat-scroll" id="chatHistorico" role="log" aria-live="polite" aria-label="Histórico do atendimento">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $chatMensagens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mensagem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $classeMensagem = $mensagem['css_class'] ?? (($mensagem['origem'] ?? 'cliente') === 'interno' ? 'equipe' : 'cliente');
                            $autor = $mensagem['autor_label'] ?? ($classeMensagem === 'equipe' ? 'Equipe' : 'Cliente');
                            $textoMensagem = trim((string) ($mensagem['mensagem_texto'] ?? $mensagem['mensagem'] ?? ''));
                        ?>
                        <div class="message-row <?php echo e($classeMensagem); ?>" data-message-id="<?php echo e($mensagem['id'] ?? 0); ?>" data-message-class="<?php echo e($classeMensagem); ?>">
                            <span class="message-avatar" aria-hidden="true"><?php echo e($iniciaisAutor($mensagem)); ?></span>
                            <div class="bubble-wrap">
                                <div class="bubble">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classeMensagem !== 'cliente'): ?><span class="bubble-name"><?php echo e($mensagem['nome'] ?? $autor); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($textoMensagem !== ''): ?><span><?php echo $mensagemComLinks($textoMensagem); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($mensagem['created_at_label'])): ?><span class="bubble-time"><?php echo e($mensagem['created_at_label']); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classeMensagem === 'cliente'): ?>✓✓<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($classeMensagem === 'cliente'): ?><span class="chat-seen-status" data-seen-status style="display:none">Visualizado pelo suporte</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($mensagem['attachments'])): ?>
                                    <div class="attachment-grid" aria-label="Anexos da mensagem">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $mensagem['attachments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anexo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <?php
                                                $nomeAnexo = $anexo['nome'] ?? 'Anexo';
                                                $isImage = (bool) ($anexo['is_image'] ?? false);
                                                $extensao = strtoupper(pathinfo($nomeAnexo, PATHINFO_EXTENSION) ?: ($isImage ? 'IMG' : 'DOC'));
                                            ?>
                                            <a class="attachment-card" href="<?php echo e($anexo['url']); ?>" target="_blank" rel="noopener noreferrer" download>
                                                <span class="file-icon <?php echo e($isImage ? 'image' : ''); ?>"><?php echo e($isImage ? 'IMG' : $extensao); ?></span>
                                                <span class="file-info"><strong><?php echo e($nomeAnexo); ?></strong><span><?php echo e($anexo['mime_type'] ?? 'Arquivo anexado'); ?></span></span>
                                                <span class="download-icon">⌄</span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isImage): ?><img class="attachment-preview" src="<?php echo e($anexo['url']); ?>" alt="<?php echo e($nomeAnexo); ?>" loading="lazy"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="empty-chat">
                            <div>
                                <strong>Nenhuma mensagem ainda.</strong><br>
                                Envie uma dúvida, documento ou atualização pelo campo abaixo. A equipe responderá por este canal.
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="chat-typing-status" data-support-typing aria-live="polite">
                    <span class="chat-typing-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                    <span data-support-typing-text>Suporte está digitando...</span>
                </div>

                <form method="POST" action="<?php echo e(route('portal.cliente.mensagem', $token)); ?>" class="composer js-feedback-form" data-processing="Enviando mensagem..." enctype="multipart/form-data" data-chat-form>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_portal_ajax" value="1">
                    <div class="hp-field" aria-hidden="true"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                    <input type="hidden" name="nome" value="<?php echo e($clientePublicoNome); ?>">
                    <input type="hidden" name="email" value="<?php echo e($clientePublicoEmail); ?>">
                    <input type="hidden" name="atendimento_id" value="<?php echo e($ticketSelecionadoId > 0 ? $ticketSelecionadoId : ''); ?>">
                    <div class="composer-client-summary" aria-label="Cliente identificado pelo link do portal">
                        <span>Cliente identificado</span>
                        <strong><?php echo e($clientePublicoNome); ?></strong>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="composer-row">
                        <label class="icon-input file-control" title="Anexar arquivo">
                            <input id="chatAnexos" class="js-chat-files" name="anexos[]" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp,application/pdf">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.4 11.6-8.5 8.5a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 0 1-2.8-2.8l8.5-8.5"/></svg>
                        </label>
                        <label class="icon-input file-control" title="Enviar imagem">
                            <input class="js-chat-files-mirror" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" tabindex="-1">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                        </label>
                        <textarea class="js-chat-message" name="mensagem" rows="1" maxlength="5000" placeholder="Digite sua dúvida, resposta ou informe quais documentos está enviando..." aria-invalid="<?php echo e($errors->has('mensagem') ? 'true' : 'false'); ?>"><?php echo e(old('mensagem')); ?></textarea>
                        <button class="send-button" type="submit" aria-label="Enviar mensagem">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        </button>
                    </div>
                    <div class="selected-files" data-selected-files aria-live="polite"></div>
                    <div class="composer-help"><span data-composer-hint>Você pode enviar arquivos: PDF, JPG, PNG, DOC, DOCX (até 10MB cada)</span><span><span data-chat-count>0</span>/5000</span></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['mensagem'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['anexos'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['anexos.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="field-error" data-form-feedback style="display:none"></div>
                </form>
            </section>

            <aside class="right-column portal-section-hidden" id="status" data-portal-section="status">
                <section class="panel status-card">
                    <h2>Status do atendimento</h2>
                    <div class="divider"></div>
                    <span class="status-pill <?php echo e($statusClasse); ?>"><?php echo e($statusLabel); ?></span>
                    <p class="status-desc"><?php echo e($statusAtendimentoDescricao); ?></p>
                    <div class="divider"></div>
                    <div class="info-list">
                        <div class="info-row"><span>♙</span><strong>Protocolo</strong><span><?php echo e($protocolo); ?></span></div>
                        <div class="info-row"><span>▣</span><strong>Abertura</strong><span><?php echo e($abertura); ?></span></div>
                        <div class="info-row"><span>◷</span><strong>Última atualização</strong><span><?php echo e($atualizacao); ?></span></div>
                        <div class="info-row"><span>♧</span><strong>Responsável</strong><span class="person"><span class="small-avatar">EQ</span><span><?php echo e($responsavel); ?><small>Suporte</small></span></span></div>
                    </div>
                </section>

                <section class="panel status-card">
                    <h2>Progresso do atendimento</h2>
                    <div class="divider"></div>
                    <div class="progress-line" aria-label="Progresso do atendimento">
                        <div class="step done"><span class="step-circle">✓</span><span>Recebido</span></div>
                        <div class="step <?php echo e($percent >= 35 ? 'active' : ''); ?>"><span class="step-circle">2</span><span>Em análise</span></div>
                        <div class="step <?php echo e($pendenciasCount > 0 ? 'active' : ''); ?>"><span class="step-circle">3</span><span>Aguardando<br>você</span></div>
                        <div class="step <?php echo e($percent >= 100 ? 'done' : ''); ?>"><span class="step-circle">4</span><span>Concluído</span></div>
                    </div>
                    <div class="divider"></div>
                    <h2 style="font-size:17px">Resumo da solicitação</h2>
                    <p class="summary-text">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($solicitacoesAbertas->isNotEmpty()): ?>
                            <?php echo e(\Illuminate\Support\Str::limit($solicitacoesAbertas->first()['descricao'] ?? $solicitacoesAbertas->first()['titulo'] ?? 'Atendimento em acompanhamento pela equipe.', 170)); ?>

                        <?php elseif($nextDelivery): ?>
                            <?php echo e(\Illuminate\Support\Str::limit($nextDelivery['titulo'] ?? 'Atendimento em acompanhamento pela equipe.', 170)); ?>

                        <?php else: ?>
                            Solicitação em acompanhamento pela equipe de suporte. Use o chat para enviar dúvidas, documentos e atualizações.
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </p>
                    <button type="button" class="all-link" style="margin-top:26px" data-portal-cluster="historico">Abrir histórico do chat <span>→</span></button>
                </section>
            </aside>
            </main>
        </div>
    </div>
</div>

<div class="alerts">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert"><strong>Pronto!</strong> <?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="errors" role="alert">
            <strong>Não foi possível continuar.</strong>
            <ul><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><li><?php echo e($erro); ?></li><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<div class="client-toast" data-client-toast role="status" aria-live="polite"></div>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($socketIoConfig['url'])): ?>
    <script src="<?php echo e(rtrim($socketIoConfig['url'], '/')); ?>/socket.io/socket.io.js" onload="window.__portalSocketIoScriptLoaded=true" onerror="window.__portalSocketIoScriptError=true"></script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>';
    const portalChatSocketConfig = <?php echo json_encode($socketIoConfig ?? [], 15, 512) ?>;
    const portalSelectedAtendimentoId = Number(<?php echo json_encode($ticketSelecionadoId ?? 0, 15, 512) ?>);
    let portalChatSocket = null;
    let portalSocketRetryTimer = null;
    let portalOfflineSyncTimer = null;
    let portalOfflineSyncInFlight = false;

    const typingState = { timer: null, stopTimer: null, lastSent: 0, active: false };
    const debugState = { lastSent: 0, lastStep: null };
    let clientChatSendingBusy = false;

    function announceClientTyping(form) {
        if (!portalChatSocket || !portalChatSocket.connected) return;

        const now = Date.now();
        const nome = form?.querySelector('[name="nome"]')?.value || '';

        if (!typingState.active || now - typingState.lastSent >= 1800) {
            typingState.active = true;
            typingState.lastSent = now;
            portalChatSocket.emit('chat:typing:start', { actor: portalChatSocketConfig.actor || 'cliente', nome: nome || 'Cliente', room: portalChatSocketConfig.room || '' });
        }

        window.clearTimeout(typingState.stopTimer);
        typingState.stopTimer = window.setTimeout(function () {
            if (!portalChatSocket || !portalChatSocket.connected || !typingState.active) return;
            typingState.active = false;
            portalChatSocket.emit('chat:typing:stop', { actor: portalChatSocketConfig.actor || 'cliente', nome: nome || 'Cliente', room: portalChatSocketConfig.room || '' });
        }, 1200);
    }

    function portalDebug(step, extra = {}) { return; }

    function showClientToast(message, type = 'info') {
        const toast = document.querySelector('[data-client-toast]');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.toggle('is-error', type === 'error');
        toast.classList.toggle('is-success', type === 'success');
        toast.classList.add('is-visible');
        window.clearTimeout(toast.dataset.timer);
        toast.dataset.timer = window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 3800);
    }

    function setButtonProcessing(button, processingText) {
        if (!button || button.dataset.processingApplied === '1') return;
        button.dataset.processingApplied = '1';
        button.disabled = true;
        button.dataset.originalHtml = button.innerHTML;
        button.innerHTML = '<span class="button-loading-text">' + (processingText || 'Enviando...') + '</span>';
    }

    function setPortalCluster(target) {
        const workspace = document.querySelector('[data-portal-workspace]');
        const normalizedTarget = target === 'historico' ? 'historico' : (target || 'chat');
        const visibleSection = normalizedTarget === 'historico' ? 'historico' : normalizedTarget;

        document.querySelectorAll('[data-portal-cluster]').forEach(function (button) {
            const active = button.dataset.portalCluster === normalizedTarget;
            button.classList.toggle('is-active', active);
            button.classList.toggle('active', active);
        });

        document.querySelectorAll('[data-portal-section]').forEach(function (section) {
            const sectionTargets = String(section.dataset.portalSection || '').split(/\s+/).filter(Boolean);

            // Atendimento deve manter o layout original da tela: pendências à esquerda,
            // chat no centro e status na direita. As demais opções do cluster continuam
            // abrindo em visual único para evitar o problema antigo das abas por hash.
            const isMobileCompact = window.matchMedia('(max-width: 760px)').matches;
            const shouldShow = normalizedTarget === 'chat'
                ? (isMobileCompact
                    ? sectionTargets.includes('chat')
                    : sectionTargets.some(function (sectionTarget) {
                        return ['pendencias', 'chat', 'status'].includes(sectionTarget);
                    }))
                : sectionTargets.includes(visibleSection);

            section.classList.toggle('portal-section-hidden', !shouldShow);
        });

        if (workspace) {
            const isMobileCompact = window.matchMedia('(max-width: 760px)').matches;
            workspace.classList.toggle('is-single-view', isMobileCompact || normalizedTarget !== 'chat');
        }

        if (normalizedTarget === 'chat' || normalizedTarget === 'historico') {
            const chatHistoricoAtual = document.getElementById('chatHistorico');
            if (chatHistoricoAtual) {
                window.setTimeout(function () {
                    chatHistoricoAtual.scrollTop = chatHistoricoAtual.scrollHeight;
                }, 50);
            }
        }

        portalDebug('cluster_change', {
            target: normalizedTarget,
            atendimentoLayoutOriginal: normalizedTarget === 'chat'
        });
    }

    document.addEventListener('click', function (event) {
        const clusterButton = event.target.closest('[data-portal-cluster]');
        if (clusterButton) {
            event.preventDefault();
            event.stopPropagation();
            setPortalCluster(clusterButton.dataset.portalCluster || 'chat');
            return;
        }

        const clicked = event.target.closest('button, a, input, textarea, select, label, [role="button"]');
        if (clicked) {
            portalDebug('click', {
                tag: clicked.tagName,
                text: (clicked.innerText || clicked.value || clicked.getAttribute('aria-label') || clicked.title || '').trim().slice(0, 120),
                id: clicked.id || null,
                className: clicked.className || null,
                href: clicked.getAttribute('href') || null,
                name: clicked.getAttribute('name') || null,
                type: clicked.getAttribute('type') || null
            });
        }
    }, true);

    const chatHistorico = document.getElementById('chatHistorico');
    if (chatHistorico) chatHistorico.scrollTop = chatHistorico.scrollHeight;
    setPortalCluster('chat');

    document.querySelectorAll('[data-toggle-all-pendings]').forEach(function (button) {
        button.addEventListener('click', function () {
            const list = button.closest('.pending-list');
            if (!list) return;
            const expanded = !list.classList.contains('is-expanded');
            list.classList.toggle('is-expanded', expanded);
            button.innerHTML = expanded ? 'Mostrar apenas as 3 principais' : 'Ver todas as ' + (button.dataset.totalPendings || '') + ' pendências <span>→</span>';
            portalDebug('toggle_all_pendings', { expanded: expanded });
        });
    });

    document.querySelectorAll('[data-toggle-pending]').forEach(function (button) {
        button.addEventListener('click', function () {
            const card = button.closest('[data-pending-card]');
            if (!card) return;
            card.classList.toggle('is-open');
            button.innerHTML = card.classList.contains('is-open') ? 'Ocultar resposta' : 'Responder pendência <span>→</span>';
            if (!card.classList.contains('is-open') && card.classList.contains('is-primary')) {
                button.innerHTML = 'Responder agora <span>→</span>';
            }
        });
    });

    document.querySelectorAll('.js-chat-message').forEach(function (textarea) {
        const form = textarea.closest('form');
        const counter = form?.querySelector('[data-chat-count]');
        const grow = function () {
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 150) + 'px';
            if (counter) counter.textContent = String(textarea.value.length);
        };
        textarea.addEventListener('input', function () {
            grow();
            if (textarea.value.trim().length > 0) {
                announceClientTyping(form);
            }
        });
        textarea.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
                event.preventDefault();
                const tamanhoMensagem = textarea.value.trim().length;
                portalDebug('chat_enter_submit', { tamanhoMensagem: tamanhoMensagem });
                if (tamanhoMensagem > 0) announceClientTyping(form);
                form?.requestSubmit();
            }
        });
        grow();
    });

    document.querySelectorAll('.js-chat-files-mirror').forEach(function (mirror) {
        mirror.addEventListener('change', function () {
            const mainInput = mirror.closest('form')?.querySelector('.js-chat-files');
            if (!mainInput || !mirror.files || mirror.files.length === 0) return;
            mainInput.files = mirror.files;
            mainInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });

    document.querySelectorAll('.js-chat-files').forEach(function (input) {
        const form = input.closest('form');
        const list = form?.querySelector('[data-selected-files]');
        const hint = form?.querySelector('[data-composer-hint]');
        const renderFiles = function () {
            if (!list) return;
            list.innerHTML = '';
            const files = Array.from(input.files || []);
            if (files.length === 0) {
                list.classList.remove('is-visible');
                if (hint) hint.textContent = 'Você pode enviar arquivos: PDF, JPG, PNG, DOC, DOCX (até 10MB cada)';
                return;
            }
            list.classList.add('is-visible');
            if (hint) hint.textContent = files.length === 1 ? '1 arquivo selecionado para envio' : files.length + ' arquivos selecionados para envio';
            files.slice(0, 5).forEach(function (file) {
                const chip = document.createElement('span');
                chip.className = 'file-chip';
                const size = file.size >= 1048576 ? (file.size / 1048576).toFixed(1).replace('.', ',') + ' MB' : Math.max(1, Math.ceil(file.size / 1024)) + ' KB';
                chip.textContent = file.name + ' • ' + size;
                list.appendChild(chip);
            });
            if (files.length > 5) {
                const chip = document.createElement('span');
                chip.className = 'file-chip';
                chip.textContent = 'Envie no máximo 5 arquivos por mensagem.';
                list.appendChild(chip);
            }
        };
        input.addEventListener('change', renderFiles);
        renderFiles();
    });



    function appendOptimisticChatMessage(form, message, filesCount) {
        const chat = document.getElementById('chatHistorico');
        if (!chat) return;

        const empty = chat.querySelector('.empty-chat');
        if (empty) empty.remove();

        const row = document.createElement('div');
        row.className = 'message-row cliente is-optimistic is-sent';
        row.dataset.messageId = 'tmp-' + Date.now();

        const wrap = document.createElement('div');
        wrap.className = 'bubble-wrap';

        const bubble = document.createElement('div');
        bubble.className = 'bubble';

        const text = document.createElement('span');
        const cleanMessage = message || (filesCount > 0 ? (filesCount === 1 ? 'Arquivo anexado' : filesCount + ' arquivos anexados') : 'Mensagem enviada');
        text.textContent = cleanMessage;
        bubble.appendChild(text);

        const time = document.createElement('span');
        time.className = 'bubble-time';
        time.textContent = 'agora';
        bubble.appendChild(time);

        const seen = document.createElement('span');
        seen.className = 'chat-seen-status';
        seen.dataset.seenStatus = '1';
        seen.style.display = 'none';
        seen.textContent = 'Visualizado pelo suporte';
        bubble.appendChild(seen);

        wrap.appendChild(bubble);
        row.appendChild(wrap);
        chat.appendChild(row);
        chat.scrollTop = chat.scrollHeight;
        return row;
    }

    function setInlineFeedback(form, message, type = 'error') {
        const feedback = form?.querySelector('[data-form-feedback]');
        if (feedback) {
            feedback.textContent = message;
            feedback.style.display = message ? 'block' : 'none';
            feedback.setAttribute('role', type === 'error' ? 'alert' : 'status');
        }
        form?.classList.toggle('is-invalid', type === 'error' && Boolean(message));
        const textarea = form?.querySelector('textarea');
        textarea?.classList.toggle('is-invalid', type === 'error' && Boolean(message));
    }

    function resetChatComposer(form) {
        const textarea = form.querySelector('.js-chat-message');
        const fileInput = form.querySelector('.js-chat-files');
        const fileMirror = form.querySelector('.js-chat-files-mirror');
        const selectedFiles = form.querySelector('[data-selected-files]');
        const hint = form.querySelector('[data-composer-hint]');
        const counter = form.querySelector('[data-chat-count]');

        if (textarea) {
            textarea.value = '';
            textarea.style.height = 'auto';
            textarea.removeAttribute('readonly');
            textarea.focus({ preventScroll: true });
        }
        if (fileInput) fileInput.value = '';
        if (fileMirror) fileMirror.value = '';
        if (selectedFiles) {
            selectedFiles.innerHTML = '';
            selectedFiles.classList.remove('is-visible');
        }
        if (hint) hint.textContent = 'Você pode enviar arquivos: PDF, JPG, PNG, DOC, DOCX (até 10MB cada)';
        if (counter) counter.textContent = '0';
    }

    function markOptimisticMessage(row, status, text) {
        if (!row) return;
        row.classList.toggle('is-sent', status === 'sent');
        row.classList.toggle('is-failed', status === 'failed');
        const time = row.querySelector('.bubble-time');
        if (time) time.textContent = text || (status === 'sent' ? 'enviado agora' : 'falha no envio');
    }

    function applyServerMessageToOptimistic(row, msg) {
        if (!row || !msg) return;
        row.classList.remove('is-optimistic');
        row.dataset.messageId = String(msg.id || row.dataset.messageId || '');
        const text = row.querySelector('.bubble > span:first-child');
        if (text && msg.text) text.textContent = msg.text;
        const time = row.querySelector('.bubble-time');
        if (time) time.textContent = msg.time || 'agora';
    }


    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
        });
    }

    function renderAttachmentCard(anexo) {
        const nome = escapeHtml(anexo.name || 'Anexo');
        const url = escapeHtml(anexo.url || '#');
        const size = escapeHtml(anexo.size || 'Arquivo anexado');
        const isImage = Boolean(anexo.is_image);
        const ext = nome.includes('.') ? nome.split('.').pop().toUpperCase().slice(0, 5) : (isImage ? 'IMG' : 'DOC');
        return '<a class="attachment-card" href="' + url + '" target="_blank" rel="noopener noreferrer" download>' +
            '<span class="file-icon ' + (isImage ? 'image' : '') + '">' + (isImage ? 'IMG' : ext) + '</span>' +
            '<span class="file-info"><strong>' + nome + '</strong><span>' + size + '</span></span>' +
            '<span class="download-icon">⌄</span>' +
            (isImage ? '<img class="attachment-preview" src="' + url + '" alt="' + nome + '" loading="lazy">' : '') +
        '</a>';
    }

    function renderChatMessages(messages, clientSeenUntilId) {
        const chat = document.getElementById('chatHistorico');
        if (!chat || !Array.isArray(messages)) return;

        const rows = Array.from(chat.querySelectorAll('[data-message-id]'));
        const currentIds = rows
            .filter(function (el) { return !String(el.dataset.messageId || '').startsWith('tmp-'); })
            .map(function (el) { return String(el.dataset.messageId || ''); })
            .join('|');
        const nextIds = messages.map(function (msg) { return String(msg.id || ''); }).join('|');
        const hasOptimisticRows = rows.some(function (el) {
            return el.classList.contains('is-optimistic') || String(el.dataset.messageId || '').startsWith('tmp-');
        });

        portalDebug('chat_render_start', {
            current_ids: currentIds,
            next_ids: nextIds,
            has_optimistic: hasOptimisticRows,
            total_dom: rows.length,
            total_server: messages.length
        });

        if (currentIds === nextIds && !hasOptimisticRows) {
            updateSeenStatus(clientSeenUntilId);
            portalDebug('chat_render_skip', { motivo: 'ids_iguais', total_server: messages.length });
            return;
        }

        chat.innerHTML = '';

        if (messages.length === 0) {
            chat.innerHTML = '<div class="empty-chat"><div><strong>Nenhuma mensagem ainda.</strong><br>Envie uma dúvida, documento ou atualização pelo campo abaixo. A equipe responderá por este canal.</div></div>';
            return;
        }

        messages.forEach(function (msg) {
            const classe = msg.class === 'equipe' ? 'equipe' : 'cliente';
            const row = document.createElement('div');
            row.className = 'message-row ' + classe;
            row.dataset.messageId = String(msg.id || '');
            row.dataset.messageClass = classe;

            const avatar = document.createElement('span');
            avatar.className = 'message-avatar';
            avatar.setAttribute('aria-hidden', 'true');
            const author = String(msg.author || (classe === 'equipe' ? 'Equipe' : 'Cliente'));
            avatar.textContent = author.slice(0, 2).toUpperCase();

            const wrap = document.createElement('div');
            wrap.className = 'bubble-wrap';

            const bubble = document.createElement('div');
            bubble.className = 'bubble';

            if (classe !== 'cliente') {
                const name = document.createElement('span');
                name.className = 'bubble-name';
                name.textContent = author;
                bubble.appendChild(name);
            }

            if (msg.text) {
                const text = document.createElement('span');
                text.textContent = msg.text;
                bubble.appendChild(text);
            }

            const time = document.createElement('span');
            time.className = 'bubble-time';
            time.textContent = (msg.time || '') + (classe === 'cliente' ? ' ✓✓' : '');
            bubble.appendChild(time);

            if (classe === 'cliente') {
                const seen = document.createElement('span');
                seen.className = 'chat-seen-status';
                seen.dataset.seenStatus = '1';
                seen.style.display = Number(msg.id || 0) <= Number(clientSeenUntilId || 0) ? 'block' : 'none';
                seen.textContent = 'Visualizado pelo suporte';
                bubble.appendChild(seen);
            }

            wrap.appendChild(bubble);

            if (Array.isArray(msg.attachments) && msg.attachments.length > 0) {
                const grid = document.createElement('div');
                grid.className = 'attachment-grid';
                grid.setAttribute('aria-label', 'Anexos da mensagem');
                grid.innerHTML = msg.attachments.map(renderAttachmentCard).join('');
                wrap.appendChild(grid);
            }

            row.appendChild(avatar);
            row.appendChild(wrap);
            chat.appendChild(row);
        });

        chat.scrollTop = chat.scrollHeight;
        updateSeenStatus(clientSeenUntilId);
        portalDebug('chat_render_done', {
            total_renderizado: messages.length,
            ultimo_id: messages.length ? Number(messages[messages.length - 1].id || 0) : 0,
            client_seen_until_id: Number(clientSeenUntilId || 0)
        });
    }

    function updateSeenStatus(clientSeenUntilId) {
        const limit = Number(clientSeenUntilId || 0);
        document.querySelectorAll('#chatHistorico .message-row.cliente[data-message-id] [data-seen-status]').forEach(function (seen) {
            const row = seen.closest('[data-message-id]');
            const id = Number(row?.dataset.messageId || 0);
            seen.style.display = limit > 0 && id > 0 && id <= limit ? 'block' : 'none';
        });
    }

    function setSupportTyping(isTyping, name) {
        const box = document.querySelector('[data-support-typing]');
        const text = document.querySelector('[data-support-typing-text]');
        if (!box) return;
        box.classList.toggle('is-visible', Boolean(isTyping));
        if (text) text.textContent = (name || 'Suporte') + ' está digitando...';
    }

    function normalizeRealtimeMessage(payload) {
        const origem = String(payload?.origem || payload?.actor || payload?.class || '').toLowerCase();
        const messageClass = payload?.class || (['cliente', 'portal_cliente', 'client', 'publico'].includes(origem) ? 'cliente' : 'equipe');

        return {
            id: payload?.id || payload?.message_id || '',
            class: messageClass === 'cliente' ? 'cliente' : 'equipe',
            author: payload?.author || payload?.usuario_nome || payload?.nome || (messageClass === 'cliente' ? 'Cliente' : 'Equipe'),
            text: payload?.text || payload?.mensagem_texto || payload?.mensagem || '',
            time: payload?.time || payload?.created_at_label || 'agora',
            attachments: Array.isArray(payload?.attachments) ? payload.attachments : (Array.isArray(payload?.anexos) ? payload.anexos : []),
        };
    }

    function appendRealtimeChatMessage(payload) {
        const msg = normalizeRealtimeMessage(payload);
        if (!msg.id) return;

        const chat = document.getElementById('chatHistorico');
        if (!chat) return;

        if (chat.querySelector('[data-message-id="' + CSS.escape(String(msg.id)) + '"]')) {
            return;
        }

        const empty = chat.querySelector('.empty-chat');
        if (empty) empty.remove();

        const classe = msg.class === 'equipe' ? 'equipe' : 'cliente';
        const row = document.createElement('div');
        row.className = 'message-row ' + classe;
        row.dataset.messageId = String(msg.id);
        row.dataset.messageClass = classe;

        const avatar = document.createElement('span');
        avatar.className = 'message-avatar';
        avatar.setAttribute('aria-hidden', 'true');
        const author = String(msg.author || (classe === 'equipe' ? 'Equipe' : 'Cliente'));
        avatar.textContent = author.slice(0, 2).toUpperCase();

        const wrap = document.createElement('div');
        wrap.className = 'bubble-wrap';

        const bubble = document.createElement('div');
        bubble.className = 'bubble';

        if (classe !== 'cliente') {
            const name = document.createElement('span');
            name.className = 'bubble-name';
            name.textContent = author;
            bubble.appendChild(name);
        }

        if (msg.text) {
            const text = document.createElement('span');
            text.textContent = msg.text;
            bubble.appendChild(text);
        }

        const time = document.createElement('span');
        time.className = 'bubble-time';
        time.textContent = (msg.time || 'agora') + (classe === 'cliente' ? ' ✓✓' : '');
        bubble.appendChild(time);

        if (classe === 'cliente') {
            const seen = document.createElement('span');
            seen.className = 'chat-seen-status';
            seen.dataset.seenStatus = '1';
            seen.style.display = 'none';
            seen.textContent = 'Visualizado pelo suporte';
            bubble.appendChild(seen);
        }

        wrap.appendChild(bubble);

        if (Array.isArray(msg.attachments) && msg.attachments.length > 0) {
            const grid = document.createElement('div');
            grid.className = 'attachment-grid';
            grid.setAttribute('aria-label', 'Anexos da mensagem');
            grid.innerHTML = msg.attachments.map(renderAttachmentCard).join('');
            wrap.appendChild(grid);
        }

        row.appendChild(avatar);
        row.appendChild(wrap);
        chat.appendChild(row);
        chat.scrollTop = chat.scrollHeight;
    }


    function latestChatMessageId() {
        const rows = Array.from(document.querySelectorAll('#chatHistorico [data-message-id]'));
        return rows.reduce(function (max, row) {
            const id = Number(row.dataset.messageId || 0);
            return Number.isFinite(id) && id > max ? id : max;
        }, 0);
    }

    async function syncPublicMessagesWhenSocketOffline(reason = 'offline') {
        if (portalChatSocket && portalChatSocket.connected) {
            stopPublicOfflineSync();
            return;
        }
        if (!portalChatSocketConfig?.syncUrl || !window.fetch || portalOfflineSyncInFlight) return;

        portalOfflineSyncInFlight = true;
        const afterId = latestChatMessageId();
        const url = new URL(portalChatSocketConfig.syncUrl, window.location.origin);
        url.searchParams.set('after_id', String(afterId));
        if (portalSelectedAtendimentoId > 0) {
            url.searchParams.set('atendimento_id', String(portalSelectedAtendimentoId));
        }

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = await response.json().catch(function () { return null; });
            const messages = Array.isArray(data?.messages) ? data.messages : [];
            messages.forEach(function (message) {
                appendRealtimeChatMessage(message);
            });
        } catch (error) {
            // Fallback silencioso: evita poluir laravel.log/console enquanto o socket estiver offline.
        } finally {
            portalOfflineSyncInFlight = false;
        }
    }

    function startPublicOfflineSync(reason = 'socket_offline') {
        if (portalChatSocket && portalChatSocket.connected) return;
        if (window.__portalPublicOfflineSyncTimer) {
            portalOfflineSyncTimer = window.__portalPublicOfflineSyncTimer;
            return;
        }
        portalDebug('socket_public_offline_sync_enabled', { reason });
        portalOfflineSyncTimer = window.setInterval(function () {
            syncPublicMessagesWhenSocketOffline(reason);
        }, 10000);
        window.__portalPublicOfflineSyncTimer = portalOfflineSyncTimer;
        syncPublicMessagesWhenSocketOffline(reason);
    }

    function stopPublicOfflineSync() {
        const timer = portalOfflineSyncTimer || window.__portalPublicOfflineSyncTimer;
        if (!timer) return;
        window.clearInterval(timer);
        portalOfflineSyncTimer = null;
        window.__portalPublicOfflineSyncTimer = null;
        portalDebug('socket_public_offline_sync_disabled');
    }


    async function persistPublicSeen(messageId) {
        const id = Number(messageId || 0);
        if (!id || !portalChatSocketConfig?.seenUrl || !window.fetch) return;

        try {
            await fetch(portalChatSocketConfig.seenUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message_id: id }),
            });
        } catch (error) {
            portalDebug('socket_public_seen_persist_error', { message_id: id, erro: String(error && error.message ? error.message : error) });
        }
    }

    function lastEquipeMessageId() {
        const rows = Array.from(document.querySelectorAll('#chatHistorico .message-row.equipe[data-message-id]'));
        return rows.reduce(function (max, row) {
            const id = Number(row.dataset.messageId || 0);
            return id > max ? id : max;
        }, 0);
    }

    function connectPublicChatSocket() {
        if (!portalChatSocketConfig?.enabled || !portalChatSocketConfig?.url) {
            portalDebug('socket_public_disabled');
            return;
        }

        if (!window.io) {
            portalDebug('socket_public_client_missing', { url: portalChatSocketConfig.url, script_error: Boolean(window.__portalSocketIoScriptError), script_loaded: Boolean(window.__portalSocketIoScriptLoaded) });
            startPublicOfflineSync('socket_client_missing');
            window.clearTimeout(portalSocketRetryTimer);
            portalSocketRetryTimer = window.setTimeout(function () {
                portalDebug('socket_public_script_retry', { url: portalChatSocketConfig.url });
                connectPublicChatSocket();
            }, 2500);
            return;
        }

        if (portalChatSocket && (portalChatSocket.connected || portalChatSocket.active)) return;

        portalChatSocket = window.io(portalChatSocketConfig.url, {
            transports: ['websocket', 'polling'],
            withCredentials: true,
            auth: {
                empresaId: portalChatSocketConfig.empresaId,
                actor: portalChatSocketConfig.actor || 'cliente',
                token: portalChatSocketConfig.token || '',
                signature: portalChatSocketConfig.signature || '',
                room: portalChatSocketConfig.room || '',
            },
        });

        portalChatSocket.on('connect', function () {
            stopPublicOfflineSync();
            portalDebug('socket_public_connected', { socket_id: portalChatSocket.id });
            const ultimoEquipe = lastEquipeMessageId();
            if (ultimoEquipe > 0) {
                portalChatSocket.emit('chat:seen', { message_id: ultimoEquipe, room: portalChatSocketConfig.room || '', at: new Date().toISOString() });
                persistPublicSeen(ultimoEquipe);
            }
        });

        portalChatSocket.on('connect_error', function (error) {
            portalDebug('socket_public_connect_error', { erro: String(error && error.message ? error.message : error) });
            startPublicOfflineSync('socket_connect_error');
        });

        portalChatSocket.on('disconnect', function (reason) {
            portalDebug('socket_public_disconnect', { reason: String(reason || '') });
            startPublicOfflineSync('socket_disconnect');
        });

        portalChatSocket.on('chat:message:new', function (payload) {
            const msg = normalizeRealtimeMessage(payload);
            portalDebug('socket_public_message_received', { message_id: Number(msg.id || 0), socket_connected: Boolean(portalChatSocket && portalChatSocket.connected), socket_id: portalChatSocket?.id || null });
            const atendimentoIdPayload = Number(msg.atendimento_id || payload?.atendimento_id || 0);
            if (portalSelectedAtendimentoId > 0 && atendimentoIdPayload > 0 && atendimentoIdPayload !== portalSelectedAtendimentoId) {
                return;
            }
            appendRealtimeChatMessage(payload);
            setSupportTyping(false);
            if (msg.class === 'equipe' && Number(msg.id || 0) > 0) {
                portalChatSocket.emit('chat:seen', { message_id: Number(msg.id), room: portalChatSocketConfig.room || '', at: new Date().toISOString() });
                persistPublicSeen(Number(msg.id));
            }
        });

        portalChatSocket.on('chat:typing:start', function (payload) {
            if (payload?.actor === 'cliente') return;
            setSupportTyping(true, payload?.nome || 'Suporte');
            window.clearTimeout(window.__supportTypingTimer);
            window.__supportTypingTimer = window.setTimeout(function () { setSupportTyping(false); }, 8000);
        });

        portalChatSocket.on('chat:typing:stop', function (payload) {
            if (payload?.actor === 'cliente') return;
            setSupportTyping(false);
        });

        portalChatSocket.on('chat:seen', function (payload) {
            if (payload?.actor === 'cliente') return;
            updateSeenStatus(Number(payload?.message_id || 0));
        });
    }

    connectPublicChatSocket();


    document.querySelectorAll('[data-chat-form]').forEach(function (form) {
        const textarea = form.querySelector('.js-chat-message');
        textarea?.addEventListener('input', function () {
            announceClientTyping(form);
        });

        form.addEventListener('submit', async function (event) {
            if (clientChatSendingBusy) {
                event.preventDefault();
                return;
            }

            const message = form.querySelector('.js-chat-message')?.value.trim() || '';
            const files = form.querySelector('.js-chat-files')?.files || [];
            const submitStartedAt = performance.now();
            portalDebug('chat_submit', { tamanhoMensagem: message.length, quantidadeArquivos: files.length, modo: 'ajax', fase: 'listener' });

            if (message === '' && files.length === 0) {
                event.preventDefault();
                const feedbackMessage = 'Digite uma mensagem ou anexe ao menos um arquivo antes de enviar.';
                showClientToast(feedbackMessage, 'error');
                setInlineFeedback(form, feedbackMessage, 'error');
                form.dataset.invalid = '1';
                form.querySelector('.js-chat-message')?.focus();
                window.setTimeout(function () { delete form.dataset.invalid; }, 120);
                return;
            }

            if (!window.fetch || !window.FormData) {
                return;
            }

            event.preventDefault();

            const requestData = new FormData(form);
            requestData.set('_portal_ajax', '1');
            const optimisticRow = appendOptimisticChatMessage(form, message, files.length);
            resetChatComposer(form);
            setInlineFeedback(form, '', 'success');

            clientChatSendingBusy = true;
            form.classList.add('is-processing');
            const submitButton = form.querySelector('button[type="submit"]');

            try {
                portalDebug('chat_ajax_start', { tamanhoMensagem: message.length, quantidadeArquivos: files.length, fase: 'fetch_inicio' });
                const response = await fetch(form.action, {
                    method: (form.method || 'POST').toUpperCase(),
                    headers: {
                        'Accept': 'application/json, text/html;q=0.9, */*;q=0.8',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Portal-Ajax': '1'
                    },
                    body: requestData,
                    credentials: 'same-origin'
                });

                let responseData = null;

                try {
                    const contentType = response.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        responseData = await response.json();
                    }
                } catch (parseError) {
                    responseData = null;
                }

                portalDebug('chat_ajax_response', {
                    status: response.status,
                    duration_ms: Math.round(performance.now() - submitStartedAt),
                    message_id: Number(responseData?.chat_message?.id || responseData?.message_id || 0),
                    fase: response.ok ? 'http_ok' : 'http_not_ok'
                });

                if (!response.ok || (responseData && responseData.ok === false)) {
                    let serverMessage = responseData?.message || responseData?.errors?.mensagem?.[0] || ('HTTP ' + response.status);
                    if (response.status === 429 || String(serverMessage).toLowerCase().includes('too many attempts')) {
                        serverMessage = 'O chat recebeu muitas atualizações ao mesmo tempo. Aguarde alguns segundos e tente enviar novamente.';
                    }
                    throw new Error(serverMessage);
                }

                markOptimisticMessage(optimisticRow, 'sent', 'agora');
                if (responseData?.chat_message) {
                    applyServerMessageToOptimistic(optimisticRow, responseData.chat_message);
                }
                if (portalChatSocket && portalChatSocket.connected && responseData?.chat_message) {
                    portalDebug('chat_socket_emit_start', { message_id: Number(responseData.chat_message.id || 0), socket_connected: true, socket_id: portalChatSocket.id });
                    responseData.chat_message.room = responseData.chat_message.room || portalChatSocketConfig.room || '';
                    responseData.chat_message.actor = responseData.chat_message.actor || portalChatSocketConfig.actor || 'cliente';
                    responseData.chat_message.class = responseData.chat_message.class || 'cliente';
                    portalChatSocket.emit('chat:message:new', responseData.chat_message, function (ack) {
                        portalDebug('chat_socket_emit_ack', { message_id: Number(responseData.chat_message.id || 0), socket_connected: Boolean(portalChatSocket && portalChatSocket.connected), socket_id: portalChatSocket?.id || null, ack: ack || null });
                    });
                    portalChatSocket.emit('chat:typing:stop', { actor: portalChatSocketConfig.actor || 'cliente', room: portalChatSocketConfig.room || '' });
                } else if (responseData?.chat_message) {
                    portalDebug('chat_socket_emit_offline', { message_id: Number(responseData.chat_message.id || 0), socket_connected: Boolean(portalChatSocket && portalChatSocket.connected), socket_id: portalChatSocket?.id || null });
                    startPublicOfflineSync('emit_offline_after_send');
                }
                setInlineFeedback(form, '', 'success');
                portalDebug('chat_ajax_success', { status: response.status, duration_ms: Math.round(performance.now() - submitStartedAt), message_id: Number(responseData?.chat_message?.id || responseData?.message_id || 0) });
            } catch (error) {
                markOptimisticMessage(optimisticRow, 'failed', 'não enviado');
                portalDebug('chat_ajax_error', { erro: String(error && error.message ? error.message : error), duration_ms: Math.round(performance.now() - submitStartedAt) });
                const errorMessage = error?.message && !String(error.message).startsWith('HTTP')
                    ? error.message
                    : 'Não foi possível enviar agora. Sua mensagem ficou na tela; tente enviar novamente.';
                setInlineFeedback(form, errorMessage, 'error');
                showClientToast(errorMessage, 'error');
                portalDebug('chat_ajax_error', { message: error?.message || String(error) });
            } finally {
                clientChatSendingBusy = false;
                form.classList.remove('is-processing');
            }
        });
    });

    document.querySelectorAll('.mini-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const textarea = form.querySelector('textarea[name="resposta"]');
            const response = textarea?.value.trim() || '';
            if (response.length < 3) {
                event.preventDefault();
                const feedbackMessage = 'Escreva uma resposta para esta pendência antes de enviar.';
                showClientToast(feedbackMessage, 'error');
                setInlineFeedback(form, feedbackMessage, 'error');
                form.dataset.invalid = '1';
                textarea?.focus();
                window.setTimeout(function () { delete form.dataset.invalid; }, 120);
                return;
            }
            setInlineFeedback(form, 'Enviando resposta da pendência...', 'success');
        });
    });

    document.querySelectorAll('textarea').forEach(function (textarea) {
        textarea.addEventListener('input', function () {
            const form = textarea.closest('form');
            if (!form) return;
            form.classList.remove('is-invalid');
            textarea.classList.remove('is-invalid');
            const feedback = form.querySelector('[data-form-feedback]');
            if (feedback && feedback.style.display !== 'none') {
                feedback.textContent = '';
                feedback.style.display = 'none';
            }
        });
    });

    document.querySelectorAll('.js-feedback-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (form.dataset.invalid === '1' || form.matches('[data-chat-form]')) return;
            window.setTimeout(function () {
                form.classList.add('is-processing');
                const pendingCard = form.closest('[data-pending-card]');
                pendingCard?.classList.add('is-processing');
                form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                    setButtonProcessing(button, form.dataset.processing || 'Enviando...');
                });
            }, 0);
        });
    });


    document.querySelectorAll('[data-mobile-summary-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const banner = button.closest('.action-banner');
            if (!banner) return;
            const isOpen = !banner.classList.contains('is-mobile-open');
            banner.classList.toggle('is-mobile-open', isOpen);
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            button.innerHTML = isOpen ? 'Ocultar orientação <span>⌃</span>' : 'Ver orientação rápida <span>⌄</span>';
        });
    });

    window.addEventListener('resize', function () {
        const active = document.querySelector('.portal-cluster-button.is-active, .side-link.is-active, .side-link.active');
        setPortalCluster(active?.dataset.portalCluster || 'chat');
    });

    <?php if(session('success')): ?>
        showClientToast(<?php echo json_encode(session('success'), 15, 512) ?>, 'success');
    <?php endif; ?>
    <?php if($errors->any()): ?>
        showClientToast('Revise os campos destacados para continuar.', 'error');
    <?php endif; ?>
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\portal\cliente\show.blade.php ENDPATH**/ ?>