@php
    $context = $context ?? 'row';
    $actions = collect($row['actions'] ?? [])->filter(fn ($action) => filled($action['url'] ?? null))->values();
    $detailArguments = [
        'type' => $row['type'] ?? null,
        'recordId' => $row['recordId'] ?? null,
        'itemId' => $row['itemId'] ?? null,
        'context' => $context,
    ];
@endphp

<div
    class="compliance-row compliance-row-readable compliance-row-filterable compliance-row-actionable {{ in_array(($row['tone'] ?? 'info'), ['danger', 'warning'], true) ? 'is-priority' : '' }}"
    data-interno-row
    data-type="{{ $row['type'] ?? '' }}"
    data-status="{{ $row['rawStatus'] ?? '' }}"
    data-priority="{{ $row['rawPriority'] ?? '' }}"
    data-urgency="{{ $row['urgencyRank'] ?? '80' }}"
    data-search="{{ e($row['searchable'] ?? (($row['title'] ?? '') . ' ' . ($row['description'] ?? '') . ' ' . ($row['meta'] ?? ''))) }}"
>
    <div>
        <div class="compliance-row-heading">
            <span class="compliance-kind-pill {{ $row['kindTone'] ?? $defaultKindTone ?? 'info' }}">{{ $row['kind'] ?? $defaultKind ?? 'Registro' }}</span>
            <span class="compliance-urgency-pill {{ $row['urgencyTone'] ?? 'info' }}" title="{{ $row['urgencyMessage'] ?? 'Prioridade operacional do registro.' }}">{{ $row['urgencyLabel'] ?? 'Acompanhar' }}</span>
            <h3>{{ $row['title'] }}</h3>
        </div>

        <div class="compliance-meta-tags" aria-label="Informações do registro">
            @forelse (($row['metaTags'] ?? []) as $tag)
                <span>{{ $tag }}</span>
            @empty
                <small>{{ $row['meta'] ?? 'Informações não disponíveis' }}</small>
            @endforelse
            <span>{{ $row['date'] }}</span>
        </div>

        <p>{{ $row['description'] }}</p>

        @if (! empty($row['urgencyMessage']))
            <small class="compliance-urgency-message {{ $row['urgencyTone'] ?? 'info' }}">{{ $row['urgencyMessage'] }}</small>
        @endif

        @if (($showNextStep ?? false) && ! empty($row['nextStep']))
            <small class="compliance-next-step">{{ $row['nextStep'] }}</small>
        @endif

        <div class="compliance-row-actions" aria-label="Ações disponíveis para {{ $row['title'] }}">
            <button
                type="button"
                class="compliance-detail-trigger"
                wire:click='mountAction("viewInternoDetails", @json($detailArguments))'
                wire:loading.attr="disabled"
                wire:target='mountAction("viewInternoDetails", @json($detailArguments))'
                data-interno-detail-action
                data-interno-action-label="Ver detalhes"
            >
                <span>Ver detalhes</span>
            </button>

            @foreach ($actions as $action)
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
            @endforeach
        </div>
    </div>

    <span class="compliance-badge {{ $row['tone'] ?? 'info' }}">{{ $row['status'] }}</span>
</div>
