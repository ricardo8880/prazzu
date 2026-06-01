<?php

namespace App\Support;

use App\Models\Configuracao;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class WhiteLabelSettings
{
    protected static string $cacheKey = 'white_label_settings';

    public static function defaults(): array
    {
        return [
            'ativo' => true,
            'nome_sistema' => config('app.name', 'Sistema'),
            'workspace_padrao' => config('app.name', 'Sistema'),

            'cor_primaria' => '#2e0af5',
            'cor_secundaria' => '#ff0000',
            'cor_destaque' => '#22c55e',
            'cor_sidebar' => '#0f172a',
            'cor_botoes' => '#2e0af5',

            'logo_light' => null,
            'logo_dark' => null,
            'favicon' => null,
            'logo_login' => null,
            'logo_email' => null,
            'login_background' => '#0f172a',
            'login_imagem_lateral' => null,

            'login_personalizado' => true,
            'login_titulo' => config('app.name', 'Sistema'),
            'login_subtitulo' => 'Acesse sua conta',

            'emails_personalizados' => true,
            'email_nome' => config('app.name', 'Sistema'),
            'email_endereco' => config('mail.from.address'),
            'email_nome_remetente' => config('app.name', 'Sistema'),
            'email_remetente' => config('mail.from.address'),
            'email_cor_template' => '#2e0af5',
            'email_assinatura' => null,

            'assistant_name' => 'Assistente Prazzu',
            'nome_assistente' => 'Assistente Prazzu',

            'documentos_personalizados' => true,
            'documentos_marca_propria' => true,
            'remover_referencias_internas' => false,
            'ocultar_referencias_internas' => false,
            'ocultar_nome_sistema' => false,
            'ocultar_rodape' => false,
            'ocultar_branding_documentos' => false,

            'multiplos_workspaces' => false,
            'multi_workspace' => false,
            'workspaces' => [],

            'dominio_personalizado' => null,
            'url_convite' => null,

            'sso_habilitado' => false,
            'sso_ativo' => false,
            'sso_provider' => null,
            'sso_client_id' => null,
            'sso_tenant_id' => null,
            'sso_redirect_url' => null,
        ];
    }

    public static function get(?string $key = null, mixed $default = null): mixed
    {
        $settings = Cache::remember(self::$cacheKey, now()->addMinutes(10), function (): array {
            return self::loadPersistedSettings();
        });

        $settings = self::mergeDefaults($settings);

        if ($key === null) {
            return (object) $settings;
        }

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    protected static function loadPersistedSettings(): array
    {
        try {
            if (Schema::hasTable('configuracoes') && Schema::hasColumn('configuracoes', 'white_label')) {
                $model = Configuracao::query()
                    ->whereNotNull('white_label')
                    ->orderBy('id')
                    ->first();

                if ($model && filled($model->white_label)) {
                    return self::toArray($model->white_label);
                }
            }
        } catch (Throwable) {
            // Mantém fallback abaixo para ambientes em instalação/manutenção.
        }

        try {
            if (Schema::hasTable('white_label')) {
                $row = DB::table('white_label')->orderBy('id')->first();

                if ($row) {
                    return self::toArray($row);
                }
            }
        } catch (Throwable) {
            // Se a tabela legada existir parcialmente, não derruba o painel.
        }

        return [];
    }

    public static function clearCache(): void
    {
        Cache::forget(self::$cacheKey);
    }

    protected static function toArray(mixed $value): array
    {
        if ($value instanceof Collection) {
            return $value->toArray();
        }

        if ($value instanceof \stdClass) {
            return (array) $value;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public static function mergeDefaults(array|object|null $data = []): array
    {
        $data = self::toArray($data);
        $merged = array_merge(self::defaults(), $data);

        $merged['ativo'] = self::boolValue($merged['ativo'] ?? true);

        foreach (['cor_primaria', 'cor_secundaria', 'cor_destaque', 'cor_sidebar', 'cor_botoes', 'login_background', 'email_cor_template'] as $colorKey) {
            $merged[$colorKey] = self::normalizeColor($merged[$colorKey] ?? null, self::defaults()[$colorKey] ?? '#2e0af5');
        }

        foreach (['logo_light', 'logo_dark', 'favicon', 'logo_login', 'logo_email', 'login_imagem_lateral'] as $imageKey) {
            $merged[$imageKey] = self::normalizeAndRepairPublicUploadPath($merged[$imageKey] ?? null);
        }

        $merged['nome_sistema'] = trim((string) ($merged['nome_sistema'] ?: config('app.name', 'Sistema')));
        $merged['workspace_padrao'] = trim((string) ($merged['workspace_padrao'] ?: $merged['nome_sistema']));

        $merged['assistant_name'] = trim((string) ($merged['assistant_name'] ?: ($merged['nome_assistente'] ?: 'Assistente Prazzu')));
        $merged['nome_assistente'] = trim((string) ($merged['nome_assistente'] ?: $merged['assistant_name']));

        $merged['login_titulo'] = trim((string) ($merged['login_titulo'] ?: $merged['workspace_padrao']));
        $merged['login_subtitulo'] = trim((string) ($merged['login_subtitulo'] ?? ''));

        $merged['email_nome'] = trim((string) ($merged['email_nome'] ?: ($merged['email_nome_remetente'] ?: $merged['workspace_padrao'])));
        $merged['email_nome_remetente'] = trim((string) ($merged['email_nome_remetente'] ?: $merged['email_nome']));

        $merged['email_endereco'] = trim((string) ($merged['email_endereco'] ?: ($merged['email_remetente'] ?: config('mail.from.address'))));
        $merged['email_remetente'] = trim((string) ($merged['email_remetente'] ?: $merged['email_endereco']));

        $merged['multiplos_workspaces'] = self::boolValue($merged['multiplos_workspaces'] ?? false) || self::boolValue($merged['multi_workspace'] ?? false);
        $merged['multi_workspace'] = $merged['multiplos_workspaces'];

        $merged['sso_habilitado'] = self::boolValue($merged['sso_habilitado'] ?? false) || self::boolValue($merged['sso_ativo'] ?? false);
        $merged['sso_ativo'] = $merged['sso_habilitado'];

        $merged['remover_referencias_internas'] = self::boolValue($merged['remover_referencias_internas'] ?? false) || self::boolValue($merged['ocultar_referencias_internas'] ?? false);
        $merged['ocultar_referencias_internas'] = $merged['remover_referencias_internas'];

        foreach (['login_personalizado', 'emails_personalizados', 'documentos_personalizados', 'documentos_marca_propria', 'ocultar_nome_sistema', 'ocultar_rodape', 'ocultar_branding_documentos'] as $boolKey) {
            $merged[$boolKey] = self::boolValue($merged[$boolKey] ?? false);
        }

        $merged['dominio_personalizado'] = self::normalizeDomain($merged['dominio_personalizado'] ?? null);
        $merged['url_convite'] = trim((string) ($merged['url_convite'] ?? '')) ?: null;

        $merged['workspaces'] = self::normalizeWorkspaces($merged['workspaces'] ?? [], $merged);

        return $merged;
    }

    protected static function setting(?string $key = null, mixed $default = null): mixed
    {
        return self::get($key, $default);
    }

    protected static function boolValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'on', 'yes', 'sim'], true);
        }

        return (bool) $value;
    }

    public static function normalizeDomain(mixed $domain): ?string
    {
        $domain = trim((string) ($domain ?? ''));

        if ($domain === '') {
            return null;
        }

        $domain = preg_replace('#^https?://#i', '', $domain) ?: $domain;
        $domain = preg_replace('#/.*$#', '', $domain) ?: $domain;

        return strtolower(trim($domain));
    }

    protected static function normalizeWorkspaces(mixed $workspaces, array $baseSettings = []): array
    {
        $workspaces = self::toArray($workspaces);
        $normalized = [];

        foreach ($workspaces as $workspace) {
            $workspace = self::toArray($workspace);

            if ($workspace === []) {
                continue;
            }

            $name = trim((string) ($workspace['nome'] ?? ''));

            if ($name === '') {
                continue;
            }

            $normalized[] = [
                'nome' => $name,
                'slug' => Str::slug((string) ($workspace['slug'] ?? $name)),
                'dominio' => self::normalizeDomain($workspace['dominio'] ?? null),
                'cor_primaria' => self::normalizeColor($workspace['cor_primaria'] ?? null, $baseSettings['cor_primaria'] ?? '#2e0af5'),
                'cor_botoes' => self::normalizeColor($workspace['cor_botoes'] ?? null, $baseSettings['cor_botoes'] ?? '#2e0af5'),
                'cor_sidebar' => self::normalizeColor($workspace['cor_sidebar'] ?? null, $baseSettings['cor_sidebar'] ?? '#0f172a'),
                'logo' => self::normalizeAndRepairPublicUploadPath($workspace['logo'] ?? null),
            ];
        }

        return $normalized;
    }

    protected static function normalizeColor(mixed $color, string $fallback): string
    {
        $color = trim((string) ($color ?? ''));

        if (preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color)) {
            if (strlen($color) === 4) {
                return '#' . strtolower($color[1] . $color[1] . $color[2] . $color[2] . $color[3] . $color[3]);
            }

            return strtolower($color);
        }

        return strtolower($fallback);
    }

    protected static function hexToRgb(string $hex): string
    {
        $hex = ltrim(self::normalizeColor($hex, '#000000'), '#');

        return implode(', ', [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ]);
    }

    protected static function darken(string $hex, int $percent = 18): string
    {
        $hex = ltrim(self::normalizeColor($hex, '#0f172a'), '#');
        $factor = max(0, min(100, 100 - $percent)) / 100;

        $parts = [
            (int) floor(hexdec(substr($hex, 0, 2)) * $factor),
            (int) floor(hexdec(substr($hex, 2, 2)) * $factor),
            (int) floor(hexdec(substr($hex, 4, 2)) * $factor),
        ];

        return sprintf('#%02x%02x%02x', $parts[0], $parts[1], $parts[2]);
    }

    public static function normalizeAndRepairPublicUploadPath(mixed $path): ?string
    {
        if ($path instanceof \stdClass) {
            $path = $path->path
                ?? $path->url
                ?? $path->name
                ?? $path->file
                ?? null;
        }

        if (is_array($path)) {
            $path = $path['path']
                ?? $path['url']
                ?? $path['name']
                ?? $path['file']
                ?? reset($path)
                ?? null;
        }

        if (blank($path) || ! is_string($path)) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path));

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = trim($path, '/');

        foreach (['public/', 'storage/', 'app/public/', 'public/storage/', 'images/white-label/', 'white-label/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
            }
        }

        return trim($path, '/') ?: null;
    }

    public static function assetUrl(mixed $path): ?string
    {
        return self::publicImageUrl($path);
    }

    public static function publicImageUrl(mixed $path): ?string
    {
        $normalizedPath = self::normalizeAndRepairPublicUploadPath($path);

        if (! $normalizedPath) {
            return null;
        }

        if (str_starts_with($normalizedPath, 'http://') || str_starts_with($normalizedPath, 'https://')) {
            return $normalizedPath;
        }

        $normalizedPath = ltrim($normalizedPath, '/');

        $publicImagePath = 'images/white-label/' . $normalizedPath;
        $publicStoragePath = 'storage/white-label/' . $normalizedPath;

        if (File::exists(public_path($publicImagePath))) {
            return asset($publicImagePath);
        }

        if (File::exists(public_path($publicStoragePath)) || File::exists(storage_path('app/public/white-label/' . $normalizedPath))) {
            return asset($publicStoragePath);
        }

        return asset($publicImagePath);
    }

    public static function deletePublicImage(string|array|null $path): bool
    {
        $normalizedPath = self::normalizeAndRepairPublicUploadPath($path);

        if (! $normalizedPath || str_starts_with($normalizedPath, 'http://') || str_starts_with($normalizedPath, 'https://')) {
            return false;
        }

        $relativePath = ltrim($normalizedPath, '/');

        $candidates = [
            public_path($relativePath),
            public_path('images/white-label/' . $relativePath),
            public_path('storage/white-label/' . $relativePath),
            public_path('storage/' . $relativePath),
            storage_path('app/public/white-label/' . $relativePath),
            storage_path('app/public/' . $relativePath),
        ];

        $deleted = false;

        foreach (array_unique($candidates) as $candidate) {
            if (File::exists($candidate) && File::isFile($candidate)) {
                $deleted = File::delete($candidate) || $deleted;
            }
        }

        return $deleted;
    }

    public static function css(): string
    {
        if (! self::isActive()) {
            return '';
        }

        $primary = self::primaryColor();
        $secondary = self::secondaryColor();
        $accent = self::accentColor();
        $sidebar = self::sidebarColor();
        $button = self::buttonColor();
        $loginBackground = self::loginBackgroundColor();

        return ':root {' . PHP_EOL
            . '    --pzu-primary: ' . $primary . ';' . PHP_EOL
            . '    --pzu-secondary: ' . $secondary . ';' . PHP_EOL
            . '    --pzu-accent: ' . $accent . ';' . PHP_EOL
            . '    --pzu-sidebar: ' . $sidebar . ';' . PHP_EOL
            . '    --pzu-button: ' . $button . ';' . PHP_EOL
            . '    --wl-primary: ' . $primary . ';' . PHP_EOL
            . '    --wl-primary-rgb: ' . self::hexToRgb($primary) . ';' . PHP_EOL
            . '    --wl-secondary: ' . $secondary . ';' . PHP_EOL
            . '    --wl-secondary-rgb: ' . self::hexToRgb($secondary) . ';' . PHP_EOL
            . '    --wl-accent: ' . $accent . ';' . PHP_EOL
            . '    --wl-accent-rgb: ' . self::hexToRgb($accent) . ';' . PHP_EOL
            . '    --wl-sidebar: ' . $sidebar . ';' . PHP_EOL
            . '    --wl-sidebar-rgb: ' . self::hexToRgb($sidebar) . ';' . PHP_EOL
            . '    --wl-sidebar-dark: ' . self::darken($sidebar, 22) . ';' . PHP_EOL
            . '    --wl-button: ' . $button . ';' . PHP_EOL
            . '    --wl-button-rgb: ' . self::hexToRgb($button) . ';' . PHP_EOL
            . '    --wl-login-background: ' . $loginBackground . ';' . PHP_EOL
            . '}' . PHP_EOL
            . '.fi-sidebar, .fi-sidebar-nav, .fi-sidebar-header {' . PHP_EOL
            . '    --sidebar-bg: var(--wl-sidebar);' . PHP_EOL
            . '    background: var(--wl-sidebar) !important;' . PHP_EOL
            . '}' . PHP_EOL
            . '.fi-sidebar-item-active a, .fi-sidebar-item.fi-active a, .fi-sidebar-item [aria-current="page"] {' . PHP_EOL
            . '    background: color-mix(in srgb, var(--wl-secondary) 22%, transparent) !important;' . PHP_EOL
            . '}' . PHP_EOL
            . '.fi-sidebar-item a:hover, .fi-sidebar-item-button:hover {' . PHP_EOL
            . '    background: color-mix(in srgb, var(--wl-accent) 18%, transparent) !important;' . PHP_EOL
            . '}' . PHP_EOL
            . '.fi-btn.fi-color-primary, .fi-ac-btn-action.fi-color-primary {' . PHP_EOL
            . '    --c-400: ' . self::hexToRgb($button) . ';' . PHP_EOL
            . '    --c-500: ' . self::hexToRgb($button) . ';' . PHP_EOL
            . '    --c-600: ' . self::hexToRgb(self::darken($button, 8)) . ';' . PHP_EOL
            . '}';
    }

    public static function isActive(): bool
    {
        return (bool) self::setting('ativo', true);
    }

    public static function displayName(): string
    {
        return (string) (self::setting('nome_sistema') ?: self::workspaceName() ?: 'Sistema');
    }

    public static function brandName(): string
    {
        return self::displayName();
    }

    public static function brandDisplayName(): string
    {
        return self::workspaceName() ?: self::displayName();
    }

    public static function workspaceName(): string
    {
        return (string) (self::setting('workspace_padrao') ?: self::displayName());
    }

    public static function enterpriseLabel(): string
    {
        return self::brandDisplayName();
    }

    public static function assistantName(): string
    {
        return (string) (self::setting('assistant_name') ?: self::setting('nome_assistente') ?: 'Assistente Prazzu');
    }

    public static function logo(): ?string
    {
        return self::logoLight() ?? self::logoDark();
    }

    public static function logoLight(): ?string
    {
        return self::assetUrl(self::setting('logo_light'));
    }

    public static function logoDark(): ?string
    {
        return self::assetUrl(self::setting('logo_dark'));
    }

    public static function favicon(): ?string
    {
        return self::assetUrl(self::setting('favicon'));
    }

    public static function loginLogo(): ?string
    {
        return self::assetUrl(self::setting('logo_login')) ?? self::logo();
    }

    public static function logoLogin(): ?string
    {
        return self::loginLogo();
    }

    public static function logoEmail(): ?string
    {
        return self::assetUrl(self::setting('logo_email')) ?? self::logo();
    }

    public static function documentLogoPath(): ?string
    {
        return self::logo();
    }

    public static function loginBackground(): ?string
    {
        return self::loginBackgroundColor();
    }

    public static function loginBackgroundColor(): string
    {
        return (string) self::setting('login_background', self::sidebarColor());
    }

    public static function loginSideImage(): ?string
    {
        return self::assetUrl(self::setting('login_imagem_lateral'));
    }

    public static function primaryColor(): string
    {
        return (string) self::setting('cor_primaria', '#2e0af5');
    }

    public static function secondaryColor(): string
    {
        return (string) self::setting('cor_secundaria', '#ff0000');
    }

    public static function accentColor(): string
    {
        return (string) self::setting('cor_destaque', '#22c55e');
    }

    public static function sidebarColor(): string
    {
        return (string) self::setting('cor_sidebar', '#0f172a');
    }

    public static function buttonColor(): string
    {
        return (string) self::setting('cor_botoes', self::primaryColor());
    }

    public static function loginTitle(): string
    {
        return (string) self::setting('login_titulo', self::brandDisplayName());
    }

    public static function loginSubtitle(): string
    {
        return (string) self::setting('login_subtitulo', '');
    }

    public static function mailFromName(): string
    {
        return (string) (self::setting('email_nome') ?: self::setting('email_nome_remetente') ?: self::brandDisplayName());
    }

    public static function mailFromAddress(): string
    {
        return (string) (self::setting('email_endereco') ?: self::setting('email_remetente') ?: config('mail.from.address'));
    }

    public static function emailBrandDataForEmpresaId(?int $empresaId = null): array
    {
        return [
            'name' => self::mailFromName(),
            'from_name' => self::mailFromName(),
            'from_address' => self::mailFromAddress(),
            'logo' => self::logoEmail(),
            'color' => self::setting('email_cor_template', self::primaryColor()),
            'signature' => self::setting('email_assinatura'),
            'hide_internal_references' => self::hideInternalReferences(),
        ];
    }

    public static function hideSystemName(): bool
    {
        return (bool) self::setting('ocultar_nome_sistema', false);
    }

    public static function hideFooter(): bool
    {
        return (bool) self::setting('ocultar_rodape', false);
    }

    public static function hideInternalReferences(): bool
    {
        return (bool) (self::setting('remover_referencias_internas', false) || self::setting('ocultar_referencias_internas', false));
    }

    public static function hideDocumentBranding(): bool
    {
        return (bool) self::setting('ocultar_branding_documentos', false);
    }

    public static function documentBrandingEnabled(): bool
    {
        return self::isActive()
            && (bool) self::setting('documentos_marca_propria', true)
            && ! self::hideDocumentBranding();
    }

    public static function ssoReady(): bool
    {
        return self::isActive()
            && (bool) self::setting('sso_ativo', false)
            && filled(self::setting('sso_provider'))
            && (filled(self::setting('sso_redirect_url')) || filled(self::setting('sso_client_id')) || filled(self::setting('sso_tenant_id')));
    }

    public static function ssoProviderLabel(): string
    {
        return match ((string) self::setting('sso_provider')) {
            'azure' => 'Microsoft Azure / Entra ID',
            'okta' => 'Okta',
            'google' => 'Google Workspace',
            'custom' => 'SSO',
            default => 'SSO',
        };
    }

    public static function ssoExternalUrl(): ?string
    {
        return self::setting('sso_redirect_url');
    }

    public static function make(): object
    {
        return new class {
            public function __call($method, $args)
            {
                if (is_callable([WhiteLabelSettings::class, $method])) {
                    return WhiteLabelSettings::$method(...$args);
                }

                if (str_starts_with((string) $method, 'get')) {
                    $key = Str::of((string) $method)
                        ->after('get')
                        ->snake()
                        ->toString();

                    return WhiteLabelSettings::get($key, $args[0] ?? null);
                }

                return null;
            }

            public function get(?string $key = null, mixed $default = null): mixed
            {
                return WhiteLabelSettings::get($key, $default);
            }
        };
    }
}
