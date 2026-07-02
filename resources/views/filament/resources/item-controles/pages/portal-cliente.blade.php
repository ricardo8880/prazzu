<x-filament-panels::page>
    @php
        $percent = (int) ($progress['percent'] ?? 0);
        $empresaNome = $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? 'Portal do Cliente';
    @endphp
<div class="portal-wrap">
        <section class="portal-hero">
            <h1>{{ $empresaNome }}</h1>
            <p>
                Acompanhe o andamento do projeto, revise entregas, consulte documentos, veja prazos,
                abra solicitações de suporte e converse com a equipe em um só lugar.
            </p>
        </section>

        <section class="portal-card">
            <header>
                <div>
                    <h2>Progresso do projeto</h2>
                    <p>Battery chart calculado com base nas tarefas reais vinculadas à empresa.</p>
                </div>
                <span class="portal-badge ok">{{ $progress['done'] ?? 0 }} concluídas</span>
            </header>

            <div class="portal-battery-wrap">
                <div>
                    <div class="portal-battery">
                        <i style="width: {{ $percent }}%"></i>
                    </div>

                    <p>
                        {{ $progress['done'] ?? 0 }} concluída(s),
                        {{ $progress['pending'] ?? 0 }} pendente(s),
                        {{ $progress['review'] ?? 0 }} em revisão/aprovação.
                    </p>
                </div>

                <div class="portal-percent">{{ $percent }}%</div>
            </div>
        </section>

        
        <section class="portal-grid three">
            <article class="portal-card">
                <header><div><h2>Próxima entrega</h2><p>O próximo prazo importante do projeto.</p></div></header>
                @if($nextDelivery)
                    <div class="portal-note">
                        <strong>{{ $nextDelivery['titulo'] ?? 'Entrega' }}</strong>
                        <small>{{ !empty($nextDelivery['data_vencimento']) ? \Carbon\Carbon::parse($nextDelivery['data_vencimento'])->format('d/m/Y') : '-' }}</small>
                    </div>
                @else
                    <div class="portal-empty">Nenhuma próxima entrega definida.</div>
                @endif
            </article>
            <article class="portal-card">
                <header><div><h2>Timeline do projeto</h2><p>Etapas principais do projeto.</p></div></header>
                @foreach($timeline as $step)
                    <div class="portal-row"><strong>{{ $step['label'] }}</strong><span class="portal-badge {{ $step['done'] ? 'ok' : '' }}">{{ $step['done'] ? 'Concluído' : 'Pendente' }}</span></div>
                @endforeach
            </article>
            <article class="portal-card">
                <header><div><h2>Histórico de aprovações</h2><p>Itens já concluídos/aprovados.</p></div></header>
                @forelse($approvalHistory as $approval)
                    <div class="portal-note"><strong>{{ $approval['titulo'] ?? 'Item' }}</strong></div>
                @empty
                    <div class="portal-empty">Nenhuma aprovação registrada.</div>
                @endforelse
            </article>
        </section>

        <section class="portal-grid two">
            <article class="portal-card">
                <header>
                    <div>
                        <h2>Pronto para revisão / aprovação</h2>
                        <p>O cliente vê apenas itens liberados para ele, sem tarefas internas da equipe.</p>
                    </div>
                </header>

                <div class="portal-list">
                    @forelse ($visibleItems as $item)
                        <div class="portal-row">
                            <div>
                                <strong>{{ $item['titulo'] ?? 'Item sem título' }}</strong>
                                <span>{{ $item['status_label'] ?? 'Sem status' }}</span>
                            </div>

                            <span class="portal-badge">{{ $item['progress'] ?? 0 }}%</span>
                        </div>
                    @empty
                        <div class="portal-empty">
                            Nenhum item liberado para revisão ou aprovação no momento.
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="portal-card">
                <header>
                    <div>
                        <h2>Calendário de entregas</h2>
                        <p>Datas reais de vencimento das entregas vinculadas ao cliente.</p>
                    </div>
                </header>

                <div class="portal-list">
                    @forelse ($calendar as $item)
                        <div class="portal-row">
                            <div>
                                <strong>{{ $item['titulo'] ?? 'Entrega' }}</strong>
                                <span>{{ $item['status_label'] ?? 'Sem status' }}</span>
                            </div>

                            <span class="portal-badge {{ ($item['is_late'] ?? false) ? 'danger' : '' }}">
                                {{ ! empty($item['data_vencimento']) ? \Carbon\Carbon::parse($item['data_vencimento'])->format('d/m/Y') : '-' }}
                            </span>
                        </div>
                    @empty
                        <div class="portal-empty">
                            Nenhuma entrega com prazo cadastrado.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="portal-grid three">
            <article class="portal-card">
                <header>
                    <div>
                        <h2>Wiki / documentos</h2>
                        <p>Regras do contrato, manuais, arquivos e links úteis.</p>
                    </div>
                </header>

                <div class="portal-list">
                    @forelse ($documents as $doc)
                        <div class="portal-note">
                            <strong>{{ $doc['titulo'] }}</strong>
                            <small>{{ strtoupper($doc['tipo']) }}</small>

                            @if (! empty($doc['conteudo']))
                                <p>{{ \Illuminate\Support\Str::limit(strip_tags($doc['conteudo']), 140) }}</p>
                            @endif

                            @if (! empty($doc['url']))
                                <a href="{{ $doc['url'] }}" target="_blank" rel="noopener noreferrer">Abrir link</a>
                            @endif
                        </div>
                    @empty
                        <div class="portal-empty">
                            Nenhum documento visível para o cliente.
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="portal-card">
                <header>
                    <div>
                        <h2>Atas de reunião</h2>
                        <p>Histórico das decisões tomadas em calls e alinhamentos.</p>
                    </div>
                </header>

                <div class="portal-list">
                    @forelse ($meetingNotes as $note)
                        <div class="portal-note">
                            <strong>{{ $note['titulo'] }}</strong>

                            @if (! empty($note['created_at']))
                                <small>{{ \Carbon\Carbon::parse($note['created_at'])->format('d/m/Y H:i') }}</small>
                            @endif

                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($note['conteudo'] ?? ''), 150) }}</p>
                        </div>
                    @empty
                        <div class="portal-empty">
                            Nenhuma ata de reunião publicada.
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="portal-card">
                <header>
                    <div>
                        <h2>Solicitações recentes</h2>
                        <p>Pedidos enviados pelo cliente e acompanhados pela equipe.</p>
                    </div>
                </header>

                <div class="portal-list">
                    @forelse ($supportQueue as $solicitacao)
                        <div class="portal-row">
                            <div>
                                <strong>{{ $solicitacao['titulo'] }}</strong>
                                <span>{{ ucfirst(str_replace('_', ' ', $solicitacao['prioridade'])) }}</span>
                            </div>

                            <span class="portal-badge">
                                {{ ucfirst(str_replace('_', ' ', $solicitacao['status'])) }}
                            </span>
                        </div>
                    @empty
                        <div class="portal-empty">
                            Nenhuma solicitação aberta ainda.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="portal-grid two">
            <article class="portal-card">
                <header>
                    <div>
                        <h2>Formulário de suporte / solicitação</h2>
                        <p>O pedido cai direto na fila de trabalho da equipe.</p>
                    </div>
                </header>

                <form wire:submit.prevent="criarSolicitacao" class="portal-form">
                    <div>
                        <input
                            type="text"
                            wire:model.defer="solicitacaoTitulo"
                            class="portal-input"
                            placeholder="Título da solicitação"
                        >
                        @error('solicitacaoTitulo')
                        <small style="color: rgb(220, 38, 38);">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <select wire:model.defer="solicitacaoPrioridade" class="portal-select">
                            @foreach (($supportForm['prioridades'] ?? []) as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('solicitacaoPrioridade')
                        <small style="color: rgb(220, 38, 38);">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <textarea
                            wire:model.defer="solicitacaoDescricao"
                            class="portal-textarea"
                            placeholder="Descreva o pedido de alteração, dúvida ou suporte..."
                        ></textarea>
                        @error('solicitacaoDescricao')
                        <small style="color: rgb(220, 38, 38);">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit" class="portal-btn" wire:loading.attr="disabled">
                        Enviar solicitação
                    </button>
                </form>
            </article>

            <article class="portal-card">
                <header>
                    <div>
                        <h2>Chat do projeto</h2>
                        <p>Mensagens centralizadas no portal, evitando conversas perdidas no WhatsApp.</p>
                    </div>
                </header>

                <div class="portal-list" style="margin-bottom: 1rem;">
                    @forelse ($chat as $message)
                        <div class="portal-note">
                            <strong>{{ $message['nome'] ?? 'Cliente' }}</strong>

                            @if (! empty($message['created_at']))
                                <small>{{ \Carbon\Carbon::parse($message['created_at'])->format('d/m/Y H:i') }}</small>
                            @endif

                            <p>{{ $message['mensagem'] }}</p>
                        </div>
                    @empty
                        <div class="portal-empty">
                            Nenhuma mensagem enviada ainda.
                        </div>
                    @endforelse
                </div>

                <form wire:submit.prevent="enviarMensagem" class="portal-form">
                    <textarea
                        wire:model.defer="chatMensagem"
                        class="portal-textarea"
                        placeholder="Escreva uma mensagem para a equipe..."
                    ></textarea>

                    @error('chatMensagem')
                    <small style="color: rgb(220, 38, 38);">{{ $message }}</small>
                    @enderror

                    <button type="submit" class="portal-btn" wire:loading.attr="disabled">
                        Enviar mensagem
                    </button>
                </form>
            </article>
        </section>
    </div>
</x-filament-panels::page>
