<x-filament-panels::page>
    <div class="prazzu-shortcuts-page">
        <section class="prazzu-shortcuts-hero" aria-labelledby="prazzu-shortcuts-title">
            <div class="prazzu-shortcuts-hero__content">
                <div class="prazzu-shortcuts-eyebrow">
                    <i class="bi bi-star-fill" aria-hidden="true"></i>
                    <span>Favoritos da sidebar</span>
                </div>

                <div>
                    <h2 id="prazzu-shortcuts-title">Deixe o menu com a sua rotina na frente</h2>
                    <p>
                        Escolha as páginas que você mais usa e organize a ordem dos favoritos. Eles aparecem no topo da coluna lateral
                        para reduzir cliques e acelerar o atendimento diário.
                    </p>
                </div>
            </div>

            <div class="prazzu-shortcuts-metrics" aria-label="Resumo dos atalhos">
                <div class="prazzu-shortcuts-metric">
                    <strong>{{ $this->favoriteCount() }}</strong>
                    <span>atalhos ativos</span>
                </div>

                <div class="prazzu-shortcuts-metric">
                    <strong>{{ $this->availableCount() }}</strong>
                    <span>páginas disponíveis</span>
                </div>
            </div>
        </section>

        <div class="prazzu-shortcuts-layout">
            <main class="prazzu-shortcuts-main">
                <form wire:submit.prevent="salvar" class="prazzu-shortcuts-card">
                    <div class="prazzu-shortcuts-card__header">
                        <div>
                            <h3>Seus atalhos favoritos</h3>
                            <p>Adicione, remova e reorganize sem preencher posição manualmente.</p>
                        </div>

                        <div class="prazzu-shortcuts-order-badge">
                            <i class="bi bi-grip-vertical" aria-hidden="true"></i>
                            <span>arraste ou use as setas</span>
                        </div>
                    </div>

                    <div class="prazzu-shortcuts-form">
                        {{ $this->form }}
                    </div>

                    <div class="prazzu-shortcuts-actions">
                        <x-filament::button type="submit" icon="heroicon-o-check" size="lg">
                            Salvar atalhos
                        </x-filament::button>
                    </div>
                </form>
            </main>

            <aside class="prazzu-shortcuts-preview" aria-label="Prévia da sidebar">
                <div class="prazzu-shortcuts-preview__header">
                    <div>
                        <h3>Prévia da sidebar</h3>
                        <p>Veja como a lista ficará no menu.</p>
                    </div>

                    <span class="prazzu-shortcuts-preview__icon" aria-hidden="true">
                        <i class="bi bi-star-fill"></i>
                    </span>
                </div>

                <div class="prazzu-sidebar-mock">
                    <div class="prazzu-sidebar-mock__group">
                        <div class="prazzu-sidebar-mock__title">
                            <i class="bi bi-star-fill" aria-hidden="true"></i>
                            <span>Favoritos</span>
                        </div>

                        <div class="prazzu-sidebar-mock__items">
                            @forelse ($this->favoritePreviewItems() as $index => $item)
                                <div class="prazzu-sidebar-mock__item is-favorite">
                                    <span class="prazzu-sidebar-mock__number">{{ $index + 1 }}</span>
                                    <span class="prazzu-sidebar-mock__text">
                                        <strong>{{ $item['label'] }}</strong>
                                        <small>{{ $item['group'] }}</small>
                                    </span>
                                </div>
                            @empty
                                <div class="prazzu-sidebar-mock__empty">
                                    <i class="bi bi-stars" aria-hidden="true"></i>
                                    <span>Adicione suas páginas principais para montar a prévia.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="prazzu-sidebar-mock__group is-muted">
                        <div class="prazzu-sidebar-mock__title">Escritório Contábil / Contabilidade</div>
                        <div class="prazzu-sidebar-mock__items">
                            <div class="prazzu-sidebar-mock__item">Home</div>
                            <div class="prazzu-sidebar-mock__item">Pendências</div>
                            <div class="prazzu-sidebar-mock__item">Clientes</div>
                            <div class="prazzu-sidebar-mock__item">Financeiro</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
