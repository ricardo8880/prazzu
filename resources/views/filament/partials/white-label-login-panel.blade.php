@php
    /** @var \App\Support\WhiteLabelSettings $whiteLabel */
    $whiteLabel = \App\Support\WhiteLabelSettings::make();
    $isLoginPage = request()->routeIs('filament.admin.auth.login') || str_contains(trim(request()->path(), '/'), 'admin/login');
@endphp

@if($whiteLabel->isActive() && $isLoginPage)
    @php
        $brandName = $whiteLabel->brandDisplayName();
        $logo = $whiteLabel->loginLogo() ?: $whiteLabel->logo();
        $sideImage = $whiteLabel->loginSideImage();
        $title = $whiteLabel->loginTitle();
        $subtitle = $whiteLabel->loginSubtitle();
    @endphp

    <aside class="prazzu-wl-login-panel" aria-label="Identidade visual {{ e($brandName) }}">
        <div class="prazzu-wl-login-panel__overlay"></div>

        @if($sideImage)
            <img
                src="{{ $sideImage }}"
                alt="{{ e($brandName) }}"
                class="prazzu-wl-login-panel__image"
                loading="eager"
            >
        @endif

        <div class="prazzu-wl-login-panel__content">
            @if($logo)
                <div class="prazzu-wl-login-panel__logo-wrap">
                    <img
                        src="{{ $logo }}"
                        alt="{{ e($brandName) }}"
                        class="prazzu-wl-login-panel__logo"
                    >
                </div>
            @endif

            <span class="prazzu-wl-login-panel__eyebrow">
                {{ $brandName }}
            </span>

            <h2 class="prazzu-wl-login-panel__title">
                {{ $title }}
            </h2>

            @if($subtitle !== '')
                <p class="prazzu-wl-login-panel__subtitle">
                    {{ $subtitle }}
                </p>
            @endif

            @if($whiteLabel->ssoReady())
                <a href="{{ route('white-label.sso.redirect') }}" class="prazzu-wl-sso-button">
                    Entrar com {{ $whiteLabel->ssoProviderLabel() }}
                </a>
            @endif
        </div>
    </aside>
@endif
