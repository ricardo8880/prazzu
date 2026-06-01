<?php
    $workmodes = [
        'default' => ['label' => 'Modo Geral', 'hint' => 'Visão completa'],
        'rh' => ['label' => 'RH', 'hint' => 'Pessoas e contratos'],
        'contabil' => ['label' => 'Contábil', 'hint' => 'Rotina fiscal'],
        'financeiro' => ['label' => 'Financeiro', 'hint' => 'Valores e cobranças'],
        'documentos' => ['label' => 'Documentos', 'hint' => 'Arquivos e assinatura'],
        'clientes' => ['label' => 'Clientes', 'hint' => 'Relacionamento'],
        'governanca' => ['label' => 'Governança', 'hint' => 'Gestão e regras'],
        'relatorios' => ['label' => 'Relatórios', 'hint' => 'Indicadores'],
    ];
?>

<div class="prazzu-sidebar-controls">
    <button type="button" id="sidebarToggleBtn" class="prazzu-sidebar-toggle" aria-label="Abrir ou fechar menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="prazzu-workmode" data-workmode-custom-select>
        <select id="workmodeSelector" class="prazzu-workmode-selector" aria-label="Selecionar modo de trabalho">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $workmodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <option value="<?php echo e($value); ?>"><?php echo e($mode['label']); ?></option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
                <span class="prazzu-workmode-eyebrow">Modo de trabalho</span>
                <strong data-workmode-selected-label>Modo Geral</strong>
            </span>

            <span class="prazzu-workmode-chevron" aria-hidden="true">
                <svg viewBox="0 0 20 20" fill="none" focusable="false">
                    <path d="M5.75 7.75 10 12.25l4.25-4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </button>

        <div id="workmodeSelectorDropdown" class="prazzu-workmode-menu" role="listbox" tabindex="-1">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $workmodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $mode): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <button
                    type="button"
                    class="prazzu-workmode-option"
                    role="option"
                    data-workmode-value="<?php echo e($value); ?>"
                    data-workmode-label="<?php echo e($mode['label']); ?>"
                    aria-selected="<?php echo e($value === 'default' ? 'true' : 'false'); ?>"
                >
                    <span>
                        <strong><?php echo e($mode['label']); ?></strong>
                        <small><?php echo e($mode['hint']); ?></small>
                    </span>

                    <svg class="prazzu-workmode-check" viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false">
                        <path d="m5 10.5 3.1 3.1L15 6.8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('sidebarToggleBtn');
        const selector = document.getElementById('workmodeSelector');
        const customSelect = document.querySelector('[data-workmode-custom-select]');
        const trigger = customSelect?.querySelector('.prazzu-workmode-trigger');
        const selectedLabel = customSelect?.querySelector('[data-workmode-selected-label]');
        const options = customSelect ? Array.from(customSelect.querySelectorAll('.prazzu-workmode-option')) : [];

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
            const normalizedMode = option ? mode : 'default';
            const normalizedOption = option || options.find((item) => item.dataset.workmodeValue === 'default');

            document.documentElement.setAttribute('data-workmode', normalizedMode);
            selector.value = normalizedMode;

            if (selectedLabel && normalizedOption) {
                selectedLabel.textContent = normalizedOption.dataset.workmodeLabel || normalizedOption.textContent.trim();
            }

            options.forEach((item) => {
                const isSelected = item.dataset.workmodeValue === normalizedMode;
                item.classList.toggle('is-selected', isSelected);
                item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });

            if (shouldPersist) {
                localStorage.setItem('workmode', normalizedMode);
            }
        };

        if (toggle) {
            toggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('sidebar-collapsed');
                closeWorkmodeMenu();
            });
        }

        if (selector) {
            const savedMode = localStorage.getItem('workmode') || 'default';

            setWorkmode(savedMode, false);

            selector.addEventListener('change', (e) => {
                setWorkmode(e.target.value);
            });
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

                setWorkmode(option.dataset.workmodeValue || 'default');
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
<?php /**PATH C:\xampp\htdocs\sistemrh\resources\views/components/sidebar-workmode-controls.blade.php ENDPATH**/ ?>