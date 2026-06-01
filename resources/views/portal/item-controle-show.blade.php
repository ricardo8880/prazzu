<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $item->titulo }} - Portal do Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
            color: #1f2937;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 16px;
        }

        .header, .card {
            background: #ffffff;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            margin-bottom: 24px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        h1 {
            margin: 0;
            font-size: 30px;
            color: #111827;
        }

        h2 {
            margin: 0 0 16px 0;
            font-size: 20px;
            color: #111827;
        }

        .empresa {
            margin-top: 8px;
            color: #4b5563;
            font-size: 15px;
        }

        .badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 18px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 13px;
            font-weight: 700;
            background: #e5e7eb;
            color: #374151;
        }

        .badge.success { background: #dcfce7; color: #166534; }
        .badge.warning { background: #fef3c7; color: #92400e; }
        .badge.danger { background: #fee2e2; color: #991b1b; }
        .badge.info { background: #dbeafe; color: #1d4ed8; }
        .badge.gray { background: #e5e7eb; color: #374151; }

        .grid {
            display: grid;
            grid-template-columns: 1.4fr 0.8fr;
            gap: 24px;
        }

        .text {
            white-space: pre-line;
            line-height: 1.6;
            color: #374151;
        }

        .info-list {
            display: grid;
            gap: 14px;
        }

        .info-item {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
        }

        .info-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .info-title {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        .checklist {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 10px;
        }

        .checklist-item {
            display: flex;
            gap: 10px;
            align-items: center;
            background: #f9fafb;
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #e5e7eb;
        }

        .check {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background: #e5e7eb;
            color: #6b7280;
            flex: 0 0 auto;
        }

        .check.done {
            background: #dcfce7;
            color: #166534;
        }

        .progress-wrap { background: #e5e7eb; border-radius: 999px; height: 12px; overflow: hidden; margin: 12px 0 18px; }
        .progress-bar { background: #2563eb; height: 100%; border-radius: 999px; }
        .timeline { list-style: none; padding: 0; margin: 0; display: grid; gap: 12px; }
        .timeline li { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; }
        .attachments {
            display: grid;
            gap: 10px;
        }

        .attachment {
            display: block;
            background: #f9fafb;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            color: #1d4ed8;
            text-decoration: none;
            font-weight: 700;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 16px;
            font-weight: 700;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 16px;
        }

        .alert-error ul { margin: 8px 0 0 20px; padding: 0; }
        .alert-error li { margin: 4px 0; }
        .field-error { color: #b91c1c; font-size: 12px; font-weight: 700; margin-top: 6px; }
        .hp-field { position: absolute !important; left: -10000px !important; width: 1px !important; height: 1px !important; overflow: hidden !important; }
        .field input[aria-invalid="true"], .field textarea[aria-invalid="true"] { border-color: #ef4444; background: #fff7f7; }

        .form-grid {
            display: grid;
            gap: 14px;
        }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 6px;
        }

        .field textarea,
        .field input[type="file"],
        .field input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 12px;
            font-size: 15px;
            outline: none;
        }

        .field textarea:focus,
        .field input:focus {
            border-color: #2563eb;
        }

        .checkbox-row {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            line-height: 1.5;
            font-size: 14px;
            color: #374151;
        }

        .checkbox-row input {
            margin-top: 4px;
        }

        .button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border: 0;
            background: #2563eb;
            color: white;
            font-weight: 700;
            border-radius: 12px;
            padding: 13px 18px;
            cursor: pointer;
            font-size: 15px;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .button[disabled] {
            opacity: .72;
            cursor: not-allowed;
        }

        .helper-box {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #78350f;
            border-radius: 14px;
            padding: 12px;
            margin: 12px 0 16px;
            line-height: 1.5;
        }

        .helper-box strong {
            display: grid;
            place-items: center;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: #facc15;
            flex: 0 0 auto;
        }

        .form-feedback {
            display: none;
            align-items: center;
            gap: 8px;
            border-radius: 12px;
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 11px 12px;
            font-weight: 700;
        }

        .spinner {
            width: 17px;
            height: 17px;
            border-radius: 999px;
            border: 3px solid #bfdbfe;
            border-top-color: #2563eb;
            animation: portalSpin .75s linear infinite;
        }

        .is-processing .form-feedback {
            display: flex;
        }

        @keyframes portalSpin {
            to { transform: rotate(360deg); }
        }

        .signature-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            display: grid;
            gap: 8px;
        }

        .hash {
            word-break: break-all;
            font-family: monospace;
            font-size: 12px;
            color: #4b5563;
        }

        .footer {
            color: #6b7280;
            font-size: 13px;
            text-align: center;
            margin-top: 24px;
        }

        @media (max-width: 850px) {
            .grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 24px;
            }

            .header, .card {
                padding: 22px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="header-top">
            <div>
                <div class="label">Portal do Cliente</div>
                <h1>{{ $item->titulo }}</h1>

                <div class="empresa">
                    {{ $item->empresa?->nome_fantasia ?: $item->empresa?->razao_social }}
                </div>
            </div>

            <div>
                <span class="badge {{ $item->getStatusExibicaoColor() }}">
                    {{ $item->getStatusExibicao() }}
                </span>
            </div>
        </div>

        <div class="badge-row">
            <span class="badge info">Categoria: {{ $item->getTipoOuCategoria() }}</span>

            <span class="badge {{ $item->getPrioridadeColor() }}">
                Prioridade: {{ $item->getPrioridadeExibicao() }}
            </span>

            <span class="badge {{ $item->getSituacaoPrazoColor() }}">
                {{ $item->getSituacaoPrazo() }}
            </span>

            <span class="badge {{ $item->getAssinaturaColor() }}">
                {{ $item->getAssinaturaResumo() }}
            </span>

            @foreach ($item->tags as $tag)
                <span class="badge {{ $tag->cor ?: 'gray' }}">#{{ $tag->nome }}</span>
            @endforeach
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-error">
            <strong>Verifique os campos:</strong>
            <ul>
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid">
        <main>
            <div class="card">
                <h2>Descrição</h2>
                <div class="text">
                    {{ filled($item->descricao) ? $item->descricao : 'Nenhuma descrição informada.' }}
                </div>
            </div>

            <div class="card">
                <h2>Checklist</h2>

                @if ($item->checklists->isEmpty())
                    <p class="text">Nenhuma etapa cadastrada.</p>
                @else
                    <div class="label">{{ $item->getChecklistResumo() }} concluído — {{ $item->getChecklistPercentual() }}%</div>
                    <div class="progress-wrap"><div class="progress-bar" style="width: {{ $item->getChecklistPercentual() }}%"></div></div>

                    <ul class="checklist">
                        @foreach ($item->checklists as $checklist)
                            <li class="checklist-item">
                                <span class="check {{ $checklist->concluido ? 'done' : '' }}">
                                    {{ $checklist->concluido ? '✓' : '•' }}
                                </span>

                                <div>
                                    <strong>{{ $checklist->titulo }}</strong>

                                    @if ($checklist->concluido && $checklist->concluido_em)
                                        <div class="label">
                                            Concluído em {{ $checklist->concluido_em->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>


            <div class="card">
                <h2>Etapas operacionais</h2>

                @if ($item->etapasOperacionais->isEmpty())
                    <p class="text">Nenhuma etapa operacional cadastrada.</p>
                @else
                    <ul class="checklist">
                        @foreach ($item->etapasOperacionais as $etapa)
                            <li class="checklist-item">
                                <span class="check {{ $etapa->status === 'concluida' ? 'done' : '' }}">
                                    {{ $etapa->status === 'concluida' ? '✓' : $etapa->ordem }}
                                </span>
                                <div>
                                    <strong>{{ $etapa->titulo }}</strong>
                                    <div class="label">
                                        {{ $etapa->getStatusExibicao() }} | Tempo: {{ $etapa->getTempoResumo() }}
                                    </div>
                                    @if($etapa->descricao)
                                        <div class="text">{{ $etapa->descricao }}</div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card">
                <h2>Timeline</h2>

                @if ($item->timelines->isEmpty())
                    <p class="text">Nenhum evento registrado.</p>
                @else
                    <ul class="timeline">
                        @foreach ($item->timelines->take(20) as $timeline)
                            <li>
                                <strong>{{ $timeline->getTipoIcone() }} {{ $timeline->titulo }}</strong>
                                <div class="label">
                                    {{ $timeline->getTipoExibicao() }} | {{ $timeline->created_at?->format('d/m/Y H:i') }} | {{ $timeline->user?->name ?: 'Sistema' }}
                                </div>
                                @if($timeline->descricao)
                                    <div class="text">{{ $timeline->descricao }}</div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card">
                <h2>Anexos</h2>

                <div class="attachments">
                    @if (filled($item->arquivo))
                        <a class="attachment" href="{{ asset('storage/' . $item->arquivo) }}" target="_blank">
                            Abrir anexo principal
                        </a>
                    @endif

                    @foreach ($item->anexos as $anexo)
                        @php
                            $caminho = $anexo->caminho ?? $anexo->arquivo ?? null;
                            $nome = $anexo->nome_original ?? $anexo->nome ?? basename((string) $caminho);
                        @endphp

                        @if ($caminho)
                            <a class="attachment" href="{{ asset('storage/' . $caminho) }}" target="_blank">
                                {{ $nome }}
                            </a>
                        @endif
                    @endforeach

                    @if (blank($item->arquivo) && $item->anexos->isEmpty())
                        <p class="text">Nenhum anexo disponível.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <h2>Enviar documento</h2>
                <p class="text">Use este espaço para enviar arquivos, comprovantes, documentos e evidências diretamente para a equipe.</p><div class="helper-box"><strong>i</strong><span>Após o upload, a equipe será avisada e o arquivo ficará vinculado ao histórico deste item.</span></div>

                <form method="POST" action="{{ route('portal.item-controles.documentos', ['token' => $item->portal_token]) }}" enctype="multipart/form-data" class="js-feedback-form" data-confirm="Deseja enviar este documento para a equipe?" data-processing="Enviando documento...">
                    @csrf
                    <div class="hp-field" aria-hidden="true"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="doc_client_name">Nome *</label>
                            <input type="text" id="doc_client_name" name="client_name" value="{{ old('client_name', $item->portal_cliente_nome) }}" required aria-invalid="{{ $errors->has('client_name') ? 'true' : 'false' }}">
                            @error('client_name')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label for="doc_client_email">E-mail</label>
                            <input type="email" id="doc_client_email" name="client_email" value="{{ old('client_email', $item->portal_cliente_email) }}" aria-invalid="{{ $errors->has('client_email') ? 'true' : 'false' }}">
                            @error('client_email')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label for="documento">Arquivo *</label>
                            <input type="file" id="documento" name="documento" required aria-invalid="{{ $errors->has('documento') ? 'true' : 'false' }}">
                            @error('documento')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label for="observacao">Observação</label>
                            <textarea id="observacao" name="observacao" rows="4" aria-invalid="{{ $errors->has('observacao') ? 'true' : 'false' }}">{{ old('observacao') }}</textarea>
                            @error('observacao')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-feedback"><span class="spinner"></span><span>Enviando documento...</span></div><button class="button" type="submit">Enviar documento</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Mensagens</h2>

                @if ($item->clientPortalMessages->isNotEmpty())
                    <ul class="timeline">
                        @foreach ($item->clientPortalMessages->take(10) as $message)
                            <li>
                                <strong>{{ $message->client_name ?: 'Cliente' }}</strong>
                                <div class="label">{{ $message->created_at?->format('d/m/Y H:i') }} @if($message->client_email) | {{ $message->client_email }} @endif</div>
                                <div class="text">{{ $message->message }}</div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text">Nenhuma mensagem enviada pelo portal ainda.</p>
                @endif

                <form method="POST" action="{{ route('portal.item-controles.mensagem', ['token' => $item->portal_token]) }}" style="margin-top: 18px;" class="js-feedback-form" data-confirm="Deseja enviar esta mensagem?" data-processing="Enviando mensagem...">
                    @csrf
                    <div class="hp-field" aria-hidden="true"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="msg_client_name">Nome *</label>
                            <input type="text" id="msg_client_name" name="client_name" value="{{ old('client_name', $item->portal_cliente_nome) }}" required aria-invalid="{{ $errors->has('client_name') ? 'true' : 'false' }}">
                            @error('client_name')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label for="msg_client_email">E-mail</label>
                            <input type="email" id="msg_client_email" name="client_email" value="{{ old('client_email', $item->portal_cliente_email) }}" aria-invalid="{{ $errors->has('client_email') ? 'true' : 'false' }}">
                            @error('client_email')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="field">
                            <label for="message">Mensagem *</label>
                            <textarea id="message" name="message" rows="5" required aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}">{{ old('message') }}</textarea>
                            @error('message')<div class="field-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-feedback"><span class="spinner"></span><span>Enviando mensagem...</span></div><button class="button" type="submit">Enviar mensagem</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Assinatura interna</h2>

                @if ($item->assinaturas->isNotEmpty())
                    <div class="signature-box">
                        @foreach ($item->assinaturas as $assinatura)
                            <div>
                                <strong>{{ $assinatura->nome }}</strong>
                                @if ($assinatura->email)
                                    — {{ $assinatura->email }}
                                @endif
                            </div>

                            <div class="label">
                                Assinado em {{ $assinatura->assinado_em?->format('d/m/Y H:i') }}
                                @if ($assinatura->ip)
                                    | IP: {{ $assinatura->ip }}
                                @endif
                            </div>

                            <div class="hash">
                                Hash: {{ $assinatura->hash_assinatura }}
                            </div>

                            @if (! $loop->last)
                                <hr>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text">
                        Preencha os dados abaixo para registrar sua assinatura eletrônica interna.
                    </p>

                    <form method="POST" action="{{ route('portal.item-controles.assinar', ['token' => $item->portal_token]) }}" class="js-feedback-form" data-confirm="Confirma o registro da assinatura eletrônica?" data-processing="Registrando assinatura...">
                        @csrf
                        <div class="hp-field" aria-hidden="true"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

                        <div class="form-grid">
                            <div class="field">
                                <label for="nome">Nome completo *</label>
                                <input
                                    type="text"
                                    id="nome"
                                    name="nome"
                                    value="{{ old('nome', $item->portal_cliente_nome) }}"
                                    required
                                    aria-invalid="{{ $errors->has('nome') ? 'true' : 'false' }}"
                                >
                                @error('nome')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            <div class="field">
                                <label for="email">E-mail</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email', $item->portal_cliente_email) }}"
                                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                >
                                @error('email')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            <div class="field">
                                <label for="assinatura_documento">Documento/CPF/CNPJ</label>
                                <input
                                    type="text"
                                    id="assinatura_documento"
                                    name="documento"
                                    value="{{ old('documento') }}"
                                    aria-invalid="{{ $errors->has('documento') ? 'true' : 'false' }}"
                                >
                                @error('documento')<div class="field-error">{{ $message }}</div>@enderror
                            </div>

                            <label class="checkbox-row">
                                <input type="checkbox" name="aceite" value="1" required aria-invalid="{{ $errors->has('aceite') ? 'true' : 'false' }}">
                                <span>
                                    Declaro que li e concordo com as informações apresentadas neste item/documento,
                                    registrando minha assinatura eletrônica interna.
                                </span>
                            </label>
                            @error('aceite')<div class="field-error">{{ $message }}</div>@enderror

                            <div class="form-feedback"><span class="spinner"></span><span>Registrando assinatura...</span></div>
                            <button class="button" type="submit">
                                Registrar assinatura
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </main>

        <aside>
            <div class="card">
                <h2>Informações</h2>

                <div class="info-list">
                    <div class="info-item">
                        <div class="info-title">Vencimento</div>
                        <div class="info-value">
                            {{ $item->data_vencimento ? $item->data_vencimento->format('d/m/Y') : 'Sem prazo' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-title">Conclusão</div>
                        <div class="info-value">
                            {{ $item->data_conclusao ? $item->data_conclusao->format('d/m/Y') : 'Não concluído' }}
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-title">Responsável</div>
                        <div class="info-value">
                            {{ $item->responsavel?->nome ?: 'Não informado' }}
                        </div>
                    </div>

                    @if ($item->responsavel?->email)
                        <div class="info-item">
                            <div class="info-title">E-mail do responsável</div>
                            <div class="info-value">{{ $item->responsavel->email }}</div>
                        </div>
                    @endif

                    @if ($item->portal_cliente_nome)
                        <div class="info-item">
                            <div class="info-title">Cliente</div>
                            <div class="info-value">{{ $item->portal_cliente_nome }}</div>
                        </div>
                    @endif

                    @if ($item->portal_expira_em)
                        <div class="info-item">
                            <div class="info-title">Link válido até</div>
                            <div class="info-value">{{ $item->portal_expira_em->format('d/m/Y H:i') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <h2>Observação</h2>
                <div class="text">
                    {{ filled($item->observacao) ? $item->observacao : 'Nenhuma observação informada.' }}
                </div>
            </div>
        </aside>
    </div>

    <div class="footer">
        Este portal é um acesso externo seguro gerado para acompanhamento e assinatura deste item.
    </div>
</div>
<script>
    document.querySelectorAll('.js-feedback-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var confirmText = form.getAttribute('data-confirm');
            if (confirmText && ! window.confirm(confirmText)) {
                event.preventDefault();
                return false;
            }

            form.classList.add('is-processing');
            form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                button.disabled = true;
                button.textContent = form.getAttribute('data-processing') || 'Processando...';
            });
        });
    });
</script>
</body>
</html>
