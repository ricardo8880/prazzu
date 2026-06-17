        <div class="at-modal" x-show="detalhe" x-cloak>
            <div class="at-modal-card wide at-ticket-modal-shell" @click.outside="$wire.fecharDetalhe()">
                @if($selectedAtendimento)
                    @php
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
                    @endphp

                    <header class="at-ticket-modal-head">
                        <div class="at-ticket-modal-title">
                            <span class="at-ticket-modal-title-icon"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i></span>
                            <div>
                                <h2>
                                    Atendimento #{{ $selectedAtendimento['id'] }} - {{ $selectedAtendimento['titulo'] }}
                                    <span class="at-badge at-badge-soft {{ $selectedAtendimento['status_tone'] ?? 'info' }}">{{ $selectedAtendimento['status_label'] ?? 'Aberto' }}</span>
                                    <span class="at-badge at-badge-soft {{ $selectedAtendimento['prioridade_tone'] ?? 'info' }}">{{ $selectedAtendimento['prioridade_label'] ?? 'Média' }}</span>
                                </h2>
                                <p>Criado em {{ $selectedAtendimento['created_at'] ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="at-ticket-modal-actions">
                            @if(! $statusFechado)
                                <details class="at-ticket-control">
                                    <summary><i class="bi bi-record-circle" aria-hidden="true"></i> Alterar status <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                    <div class="at-ticket-control-panel">
                                        @if($statusAtual !== \App\Support\AtendimentoStatus::EM_ANDAMENTO)
                                            <button type="button" wire:click="mudarStatusRapido({{ $selectedAtendimento['id'] }}, 'em_andamento')"><span class="at-dot primary"></span> Em andamento</button>
                                        @endif
                                        @if($statusAtual !== \App\Support\AtendimentoStatus::AGUARDANDO_CLIENTE)
                                            <button type="button" wire:click="mudarStatusRapido({{ $selectedAtendimento['id'] }}, 'aguardando_cliente')"><span class="at-dot warning"></span> Aguardando cliente</button>
                                        @endif
                                        @if($statusAtual !== \App\Support\AtendimentoStatus::RESOLVIDO)
                                            <button type="button" wire:click="mudarStatusRapido({{ $selectedAtendimento['id'] }}, 'resolvido')"><span class="at-dot success"></span> Resolvido</button>
                                        @endif
                                    </div>
                                </details>

                                <details class="at-ticket-control">
                                    <summary><i class="bi bi-person-plus" aria-hidden="true"></i> Atribuir a... <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                    <div class="at-ticket-control-panel">
                                        @foreach($responsaveis as $resp)
                                            <button type="button" wire:click="atribuirResponsavelDetalhe({{ $resp['id'] }})"><i class="bi bi-person" aria-hidden="true"></i> {{ $resp['nome'] }}</button>
                                        @endforeach
                                        @if(empty($responsaveis))
                                            <button type="button" disabled><i class="bi bi-info-circle" aria-hidden="true"></i> Nenhum responsável disponível</button>
                                        @endif
                                    </div>
                                </details>
                            @else
                                <button type="button" class="at-ticket-header-action" wire:click="reabrirAtendimento({{ $selectedAtendimento['id'] }})"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Reabrir</button>
                            @endif

                            <details class="at-ticket-control at-ticket-more-control">
                                <summary class="at-ticket-icon-summary" title="Mais opções"><i class="bi bi-three-dots" aria-hidden="true"></i></summary>
                                <div class="at-ticket-control-panel at-ticket-more-panel">
                                    <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText('#{{ $selectedAtendimento['id'] }}')"><i class="bi bi-clipboard" aria-hidden="true"></i> Copiar protocolo</button>
                                    @if(! $statusFechado && ! $selectedAtendimento['responsavel_id'])
                                        <button type="button" wire:click="assumirAtendimento({{ $selectedAtendimento['id'] }})"><i class="bi bi-person-check" aria-hidden="true"></i> Assumir atendimento</button>
                                    @endif
                                    @if(! $statusFechado)
                                        <button type="button" wire:click="criarTarefaDoAtendimento"><i class="bi bi-check2-square" aria-hidden="true"></i> Criar tarefa</button>
                                        <button type="button" wire:click="criarPendenciaDoAtendimento"><i class="bi bi-clipboard-plus" aria-hidden="true"></i> Criar pendência</button>
                                        <button type="button" wire:click="solicitarDocumentoDoAtendimento"><i class="bi bi-file-earmark-plus" aria-hidden="true"></i> Solicitar documento</button>
                                    @endif
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
                                    <div class="at-ticket-detail-row"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><span>Protocolo</span><strong>#{{ $selectedAtendimento['id'] }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-geo-alt" aria-hidden="true"></i><span>Origem</span><strong>{{ $selectedAtendimento['origem_label'] ?? ucfirst($selectedAtendimento['origem'] ?? 'Manual') }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-list-ul" aria-hidden="true"></i><span>Categoria</span><strong>{{ $selectedAtendimento['canal_label'] ?? ucfirst($selectedAtendimento['canal'] ?? 'Interno') }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-envelope" aria-hidden="true"></i><span>Assunto</span><strong>{{ $selectedAtendimento['titulo'] ?? '-' }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-stars" aria-hidden="true"></i><span>Prioridade</span><strong>{{ $selectedAtendimento['prioridade_label'] ?? '-' }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-arrow-repeat" aria-hidden="true"></i><span>Status</span><strong>{{ $selectedAtendimento['status_label'] ?? '-' }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-check2-square" aria-hidden="true"></i><span>Tarefas</span><strong>{{ $selectedAtendimento['tarefas_count'] ?? 0 }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-hourglass-split" aria-hidden="true"></i><span>Aguardando</span><strong>{{ $selectedAtendimento['aguardando_label'] ?? '-' }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-stopwatch" aria-hidden="true"></i><span>Tempo aguardando</span><strong>{{ $selectedAtendimento['tempo_aguardando_detalhe'] ?? '-' }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-clock" aria-hidden="true"></i><span>SLA</span><strong class="{{ !empty($selectedAtendimento['sla_vencido']) ? 'at-ticket-sla-danger' : 'at-ticket-sla-ok' }}">{{ !empty($selectedAtendimento['sla_vencido']) ? $selectedAtendimento['sla_texto'] : 'Dentro do prazo' }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-calendar2" aria-hidden="true"></i><span>Criado em</span><strong>{{ $selectedAtendimento['created_at'] ?? '-' }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-clock-history" aria-hidden="true"></i><span>Atualizado em</span><strong>{{ $selectedAtendimento['updated_at'] ?? '-' }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-lightning-charge" aria-hidden="true"></i><span>Primeira resposta</span><strong>{{ $selectedAtendimento['primeira_resposta_em'] ?? '-' }}</strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-check2-square" aria-hidden="true"></i><span>Resolução</span><strong>{{ $selectedAtendimento['resolvido_em'] ?? '-' }}</strong></div>
                                </div>
                            </section>

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header"><span>Histórico operacional</span><small>{{ $eventosOperacionais->count() }}</small></header>
                                <div class="at-ticket-operational-timeline">
                                    @forelse($eventosOperacionais->take(8) as $evento)
                                        <div class="at-ticket-op-event tone-{{ $evento['operacional_tone'] ?? 'neutral' }}">
                                            <span class="at-ticket-op-icon"><i class="bi {{ $evento['operacional_icon'] ?? 'bi-activity' }}" aria-hidden="true"></i></span>
                                            <div class="at-ticket-op-body">
                                                <strong>{{ $evento['operacional_titulo'] ?? ($evento['tipo_label'] ?? 'Registro') }}</strong>
                                                <small>{{ $evento['created_at'] ?? '-' }} · {{ $evento['operacional_actor'] ?? ($evento['usuario'] ?? 'Sistema') }}</small>
                                                @if(trim((string) ($evento['operacional_detalhe'] ?? '')) !== '')
                                                    <p>{{ $evento['operacional_detalhe'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="at-empty">Nenhum evento operacional registrado.</div>
                                    @endforelse
                                </div>
                            </section>

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header"><span>Anexos</span><small>{{ $anexosDoAtendimento->count() }}</small></header>
                                <div class="at-ticket-attachments">
                                    @forelse($anexosDoAtendimento->take(4) as $anexo)
                                        <div class="at-ticket-file-row">
                                            <span><i class="bi bi-file-earmark" aria-hidden="true"></i></span>
                                            <div>
                                                <strong>{{ $anexo['nome_original'] ?? 'Anexo' }}</strong>
                                                <small>{{ $anexo['tamanho_label'] ?? 'Arquivo' }} · {{ $anexo['log_data'] ?? '-' }}</small>
                                            </div>
                                            @if(!empty($anexo['log_id']) && !empty($anexo['hash']))
                                                <button type="button" class="at-ticket-download-btn" wire:click="baixarAnexoHistorico({{ (int) $anexo['log_id'] }}, '{{ $anexo['hash'] }}')" title="Baixar anexo"><i class="bi bi-download" aria-hidden="true"></i></button>
                                            @else
                                                <span></span>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="at-empty">Nenhum anexo neste atendimento.</div>
                                    @endforelse
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
                                    @if($primeiroLogCliente === null && trim((string) ($selectedAtendimento['descricao'] ?? '')) !== '')
                                        <article class="at-ticket-message client">
                                            <span class="at-ticket-chat-avatar client">{{ $clienteInicial }}</span>
                                            <div class="at-ticket-message-card">
                                                <strong>{{ $selectedAtendimento['empresa_nome'] ?? 'Cliente' }} <span>(Cliente)</span></strong>
                                                <p>{{ $selectedAtendimento['descricao'] }}</p>
                                            </div>
                                            <span class="at-ticket-message-time">{{ $selectedAtendimento['created_at'] ?? '-' }}</span>
                                        </article>
                                    @endif

                                    @forelse($timeline as $log)
                                        @php
                                            $origemLog = $log['origem'] ?? 'sistema';
                                            $isCliente = in_array($origemLog, ['cliente', 'portal', 'publico'], true);
                                            $isSuporte = in_array($origemLog, ['suporte', 'interno'], true);
                                            $tipoCard = $isCliente ? 'client' : ($isSuporte ? 'support' : 'system');
                                            $inicialLog = $isCliente ? $clienteInicial : ($isSuporte ? $responsavelInicial : 'S');
                                        @endphp
                                        <article wire:key="ticket-chat-{{ $log['id'] ?? md5(($log['created_at'] ?? '') . ($log['mensagem'] ?? '')) }}" class="at-ticket-message {{ $tipoCard }}">
                                            <span class="at-ticket-chat-avatar {{ $tipoCard }}">{{ $inicialLog }}</span>
                                            <div class="at-ticket-message-card">
                                                <strong>{{ $log['usuario'] ?? ($isCliente ? ($selectedAtendimento['empresa_nome'] ?? 'Cliente') : 'Suporte') }} <span>{{ $isCliente ? '(Cliente)' : ($isSuporte ? '(Suporte)' : '(Sistema)') }}</span></strong>
                                                @if(trim((string) ($log['mensagem'] ?? '')) !== '')
                                                    <p>{{ $log['mensagem'] }}</p>
                                                @else
                                                    <p>{{ $log['tipo_label'] ?? 'Registro do sistema' }}</p>
                                                @endif
                                                @if(! empty($log['anexos']))
                                                    <div class="at-ticket-message-files">
                                                        @foreach($log['anexos'] as $anexo)
                                                            <button type="button" class="at-ticket-attachment-btn" wire:click="baixarAnexoHistorico({{ $log['id'] }}, '{{ $anexo['hash'] }}')">
                                                                <i class="bi bi-file-earmark" aria-hidden="true"></i> {{ $anexo['nome_original'] }} <i class="bi bi-download" aria-hidden="true"></i>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="at-ticket-message-time">{{ $log['created_at'] ?? '-' }} <i class="bi bi-check2-all" aria-hidden="true"></i></span>
                                        </article>
                                    @empty
                                        <div class="at-empty">Nenhuma interação registrada. A descrição inicial aparece como contexto do atendimento.</div>
                                    @endforelse
                                </div>
                            </div>

                            @if(! $statusFechado && $temCanalResposta)
                                <section class="at-ticket-reply-box">
                                    <textarea wire:model="novaRespostaCliente" placeholder="Digite sua resposta..."></textarea>
                                    <div class="at-ticket-reply-actions">
                                        <div class="at-ticket-reply-left">
                                            <label class="at-ticket-upload-control" title="Anexar arquivo">
                                                <i class="bi bi-paperclip" aria-hidden="true"></i>
                                                <input type="file" wire:model="portalAnexos" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,image/jpeg,image/png,image/webp,application/pdf">
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
                                            <button type="button" class="at-ticket-send-btn" wire:click="responderCliente" wire:loading.attr="disabled" wire:target="responderCliente,portalAnexos">
                                                <i class="bi bi-send" aria-hidden="true"></i>
                                                <span wire:loading.remove wire:target="responderCliente,portalAnexos">Enviar resposta</span>
                                                <span wire:loading wire:target="responderCliente,portalAnexos">Enviando...</span>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="at-ticket-reply-hint" wire:loading.remove wire:target="portalAnexos">Pressione o botão para enviar ao portal do cliente</small>
                                    <small class="at-ticket-reply-hint" wire:loading wire:target="portalAnexos">Carregando anexo...</small>
                                    @error('novaRespostaCliente') <small class="at-ticket-reply-hint danger">{{ $message }}</small> @enderror
                                    @error('portalAnexos') <small class="at-ticket-reply-hint danger">{{ $message }}</small> @enderror
                                    @error('portalAnexos.*') <small class="at-ticket-reply-hint danger">{{ $message }}</small> @enderror
                                </section>
                            @elseif(! $statusFechado && ! $temCanalResposta)
                                <div class="at-ticket-finalized danger"><strong>Cliente sem canal de resposta.</strong><br>Cadastre e-mail ou vínculo de portal antes de enviar mensagem.</div>
                            @else
                                <div class="at-ticket-finalized">Atendimento finalizado. Reabra para responder ao cliente.</div>
                            @endif
                        </main>

                        <aside class="at-ticket-right">
                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Cliente</header>
                                <div class="at-ticket-person-card">
                                    <span class="at-ticket-person-avatar">{{ $clienteInicial }}</span>
                                    <div>
                                        <strong>{{ $selectedAtendimento['empresa_nome'] ?? 'Cliente' }}</strong>
                                        <small>{{ $clienteEmail }}</small>
                                    </div>
                                </div>
                                <div class="at-ticket-mini-info">
                                    <span>Empresa ID</span><strong>#{{ $selectedAtendimento['empresa_id'] ?? '-' }}</strong>
                                    @if(!empty($selectedAtendimento['crm_cliente_id']))
                                        <span>Cliente CRM</span><strong>#{{ $selectedAtendimento['crm_cliente_id'] }}</strong>
                                    @endif
                                </div>
                            </section>

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Responsável</header>
                                <div class="at-ticket-person-card">
                                    <span class="at-ticket-person-avatar support">{{ $responsavelInicial }}</span>
                                    <div>
                                        <strong>{{ $responsavelNome }}</strong>
                                        <small>{{ $selectedAtendimento['responsavel_email'] ?? 'Sem e-mail' }}</small>
                                    </div>
                                </div>
                                @if(! $selectedAtendimento['responsavel_id'])
                                    @if(! $statusFechado)
                                        <button type="button" class="at-ticket-side-btn" wire:click="assumirAtendimento({{ $selectedAtendimento['id'] }})"><i class="bi bi-person-check" aria-hidden="true"></i> Assumir atendimento</button>
                                    @else
                                        <div class="at-ticket-hint">Atendimento fechado sem responsável.</div>
                                    @endif
                                @else
                                    @if(! $statusFechado)
                                        <div class="at-ticket-hint">Para trocar o responsável, use o menu <strong>Atribuir a...</strong> no topo.</div>
                                    @else
                                        <div class="at-ticket-hint">Atendimento fechado. Reabra para alterar o responsável.</div>
                                    @endif
                                @endif
                            </section>

                            <section class="at-ticket-panel at-ticket-next-panel tone-{{ $proximaAcao['tone'] }}">
                                <header class="at-ticket-panel-header">Próxima ação recomendada</header>
                                <div class="at-ticket-next-action">
                                    <span><i class="bi {{ $proximaAcao['icon'] }}" aria-hidden="true"></i></span>
                                    <div>
                                        <strong>{{ $proximaAcao['titulo'] }}</strong>
                                        <p>{{ $proximaAcao['texto'] }}</p>
                                    </div>
                                </div>
                            </section>

                            @if(! $statusFechado)
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
                            @endif

                            @if(! $statusFechado)
                                <section class="at-ticket-panel">
                                    <header class="at-ticket-panel-header">Ações rápidas</header>
                                    <div class="at-ticket-quick-list">
                                        @if(! $selectedAtendimento['responsavel_id'])
                                            <button type="button" class="at-ticket-quick-btn" wire:click="assumirAtendimento({{ $selectedAtendimento['id'] }})"><i class="bi bi-person-check" aria-hidden="true"></i> Assumir atendimento</button>
                                        @endif
                                        @if($statusAtual !== \App\Support\AtendimentoStatus::EM_ANDAMENTO)
                                            <button type="button" class="at-ticket-quick-btn" wire:click="mudarStatusRapido({{ $selectedAtendimento['id'] }}, 'em_andamento')"><span class="at-dot primary"></span> Marcar como em andamento</button>
                                        @endif
                                        @if($statusAtual !== \App\Support\AtendimentoStatus::AGUARDANDO_CLIENTE)
                                            <button type="button" class="at-ticket-quick-btn" wire:click="mudarStatusRapido({{ $selectedAtendimento['id'] }}, 'aguardando_cliente')"><span class="at-dot warning"></span> Marcar como aguardando cliente</button>
                                        @endif
                                        @if($statusAtual !== \App\Support\AtendimentoStatus::RESOLVIDO)
                                            <button type="button" class="at-ticket-quick-btn" wire:click="mudarStatusRapido({{ $selectedAtendimento['id'] }}, 'resolvido')"><span class="at-dot success"></span> Marcar como resolvido</button>
                                        @endif
                                        <button type="button" class="at-ticket-quick-btn" wire:click="criarTarefaDoAtendimento" wire:loading.attr="disabled" wire:target="criarTarefaDoAtendimento"><i class="bi bi-check2-square" aria-hidden="true"></i> Criar tarefa do ticket</button>
                                        <button type="button" class="at-ticket-quick-btn" wire:click="criarPendenciaDoAtendimento" wire:loading.attr="disabled" wire:target="criarPendenciaDoAtendimento"><i class="bi bi-clipboard-plus" aria-hidden="true"></i> Criar pendência interna</button>
                                        <button type="button" class="at-ticket-quick-btn" wire:click="solicitarDocumentoDoAtendimento" wire:loading.attr="disabled" wire:target="solicitarDocumentoDoAtendimento"><i class="bi bi-file-earmark-plus" aria-hidden="true"></i> Solicitar documento no portal</button>
                                    </div>
                                </section>
                            @endif

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Histórico de status</header>
                                <div class="at-ticket-status-list">
                                    @foreach($statusOptions as $statusKey => $statusMeta)
                                        <div class="at-ticket-status-item">
                                            <span class="at-ticket-status-circle {{ ($selectedAtendimento['status'] ?? '') === $statusKey ? ($statusMeta['tone'] ?? 'info') : '' }}"></span>
                                            <span>{{ $statusMeta['label'] }}</span>
                                            <small>{{ ($selectedAtendimento['status'] ?? '') === $statusKey ? ($selectedAtendimento['updated_at'] ?? '-') : '-' }}</small>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        </aside>
                    </div>
                @else
                    <header class="at-ticket-modal-head">
                        <div class="at-ticket-modal-title">
                            <span class="at-ticket-modal-title-icon"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i></span>
                            <div><h2>Carregando atendimento...</h2><p>Aguarde enquanto os dados são preparados.</p></div>
                        </div>
                        <button type="button" class="at-ticket-icon-btn" title="Fechar" wire:click="fecharDetalhe"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                    </header>
                @endif
            </div>
        </div>
