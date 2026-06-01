<div class="prazzu-workmode-selector" aria-label="Seletor de modo de trabalho">
    <label class="prazzu-workmode-selector__label" for="workmodeSelector">
        Modo
    </label>

    <select id="workmodeSelector" class="prazzu-workmode-selector__select">
        <option value="default">Geral</option>
        <option value="rh">RH</option>
        <option value="contabil">Contábil</option>
        <option value="financeiro">Financeiro</option>
        <option value="documentos">Documentos</option>
        <option value="clientes">Clientes</option>
        <option value="governanca">Governança</option>
        <option value="relatorios">Relatórios</option>
    </select>
</div>

<style>
    .prazzu-workmode-selector {
        position: fixed;
        top: 16px;
        left: 70px;
        z-index: 99999;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 42px;
        padding: 4px 8px;
        border: 1px solid rgba(6, 55, 109, .10);
        border-radius: 12px;
        background: rgba(255, 255, 255, .94);
        box-shadow: 0 12px 30px rgba(6, 55, 109, .12);
        backdrop-filter: blur(12px);
    }

    .prazzu-workmode-selector__label {
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .prazzu-workmode-selector__select {
        height: 32px;
        min-width: 122px;
        border: 0;
        border-radius: 8px;
        background: #f8fafc;
        color: #0f172a;
        cursor: pointer;
        font-size: 13px;
        font-weight: 800;
        outline: none;
        padding: 0 8px;
    }

    html.sidebar-collapsed .prazzu-workmode-selector {
        left: 84px;
    }

    @media (max-width: 1023.98px) {
        .prazzu-workmode-selector {
            top: 10px;
            left: 64px;
            min-height: 38px;
            padding: 3px 6px;
        }

        .prazzu-workmode-selector__label {
            display: none;
        }

        .prazzu-workmode-selector__select {
            min-width: 108px;
            height: 30px;
            font-size: 12px;
        }
    }
</style>

<script>
(function () {
    const STORAGE_KEY = 'workmode';
    const DEFAULT_MODE = 'default';

    function applyWorkmode(mode) {
        const normalizedMode = mode || DEFAULT_MODE;

        document.documentElement.setAttribute('data-workmode', normalizedMode);

        try {
            localStorage.setItem(STORAGE_KEY, normalizedMode);
        } catch (error) {
            // Evita quebrar o painel caso o navegador bloqueie localStorage.
        }

        const selector = document.getElementById('workmodeSelector');

        if (selector && selector.value !== normalizedMode) {
            selector.value = normalizedMode;
        }
    }

    function getSavedWorkmode() {
        try {
            return localStorage.getItem(STORAGE_KEY) || DEFAULT_MODE;
        } catch (error) {
            return DEFAULT_MODE;
        }
    }

    function initWorkmodeSelector() {
        const selector = document.getElementById('workmodeSelector');

        applyWorkmode(getSavedWorkmode());

        if (!selector || selector.dataset.workmodeReady === '1') {
            return;
        }

        selector.dataset.workmodeReady = '1';

        selector.addEventListener('change', function (event) {
            applyWorkmode(event.target.value);
        });
    }

    initWorkmodeSelector();

    document.addEventListener('DOMContentLoaded', initWorkmodeSelector);
    document.addEventListener('livewire:navigated', initWorkmodeSelector);
})();
</script>
