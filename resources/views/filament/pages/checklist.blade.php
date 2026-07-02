<x-filament-panels::page>

    @php
        $resumo = $this->getResumo();
        $itens = $this->getItens();
    @endphp

    <div class="tp-page">
        <section class="contabilidade-lote3-scope" aria-label="Propósito da aba Checklists">
            <div class="contabilidade-lote3-scope__top">
                <div>
                    <span class="contabilidade-lote3-eyebrow"><i class="bi bi-card-checklist"></i> Checklists</span>
                    <h2>Conferência das etapas obrigatórias</h2>
                    <p>Esta aba concentra etapas e validações. Ela não substitui Pendências nem Documentos; apenas mostra o que precisa ser conferido para avançar com segurança.</p>
                </div>
                <div class="contabilidade-lote3-actions">
                    <a class="contabilidade-lote3-action primary" href="{{ \App\Filament\Pages\Pendencias::getUrl() }}"><i class="bi bi-list-check"></i> Pendências</a>
                    <a class="contabilidade-lote3-action" href="{{ \App\Filament\Pages\Kanban::getUrl() }}"><i class="bi bi-columns-gap"></i> Kanban</a>
                </div>
            </div>
        </section>

        <div class="tp-action-loading" wire:loading.flex>
            <span class="tp-spinner"></span>
            <span>Carregando checklist...</span>
        </div>
        <div class="tp-hero">
            <div>
                <span class="tp-eyebrow">TRABALHO</span>
                <h2>Checklist</h2>
                <p>Acompanhamento das etapas dos itens de controle, com progresso geral e pendências recentes.</p>
            </div>
        </div>

        <div class="tp-metrics tp-metrics-4">
            <div class="tp-card"><span>Total</span><strong>{{ $resumo['total'] }}</strong><small>etapas criadas</small></div>
            <div class="tp-card"><span>Pendentes</span><strong>{{ $resumo['pendentes'] }}</strong><small>faltam concluir</small></div>
            <div class="tp-card tp-success"><span>Concluídas</span><strong>{{ $resumo['concluidos'] }}</strong><small>finalizadas</small></div>
            <div class="tp-card"><span>Progresso</span><strong>{{ $resumo['percentual'] }}%</strong><small>conclusão geral</small></div>
        </div>

        <x-filament::section>
            <x-slot name="heading">Últimas etapas</x-slot>

            <div class="tp-list">
                @forelse($itens as $item)
                    <a href="{{ $item['url'] }}" class="tp-list-row tp-list-link">
                        <div class="tp-check @if($item['concluido']) is-done @endif">{{ $item['concluido'] ? '✓' : '!' }}</div>
                        <div class="tp-list-main">
                            <strong>{{ $item['titulo'] }}</strong>
                            <span>{{ $item['item'] }} • {{ $item['empresa'] }} • {{ $item['responsavel'] }}</span>
                        </div>
                        <b>{{ $item['concluido'] ? 'Concluído' : 'Pendente' }}</b>
                    </a>
                @empty
                    <div class="tp-empty tp-empty-actionable">
                        <div class="tp-empty-icon">✓</div>
                        <strong>Nenhuma etapa de checklist encontrada</strong>
                        <p>Crie ou abra um item de controle para adicionar as etapas que precisam ser conferidas antes da finalização.</p>
                        <a href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('create') }}" class="tp-empty-link">Criar novo item</a>
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
