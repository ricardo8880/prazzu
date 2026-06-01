<x-filament-panels::page>
    @php
        $percent = (int) ($progress['percent'] ?? 0);
        $empresaNome = $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? 'Portal do Cliente';
    @endphp

    <style>
        .portal-wrap {
            display: grid;
            gap: 1.25rem;
        }

        .portal-hero {
            border-radius: 1.5rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(15, 23, 42, .96), rgba(30, 64, 175, .90));
            color: white;
            box-shadow: 0 24px 50px rgba(15, 23, 42, .18);
            overflow: hidden;
            position: relative;
        }

        .portal-hero:after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            right: -90px;
            top: -110px;
            background: rgba(255, 255, 255, .12);
            border-radius: 999px;
        }

        .portal-hero h1 {
            font-size: 1.65rem;
            font-weight: 800;
            margin: 0;
        }

        .portal-hero p {
            margin-top: .35rem;
            color: rgba(255, 255, 255, .82);
            max-width: 740px;
        }

        .portal-grid {
            display: grid;
            gap: 1rem;
        }

        .portal-grid.two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .portal-grid.three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .portal-card {
            border-radius: 1.25rem;
            background: white;
            border: 1px solid rgba(148, 163, 184, .25);
            padding: 1.15rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .06);
        }

        .dark .portal-card {
            background: rgba(15, 23, 42, .72);
            border-color: rgba(148, 163, 184, .18);
        }

        .portal-card header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .portal-card h2 {
            font-size: 1rem;
            font-weight: 800;
            margin: 0;
        }

        .portal-card p {
            color: rgb(100, 116, 139);
            font-size: .875rem;
            margin-top: .25rem;
        }

        .dark .portal-card p {
            color: rgb(203, 213, 225);
        }

        .portal-battery-wrap {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: center;
        }

        .portal-battery {
            height: 2.1rem;
            border-radius: 999px;
            border: 2px solid rgba(15, 23, 42, .16);
            padding: .22rem;
            background: rgba(241, 245, 249, .95);
            position: relative;
        }

        .portal-battery:after {
            content: "";
            width: .45rem;
            height: 1rem;
            background: rgba(15, 23, 42, .28);
            position: absolute;
            right: -.55rem;
            top: 50%;
            transform: translateY(-50%);
            border-radius: 0 .35rem .35rem 0;
        }

        .portal-battery i {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #22c55e, #84cc16);
        }

        .portal-percent {
            font-size: 2rem;
            font-weight: 900;
            color: rgb(22, 101, 52);
        }

        .portal-list {
            display: grid;
            gap: .75rem;
        }

        .portal-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .85rem;
            border-radius: 1rem;
            background: rgba(248, 250, 252, .95);
            border: 1px solid rgba(226, 232, 240, .9);
        }

        .dark .portal-row {
            background: rgba(30, 41, 59, .72);
            border-color: rgba(148, 163, 184, .18);
        }

        .portal-row strong {
            display: block;
            font-weight: 800;
            font-size: .92rem;
        }

        .portal-row span {
            display: block;
            font-size: .78rem;
            color: rgb(100, 116, 139);
            margin-top: .15rem;
        }

        .dark .portal-row span {
            color: rgb(203, 213, 225);
        }

        .portal-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .25rem .6rem;
            font-size: .75rem;
            font-weight: 800;
            background: rgba(59, 130, 246, .12);
            color: rgb(37, 99, 235);
            white-space: nowrap;
        }

        .portal-badge.danger {
            background: rgba(239, 68, 68, .12);
            color: rgb(220, 38, 38);
        }

        .portal-badge.ok {
            background: rgba(34, 197, 94, .12);
            color: rgb(22, 163, 74);
        }

        .portal-empty {
            border-radius: 1rem;
            padding: 1rem;
            background: rgba(248, 250, 252, .95);
            border: 1px dashed rgba(148, 163, 184, .6);
            color: rgb(100, 116, 139);
            font-size: .875rem;
        }

        .dark .portal-empty {
            background: rgba(30, 41, 59, .5);
            color: rgb(203, 213, 225);
        }

        .portal-form {
            display: grid;
            gap: .75rem;
        }

        .portal-input,
        .portal-select,
        .portal-textarea {
            width: 100%;
            border-radius: .9rem;
            border: 1px solid rgba(148, 163, 184, .5);
            padding: .75rem .85rem;
            background: white;
            color: rgb(15, 23, 42);
            outline: none;
        }

        .dark .portal-input,
        .dark .portal-select,
        .dark .portal-textarea {
            background: rgba(15, 23, 42, .7);
            color: white;
            border-color: rgba(148, 163, 184, .35);
        }

        .portal-textarea {
            min-height: 110px;
            resize: vertical;
        }

        .portal-btn {
            border-radius: .9rem;
            padding: .75rem 1rem;
            background: rgb(37, 99, 235);
            color: white;
            font-weight: 800;
            border: none;
            cursor: pointer;
        }

        .portal-btn:hover {
            background: rgb(29, 78, 216);
        }

        .portal-note {
            border-left: 4px solid rgba(59, 130, 246, .65);
            padding: .75rem .85rem;
            border-radius: .75rem;
            background: rgba(239, 246, 255, .75);
        }

        .dark .portal-note {
            background: rgba(30, 64, 175, .16);
        }

        .portal-note strong {
            display: block;
            font-weight: 800;
        }

        .portal-note small {
            display: block;
            color: rgb(100, 116, 139);
            margin-top: .2rem;
        }

        .dark .portal-note small {
            color: rgb(203, 213, 225);
        }

        @media (max-width: 1024px) {
            .portal-grid.two,
            .portal-grid.three {
                grid-template-columns: 1fr;
            }

            .portal-battery-wrap {
                grid-template-columns: 1fr;
            }
        }
    </style>

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
