@php
    use Illuminate\Support\Facades\Route;

    $globalSearchEndpoint = Route::has('admin.global-search')
        ? route('admin.global-search')
        : url('/admin/busca-global');
@endphp

<div
    x-data="prazzuGlobalSearch({ endpoint: @js($globalSearchEndpoint) })"
    x-on:keydown.window="handleShortcut($event)"
    class="prazzu-global-search"
>
    <button type="button" class="prazzu-global-search-trigger" x-on:click="openSearch()" aria-label="Abrir busca global">
        <i class="bi bi-search"></i>
        <span>Buscar no sistema</span>
        <kbd>Ctrl K</kbd>
    </button>

    <div x-cloak x-show="open" x-transition.opacity class="prazzu-global-search-backdrop" x-on:click.self="closeSearch()">
        <section class="prazzu-global-search-modal" role="dialog" aria-modal="true" aria-label="Busca global">
            <header class="prazzu-global-search-header">
                <div class="prazzu-global-search-input-wrap">
                    <i class="bi bi-search"></i>
                    <input
                        x-ref="input"
                        x-model.debounce.300ms="query"
                        x-on:input.debounce.300ms="runSearch()"
                        x-on:keydown.escape.prevent="closeSearch()"
                        type="search"
                        placeholder="Busque documentos, clientes, contratos, usuários, pendências, anexos, códigos e comentários..."
                        autocomplete="off"
                    />
                </div>
                <button type="button" class="prazzu-global-search-close" x-on:click="closeSearch()" aria-label="Fechar busca">
                    <i class="bi bi-x-lg"></i>
                </button>
            </header>

            <div class="prazzu-global-search-body">
                <template x-if="query.trim().length < 2">
                    <div class="prazzu-global-search-start">
                        <section class="prazzu-global-search-empty is-compact">
                            <div class="prazzu-global-search-empty-icon"><i class="bi bi-command"></i></div>
                            <div>
                                <h3>Busca global pronta para usar</h3>
                                <p>Digite pelo menos 2 caracteres para localizar registros reais do sistema ou use um atalho abaixo.</p>
                            </div>
                        </section>

                        <section class="prazzu-global-search-shortcuts" x-show="searchShortcuts.length">
                            <div class="prazzu-global-search-section-heading">
                                <strong>Atalhos de busca</strong>
                                <span>clique para pesquisar rápido</span>
                            </div>
                            <div class="prazzu-global-search-shortcut-grid">
                                <template x-for="shortcut in searchShortcuts" :key="shortcut.label">
                                    <button type="button" class="prazzu-global-search-shortcut" x-on:click="applyShortcut(shortcut.query)">
                                        <i :class="['bi', shortcut.icon]"></i>
                                        <span x-text="shortcut.label"></span>
                                    </button>
                                </template>
                            </div>
                        </section>

                        <section class="prazzu-global-search-quicklinks" x-show="quickLinks.length">
                            <div class="prazzu-global-search-section-heading">
                                <strong>Ações rápidas</strong>
                                <span>atalhos úteis do dia a dia</span>
                            </div>
                            <div class="prazzu-global-search-quicklinks-grid">
                                <template x-for="link in quickLinks" :key="link.label">
                                    <a :href="link.url" class="prazzu-global-search-quicklink">
                                        <i :class="['bi', link.icon]"></i>
                                        <span x-text="link.label"></span>
                                    </a>
                                </template>
                            </div>
                        </section>

                        <template x-if="recentGroups.length">
                            <section class="prazzu-global-search-recent">
                                <div class="prazzu-global-search-section-heading">
                                    <strong>Resultados recentes</strong>
                                    <span>últimos registros movimentados</span>
                                </div>

                                <template x-for="group in recentGroups" :key="group.title">
                                    <div class="prazzu-global-search-group is-recent">
                                        <div class="prazzu-global-search-group-title">
                                            <span><i :class="['bi', group.icon]"></i></span>
                                            <div>
                                                <h4 x-text="group.title"></h4>
                                                <p x-text="group.description"></p>
                                            </div>
                                        </div>

                                        <div class="prazzu-global-search-list">
                                            <template x-for="item in group.items" :key="item.type + item.title + item.url">
                                                <a :href="item.url" class="prazzu-global-search-result">
                                                    <span :class="['prazzu-global-search-result-icon', 'is-' + item.color]"><i :class="['bi', item.icon]"></i></span>
                                                    <span class="prazzu-global-search-result-content">
                                                        <strong x-text="item.title"></strong>
                                                        <small x-text="item.subtitle"></small>
                                                    </span>
                                                    <span class="prazzu-global-search-result-meta">
                                                        <b x-text="item.type"></b>
                                                        <em x-show="item.meta" x-text="item.meta"></em>
                                                    </span>
                                                </a>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </section>
                        </template>
                    </div>
                </template>

                <template x-if="loading">
                    <div class="prazzu-global-search-loading">
                        <span></span><span></span><span></span>
                        <strong>Buscando registros...</strong>
                    </div>
                </template>

                <template x-if="! loading && errorMessage">
                    <div class="prazzu-global-search-empty is-error">
                        <div class="prazzu-global-search-empty-icon"><i class="bi bi-exclamation-triangle"></i></div>
                        <h3>Não foi possível concluir a busca</h3>
                        <p x-text="errorMessage"></p>
                        <button type="button" class="prazzu-global-search-retry" x-on:click="runSearch()">Tentar novamente</button>
                    </div>
                </template>

                <template x-if="! loading && ! errorMessage && query.trim().length >= 2 && total === 0">
                    <div class="prazzu-global-search-empty">
                        <div class="prazzu-global-search-empty-icon"><i class="bi bi-search-heart"></i></div>
                        <h3>Nada encontrado</h3>
                        <p>Tente buscar por nome do cliente, título da pendência, número de contrato, responsável, e-mail, código, anexo ou comentário.</p>
                    </div>
                </template>

                <template x-if="! loading && ! errorMessage && total > 0">
                    <div class="prazzu-global-search-results">
                        <template x-if="partialErrorMessage">
                            <div class="prazzu-global-search-partial-warning">
                                <i class="bi bi-info-circle"></i>
                                <span x-text="partialErrorMessage"></span>
                            </div>
                        </template>

                        <div class="prazzu-global-search-summary">
                            <div>
                                <strong x-text="total"></strong>
                                <span>resultado(s) encontrado(s)</span>
                            </div>
                            <em>agrupado por tipo para você não se perder</em>
                        </div>

                        <template x-for="group in groups" :key="group.title">
                            <div class="prazzu-global-search-group">
                                <div class="prazzu-global-search-group-title">
                                    <span><i :class="['bi', group.icon]"></i></span>
                                    <div>
                                        <h4 x-text="group.title"></h4>
                                        <p x-text="group.description"></p>
                                    </div>
                                </div>

                                <div class="prazzu-global-search-list">
                                    <template x-for="item in group.items" :key="item.type + item.title + item.url">
                                        <a :href="item.url" class="prazzu-global-search-result">
                                            <span :class="['prazzu-global-search-result-icon', 'is-' + item.color]"><i :class="['bi', item.icon]"></i></span>
                                            <span class="prazzu-global-search-result-content">
                                                <strong x-text="item.title"></strong>
                                                <small x-text="item.subtitle"></small>
                                            </span>
                                            <span class="prazzu-global-search-result-meta">
                                                <b x-text="item.type"></b>
                                                <em x-show="item.meta" x-text="item.meta"></em>
                                            </span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </section>
    </div>
</div>

<script>
    window.prazzuGlobalSearch = function ({ endpoint }) {
        return {
            endpoint,
            open: false,
            query: '',
            loading: false,
            groups: [],
            recentGroups: [],
            total: 0,
            quickLinks: [],
            searchShortcuts: [],
            abortController: null,
            errorMessage: '',
            partialErrorMessage: '',

            init() {
                this.runSearch();
            },

            openSearch() {
                this.open = true;
                this.$nextTick(() => this.$refs.input?.focus());
            },

            closeSearch() {
                this.open = false;
            },

            applyShortcut(value) {
                this.query = value || '';
                this.runSearch();
                this.$nextTick(() => this.$refs.input?.focus());
            },

            handleShortcut(event) {
                const isMac = navigator.platform.toUpperCase().includes('MAC');
                const pressed = (isMac ? event.metaKey : event.ctrlKey) && event.key.toLowerCase() === 'k';

                if (! pressed) {
                    return;
                }

                event.preventDefault();
                this.openSearch();
            },

            async runSearch() {
                if (this.abortController) {
                    this.abortController.abort();
                }

                this.abortController = new AbortController();
                this.loading = this.query.trim().length >= 2;
                this.errorMessage = '';
                this.partialErrorMessage = '';

                try {
                    const url = new URL(this.endpoint, window.location.origin);
                    url.searchParams.set('q', this.query.trim());

                    const response = await fetch(url.toString(), {
                        headers: { 'Accept': 'application/json' },
                        signal: this.abortController.signal,
                    });

                    const data = await response.json().catch(() => ({}));

                    if (! response.ok) {
                        throw new Error(data.message || 'A busca encontrou uma instabilidade. Nenhum dado foi alterado.');
                    }
                    this.groups = data.groups || [];
                    this.recentGroups = data.recent_groups || [];
                    this.total = data.total || 0;
                    this.quickLinks = data.quick_links || [];
                    this.searchShortcuts = data.search_shortcuts || [];
                    this.partialErrorMessage = data.partial_error_message || '';
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        this.groups = [];
                        this.recentGroups = [];
                        this.total = 0;
                        this.errorMessage = error.message || 'A busca encontrou uma instabilidade. Tente novamente em instantes.';
                    }
                } finally {
                    this.loading = false;
                }
            },
        };
    };
</script>
