<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $item->titulo }} - Portal do Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=lote3">

</head>
<body class="portal-item-controle-public">
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
