<?php
    $whiteLabel = \App\Support\WhiteLabelSettings::make();
    $whiteLabelCss = \App\Support\WhiteLabelSettings::css();
    $favicon = $whiteLabel->favicon();
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($favicon): ?>
    <link rel="icon" href="<?php echo e($favicon); ?>">
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($whiteLabelCss)): ?>
    <style id="prazzu-white-label-runtime">
        <?php echo $whiteLabelCss; ?>

    </style>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php
    $isWhiteLabelLoginPage = $whiteLabel->isActive()
        && (request()->routeIs('filament.admin.auth.login') || str_contains(trim(request()->path(), '/'), 'admin/login'));
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isWhiteLabelLoginPage): ?>
    <?php
        $loginBackground = $whiteLabel->loginBackgroundColor();
        $loginSideImage = $whiteLabel->loginSideImage();
        $loginLogo = $whiteLabel->loginLogo() ?: $whiteLabel->logo();
    ?>

    <style id="prazzu-white-label-login-runtime">
        :root {
            --wl-login-background: <?php echo e($loginBackground); ?>;
            --wl-login-side-image: <?php if($loginSideImage): ?> url('<?php echo e($loginSideImage); ?>') <?php else: ?> none <?php endif; ?>;
        }

        html,
        body {
            min-height: 100%;
        }

        body:has(.prazzu-wl-login-panel) {
            background:
                radial-gradient(circle at top right, rgba(var(--wl-primary-rgb, 245, 158, 11), .22), transparent 32rem),
                linear-gradient(135deg, var(--wl-login-background), var(--wl-secondary, #111827)) !important;
        }

        .prazzu-wl-login-panel {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 0;
            width: min(46vw, 720px);
            min-height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(circle at 25% 20%, rgba(var(--wl-button-rgb, 245, 158, 11), .38), transparent 22rem),
                linear-gradient(145deg, var(--wl-login-background), var(--wl-sidebar-dark, #020617));
            color: #ffffff;
        }

        .prazzu-wl-login-panel__image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: .28;
            filter: saturate(1.08) contrast(1.05);
        }

        .prazzu-wl-login-panel__overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
            background:
                linear-gradient(180deg, rgba(2, 6, 23, .15), rgba(2, 6, 23, .78)),
                radial-gradient(circle at bottom left, rgba(var(--wl-primary-rgb, 245, 158, 11), .28), transparent 28rem);
        }

        .prazzu-wl-login-panel__content {
            position: relative;
            z-index: 2;
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            justify-content: flex-end;
            padding: clamp(2rem, 5vw, 5rem);
        }

        .prazzu-wl-login-panel__logo-wrap {
            display: inline-flex;
            width: fit-content;
            max-width: 260px;
            align-items: center;
            justify-content: center;
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, .24);
            background: rgba(255, 255, 255, .94);
            padding: 14px 18px;
            box-shadow: 0 24px 60px rgba(2, 6, 23, .28);
            backdrop-filter: blur(18px);
        }

        .prazzu-wl-login-panel__logo {
            max-height: 70px;
            max-width: 220px;
            object-fit: contain;
        }

        .prazzu-wl-login-panel__eyebrow {
            margin-top: 2rem;
            display: inline-flex;
            width: fit-content;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .22);
            background: rgba(255, 255, 255, .12);
            padding: .45rem .8rem;
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
            backdrop-filter: blur(14px);
        }

        .prazzu-wl-login-panel__title {
            margin-top: 1.1rem;
            max-width: 560px;
            font-size: clamp(2rem, 4.2vw, 4.5rem);
            line-height: .96;
            font-weight: 900;
            letter-spacing: -.055em;
        }

        .prazzu-wl-login-panel__subtitle {
            margin-top: 1.1rem;
            max-width: 520px;
            color: rgba(255, 255, 255, .82);
            font-size: clamp(.98rem, 1.2vw, 1.14rem);
            line-height: 1.75;
        }

        body:has(.prazzu-wl-login-panel) .fi-simple-layout {
            position: relative;
            min-height: 100vh;
            background:
                radial-gradient(circle at 78% 18%, rgba(var(--wl-primary-rgb, 245, 158, 11), .13), transparent 20rem),
                linear-gradient(135deg, rgba(255, 255, 255, .86), rgba(248, 250, 252, .72)) !important;
        }

        body:has(.prazzu-wl-login-panel) .fi-simple-main-ctn {
            position: relative;
            z-index: 2;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            margin-left: min(46vw, 720px);
            padding: clamp(1rem, 4vw, 4rem);
        }

        body:has(.prazzu-wl-login-panel) .fi-simple-main {
            width: min(100%, 460px) !important;
            max-width: 460px !important;
        }

        body:has(.prazzu-wl-login-panel) .fi-simple-page {
            border-radius: 28px;
            border: 1px solid rgba(148, 163, 184, .22);
            background: rgba(255, 255, 255, .94);
            padding: clamp(1.25rem, 2.5vw, 2.25rem);
            box-shadow: 0 30px 90px rgba(15, 23, 42, .16);
            backdrop-filter: blur(22px);
        }

        body:has(.prazzu-wl-login-panel) .fi-simple-page-content {
            gap: 1.35rem;
        }

        body:has(.prazzu-wl-login-panel) .fi-simple-header,
        body:has(.prazzu-wl-login-panel) .fi-simple-header-heading,
        body:has(.prazzu-wl-login-panel) .fi-simple-header-subheading {
            text-align: center;
        }

        body:has(.prazzu-wl-login-panel) .fi-simple-header-heading {
            color: #0f172a;
            font-size: clamp(1.45rem, 2.5vw, 2rem);
            line-height: 1.12;
            font-weight: 900;
            letter-spacing: -.035em;
        }

        body:has(.prazzu-wl-login-panel) .fi-simple-header-subheading {
            color: #64748b;
            font-size: .95rem;
            line-height: 1.65;
        }

        body:has(.prazzu-wl-login-panel) .fi-logo,
        body:has(.prazzu-wl-login-panel) .fi-simple-header-logo {
            max-height: 72px !important;
            object-fit: contain;
        }

        body:has(.prazzu-wl-login-panel) .fi-input-wrp:focus-within,
        body:has(.prazzu-wl-login-panel) .fi-fo-field-wrp:focus-within .fi-input-wrp {
            border-color: rgba(var(--wl-primary-rgb, 245, 158, 11), .42) !important;
            box-shadow: 0 0 0 4px rgba(var(--wl-primary-rgb, 245, 158, 11), .10) !important;
        }

        body:has(.prazzu-wl-login-panel) .fi-btn.fi-color-primary,
        body:has(.prazzu-wl-login-panel) .fi-form-actions .fi-btn {
            width: 100%;
            justify-content: center;
            border-radius: 999px !important;
            padding-block: .78rem !important;
            font-weight: 800 !important;
        }

        .dark body:has(.prazzu-wl-login-panel) .fi-simple-layout,
        .dark:has(.prazzu-wl-login-panel) .fi-simple-layout {
            background:
                radial-gradient(circle at 78% 18%, rgba(var(--wl-primary-rgb, 245, 158, 11), .18), transparent 20rem),
                linear-gradient(135deg, rgba(15, 23, 42, .90), rgba(2, 6, 23, .76)) !important;
        }

        .dark body:has(.prazzu-wl-login-panel) .fi-simple-page,
        .dark:has(.prazzu-wl-login-panel) .fi-simple-page {
            border-color: rgba(148, 163, 184, .18);
            background: rgba(15, 23, 42, .88);
            box-shadow: 0 30px 90px rgba(0, 0, 0, .35);
        }

        .dark body:has(.prazzu-wl-login-panel) .fi-simple-header-heading,
        .dark:has(.prazzu-wl-login-panel) .fi-simple-header-heading {
            color: #f8fafc;
        }

        .dark body:has(.prazzu-wl-login-panel) .fi-simple-header-subheading,
        .dark:has(.prazzu-wl-login-panel) .fi-simple-header-subheading {
            color: #cbd5e1;
        }

        @media (max-width: 1024px) {
            .prazzu-wl-login-panel {
                display: none;
            }

            body:has(.prazzu-wl-login-panel) .fi-simple-main-ctn {
                margin-left: 0;
            }
        }

        @media (max-width: 640px) {
            body:has(.prazzu-wl-login-panel) .fi-simple-main-ctn {
                padding: 1rem;
            }

            body:has(.prazzu-wl-login-panel) .fi-simple-page {
                border-radius: 22px;
                padding: 1.15rem;
            }
        }
    </style>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\partials\white-label-head.blade.php ENDPATH**/ ?>