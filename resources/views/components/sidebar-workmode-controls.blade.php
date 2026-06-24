@php
    use App\Support\AccountingProfileNavigation;
    use App\Support\ProductProfileNavigation;

    $authUser = auth()->user();
    $accountingProfiles = AccountingProfileNavigation::browserPayload();
    $currentAccountingProfile = AccountingProfileNavigation::currentProfileKey($authUser);
    $accountingAdministrativeLabels = AccountingProfileNavigation::administrativeLabelsFor($authUser);

    $workmodes = ProductProfileNavigation::profiles();
    $defaultWorkmode = ProductProfileNavigation::defaultProfile();
    $navigationProfiles = ProductProfileNavigation::browserPayload();
@endphp

<div class="prazzu-sidebar-controls">
    <button type="button" id="sidebarToggleBtn" class="prazzu-sidebar-toggle" aria-label="Abrir ou fechar menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="prazzu-workmode" data-workmode-custom-select>
        <select id="workmodeSelector" class="prazzu-workmode-selector" aria-label="Selecionar perfil de produto">
            @foreach ($workmodes as $value => $mode)
                <option value="{{ $value }}">{{ $mode['label'] }}</option>
            @endforeach
        </select>

        <button
            type="button"
            class="prazzu-workmode-trigger"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-controls="workmodeSelectorDropdown"
        >
            <span class="prazzu-workmode-trigger-icon" aria-hidden="true">
                <svg viewBox="0 0 20 20" fill="none" focusable="false">
                    <path d="M4.75 5.75A2.75 2.75 0 0 1 7.5 3h5A2.75 2.75 0 0 1 15.25 5.75v8.5A2.75 2.75 0 0 1 12.5 17h-5a2.75 2.75 0 0 1-2.75-2.75v-8.5Z" stroke="currentColor" stroke-width="1.45"/>
                    <path d="M7.25 7.25h5.5M7.25 10h5.5M7.25 12.75h3.25" stroke="currentColor" stroke-width="1.45" stroke-linecap="round"/>
                </svg>
            </span>

            <span class="prazzu-workmode-trigger-text">
                <span class="prazzu-workmode-eyebrow">Perfil de produto</span>
                <strong data-workmode-selected-label>{{ $workmodes[$defaultWorkmode]['label'] ?? 'Perfil Completo' }}</strong>
                <small data-workmode-selected-hint>{{ $workmodes[$defaultWorkmode]['hint'] ?? 'Todos os módulos do sistema' }}</small>
            </span>

            <span class="prazzu-workmode-chevron" aria-hidden="true">
                <svg viewBox="0 0 20 20" fill="none" focusable="false">
                    <path d="M5.75 7.75 10 12.25l4.25-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </button>

        <div id="workmodeSelectorDropdown" class="prazzu-workmode-menu" role="listbox" tabindex="-1">
            @foreach ($workmodes as $value => $mode)
                <button
                    type="button"
                    class="prazzu-workmode-option"
                    role="option"
                    data-workmode-value="{{ $value }}"
                    data-workmode-label="{{ $mode['label'] }}"
                    data-workmode-hint="{{ $mode['hint'] }}"
                    data-workmode-description="{{ $mode['description'] ?? $mode['hint'] }}"
                    aria-selected="{{ $value === $defaultWorkmode ? 'true' : 'false' }}"
                >
                    <span>
                        <strong>{{ $mode['label'] }}</strong>
                        <small>{{ $mode['hint'] }}</small>
                    </span>

                    <svg class="prazzu-workmode-check" viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false">
                        <path d="m5 10.5 3.1 3.1L15 6.8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            @endforeach
        </div>
    </div>
</div>

<script>
    window.PrazzuProductProfiles = @json($navigationProfiles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    window.PrazzuDefaultProductProfile = @json($defaultWorkmode, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    window.PrazzuAccountingProfiles = @json($accountingProfiles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    window.PrazzuCurrentAccountingProfile = @json($currentAccountingProfile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    window.PrazzuAccountingAdministrativeLabels = @json($accountingAdministrativeLabels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('sidebarToggleBtn');
        const selector = document.getElementById('workmodeSelector');
        const customSelect = document.querySelector('[data-workmode-custom-select]');
        const trigger = customSelect?.querySelector('.prazzu-workmode-trigger');
        const selectedLabel = customSelect?.querySelector('[data-workmode-selected-label]');
        const selectedHint = customSelect?.querySelector('[data-workmode-selected-hint]');
        const options = customSelect ? Array.from(customSelect.querySelectorAll('.prazzu-workmode-option')) : [];
        const profiles = window.PrazzuProductProfiles || {};
        const defaultMode = window.PrazzuDefaultProductProfile || 'completo';
        const accountingProfiles = window.PrazzuAccountingProfiles || {};
        const currentAccountingProfile = window.PrazzuCurrentAccountingProfile || null;
        const accountingAdministrativeLabels = Array.isArray(window.PrazzuAccountingAdministrativeLabels)
            ? window.PrazzuAccountingAdministrativeLabels
            : [];

        const normalize = (value) => String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();

        const administrativeAllowed = new Set(accountingAdministrativeLabels.map(normalize));

        const navItemSelector = [
            '.fi-sidebar-item',
            'li',
            '[data-sidebar-item]',
            '[data-nav-item]'
        ].join(',');

        const getSidebar = () => document.querySelector('.fi-sidebar-nav')
            || document.querySelector('aside nav')
            || document.querySelector('aside');

        const getLinkLabel = (link) => {
            const labelNode = link.querySelector('.fi-sidebar-item-label')
                || link.querySelector('[class*="label"]')
                || link.querySelector('span:not([aria-hidden="true"])');

            return (labelNode?.textContent || link.getAttribute('aria-label') || link.textContent || '').trim();
        };

        const getGroupLabel = (group) => {
            const labelNode = group.querySelector('.fi-sidebar-group-label')
                || group.querySelector('[class*="group-label"]')
                || group.querySelector('button span:not([aria-hidden="true"])')
                || group.querySelector('span:not([aria-hidden="true"])');

            return (group.dataset.groupLabel || labelNode?.textContent || '').trim();
        };

        const buildAllowedLabels = (profile) => {
            const visibleLabels = Array.isArray(profile.visibleLabels) ? profile.visibleLabels : ['*'];
            const aliases = profile.aliases && typeof profile.aliases === 'object' ? profile.aliases : {};
            const allowed = new Set(visibleLabels.map(normalize));

            Object.entries(aliases).forEach(([alias, original]) => {
                if (allowed.has(normalize(original))) {
                    allowed.add(normalize(alias));
                }
            });

            return allowed;
        };

        const getNavContainer = (link) => link.closest(navItemSelector) || link;

        const resetProfileVisibility = () => {
            document.querySelectorAll('.prazzu-profile-hidden, .prazzu-accounting-profile-hidden, .prazzu-profile-empty-group').forEach((item) => {
                item.classList.remove('prazzu-profile-hidden', 'prazzu-accounting-profile-hidden', 'prazzu-profile-empty-group');
                item.removeAttribute('aria-hidden');
            });
        };

        const updateEmptyGroups = () => {
            const sidebar = getSidebar();

            if (!sidebar) {
                return;
            }

            const groups = Array.from(sidebar.querySelectorAll('[data-group-label], .fi-sidebar-group'));

            groups.forEach((group) => {
                const visibleLinks = Array.from(group.querySelectorAll('a')).filter((link) => {
                    const container = getNavContainer(link);
                    return !container.classList.contains('prazzu-profile-hidden')
                        && !container.classList.contains('prazzu-accounting-profile-hidden');
                });

                if (!visibleLinks.length) {
                    group.classList.add('prazzu-profile-empty-group');
                    group.setAttribute('aria-hidden', 'true');
                }
            });
        };


        const applyAccountingProfileNavigation = () => {
            if (!currentAccountingProfile) {
                return;
            }

            const profile = accountingProfiles[currentAccountingProfile];
            const sidebar = getSidebar();

            if (!profile || !sidebar) {
                return;
            }

            const allowed = buildAllowedLabels(profile);

            accountingAdministrativeLabels.forEach((label) => allowed.add(normalize(label)));

            Array.from(sidebar.querySelectorAll('a')).forEach((link) => {
                const container = getNavContainer(link);

                if (container.classList.contains('prazzu-profile-hidden')) {
                    return;
                }

                const label = normalize(getLinkLabel(link));
                const group = link.closest('[data-group-label], .fi-sidebar-group');
                const groupLabel = normalize(group ? getGroupLabel(group) : '');

                if (!label) {
                    return;
                }

                if (allowed.has(label) || allowed.has(groupLabel)) {
                    return;
                }

                container.classList.add('prazzu-accounting-profile-hidden');
                container.setAttribute('aria-hidden', 'true');
            });
        };

        const applyProductProfileNavigation = (mode) => {
            resetProfileVisibility();

            const profile = profiles[mode] || profiles[defaultMode] || {};
            const visibleLabels = Array.isArray(profile.visibleLabels) ? profile.visibleLabels : ['*'];
            const hiddenLabels = Array.isArray(profile.hiddenLabels) ? profile.hiddenLabels : [];
            const showEverything = visibleLabels.includes('*') || mode === 'completo';
            const sidebar = getSidebar();

            if (!sidebar) {
                return;
            }

            if (showEverything) {
                applyAccountingProfileNavigation();
                updateEmptyGroups();
                return;
            }

            const allowed = buildAllowedLabels(profile);
            const forcedHidden = new Set(hiddenLabels.map(normalize));

            Array.from(sidebar.querySelectorAll('a')).forEach((link) => {
                const label = normalize(getLinkLabel(link));
                const group = link.closest('[data-group-label], .fi-sidebar-group');
                const groupLabel = normalize(group ? getGroupLabel(group) : '');

                if (!label) {
                    return;
                }

                if (forcedHidden.has(label) || forcedHidden.has(groupLabel)) {
                    const container = getNavContainer(link);
                    container.classList.add('prazzu-profile-hidden');
                    container.setAttribute('aria-hidden', 'true');
                    return;
                }

                if (administrativeAllowed.has(label) || administrativeAllowed.has(groupLabel)) {
                    return;
                }

                if (allowed.has(label) || allowed.has(groupLabel)) {
                    return;
                }

                const container = getNavContainer(link);
                container.classList.add('prazzu-profile-hidden');
                container.setAttribute('aria-hidden', 'true');
            });

            applyAccountingProfileNavigation();
            updateEmptyGroups();
        };

        const closeWorkmodeMenu = () => {
            if (!customSelect || !trigger) {
                return;
            }

            customSelect.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        };

        const openWorkmodeMenu = () => {
            if (!customSelect || !trigger) {
                return;
            }

            customSelect.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        };

        const setWorkmode = (mode, shouldPersist = true) => {
            if (!selector) {
                return;
            }

            const option = options.find((item) => item.dataset.workmodeValue === mode);
            const normalizedMode = option ? mode : defaultMode;
            const normalizedOption = option || options.find((item) => item.dataset.workmodeValue === defaultMode);
            const profile = profiles[normalizedMode] || profiles[defaultMode] || {};

            document.documentElement.setAttribute('data-workmode', normalizedMode);
            document.documentElement.setAttribute('data-product-profile', normalizedMode);

            if (currentAccountingProfile) {
                document.documentElement.setAttribute('data-accounting-profile', currentAccountingProfile);
            } else {
                document.documentElement.removeAttribute('data-accounting-profile');
            }
            selector.value = normalizedMode;

            if (selectedLabel && normalizedOption) {
                selectedLabel.textContent = normalizedOption.dataset.workmodeLabel || normalizedOption.textContent.trim();
            }

            if (selectedHint) {
                const visibleCount = Number.isInteger(profile.visibleCount) ? `${profile.visibleCount} abas` : 'todas as abas';
                selectedHint.textContent = `${profile.hint || normalizedOption?.dataset.workmodeHint || ''} · ${visibleCount}`;
            }

            options.forEach((item) => {
                const isSelected = item.dataset.workmodeValue === normalizedMode;
                item.classList.toggle('is-selected', isSelected);
                item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });

            applyProductProfileNavigation(normalizedMode);

            if (shouldPersist) {
                localStorage.setItem('workmode', normalizedMode);
                localStorage.setItem('productProfile', normalizedMode);
            }
        };

        const scheduleProfileRefresh = () => {
            const currentMode = selector?.value || localStorage.getItem('productProfile') || defaultMode;
            window.requestAnimationFrame(() => applyProductProfileNavigation(currentMode));
        };

        if (toggle) {
            toggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('sidebar-collapsed');
                closeWorkmodeMenu();
            });
        }

        if (selector) {
            const legacyModeMap = {
                default: 'completo',
                contabil: 'escritorio_contabil',
                financeiro: 'operacional',
                documentos: 'operacional',
                clientes: 'operacional',
                governanca: 'completo',
                relatorios: 'completo',
            };
            const legacyMode = localStorage.getItem('workmode');
            const savedMode = localStorage.getItem('productProfile') || legacyModeMap[legacyMode] || legacyMode || defaultMode;

            setWorkmode(savedMode, false);

            selector.addEventListener('change', (e) => {
                setWorkmode(e.target.value);
            });
        }

        const sidebar = getSidebar();

        if (sidebar && 'MutationObserver' in window) {
            const observer = new MutationObserver(scheduleProfileRefresh);
            observer.observe(sidebar, { childList: true, subtree: true });
        }

        if (trigger) {
            trigger.addEventListener('click', (event) => {
                event.stopPropagation();

                if (customSelect.classList.contains('is-open')) {
                    closeWorkmodeMenu();
                    return;
                }

                openWorkmodeMenu();
            });

            trigger.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    openWorkmodeMenu();
                    options.find((item) => item.getAttribute('aria-selected') === 'true')?.focus();
                }
            });
        }

        options.forEach((option) => {
            option.addEventListener('click', (event) => {
                event.stopPropagation();

                setWorkmode(option.dataset.workmodeValue || defaultMode);
                selector?.dispatchEvent(new Event('change', { bubbles: true }));
                closeWorkmodeMenu();
                trigger?.focus();
            });

            option.addEventListener('keydown', (event) => {
                const currentIndex = options.indexOf(option);

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    options[(currentIndex + 1) % options.length]?.focus();
                }

                if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    options[(currentIndex - 1 + options.length) % options.length]?.focus();
                }

                if (event.key === 'Escape') {
                    event.preventDefault();
                    closeWorkmodeMenu();
                    trigger?.focus();
                }
            });
        });

        document.addEventListener('click', (event) => {
            if (customSelect && !customSelect.contains(event.target)) {
                closeWorkmodeMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeWorkmodeMenu();
            }
        });
    });
</script>
