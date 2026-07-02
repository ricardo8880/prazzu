@php
    $assinatura = $assinatura ?? null;
    $item = $item ?? null;
    $tone = $assinatura['tone'] ?? 'gray';
    $portalUrl = $assinatura['portal_url'] ?? null;
@endphp

<div id="painel-assinaturas" class="signature-panel">
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
