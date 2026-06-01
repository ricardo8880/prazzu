<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/inteligencia-produto.css') }}">

    <section class="pi-page">
        <header class="pi-card pi-hero">
            <div class="pi-hero__content">
                <span class="pi-eyebrow">Módulo interno exclusivo do super admin</span>
                <h2 class="pi-title">Inteligência do Produto sem IA externa</h2>
                <p class="pi-description">
                    Arquive comentários de concorrentes, gere relatórios heurísticos, veja oportunidades de UX, SEO e roadmap, e exporte um prompt pronto para enviar ao ChatGPT. Nenhuma API de IA é chamada nesta versão.
                </p>
            </div>

            <div class="pi-actions pi-actions--hero">
                <button type="button" wire:click="generateReport" class="pi-btn pi-btn--secondary">Atualizar relatório</button>
                <button type="button" wire:click="exportJson" class="pi-btn pi-btn--info">Baixar JSON</button>
                <button type="button" wire:click="exportPrompt" class="pi-btn pi-btn--success">Baixar prompt</button>
            </div>
        </header>

        <div class="pi-kpi-grid">
            <article class="pi-kpi-card pi-kpi-card--featured">
                <span class="pi-kpi-card__label">Product Health</span>
                <strong class="pi-kpi-card__value">{{ data_get($report, 'summary.product_health_score', 100) }}/100</strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Comentários</span>
                <strong class="pi-kpi-card__value">{{ data_get($report, 'summary.market_comments_total', 0) }}</strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Negativos</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--danger">{{ data_get($report, 'summary.negative_comments_total', 0) }}</strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Positivos</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--success">{{ data_get($report, 'summary.positive_comments_total', 0) }}</strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Mistos</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--warning">{{ data_get($report, 'summary.mixed_comments_total', 0) }}</strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Neutros</span>
                <strong class="pi-kpi-card__value">{{ data_get($report, 'summary.neutral_comments_total', 0) }}</strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Problemas críticos</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--danger">{{ data_get($report, 'summary.critical_problems_total', 0) }}</strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Oportunidades</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--info">{{ data_get($report, 'summary.opportunities_total', 0) }}</strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Pontos fortes</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--success">{{ data_get($report, 'summary.strength_categories_total', 0) }}</strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Aprendizados</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--warning">{{ data_get($report, 'summary.market_learnings_total', 0) }}</strong>
            </article>
        </div>

        <article class="pi-card pi-executive-card">
            <div class="pi-section-header pi-section-header--row">
                <div>
                    <h3 class="pi-section-title">Resumo executivo</h3>
                    <p class="pi-section-text">A primeira decisão sugerida aparece aqui para o super admin bater o olho e saber onde agir.</p>
                </div>
                <span class="pi-badge pi-badge--info">Período: {{ data_get($report, 'period_days', $periodDays) }} dias</span>
            </div>

            @php($firstProblem = data_get($report, 'top_problems.0'))
            @php($firstRoadmap = data_get($report, 'recommended_roadmap.0'))

            @if($firstProblem)
                <div class="pi-executive-grid">
                    <div class="pi-executive-panel">
                        <span class="pi-eyebrow">Maior risco detectado</span>
                        <h4 class="pi-executive-title">{{ $firstProblem['category'] }}</h4>
                        <p class="pi-section-text"><strong>Dor real:</strong> {{ $firstProblem['real_pain'] ?? 'Não classificada' }}</p>
                    </div>

                    <div class="pi-executive-panel">
                        <span class="pi-eyebrow">Ação recomendada agora</span>
                        <h4 class="pi-executive-title">{{ $firstRoadmap['what_to_do'] ?? ($firstProblem['recommended_action'] ?? 'Analisar comentários importados') }}</h4>
                        <p class="pi-section-text"><strong>Evitar:</strong> {{ $firstRoadmap['what_not_to_do'] ?? ($firstProblem['what_not_to_do'] ?? 'Não decidir sem evidência') }}</p>
                    </div>

                    <div class="pi-executive-panel">
                        <span class="pi-eyebrow">Evidência</span>
                        <h4 class="pi-executive-title">{{ $firstProblem['total'] }} ocorrência(s)</h4>
                        <p class="pi-section-text">{{ $firstProblem['negative_total'] }} negativa(s), confiança {{ $firstProblem['confidence'] ?? 'baixa' }}, score {{ $firstProblem['priority_score'] ?? 0 }}.</p>
                    </div>
                </div>
            @else
                <div class="pi-empty-state">Importe comentários para o sistema gerar a primeira recomendação executiva.</div>
            @endif
        </article>

        <div class="pi-main-grid">
            <article class="pi-card pi-import-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Importar comentários</h3>
                    <p class="pi-section-text">Cole uma avaliação/post por vez ou separe comentários diferentes com --- em uma linha. O sistema arquiva, identifica categoria, sentimento, dor real, insight, aprendizado, ponto forte, impacto e ação recomendada.</p>
                </div>

                <div class="pi-form">
                    <div class="pi-form-grid pi-form-grid--two">
                        <label class="pi-field">
                            <span class="pi-label">Fonte</span>
                            <input type="text" wire:model.defer="sourceName" class="pi-input" placeholder="Reddit, Google Reviews, YouTube...">
                            @error('sourceName') <span class="pi-error">{{ $message }}</span> @enderror
                        </label>

                        <label class="pi-field">
                            <span class="pi-label">Concorrente</span>
                            <input type="text" wire:model.defer="competitorName" class="pi-input" placeholder="ClickUp, Asana, Monday...">
                            @error('competitorName') <span class="pi-error">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div class="pi-form-grid pi-form-grid--three">
                        <label class="pi-field">
                            <span class="pi-label">Tipo</span>
                            <select wire:model.defer="sourceType" class="pi-input">
                                <option value="reddit">Reddit</option>
                                <option value="google_reviews">Google Reviews</option>
                                <option value="youtube">YouTube</option>
                                <option value="g2">G2</option>
                                <option value="capterra">Capterra</option>
                                <option value="app_store">App Store</option>
                                <option value="play_store">Play Store</option>
                                <option value="manual">Manual</option>
                            </select>
                        </label>

                        <label class="pi-field">
                            <span class="pi-label">Nota</span>
                            <select wire:model.defer="rating" class="pi-input">
                                <option value="">Sem nota</option>
                                <option value="1">1 estrela</option>
                                <option value="2">2 estrelas</option>
                                <option value="3">3 estrelas</option>
                                <option value="4">4 estrelas</option>
                                <option value="5">5 estrelas</option>
                            </select>
                        </label>

                        <label class="pi-field">
                            <span class="pi-label">Idioma</span>
                            <input type="text" wire:model.defer="language" class="pi-input" placeholder="pt-BR">
                        </label>
                    </div>

                    <label class="pi-field">
                        <span class="pi-label">URL de origem</span>
                        <input type="text" wire:model.defer="sourceUrl" class="pi-input" placeholder="Opcional">
                    </label>

                    <label class="pi-field">
                        <span class="pi-label">Comentários</span>
                        <textarea wire:model.defer="commentsText" rows="12" class="pi-input pi-textarea" placeholder="Cole aqui um post/avaliação completo. Para importar vários comentários de uma vez, separe cada comentário com uma linha contendo apenas ---."></textarea>
                        @error('commentsText') <span class="pi-error">{{ $message }}</span> @enderror
                    </label>

                    <button type="button" wire:click="importComments" class="pi-btn pi-btn--primary pi-btn--block">Arquivar e classificar comentários</button>
                </div>
            </article>

            <div class="pi-stack">
                <article class="pi-card">
                    <div class="pi-section-header pi-section-header--row">
                        <div>
                            <h3 class="pi-section-title">Principais problemas detectados</h3>
                            <p class="pi-section-text">Ordenado por prioridade calculada: frequência, negatividade, gravidade, impacto e confiança da evidência.</p>
                        </div>

                        <label class="pi-field pi-field--period">
                            <span class="pi-label">Período/dias</span>
                            <input type="number" min="1" max="3650" wire:model.defer="periodDays" class="pi-input">
                        </label>
                    </div>

                    <div class="pi-list">
                        @forelse(data_get($report, 'top_problems', []) as $problem)
                            <div class="pi-problem-card">
                                <div class="pi-problem-card__header">
                                    <div class="pi-problem-card__content">
                                        <h4 class="pi-problem-card__title">{{ $problem['category'] }}</h4>
                                        <p class="pi-problem-card__pain"><strong>Dor real:</strong> {{ $problem['real_pain'] ?? 'Não classificada' }}</p>
                                        <p class="pi-problem-card__text"><strong>Insight:</strong> {{ $problem['insight'] ?? 'Não informado' }}</p>
                                        <p class="pi-problem-card__text"><strong>Aprendizado:</strong> {{ $problem['market_learning'] ?? 'Não informado' }}</p>
                                        <p class="pi-problem-card__text"><strong>O que fazer:</strong> {{ $problem['what_to_do'] ?? ($problem['recommended_action'] ?? 'Analisar manualmente') }}</p>
                                        <p class="pi-problem-card__text"><strong>O que não fazer:</strong> {{ $problem['what_not_to_do'] ?? 'Não informado' }}</p>
                                        <p class="pi-problem-card__text"><strong>Oportunidade:</strong> {{ $problem['opportunity'] }}</p>
                                    </div>

                                    <div class="pi-badge-group">
                                        <span class="pi-badge">{{ $problem['total'] }} ocorrência(s)</span>
                                        <span class="pi-badge pi-badge--danger">Score {{ $problem['priority_score'] }}</span>
                                        <span class="pi-badge pi-badge--info">Impacto {{ $problem['impact'] ?? '-' }}</span>
                                        <span class="pi-badge pi-badge--warning">Confiança {{ $problem['confidence'] ?? 'baixa' }}</span>
                                    </div>
                                </div>

                                @if(! empty($problem['source_breakdown']))
                                    <div class="pi-keyword-list">
                                        @foreach($problem['source_breakdown'] as $source)
                                            <span class="pi-keyword">{{ $source['source_name'] }}: {{ $source['total'] }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if(! empty($problem['seo_keywords']))
                                    <div class="pi-keyword-list">
                                        @foreach($problem['seo_keywords'] as $keyword)
                                            <span class="pi-keyword">{{ $keyword }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="pi-empty-state">Nenhum comentário de mercado arquivado ainda. Importe comentários para gerar o primeiro relatório.</div>
                        @endforelse
                    </div>
                </article>

                <article class="pi-card">
                    <div class="pi-section-header">
                        <h3 class="pi-section-title">Roadmap sugerido</h3>
                    </div>

                    <div class="pi-list">
                        @forelse(data_get($report, 'recommended_roadmap', []) as $item)
                            <div class="pi-roadmap-card">
                                <div class="pi-roadmap-card__content">
                                    <strong class="pi-roadmap-card__title">{{ $item['problem'] }}</strong>
                                    <p class="pi-roadmap-card__text"><strong>Fazer:</strong> {{ $item['what_to_do'] ?? $item['recommended_action'] }}</p>
                                    <p class="pi-roadmap-card__text"><strong>Não fazer:</strong> {{ $item['what_not_to_do'] ?? 'Não informado' }}</p>
                                    <small class="pi-roadmap-card__meta">{{ $item['why'] }}</small>
                                    @if(! empty($item['real_pain']))
                                        <small class="pi-roadmap-card__meta"><strong>Dor real:</strong> {{ $item['real_pain'] }}</small>
                                    @endif
                                    @if(! empty($item['market_learning']))
                                        <small class="pi-roadmap-card__meta"><strong>Aprendizado:</strong> {{ $item['market_learning'] }}</small>
                                    @endif
                                </div>

                                <div class="pi-badge-group">
                                    <span class="pi-badge pi-badge--warning">{{ $item['priority'] }}</span>
                                    <span class="pi-badge">{{ $item['complexity'] }}</span>
                                    <span class="pi-badge pi-badge--info">{{ $item['confidence'] ?? 'baixa' }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="pi-muted-text">O roadmap aparecerá depois da importação dos primeiros comentários.</p>
                        @endforelse
                    </div>
                </article>
            </div>
        </div>

        <div class="pi-bottom-grid pi-bottom-grid--analysis">
            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Aprendizados do mercado</h3>
                    <p class="pi-section-text">Transforma comentários em conhecimento acionável: insight, o que fazer e o que evitar.</p>
                </div>

                <div class="pi-list">
                    @forelse(data_get($report, 'market_learnings', []) as $learning)
                        <div class="pi-comment-card">
                            <div class="pi-comment-card__meta">
                                <strong>{{ $learning['title'] }}</strong>
                                <span>•</span>
                                <span>{{ $learning['type'] }}</span>
                                <span>• confiança {{ $learning['confidence'] }}</span>
                            </div>
                            <p class="pi-comment-card__insight"><strong>Aprendizado:</strong> {{ $learning['learning'] }}</p>
                            <p class="pi-comment-card__insight"><strong>Insight:</strong> {{ $learning['insight'] }}</p>
                            <p class="pi-comment-card__insight"><strong>Fazer:</strong> {{ $learning['what_to_do'] }}</p>
                            <p class="pi-comment-card__insight"><strong>Não fazer:</strong> {{ $learning['what_not_to_do'] }}</p>
                            <p class="pi-comment-card__text"><strong>Evidência:</strong> {{ $learning['evidence'] }}</p>
                        </div>
                    @empty
                        <p class="pi-muted-text">Os aprendizados aparecerão depois da importação dos primeiros comentários.</p>
                    @endforelse
                </div>
            </article>

            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Pontos fortes detectados</h3>
                    <p class="pi-section-text">Elogios e padrões positivos que mostram o que o mercado valoriza e você não deve perder.</p>
                </div>

                <div class="pi-list">
                    @forelse(data_get($report, 'market_strengths', []) as $strength)
                        <div class="pi-problem-card">
                            <div class="pi-problem-card__header">
                                <div class="pi-problem-card__content">
                                    <h4 class="pi-problem-card__title">{{ $strength['category'] }}</h4>
                                    <p class="pi-problem-card__text"><strong>Insight:</strong> {{ $strength['insight'] }}</p>
                                    <p class="pi-problem-card__text"><strong>Aprendizado:</strong> {{ $strength['market_learning'] }}</p>
                                    <p class="pi-problem-card__text"><strong>Preservar/fazer:</strong> {{ $strength['what_to_do'] }}</p>
                                    <p class="pi-problem-card__text"><strong>Evitar:</strong> {{ $strength['what_not_to_do'] }}</p>
                                </div>
                                <div class="pi-badge-group">
                                    <span class="pi-badge pi-badge--success">{{ $strength['total'] }} ocorrência(s)</span>
                                    <span class="pi-badge pi-badge--warning">Confiança {{ $strength['confidence'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="pi-muted-text">Nenhum ponto forte detectado ainda.</p>
                    @endforelse
                </div>
            </article>
        </div>

        <div class="pi-bottom-grid pi-bottom-grid--analysis">
            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Frequência por fonte</h3>
                    <p class="pi-section-text">Mostra se a reclamação aparece em uma fonte isolada ou se está se repetindo em vários lugares.</p>
                </div>

                <div class="pi-list">
                    @forelse(data_get($report, 'source_frequency', []) as $source)
                        <div class="pi-roadmap-card">
                            <div class="pi-roadmap-card__content">
                                <strong class="pi-roadmap-card__title">{{ $source['source_name'] }}</strong>
                                <p class="pi-roadmap-card__text">Tipo: {{ $source['source_type'] }}</p>
                                @if(! empty($source['top_categories']))
                                    <small class="pi-roadmap-card__meta"><strong>Categorias:</strong>
                                        @foreach($source['top_categories'] as $category => $total)
                                            {{ $category }} ({{ $total }})@if(! $loop->last), @endif
                                        @endforeach
                                    </small>
                                @endif
                            </div>
                            <div class="pi-badge-group">
                                <span class="pi-badge">{{ $source['total'] }} total</span>
                                <span class="pi-badge pi-badge--danger">{{ $source['negative_total'] }} neg.</span>
                                <span class="pi-badge pi-badge--success">{{ $source['positive_total'] }} pos.</span>
                            </div>
                        </div>
                    @empty
                        <p class="pi-muted-text">Nenhuma fonte analisada ainda.</p>
                    @endforelse
                </div>
            </article>

            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Contradições detectadas</h3>
                    <p class="pi-section-text">Ajuda a evitar decisões erradas quando o mercado elogia e reclama de coisas parecidas.</p>
                </div>

                <div class="pi-list">
                    @forelse(data_get($report, 'contradictions', []) as $contradiction)
                        <div class="pi-alert-card">
                            <div class="pi-alert-card__header">
                                <strong>{{ $contradiction['title'] }}</strong>
                                <span class="pi-badge pi-badge--warning">{{ $contradiction['confidence'] }}</span>
                            </div>
                            <p class="pi-alert-card__text"><strong>Resumo:</strong> {{ $contradiction['summary'] }}</p>
                            <p class="pi-alert-card__text"><strong>Risco:</strong> {{ $contradiction['risk'] }}</p>
                            <p class="pi-alert-card__text"><strong>Decisão:</strong> {{ $contradiction['decision'] }}</p>
                        </div>
                    @empty
                        <p class="pi-muted-text">Nenhuma contradição detectada ainda.</p>
                    @endforelse
                </div>
            </article>
        </div>

        <div class="pi-bottom-grid">
            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Prompt pronto para enviar ao ChatGPT</h3>
                    <p class="pi-section-text">Copie este conteúdo e envie aqui quando quiser que eu analise os resultados.</p>
                </div>

                <textarea readonly rows="12" class="pi-input pi-textarea pi-textarea--report">{{ data_get($report, 'prompt_for_chatgpt') }}</textarea>
            </article>

            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Últimos comentários arquivados</h3>
                </div>

                <div class="pi-list">
                    @forelse($latestComments as $comment)
                        <div class="pi-comment-card">
                            <div class="pi-comment-card__meta">
                                <strong>{{ $comment['competitor'] }}</strong>
                                <span>•</span>
                                <span>{{ $comment['source'] }}</span>
                                @if($comment['rating'])
                                    <span>• {{ $comment['rating'] }} estrela(s)</span>
                                @endif
                                <span>• {{ $comment['created_at'] }}</span>
                            </div>

                            <p class="pi-comment-card__text">{{ $comment['text'] }}</p>

                            <div class="pi-badge-group">
                                <span class="pi-badge">{{ $comment['sentiment'] }}</span>
                                <span class="pi-badge pi-badge--info">{{ $comment['category'] }}</span>
                                @if(! empty($comment['impact']))
                                    <span class="pi-badge pi-badge--warning">Impacto {{ $comment['impact'] }}</span>
                                @endif
                            </div>

                            @if(! empty($comment['real_pain']))
                                <p class="pi-comment-card__insight"><strong>Dor real:</strong> {{ $comment['real_pain'] }}</p>
                            @endif

                            @if(! empty($comment['insight']))
                                <p class="pi-comment-card__insight"><strong>Insight:</strong> {{ $comment['insight'] }}</p>
                            @endif

                            @if(! empty($comment['market_learning']))
                                <p class="pi-comment-card__insight"><strong>Aprendizado:</strong> {{ $comment['market_learning'] }}</p>
                            @endif

                            @if(! empty($comment['recommended_action']))
                                <p class="pi-comment-card__insight"><strong>Ação:</strong> {{ $comment['recommended_action'] }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="pi-muted-text">Nenhum comentário importado ainda.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
</x-filament-panels::page>
