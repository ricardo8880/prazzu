<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesAdvancedPermissions;
use App\Filament\Resources\Empresas\EmpresaResource;
use App\Filament\Resources\Users\UserResource;
use App\Support\CachedSchema;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class CentralAdministrativa extends Page
{
    use UsesAdvancedPermissions;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static string | UnitEnum | null $navigationGroup = 'Administração';

    protected static ?string $navigationLabel = 'Administração';

    protected static ?string $title = 'Administração';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.central-administrativa';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        return static::canAdvancedPermission('governanca.view')
            || static::canAdvancedPermission('configuracoes.view')
            || static::canAdvancedPermission('auditoria.view');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function modulos(): array
    {
        return [
            [
                'key' => 'empresa',
                'titulo' => 'Empresa',
                'descricao' => 'Atualize dados cadastrais, identificação visual e informações principais do escritório.',
                'icone' => 'bi-building',
                'url' => $this->safeUrl(EmpresaAdministrativa::class) ?: $this->safeUrl(EmpresaResource::class),
                'acao' => 'Editar empresa',
                'disponivel' => $this->canOpenPage(EmpresaAdministrativa::class) || $this->canOpenResource(EmpresaResource::class),
            ],
            [
                'key' => 'usuarios',
                'titulo' => 'Usuários',
                'descricao' => 'Gerencie pessoas, cargos, acessos e equipes sem sair das configurações.',
                'icone' => 'bi-people',
                'url' => $this->safeUrl(Usuarios::class) ?: $this->safeUrl(UserResource::class),
                'acao' => 'Gerenciar usuários',
                'disponivel' => $this->canOpenPage(Usuarios::class) || $this->canOpenResource(UserResource::class),
                'atalhos' => [
                    ['label' => 'Equipes', 'url' => $this->safeUrl(Equipes::class)],
                ],
            ],
            [
                'key' => 'permissoes',
                'titulo' => 'Permissões',
                'descricao' => 'Defina o que cada perfil pode visualizar, alterar ou aprovar no sistema.',
                'icone' => 'bi-shield-lock',
                'url' => $this->safeUrl(Permissoes::class),
                'acao' => 'Configurar acessos',
                'disponivel' => $this->canOpenPage(Permissoes::class),
                'atalhos' => [
                    ['label' => 'Perfis', 'url' => $this->safeUrl(Permissoes::class, ['tab' => 'perfis'])],
                    ['label' => 'Matriz de acesso', 'url' => $this->safeUrl(Permissoes::class, ['tab' => 'matriz'])],
                    ['label' => 'Exceções', 'url' => $this->safeUrl(Permissoes::class, ['tab' => 'excecoes'])],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|int|null>>
     */
    public function resumoConta(): array
    {
        return [
            [
                'label' => 'Usuários ativos',
                'value' => $this->countWhere('users', ['ativo' => true]) ?? $this->countTable('users'),
                'icon' => 'bi-person-check',
            ],
            [
                'label' => 'Clientes cadastrados',
                'value' => $this->countTable('crm_clientes'),
                'icon' => 'bi-briefcase',
            ],
            [
                'label' => 'Empresas',
                'value' => $this->countTable('empresas'),
                'icon' => 'bi-buildings',
            ],
            [
                'label' => 'Perfis de acesso',
                'value' => $this->countTable('prazzu_roles'),
                'icon' => 'bi-person-badge',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function saudeConta(): array
    {
        $empresas = $this->countTable('empresas');
        $usuarios = $this->countTable('users');
        $perfis = $this->countTable('prazzu_roles');
        $permissoes = $this->countTable('prazzu_permissions');

        return [
            [
                'label' => 'Empresa cadastrada',
                'ok' => $empresas > 0,
                'texto' => $empresas > 0 ? 'Dados principais encontrados.' : 'Cadastre os dados da empresa.',
            ],
            [
                'label' => 'Equipe com acesso',
                'ok' => $usuarios > 0,
                'texto' => $usuarios > 0 ? $usuarios . ' usuário(s) no sistema.' : 'Adicione o primeiro usuário.',
            ],
            [
                'label' => 'Perfis configurados',
                'ok' => $perfis > 0 && $permissoes > 0,
                'texto' => ($perfis > 0 && $permissoes > 0) ? 'Controle de acesso disponível.' : 'Revise os perfis de acesso.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    public function acoesRapidas(): array
    {
        return array_values(array_filter([
            [
                'label' => 'Editar dados da empresa',
                'icon' => 'bi-pencil-square',
                'url' => $this->safeUrl(EmpresaAdministrativa::class) ?: $this->safeUrl(EmpresaResource::class),
            ],
            [
                'label' => 'Gerenciar usuários',
                'icon' => 'bi-person-plus',
                'url' => $this->safeUrl(Usuarios::class) ?: $this->safeUrl(UserResource::class),
            ],
            [
                'label' => 'Revisar permissões',
                'icon' => 'bi-shield-check',
                'url' => $this->safeUrl(Permissoes::class),
            ],
            [
                'label' => 'Ver atividade recente',
                'icon' => 'bi-clock-history',
                'url' => $this->safeUrl(AuditoriaAdministrativa::class) ?: $this->safeUrl(Auditoria::class),
            ],
        ], fn (array $acao): bool => filled($acao['url'] ?? null)));
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function atividadeRecente(): array
    {
        $items = $this->latestActivityFromActivityLog();

        if ($items !== []) {
            return $items;
        }

        $items = $this->latestActivityFromAuditTimeline();

        if ($items !== []) {
            return $items;
        }

        return [
            ['titulo' => 'Nenhuma atividade recente encontrada', 'descricao' => 'As alterações administrativas aparecerão aqui quando houver registros.', 'quando' => 'Agora'],
        ];
    }

    private function countTable(string $table): int
    {
        try {
            return CachedSchema::hasTable($table) ? (int) DB::table($table)->count() : 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * @param array<string, mixed> $where
     */
    private function countWhere(string $table, array $where): ?int
    {
        try {
            if (! CachedSchema::hasTable($table)) {
                return null;
            }

            $query = DB::table($table);

            foreach ($where as $column => $value) {
                if (! CachedSchema::hasColumn($table, $column)) {
                    return null;
                }

                $query->where($column, $value);
            }

            return (int) $query->count();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function latestActivityFromActivityLog(): array
    {
        try {
            if (! CachedSchema::hasTable('activity_log')) {
                return [];
            }

            return DB::table('activity_log')
                ->latest('created_at')
                ->limit(4)
                ->get()
                ->map(fn ($row): array => [
                    'titulo' => $this->stringValue($row->description ?? 'Atividade registrada'),
                    'descricao' => $this->stringValue($row->log_name ?? 'Alteração no sistema'),
                    'quando' => $this->formatDate($row->created_at ?? null),
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function latestActivityFromAuditTimeline(): array
    {
        try {
            if (! CachedSchema::hasTable('audit_timeline')) {
                return [];
            }

            return DB::table('audit_timeline')
                ->latest('created_at')
                ->limit(4)
                ->get()
                ->map(fn ($row): array => [
                    'titulo' => $this->stringValue($row->acao ?? $row->event ?? 'Alteração registrada'),
                    'descricao' => $this->stringValue($row->entidade ?? $row->subject_type ?? 'Histórico administrativo'),
                    'quando' => $this->formatDate($row->created_at ?? null),
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function formatDate(mixed $value): string
    {
        try {
            return $value ? Carbon::parse($value)->diffForHumans() : 'Sem data';
        } catch (\Throwable) {
            return 'Sem data';
        }
    }

    private function stringValue(mixed $value): string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        return $value !== '' ? $value : 'Registro administrativo';
    }

    private function safeUrl(string $class, array $parameters = []): ?string
    {
        try {
            return method_exists($class, 'getUrl') ? $class::getUrl($parameters) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function canOpenPage(string $class): bool
    {
        try {
            return method_exists($class, 'canAccess') ? (bool) $class::canAccess() : true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function canOpenResource(string $class): bool
    {
        try {
            return method_exists($class, 'canViewAny') ? (bool) $class::canViewAny() : true;
        } catch (\Throwable) {
            return false;
        }
    }
}
