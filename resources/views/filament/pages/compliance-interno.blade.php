<x-filament-panels::page>

    <div class="compliance-page compliance-page-interno compliance-page-interno-workflow" data-compliance-interno>
        <section class="compliance-hero compliance-hero-interno">
            <div>
                <span>GOVERNANÇA INTERNA</span>
                <h1>Central de trabalho interna</h1>
                <p>Veja primeiro o que exige ação, acompanhe o que está em andamento e consulte documentos ou evidências recentes sem precisar interpretar códigos técnicos.</p>
            </div>
        </section>

        <section class="compliance-usage-guide" aria-label="Como usar esta central">
            <article>
                <strong>1</strong>
                <div>
                    <span>Comece pelas pendências</span>
                    <p>Use a coluna “Precisa da sua atenção” para resolver primeiro o que exige decisão ou acompanhamento imediato.</p>
                </div>
            </article>
            <article>
                <strong>2</strong>
                <div>
                    <span>Filtre sem se perder</span>
                    <p>Clique nos cards ou use a busca para encontrar registros por empresa, responsável, status, prioridade ou descrição.</p>
                </div>
            </article>
            <article>
                <strong>3</strong>
                <div>
                    <span>Abra o fluxo certo</span>
                    <p>Use “Ver detalhes” para entender o contexto e os botões de ação para ir direto à tela correta do sistema.</p>
                </div>
            </article>
        </section>

        <section class="compliance-urgency-guide" aria-label="Legenda de urgência operacional">
            <article class="danger">
                <strong>Urgente</strong>
                <span>Resolva primeiro</span>
            </article>
            <article class="warning">
                <strong>Atenção</strong>
                <span>Acompanhe de perto</span>
            </article>
            <article class="info">
                <strong>Acompanhar</strong>
                <span>Fluxo em andamento</span>
            </article>
            <article class="ok">
                <strong>Sem ação necessária</strong>
                <span>Consulta ou evidência</span>
            </article>
        </section>

        <section class="compliance-stats compliance-stats-actionable" aria-label="Resumo da governança interna">
            @foreach (($data['stats'] ?? []) as $stat)
                <button
                    type="button"
                    class="compliance-stat compliance-stat-filter"
                    data-interno-card-filter="1"
                    data-filter-type="{{ $stat['type'] ?? '' }}"
                    data-filter-status="{{ $stat['status'] ?? '' }}"
                    data-filter-priority="{{ $stat['priority'] ?? '' }}"
                    aria-label="Filtrar por {{ $stat['label'] }}"
                >
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small>{{ $stat['hint'] }}</small>
                </button>
            @endforeach
        </section>


        <section class="compliance-personal-focus" data-interno-section aria-label="Pendências priorizadas para o usuário">
            <article class="compliance-card compliance-personal-focus-card">
                <header>
                    <div>
                        <span class="compliance-section-kicker">{{ ($data['myPendings']['personalized'] ?? false) ? 'FOCO DO USUÁRIO' : 'FOCO OPERACIONAL' }}</span>
                        <h2>{{ $data['myPendings']['title'] ?? 'Pendências para resolver primeiro' }}</h2>
                        <p>{{ $data['myPendings']['subtitle'] ?? 'Veja primeiro os registros que exigem ação ou acompanhamento imediato.' }}</p>
                    </div>
                    <span class="compliance-badge {{ $data['myPendings']['tone'] ?? 'info' }}" data-section-badge>{{ $data['myPendings']['count'] ?? 0 }}</span>
                </header>

                <div class="compliance-personal-focus-grid">
                    <div class="compliance-personal-focus-summary">
                        <strong>{{ $data['myPendings']['count'] ?? 0 }}</strong>
                        <span>{{ ($data['myPendings']['count'] ?? 0) === 1 ? 'registro em destaque' : 'registros em destaque' }}</span>
                        <p>Comece por esta área antes de consultar o restante da governança interna.</p>
                    </div>

                    <div class="compliance-list compliance-personal-focus-list">
                        @forelse (($data['myPendings']['items'] ?? []) as $row)
                            @include('filament.pages.partials.compliance-interno-row', [
                                'row' => $row,
                                'defaultKindTone' => $row['kindTone'] ?? 'warning',
                                'defaultKind' => $row['kind'] ?? 'Pendência',
                                'context' => 'my-pendings-' . $loop->index,
                                'showNextStep' => true,
                            ])
                        @empty
                            <div class="compliance-empty" data-interno-original-empty>{{ $data['myPendings']['empty'] ?? 'Nenhuma pendência prioritária encontrada agora.' }}</div>
                        @endforelse

                        <div class="compliance-empty compliance-filter-empty" data-interno-filter-empty hidden>Nenhuma pendência em destaque corresponde aos filtros aplicados.</div>
                    </div>
                </div>
            </article>
        </section>

        <section class="compliance-filter-panel" aria-label="Filtros da governança interna">
            <div class="compliance-filter-panel-header">
                <div>
                    <span>BUSCA E FILTROS</span>
                    <h2>Encontre rapidamente o que precisa</h2>
                    <p>Filtre por tipo, status, prioridade ou pesquise por título, empresa, responsável e descrição.</p>
                </div>
                <button type="button" class="compliance-filter-clear" data-interno-clear>Limpar filtros</button>
            </div>

            <div class="compliance-filter-grid">
                <label>
                    <span>Pesquisar</span>
                    <input type="search" data-interno-search placeholder="Ex.: contrato, aprovação, empresa, responsável">
                </label>

                <label>
                    <span>Tipo</span>
                    <select data-interno-type>
                        <option value="">Todos os tipos</option>
                        @foreach (($data['filters']['types'] ?? []) as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>Status</span>
                    <select data-interno-status>
                        <option value="">Todos os status</option>
                        @foreach (($data['filters']['statuses'] ?? []) as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>Prioridade</span>
                    <select data-interno-priority>
                        <option value="">Todas as prioridades</option>
                        @foreach (($data['filters']['priorities'] ?? []) as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="compliance-filter-feedback" data-interno-feedback aria-live="polite">Mostrando todos os registros disponíveis nesta página.</div>
        </section>

        <section class="compliance-workflow-board" aria-label="Fluxo de trabalho da governança interna">
            @foreach (($data['workflow'] ?? []) as $sectionKey => $section)
                <article class="compliance-card compliance-workflow-column compliance-workflow-column-{{ $sectionKey }}" data-interno-section>
                    <header>
                        <div>
                            <span class="compliance-section-kicker" data-section-counter>{{ $section['count'] ?? 0 }} item(ns)</span>
                            <h2>{{ $section['title'] }}</h2>
                            <p>{{ $section['subtitle'] }}</p>
                        </div>
                        <span class="compliance-badge {{ $section['tone'] ?? 'info' }}" data-section-badge>{{ $section['count'] ?? 0 }}</span>
                    </header>

                    <div class="compliance-list compliance-workflow-list">
                        @forelse (($section['items'] ?? []) as $row)
                            @include('filament.pages.partials.compliance-interno-row', [
                                'row' => $row,
                                'defaultKindTone' => $row['kindTone'] ?? 'info',
                                'defaultKind' => $row['kind'] ?? 'Registro',
                                'context' => 'workflow-' . $sectionKey . '-' . $loop->index,
                                'showNextStep' => true,
                            ])
                        @empty
                            <div class="compliance-empty" data-interno-original-empty>{{ $section['empty'] ?? 'Nenhum item encontrado.' }}</div>
                        @endforelse

                        <div class="compliance-empty compliance-filter-empty" data-interno-filter-empty hidden>Nenhum item desta coluna corresponde aos filtros aplicados.</div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="compliance-section-title" aria-label="Consulta por tipo de registro">
            <div>
                <span>CONSULTA ORGANIZADA</span>
                <h2>Registros por tipo</h2>
                <p>Use esta área para consultar rapidamente as listas originais, agora com leitura mais amigável e separação clara por contexto.</p>
            </div>
        </section>

        <section class="compliance-grid equal compliance-interno-type-grid">
            <article class="compliance-card" data-interno-section>
                <header>
                    <div>
                        <h2>Aprovações</h2>
                        <p>Fluxos internos aguardando decisão ou já respondidos.</p>
                    </div>
                    <span class="compliance-badge info" data-section-badge>{{ count($data['approvals'] ?? []) }}</span>
                </header>

                <div class="compliance-list">
                    @forelse (($data['approvals'] ?? []) as $row)
                        @include('filament.pages.partials.compliance-interno-row', ['row' => $row, 'defaultKindTone' => 'warning', 'defaultKind' => 'Aprovação', 'context' => 'approvals-' . $loop->index, 'showNextStep' => true])
                    @empty
                        <div class="compliance-empty" data-interno-original-empty>Nenhuma aprovação interna encontrada.</div>
                    @endforelse
                    <div class="compliance-empty compliance-filter-empty" data-interno-filter-empty hidden>Nenhuma aprovação corresponde aos filtros aplicados.</div>
                </div>
            </article>

            <article class="compliance-card" data-interno-section>
                <header>
                    <div>
                        <h2>Assinaturas</h2>
                        <p>Evidências de ciência, aceite e assinatura eletrônica interna.</p>
                    </div>
                    <span class="compliance-badge info" data-section-badge>{{ count($data['signatures'] ?? []) }}</span>
                </header>

                <div class="compliance-list">
                    @forelse (($data['signatures'] ?? []) as $row)
                        @include('filament.pages.partials.compliance-interno-row', ['row' => $row, 'defaultKindTone' => 'warning', 'defaultKind' => 'Assinatura', 'context' => 'signatures-' . $loop->index, 'showNextStep' => true])
                    @empty
                        <div class="compliance-empty" data-interno-original-empty>Nenhuma assinatura interna encontrada.</div>
                    @endforelse
                    <div class="compliance-empty compliance-filter-empty" data-interno-filter-empty hidden>Nenhuma assinatura corresponde aos filtros aplicados.</div>
                </div>
            </article>
        </section>

        <section class="compliance-grid equal compliance-interno-type-grid">
            <article class="compliance-card" data-interno-section>
                <header>
                    <div>
                        <h2>Documentos</h2>
                        <p>Documentos, atas, links úteis e wikis recentes.</p>
                    </div>
                    <span class="compliance-badge info" data-section-badge>{{ count($data['documents'] ?? []) }}</span>
                </header>

                <div class="compliance-list">
                    @forelse (($data['documents'] ?? []) as $row)
                        @include('filament.pages.partials.compliance-interno-row', ['row' => $row, 'defaultKindTone' => 'info', 'defaultKind' => 'Documento', 'context' => 'documents-' . $loop->index, 'showNextStep' => true])
                    @empty
                        <div class="compliance-empty" data-interno-original-empty>Nenhum documento interno encontrado.</div>
                    @endforelse
                    <div class="compliance-empty compliance-filter-empty" data-interno-filter-empty hidden>Nenhum documento corresponde aos filtros aplicados.</div>
                </div>
            </article>

            <article class="compliance-card" data-interno-section>
                <header>
                    <div>
                        <h2>Solicitações abertas</h2>
                        <p>Pedidos que ainda precisam de acompanhamento ou resposta.</p>
                    </div>
                    <span class="compliance-badge info" data-section-badge>{{ count($data['requests'] ?? []) }}</span>
                </header>

                <div class="compliance-list">
                    @forelse (($data['requests'] ?? []) as $row)
                        @include('filament.pages.partials.compliance-interno-row', ['row' => $row, 'defaultKindTone' => 'info', 'defaultKind' => 'Solicitação', 'context' => 'requests-' . $loop->index, 'showNextStep' => true])
                    @empty
                        <div class="compliance-empty" data-interno-original-empty>Nenhuma solicitação aberta encontrada.</div>
                    @endforelse
                    <div class="compliance-empty compliance-filter-empty" data-interno-filter-empty hidden>Nenhuma solicitação corresponde aos filtros aplicados.</div>
                </div>
            </article>
        </section>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-compliance-interno]');

            if (! root) {
                return;
            }

            const searchInput = root.querySelector('[data-interno-search]');
            const typeSelect = root.querySelector('[data-interno-type]');
            const statusSelect = root.querySelector('[data-interno-status]');
            const prioritySelect = root.querySelector('[data-interno-priority]');
            const clearButton = root.querySelector('[data-interno-clear]');
            const feedback = root.querySelector('[data-interno-feedback]');
            const rows = Array.from(root.querySelectorAll('[data-interno-row]'));
            const sections = Array.from(root.querySelectorAll('[data-interno-section]'));
            const cards = Array.from(root.querySelectorAll('[data-interno-card-filter]'));
            const flowActions = Array.from(root.querySelectorAll('[data-interno-flow-action]'));
            const detailActions = Array.from(root.querySelectorAll('[data-interno-detail-action]'));
            const feedbackStorageKey = 'compliance-interno-feedback';

            const normalize = (value) => (value || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();

            const ensureToast = () => {
                let toast = root.querySelector('[data-interno-toast]');

                if (toast) {
                    return toast;
                }

                toast = document.createElement('div');
                toast.className = 'compliance-interno-toast';
                toast.dataset.internoToast = '1';
                toast.setAttribute('role', 'status');
                toast.setAttribute('aria-live', 'polite');
                toast.hidden = true;
                root.prepend(toast);

                return toast;
            };

            const showToast = (message, tone = 'info') => {
                const toast = ensureToast();

                toast.textContent = message;
                toast.dataset.tone = tone;
                toast.hidden = false;
                toast.classList.remove('is-visible');

                window.requestAnimationFrame(() => toast.classList.add('is-visible'));

                window.clearTimeout(toast._internoTimer);
                toast._internoTimer = window.setTimeout(() => {
                    toast.classList.remove('is-visible');

                    window.setTimeout(() => {
                        toast.hidden = true;
                    }, 220);
                }, 4200);
            };

            const storedFeedback = window.sessionStorage.getItem(feedbackStorageKey);

            if (storedFeedback) {
                window.sessionStorage.removeItem(feedbackStorageKey);
                showToast(storedFeedback, 'success');
            }

            const applyFilters = () => {
                const query = normalize(searchInput?.value || '');
                const type = typeSelect?.value || '';
                const status = statusSelect?.value || '';
                const priority = prioritySelect?.value || '';
                let visibleTotal = 0;

                rows.forEach((row) => {
                    const rowText = normalize(row.dataset.search || row.textContent || '');
                    const rowType = row.dataset.type || '';
                    const rowStatus = row.dataset.status || '';
                    const rowPriority = row.dataset.priority || '';

                    const visible = (! query || rowText.includes(query))
                        && (! type || rowType === type)
                        && (! status || rowStatus === status)
                        && (! priority || rowPriority === priority);

                    row.hidden = ! visible;

                    if (visible) {
                        visibleTotal += 1;
                    }
                });

                sections.forEach((section) => {
                    const sectionRows = Array.from(section.querySelectorAll('[data-interno-row]'));
                    const visibleRows = sectionRows.filter((row) => ! row.hidden).length;
                    const filterEmpty = section.querySelector('[data-interno-filter-empty]');
                    const originalEmpty = section.querySelector('[data-interno-original-empty]');
                    const badge = section.querySelector('[data-section-badge]');
                    const counter = section.querySelector('[data-section-counter]');

                    if (filterEmpty) {
                        filterEmpty.hidden = sectionRows.length === 0 || visibleRows > 0;
                    }

                    if (originalEmpty) {
                        originalEmpty.hidden = sectionRows.length > 0;
                    }

                    if (badge) {
                        badge.textContent = visibleRows;
                    }

                    if (counter) {
                        counter.textContent = `${visibleRows} item(ns)`;
                    }
                });

                cards.forEach((card) => {
                    card.classList.toggle('is-active',
                        (!! type && card.dataset.filterType === type)
                        || (!! status && card.dataset.filterStatus === status)
                        || (!! priority && card.dataset.filterPriority === priority)
                    );
                });

                if (feedback) {
                    const hasFilters = !! (query || type || status || priority);
                    feedback.textContent = hasFilters
                        ? `${visibleTotal} registro(s) encontrado(s) com os filtros aplicados.`
                        : 'Mostrando todos os registros disponíveis nesta página.';
                }
            };

            [searchInput, typeSelect, statusSelect, prioritySelect].forEach((field) => {
                field?.addEventListener('input', applyFilters);
                field?.addEventListener('change', applyFilters);
            });

            clearButton?.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (typeSelect) typeSelect.value = '';
                if (statusSelect) statusSelect.value = '';
                if (prioritySelect) prioritySelect.value = '';
                applyFilters();
            });

            cards.forEach((card) => {
                card.addEventListener('click', () => {
                    const nextType = card.dataset.filterType || '';
                    const nextStatus = card.dataset.filterStatus || '';
                    const nextPriority = card.dataset.filterPriority || '';

                    const currentType = typeSelect?.value || '';
                    const shouldClearCardFilter = currentType === nextType
                        && (! nextStatus || statusSelect?.value === nextStatus)
                        && (! nextPriority || prioritySelect?.value === nextPriority);

                    if (typeSelect) typeSelect.value = shouldClearCardFilter ? '' : nextType;
                    if (statusSelect) statusSelect.value = shouldClearCardFilter ? '' : nextStatus;
                    if (prioritySelect) prioritySelect.value = shouldClearCardFilter ? '' : nextPriority;

                    applyFilters();
                    root.querySelector('.compliance-filter-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            });

            flowActions.forEach((action) => {
                action.addEventListener('click', () => {
                    const label = action.dataset.internoActionLabel || 'Abrir fluxo';
                    const recordTitle = action.dataset.internoRecordTitle || 'registro selecionado';
                    const message = `${label}: abrindo o fluxo de “${recordTitle}”.`;

                    action.classList.add('is-loading');
                    action.setAttribute('aria-busy', 'true');
                    root.classList.add('has-interno-action-feedback');
                    showToast(message, 'info');

                    try {
                        window.sessionStorage.setItem(feedbackStorageKey, 'Você voltou para a Governança Interna. Os dados exibidos foram recarregados conforme o estado atual do sistema.');
                    } catch (error) {
                        // Navegação continua normalmente mesmo se o navegador bloquear sessionStorage.
                    }
                }, { passive: true });
            });

            detailActions.forEach((action) => {
                action.addEventListener('click', () => {
                    action.classList.add('is-loading');
                    action.setAttribute('aria-busy', 'true');
                    showToast('Carregando os detalhes do registro selecionado.', 'info');

                    window.setTimeout(() => {
                        action.classList.remove('is-loading');
                        action.removeAttribute('aria-busy');
                    }, 1600);
                }, { passive: true });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    const toast = root.querySelector('[data-interno-toast]');

                    if (toast) {
                        toast.classList.remove('is-visible');
                        toast.hidden = true;
                    }
                }
            });

            applyFilters();
        })();
    </script>
</x-filament-panels::page>
