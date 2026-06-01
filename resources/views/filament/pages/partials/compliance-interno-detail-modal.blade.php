@php
    $row = $row ?? [];
    $actions = collect($row['actions'] ?? [])->filter(fn ($action) => filled($action['url'] ?? null))->values();
    $detailCards = $row['detailCards'] ?? [];
@endphp

<div class="compliance-interno-filament-modal">
    <div class="compliance-interno-detail-flow">
        <article>
            <span>Status atual</span>
            <strong>{{ $row['status'] ?? 'Indisponível' }}</strong>
            <small>{{ $row['nextStep'] ?? 'Verifique o registro para definir a próxima ação.' }}</small>
        </article>

        <article class="compliance-detail-urgency {{ $row['urgencyTone'] ?? 'info' }}">
            <span>Urgência</span>
            <strong>{{ $row['urgencyLabel'] ?? 'Acompanhar' }}</strong>
            <small>{{ $row['urgencyMessage'] ?? 'Prioridade operacional do registro.' }}</small>
        </article>

        @foreach ($detailCards as $card)
            <article>
                <span>{{ $card['label'] ?? 'Informação' }}</span>
                <strong>{{ $card['value'] ?? 'Não informado' }}</strong>
                @if (! empty($card['hint']))
                    <small>{{ $card['hint'] }}</small>
                @endif
            </article>
        @endforeach
    </div>

    <div class="compliance-interno-detail-actions">
        <div>
            <span>Próximas ações</span>
            <p>Use os botões abaixo para continuar no fluxo correto, sem precisar procurar a tela manualmente no menu.</p>
        </div>

        <div>
            @forelse ($actions as $action)
                <a
                    href="{{ $action['url'] }}"
                    class="compliance-action-button {{ $action['style'] ?? 'secondary' }}"
                    data-interno-flow-action
                    data-interno-action-label="{{ $action['label'] ?? 'Abrir' }}"
                    data-interno-record-title="{{ $row['title'] ?? 'registro interno' }}"
                    @if (! empty($action['external'])) target="_blank" rel="noopener noreferrer" @endif
                    @if (! empty($action['hint'])) title="{{ $action['hint'] }}" @endif
                >
                    <span>{{ $action['label'] ?? 'Abrir' }}</span>
                </a>
            @empty
                <span class="compliance-action-unavailable">Este registro é apenas informativo nesta tela.</span>
            @endforelse
        </div>
    </div>
</div>
