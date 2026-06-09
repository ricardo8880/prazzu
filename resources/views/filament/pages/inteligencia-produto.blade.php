<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/inteligencia-produto.css') }}">

    <section class="pi-page">
        <header class="pi-card pi-hero">
            <div class="pi-hero__content">
                <span class="pi-eyebrow">Módulo interno exclusivo do super admin</span>
                <h2 class="pi-title">Inteligência do Produto</h2>
                <p class="pi-description">
                    Importe comentários para o banco, visualize exatamente o texto salvo em cada registro e baixe um arquivo .txt com os comentários armazenados.
                </p>
            </div>

            <div class="pi-actions pi-actions--hero">
                <button type="button" wire:click="exportPrompt" class="pi-btn pi-btn--success">
                    Baixar prompt
                </button>
            </div>
        </header>

        <div class="pi-tab-panel pi-bottom-grid pi-bottom-grid--analysis">
            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Importação</h3>
                    <p class="pi-section-text">
                        Cole os comentários no campo abaixo e envie para o banco. O texto será salvo sem classificação, sem resumo e sem alteração de conteúdo.
                    </p>
                </div>

                <form wire:submit.prevent="importComments" class="pi-form-grid">
                    <label class="pi-field pi-field--full">
                        <span>Comentários</span>
                        <textarea
                            rows="14"
                            wire:model.defer="commentsText"
                            class="pi-input pi-textarea"
                            placeholder="Cole aqui os comentários que deseja salvar no banco."
                        ></textarea>
                        @error('commentsText')
                            <small class="pi-field-error">{{ $message }}</small>
                        @enderror
                    </label>

                    <div class="pi-actions pi-actions--form">
                        <button type="submit" class="pi-btn pi-btn--primary">
                            Enviar comentários para o banco
                        </button>
                    </div>
                </form>
            </article>

            <article class="pi-card">
                <div class="pi-section-header pi-section-header--row">
                    <div>
                        <h3 class="pi-section-title">Comentários salvos no banco</h3>
                        <p class="pi-section-text">
                            Exibindo os últimos 200 registros. O botão Baixar prompt exporta todos os comentários armazenados em <strong>ai_market_comments.original_text</strong>.
                        </p>
                    </div>

                    <span class="pi-badge pi-badge--info">{{ $commentsTotal }} comentário(s)</span>
                </div>

                <div class="pi-list">
                    @forelse($comments as $comment)
                        <div class="pi-comment-card">
                            <div class="pi-comment-card__meta">
                                <strong>#{{ $comment['id'] }}</strong>
                                @if($comment['created_at'])
                                    <span>•</span>
                                    <span>{{ $comment['created_at'] }}</span>
                                @endif
                            </div>

                            <pre class="pi-comment-card__text" style="white-space: pre-wrap; font-family: inherit; margin: 0;">{{ $comment['original_text'] }}</pre>
                        </div>
                    @empty
                        <div class="pi-empty-state">
                            Nenhum comentário salvo ainda. Use o campo Comentários para enviar o primeiro conteúdo ao banco.
                        </div>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
</x-filament-panels::page>
