@php
    $assinatura = $assinatura ?? null;
    $item = $item ?? null;
    $tone = $assinatura['tone'] ?? 'gray';
    $portalUrl = $assinatura['portal_url'] ?? null;
@endphp

<div id="painel-assinaturas" class="signature-panel">
    <style>
        .signature-panel {
            --sig-border: rgba(148, 163, 184, .28);
            --sig-muted: #64748b;
            --sig-title: #0f172a;
            --sig-bg: #ffffff;
            --sig-soft: #f8fafc;
            --sig-primary: #f59e0b;
            --sig-success: #16a34a;
            --sig-warning: #d97706;
            --sig-danger: #dc2626;
            --sig-gray: #64748b;
            background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
            border: 1px solid var(--sig-border);
            border-radius: 22px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .07);
            color: var(--sig-title);
            overflow: hidden;
        }
        .dark .signature-panel {
            --sig-border: rgba(148, 163, 184, .22);
            --sig-muted: #94a3b8;
            --sig-title: #f8fafc;
            --sig-bg: #0f172a;
            --sig-soft: #111827;
            background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
            box-shadow: 0 18px 45px rgba(0, 0, 0, .28);
        }
        .signature-header, .signature-body, .signature-footer { padding: 20px; }
        .signature-header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; border-bottom: 1px solid var(--sig-border); }
        .signature-eyebrow { color: var(--sig-primary); font-size: 11px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        .signature-title { margin: 4px 0 6px; font-size: 20px; line-height: 1.2; font-weight: 850; }
        .signature-subtitle { margin: 0; color: var(--sig-muted); font-size: 14px; max-width: 780px; }
        .signature-status { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; font-size: 13px; font-weight: 800; white-space: nowrap; border: 1px solid var(--sig-border); }
        .signature-status.success { color: var(--sig-success); background: rgba(22, 163, 74, .10); }
        .signature-status.warning { color: var(--sig-warning); background: rgba(217, 119, 6, .11); }
        .signature-status.danger { color: var(--sig-danger); background: rgba(220, 38, 38, .10); }
        .signature-status.gray { color: var(--sig-gray); background: rgba(100, 116, 139, .10); }
        .signature-dot { width: 9px; height: 9px; border-radius: 999px; background: currentColor; box-shadow: 0 0 0 4px color-mix(in srgb, currentColor 16%, transparent); }
        .signature-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .signature-metric { background: var(--sig-bg); border: 1px solid var(--sig-border); border-radius: 16px; padding: 14px; }
        .signature-metric span { display: block; color: var(--sig-muted); font-size: 12px; font-weight: 700; margin-bottom: 6px; }
        .signature-metric strong { display: block; font-size: 16px; font-weight: 850; }
        .signature-columns { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 14px; margin-top: 14px; }
        .signature-card { background: var(--sig-bg); border: 1px solid var(--sig-border); border-radius: 18px; overflow: hidden; }
        .signature-card header { display: flex; justify-content: space-between; gap: 12px; align-items: center; padding: 14px 16px; border-bottom: 1px solid var(--sig-border); }
        .signature-card h3 { margin: 0; font-size: 15px; font-weight: 850; }
        .signature-card small { color: var(--sig-muted); font-weight: 700; }
        .signature-list { display: grid; gap: 10px; padding: 14px; }
        .signature-person { border: 1px solid var(--sig-border); background: var(--sig-soft); border-radius: 14px; padding: 12px; }
        .signature-person strong { display: block; font-size: 14px; font-weight: 850; }
        .signature-person span, .signature-person small { display: block; color: var(--sig-muted); font-size: 12px; margin-top: 3px; word-break: break-word; }
        .signature-empty { padding: 18px; color: var(--sig-muted); font-size: 14px; background: var(--sig-soft); border-radius: 14px; border: 1px dashed var(--sig-border); }
        .signature-footer { display: flex; justify-content: space-between; align-items: center; gap: 12px; border-top: 1px solid var(--sig-border); background: rgba(248, 250, 252, .55); }
        .dark .signature-footer { background: rgba(15, 23, 42, .55); }
        .signature-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .signature-button { border: 1px solid var(--sig-border); background: var(--sig-bg); border-radius: 12px; padding: 10px 13px; font-size: 13px; font-weight: 800; cursor: pointer; transition: .15s ease; text-decoration: none; color: var(--sig-title); display: inline-flex; align-items: center; gap: 8px; }
        .signature-button:hover { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(15, 23, 42, .10); }
        .signature-button.primary { background: var(--sig-primary); color: #111827; border-color: var(--sig-primary); }
        .signature-note { color: var(--sig-muted); font-size: 12px; }
        .signature-clicksign { margin-top: 14px; border: 1px solid var(--sig-border); border-radius: 16px; padding: 14px; background: var(--sig-bg); display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
        .signature-clicksign strong { display: block; font-size: 14px; font-weight: 850; }
        .signature-clicksign span { display: block; color: var(--sig-muted); font-size: 12px; margin-top: 4px; }
        @media (max-width: 980px) { .signature-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .signature-columns { grid-template-columns: 1fr; } .signature-header, .signature-footer { flex-direction: column; align-items: stretch; } }
        @media (max-width: 640px) { .signature-grid { grid-template-columns: 1fr; } .signature-header, .signature-body, .signature-footer { padding: 16px; } }
    </style>

    <div class="signature-header">
        <div>
            <div class="signature-eyebrow">Assinatura do documento</div>
            <h2 class="signature-title">Controle operacional de assinatura</h2>
            <p class="signature-subtitle">Acompanhe quem já assinou, quem ainda falta assinar, datas do processo, link do portal e sincronização com Clicksign quando configurada.</p>
        </div>

        <div class="signature-status {{ $tone }}">
            <span class="signature-dot"></span>
            {{ $assinatura['label'] ?? 'Não enviada' }}
        </div>
    </div>

    <div class="signature-body">
        <div class="signature-grid">
            <div class="signature-metric">
                <span>Assinados</span>
                <strong>{{ $assinatura['total_assinados'] ?? 0 }}</strong>
            </div>
            <div class="signature-metric">
                <span>Pendentes</span>
                <strong>{{ $assinatura['total_pendentes'] ?? 0 }}</strong>
            </div>
            <div class="signature-metric">
                <span>Enviado em</span>
                <strong>{{ ($assinatura['enviado_em'] ?? null)?->format('d/m/Y H:i') ?? 'Ainda não enviado' }}</strong>
            </div>
            <div class="signature-metric">
                <span>Concluído em</span>
                <strong>{{ ($assinatura['concluido_em'] ?? null)?->format('d/m/Y H:i') ?? 'Pendente' }}</strong>
            </div>
        </div>

        <div class="signature-columns">
            <section class="signature-card">
                <header>
                    <h3>Quem assinou</h3>
                    <small>{{ $assinatura['total_assinados'] ?? 0 }} registro(s)</small>
                </header>
                <div class="signature-list">
                    @forelse (($assinatura['assinantes_concluidos'] ?? []) as $assinante)
                        <div class="signature-person">
                            <strong>{{ $assinante['nome'] }}</strong>
                            @if (! empty($assinante['email']))<span>{{ $assinante['email'] }}</span>@endif
                            @if (! empty($assinante['documento']))<span>Documento: {{ $assinante['documento'] }}</span>@endif
                            <small>{{ $assinante['origem'] ?? 'Assinatura' }} · {{ ($assinante['assinado_em'] ?? null)?->format('d/m/Y H:i') ?? 'Data não registrada' }}</small>
                            @if (! empty($assinante['hash']))<small>Hash: {{ $assinante['hash'] }}</small>@endif
                        </div>
                    @empty
                        <div class="signature-empty">Nenhuma assinatura registrada ainda. Use “Reenviar assinatura” para ativar o portal e compartilhar o link com o assinante.</div>
                    @endforelse
                </div>
            </section>

            <section class="signature-card">
                <header>
                    <h3>Quem falta assinar</h3>
                    <small>{{ $assinatura['total_pendentes'] ?? 0 }} pendente(s)</small>
                </header>
                <div class="signature-list">
                    @forelse (($assinatura['assinantes_pendentes'] ?? []) as $assinante)
                        <div class="signature-person">
                            <strong>{{ $assinante['nome'] }}</strong>
                            @if (! empty($assinante['email']))<span>{{ $assinante['email'] }}</span>@endif
                            @if (! empty($assinante['documento']))<span>Documento: {{ $assinante['documento'] }}</span>@endif
                            <small>{{ $assinante['origem'] ?? 'Pendente' }}</small>
                        </div>
                    @empty
                        <div class="signature-empty">Não há assinantes pendentes para este item.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="signature-clicksign">
            <div>
                <strong>Clicksign</strong>
                <span>
                    @if (($assinatura['clicksign']['habilitado'] ?? false) && ! empty($assinatura['clicksign']['document_key']))
                        Integração configurada para este documento. Última sincronização: {{ ($assinatura['clicksign']['ultima_sincronizacao_em'] ?? null)?->format('d/m/Y H:i') ?? 'ainda não sincronizada' }}.
                    @elseif (($assinatura['clicksign']['habilitado'] ?? false))
                        Token configurado, mas este item ainda não possui chave de documento Clicksign registrada.
                    @else
                        Token/base URL da Clicksign não configurados no ambiente. O fluxo interno pelo portal continua funcionando normalmente.
                    @endif
                </span>
                @if (! empty($assinatura['clicksign']['mensagem']))
                    <span>{{ $assinatura['clicksign']['mensagem'] }}</span>
                @endif
            </div>
            @if (! empty($assinatura['clicksign']['document_key']))
                <span class="signature-status gray">Doc: {{ $assinatura['clicksign']['document_key'] }}</span>
            @endif
        </div>
    </div>

    <div class="signature-footer">
        <div class="signature-note">
            @if (($assinatura['ultimo_reenvio_em'] ?? null))
                Último reenvio em {{ $assinatura['ultimo_reenvio_em']->format('d/m/Y H:i') }}.
            @elseif (($assinatura['ultima_consulta_em'] ?? null))
                Última consulta em {{ $assinatura['ultima_consulta_em']->format('d/m/Y H:i') }}.
            @else
                Use as ações para manter o acompanhamento da assinatura atualizado.
            @endif
        </div>

        <div class="signature-actions">
            @if ($portalUrl)
                <a class="signature-button" href="{{ $portalUrl }}" target="_blank" rel="noopener">Abrir portal</a>
            @endif

            @if ($podeGerenciar)
                <button type="button" class="signature-button" wire:click="consultarStatus" wire:loading.attr="disabled">Consultar status</button>
                <button type="button" class="signature-button primary" wire:click="reenviarAssinatura" wire:loading.attr="disabled">Reenviar assinatura</button>
            @endif
        </div>
    </div>
</div>
