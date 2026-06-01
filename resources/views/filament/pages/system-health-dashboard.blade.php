<x-filament-panels::page>
    @once
        <link rel="stylesheet" href="{{ asset('css/system-health-dashboard.css') }}?v=20260518">
    @endonce

    @php
        $report = $this->report ?? [];
        $summary = $report['summary'] ?? ['ok' => 0, 'warning' => 0, 'error' => 0, 'total' => 0];
        $status = $report['status'] ?? 'unknown';
        $statusLabel = match ($status) {
            'healthy' => 'Saudável',
            'attention' => 'Atenção',
            'critical' => 'Crítico',
            default => 'Não executado',
        };
        $statusClass = match ($status) {
            'healthy' => 'shd-badge--ok',
            'attention' => 'shd-badge--warning',
            'critical' => 'shd-badge--error',
            default => 'shd-badge--neutral',
        };
        $statusClasses = [
            'ok' => 'shd-badge--ok',
            'warning' => 'shd-badge--warning',
            'error' => 'shd-badge--error',
        ];
        $labels = ['ok' => 'OK', 'warning' => 'Avisos', 'error' => 'Erros'];
    @endphp

    <div class="shd-panel">
        <section class="shd-hero">
            <div class="shd-hero__content">
                <div class="shd-hero__text">
                    <div class="shd-hero__meta">
                        <span class="shd-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        <span class="shd-muted">Última verificação: {{ $report['generated_at_human'] ?? 'Ainda não executado' }}</span>
                    </div>

                    <div>
                        <h2 class="shd-title">Centro de saúde operacional do SistemRH / Prazzu</h2>
                        <p class="shd-description">
                            Monitore ambiente, banco, portal público, financeiro, uploads, comandos, logs e arquivos críticos em uma única tela. O painel é não destrutivo: apenas lê dados e aponta riscos.
                        </p>
                    </div>
                </div>

                <div class="shd-actions">
                    <label class="shd-limit">
                        <span class="shd-limit__label">Limite</span>
                        <input
                            type="number"
                            min="10"
                            max="5000"
                            step="10"
                            wire:model="limit"
                            class="shd-input"
                        />
                    </label>

                    <button
                        type="button"
                        wire:click="runHealthCheck"
                        wire:loading.attr="disabled"
                        class="shd-button shd-button--primary"
                    >
                        <span wire:loading.remove wire:target="runHealthCheck">Executar diagnóstico agora</span>
                        <span wire:loading wire:target="runHealthCheck">Executando...</span>
                    </button>

                    <button
                        type="button"
                        wire:click="exportJson"
                        class="shd-button shd-button--dark"
                    >
                        Exportar JSON
                    </button>
                </div>
            </div>
        </section>

        <section class="shd-summary-grid">
            <div class="shd-card shd-card--summary">
                <p class="shd-card__label">Status geral</p>
                <p class="shd-card__value">{{ $statusLabel }}</p>
                <p class="shd-card__hint">Duração: {{ $report['duration_ms'] ?? 0 }}ms</p>
            </div>

            <div class="shd-card shd-card--summary">
                <p class="shd-card__label">OK</p>
                <p class="shd-card__value shd-text-ok">{{ $summary['ok'] ?? 0 }}</p>
                <p class="shd-card__hint">Checks saudáveis</p>
            </div>

            <div class="shd-card shd-card--summary">
                <p class="shd-card__label">Avisos</p>
                <p class="shd-card__value shd-text-warning">{{ $summary['warning'] ?? 0 }}</p>
                <p class="shd-card__hint">Ajustes recomendados</p>
            </div>

            <div class="shd-card shd-card--summary">
                <p class="shd-card__label">Erros</p>
                <p class="shd-card__value shd-text-error">{{ $summary['error'] ?? 0 }}</p>
                <p class="shd-card__hint">Correção prioritária</p>
            </div>
        </section>

        <section class="shd-section-grid">
            @forelse (($report['sections'] ?? []) as $section)
                @php
                    $sectionStatus = $section['status'] ?? 'warning';
                    $sectionClass = $statusClasses[$sectionStatus] ?? $statusClasses['warning'];
                @endphp

                <article class="shd-section-card">
                    <header class="shd-section-card__header">
                        <div class="shd-section-card__heading">
                            <span class="shd-section-icon {{ $sectionClass }}">{{ strtoupper(substr($section['name'] ?? 'S', 0, 1)) }}</span>
                            <div>
                                <h3 class="shd-section-title">{{ $section['name'] ?? 'Seção' }}</h3>
                                <p class="shd-section-description">{{ $section['description'] ?? '' }}</p>
                            </div>
                        </div>

                        <div class="shd-section-counts">
                            <span class="shd-pill shd-pill--ok">{{ $section['summary']['ok'] ?? 0 }} OK</span>
                            <span class="shd-pill shd-pill--warning">{{ $section['summary']['warning'] ?? 0 }} Avisos</span>
                            <span class="shd-pill shd-pill--error">{{ $section['summary']['error'] ?? 0 }} Erros</span>
                        </div>
                    </header>

                    <div class="shd-check-list">
                        @foreach (($section['items'] ?? []) as $item)
                            @php
                                $itemStatus = $item['status'] ?? 'warning';
                                $itemClass = $statusClasses[$itemStatus] ?? $statusClasses['warning'];
                                $itemLabel = $labels[$itemStatus] ?? 'Aviso';
                            @endphp
                            <div class="shd-check-item">
                                <div class="shd-check-item__layout">
                                    <span class="shd-check-status {{ $itemClass }}">{{ $itemLabel }}</span>
                                    <div class="shd-check-item__content">
                                        <p class="shd-check-title">{{ $item['title'] ?? 'Verificação' }}</p>
                                        @if (! empty($item['detail']))
                                            <p class="shd-check-detail">{{ $item['detail'] }}</p>
                                        @endif
                                        @if (! empty($item['action']))
                                            <p class="shd-action-note">Ação recomendada: {{ $item['action'] }}</p>
                                        @endif
                                        @if (! empty($item['context']))
                                            <details class="shd-details">
                                                <summary class="shd-details__summary">Ver contexto técnico</summary>
                                                <pre class="shd-code-block">{{ json_encode($item['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </details>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="shd-empty">
                    <h3 class="shd-empty__title">Nenhum diagnóstico executado ainda.</h3>
                    <p class="shd-empty__text">Clique em “Executar diagnóstico agora” para gerar o primeiro relatório.</p>
                </div>
            @endforelse
        </section>
    </div>
</x-filament-panels::page>
