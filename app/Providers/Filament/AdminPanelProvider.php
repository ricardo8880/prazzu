<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Home;
use App\Http\Middleware\CheckEmpresaPagamento;
use App\Support\WhiteLabelSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $whiteLabel = WhiteLabelSettings::make();

        FilamentAsset::register([
            Css::make(
                'bootstrap-icons',
                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css'
            ),
            Css::make(
                'prazzu-theme',
                asset('css/prazzu-theme.css') . '?v=20260520-sidebar-logo-final'
            ),
            Css::make(
                'white-label',
                asset('css/white-label.css') . '?v=20260515-white-label-e2e-fix'
            ),
            Css::make(
                'prazzu-global-search',
                asset('css/prazzu-global-search.css') . '?v=20260512-global-search-v2'
            ),
            Css::make(
                'prazzu-menu-ux',
                asset('css/prazzu-menu-ux.css') . '?v=20260512-menu-ux'
            ),
            Css::make(
                'prazzu-ui-standard',
                asset('css/prazzu-ui-standard.css') . '?v=20260514-restore-original-ui-standard'
            ),
            Css::make(
                'prazzu-global',
                asset('css/prazzu-global.css') . '?v=20260520-sidebar-logo-final'
            ),
            Css::make(
                'workmode-order',
                asset('css/workmode-order.css') . '?v=20260601-product-profiles-lote3'
            ),
            Css::make(
                'workmode-ux',
                asset('css/workmode-ux.css') . '?v=20260601-product-profiles-lote3'
            ),
        ]);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->homeUrl(fn (): string => Home::getUrl())

            ->brandName(
                $whiteLabel->hideSystemName()
                    ? ($whiteLabel->get('workspace_padrao') ?: 'Workspace')
                    : config('app.name')
            )
            ->brandLogo(
                $whiteLabel->logo() ?: asset('images/logo.png')
            )
            ->brandLogoHeight('42px')
            ->favicon(
                $whiteLabel->favicon() ?: asset('favicon.ico')
            )

            ->navigationGroups([
                NavigationGroup::make('Trabalho')
                    ->collapsible(false),
                NavigationGroup::make('Governança')
                    ->collapsible(false),
                NavigationGroup::make('Clientes')
                    ->collapsible(false),
                NavigationGroup::make('Documentos')
                    ->collapsible(false),
                NavigationGroup::make('Financeiro')
                    ->collapsible(false),
                NavigationGroup::make('Relatórios')
                    ->collapsible(false),
                NavigationGroup::make('Configurações')
                    ->collapsible(false),
            ])

            ->login(Login::class)

            ->colors([
                'primary' => $whiteLabel->isActive()
                    ? Color::hex($whiteLabel->primaryColor())
                    : Color::Violet,
            ])

            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->pages([
                Home::class,
            ])

            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])

            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => Blade::render('@include("filament.partials.white-label-head")')
            )

            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn () => Blade::render('@include("filament.partials.white-label-login-panel")')
            )

            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn () => Blade::render('@include("components.sidebar-workmode-controls")')
            )

            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => auth()->check()
                    ? Blade::render('@include("components.global-search")')
                    : ''
            )

            ->authMiddleware([
                Authenticate::class,
                CheckEmpresaPagamento::class,
            ]);
    }
}
