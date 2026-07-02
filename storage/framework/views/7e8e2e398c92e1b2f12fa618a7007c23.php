<?php
    /** @var \App\Support\WhiteLabelSettings $whiteLabel */
    $whiteLabel = \App\Support\WhiteLabelSettings::make();
    $isLoginPage = request()->routeIs('filament.admin.auth.login') || str_contains(trim(request()->path(), '/'), 'admin/login');
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($whiteLabel->isActive() && $isLoginPage): ?>
    <?php
        $brandName = $whiteLabel->brandDisplayName();
        $logo = $whiteLabel->loginLogo() ?: $whiteLabel->logo();
        $sideImage = $whiteLabel->loginSideImage();
        $title = $whiteLabel->loginTitle();
        $subtitle = $whiteLabel->loginSubtitle();
    ?>

    <aside class="prazzu-wl-login-panel" aria-label="Identidade visual <?php echo e(e($brandName)); ?>">
        <div class="prazzu-wl-login-panel__overlay"></div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sideImage): ?>
            <img
                src="<?php echo e($sideImage); ?>"
                alt="<?php echo e(e($brandName)); ?>"
                class="prazzu-wl-login-panel__image"
                loading="eager"
            >
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="prazzu-wl-login-panel__content">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logo): ?>
                <div class="prazzu-wl-login-panel__logo-wrap">
                    <img
                        src="<?php echo e($logo); ?>"
                        alt="<?php echo e(e($brandName)); ?>"
                        class="prazzu-wl-login-panel__logo"
                    >
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <span class="prazzu-wl-login-panel__eyebrow">
                <?php echo e($brandName); ?>

            </span>

            <h2 class="prazzu-wl-login-panel__title">
                <?php echo e($title); ?>

            </h2>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subtitle !== ''): ?>
                <p class="prazzu-wl-login-panel__subtitle">
                    <?php echo e($subtitle); ?>

                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($whiteLabel->ssoReady()): ?>
                <a href="<?php echo e(route('white-label.sso.redirect')); ?>" class="prazzu-wl-sso-button">
                    Entrar com <?php echo e($whiteLabel->ssoProviderLabel()); ?>

                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </aside>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\partials\white-label-login-panel.blade.php ENDPATH**/ ?>