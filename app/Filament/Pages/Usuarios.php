<?php

namespace App\Filament\Pages;


use App\Support\CachedSchema;
use App\Models\Empresa;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class Usuarios extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static string | UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?string $title = 'Usuários';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.usuarios-management';

    public string $search = '';
    public string $roleFilter = 'todos';
    public string $perfilContabilFilter = 'todos';
    public string $lastAccessFilter = 'todos';

    public function updateUserRole(int $userId, string $role): void
    {
        if (! in_array($role, array_keys($this->roleOptions()), true)) {
            return;
        }

        $user = User::query()->find($userId);

        if (! $user) {
            Notification::make()->title('Usuário não encontrado.')->danger()->send();
            return;
        }

        if ($user->id === auth()->id() && $role !== $user->role) {
            Notification::make()->title('Você não pode alterar o próprio cargo nesta tela.')->warning()->send();
            return;
        }

        $user->forceFill(['role' => $role])->save();

        Notification::make()->title('Cargo atualizado com sucesso.')->success()->send();
    }


    public function updateUserPerfilContabil(int $userId, ?string $perfilContabil): void
    {
        $perfilContabil = blank($perfilContabil) ? null : $perfilContabil;

        if ($perfilContabil !== null && ! in_array($perfilContabil, array_keys($this->perfilContabilOptions()), true)) {
            return;
        }

        $user = User::query()->find($userId);

        if (! $user) {
            Notification::make()->title('Usuário não encontrado.')->danger()->send();
            return;
        }

        $authUser = auth()->user();

        if (! $authUser?->isSuperAdmin() && (int) $authUser?->empresa_id !== (int) $user->empresa_id) {
            Notification::make()->title('Você não tem permissão para alterar este usuário.')->danger()->send();
            return;
        }

        $user->forceFill(['perfil_contabil' => $perfilContabil])->save();

        Notification::make()->title('Perfil contábil atualizado com sucesso.')->success()->send();
    }

    public function removeUserAccess(int $userId): void
    {
        $user = User::query()->find($userId);

        if (! $user) {
            Notification::make()->title('Usuário não encontrado.')->danger()->send();
            return;
        }

        if ($user->id === auth()->id()) {
            Notification::make()->title('Você não pode remover o próprio acesso.')->warning()->send();
            return;
        }

        $user->forceFill([
            'remember_token' => null,
            'email_verified_at' => null,
            'role' => 'guest',
        ])->save();

        Notification::make()->title('Acesso removido e usuário convertido para convidado.')->success()->send();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->roleFilter = 'todos';
        $this->perfilContabilFilter = 'todos';
        $this->lastAccessFilter = 'todos';
    }

    protected function getViewData(): array
    {
        $authUser = auth()->user();
        $companyId = $authUser?->isSuperAdmin() ? null : $authUser?->empresa_id;

        $usersQuery = User::query()
            ->with('empresa')
            ->when($companyId, fn ($query) => $query->where('empresa_id', $companyId))
            ->when(trim($this->search) !== '', function ($query): void {
                $search = '%' . trim($this->search) . '%';
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', $search)->orWhere('email', 'like', $search);
                });
            })
            ->when($this->roleFilter !== 'todos', fn ($query) => $query->where('role', $this->roleFilter))
            ->when($this->perfilContabilFilter !== 'todos', fn ($query) => $query->where('perfil_contabil', $this->perfilContabilFilter));

        if ($this->lastAccessFilter !== 'todos') {
            $dateColumn = $this->lastAccessColumn();

            if ($dateColumn) {
                $limitDate = match ($this->lastAccessFilter) {
                    '30' => now()->subDays(30),
                    '60' => now()->subDays(60),
                    '90' => now()->subDays(90),
                    'nunca' => null,
                    default => null,
                };

                if ($this->lastAccessFilter === 'nunca') {
                    $usersQuery->whereNull($dateColumn);
                } elseif ($limitDate) {
                    $usersQuery->where(function ($query) use ($dateColumn, $limitDate): void {
                        $query->whereNull($dateColumn)->orWhere($dateColumn, '<=', $limitDate);
                    });
                }
            }
        }

        $users = $usersQuery->latest('id')->limit(80)->get();
        $company = $authUser?->isSuperAdmin() ? null : Empresa::query()->find($authUser?->empresa_id);
        $totalUsers = User::query()->when($companyId, fn ($query) => $query->where('empresa_id', $companyId))->count();
        $seatLimit = $company?->limite_usuarios_plano ?? ($authUser?->isSuperAdmin() ? 'Todas as empresas' : 0);
        $seatsAvailable = is_numeric($seatLimit) ? max(0, (int) $seatLimit - $totalUsers) : '-';
        $lastAccessColumn = $this->lastAccessColumn();

        return [
            'users' => $users,
            'roleOptions' => $this->roleOptions(),
            'perfilContabilOptions' => $this->perfilContabilOptions(),
            'teamsByUser' => $this->teamsByUser($users->pluck('id')->all()),
            'lastAccessColumn' => $lastAccessColumn,
            'stats' => [
                ['label' => 'Usuários ativos', 'value' => $totalUsers, 'hint' => 'Contabilizados no plano'],
                ['label' => 'Assentos do plano', 'value' => $seatLimit, 'hint' => 'Limite configurado'],
                ['label' => 'Convites disponíveis', 'value' => $seatsAvailable, 'hint' => 'Assentos livres'],
                ['label' => 'Convidados', 'value' => User::query()->when($companyId, fn ($q) => $q->where('empresa_id', $companyId))->where('role', 'guest')->count(), 'hint' => 'Acesso limitado'],
            ],
        ];
    }

    private function roleOptions(): array
    {
        return [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'gestor' => 'Gestor',
            'user' => 'Usuário',
            'guest' => 'Convidado',
        ];
    }


    private function perfilContabilOptions(): array
    {
        return User::perfilContabilOptions();
    }

    private function lastAccessColumn(): ?string
    {
        foreach (['last_access_at', 'last_login_at', 'last_seen_at', 'updated_at'] as $column) {
            if (CachedSchema::hasColumn('users', $column)) {
                return $column;
            }
        }

        return null;
    }

    private function teamsByUser(array $userIds): array
    {
        if (empty($userIds) || ! CachedSchema::hasTable('prazzu_team_user') || ! CachedSchema::hasTable('prazzu_teams')) {
            return [];
        }

        return DB::table('prazzu_team_user')
            ->join('prazzu_teams', 'prazzu_teams.id', '=', 'prazzu_team_user.team_id')
            ->whereIn('prazzu_team_user.user_id', $userIds)
            ->select('prazzu_team_user.user_id', 'prazzu_teams.name')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->pluck('name')->implode(', '))
            ->all();
    }

    public function formatLastAccess(?string $value): string
    {
        if (! $value) {
            return 'Nunca acessou';
        }

        return Carbon::parse($value)->format('d/m/Y H:i');
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
