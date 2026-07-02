<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesAdvancedPermissions;
use App\Models\User;
use App\Support\CachedSchema;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class Equipes extends Page
{
    use UsesAdvancedPermissions;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static string | UnitEnum | null $navigationGroup = 'Administração';

    protected static ?string $navigationLabel = 'Equipes';

    protected static ?string $title = 'Equipes';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.equipes';

    public string $name = '';
    public ?string $description = null;
    public ?int $teamId = null;
    public ?int $userId = null;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return (bool) $user && ($user->isSuperAdmin() || static::canAdvancedPermission('governanca.view'));
    }

    public function criarEquipe(): void
    {
        if (! $this->podeEditar()) {
            Notification::make()->title('Você não tem permissão para criar equipes.')->danger()->send();
            return;
        }

        if (! $this->tabelasDisponiveis()) {
            Notification::make()->title('Tabelas de equipes não encontradas.')->warning()->send();
            return;
        }

        $name = trim($this->name);

        if ($name === '') {
            Notification::make()->title('Informe o nome da equipe.')->warning()->send();
            return;
        }

        DB::table('prazzu_teams')->insert([
            'name' => $name,
            'description' => blank($this->description) ? null : trim((string) $this->description),
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->name = '';
        $this->description = null;

        Notification::make()->title('Equipe criada.')->success()->send();
    }

    public function alternarEquipe(int $teamId): void
    {
        if (! $this->podeEditar() || ! $this->tabelasDisponiveis()) {
            return;
        }

        $team = DB::table('prazzu_teams')->where('id', $teamId)->first();

        if (! $team) {
            return;
        }

        DB::table('prazzu_teams')->where('id', $teamId)->update([
            'active' => $team->active ? 0 : 1,
            'updated_at' => now(),
        ]);

        Notification::make()->title('Status da equipe atualizado.')->success()->send();
    }

    public function vincularUsuario(): void
    {
        if (! $this->podeEditar()) {
            Notification::make()->title('Você não tem permissão para vincular usuários.')->danger()->send();
            return;
        }

        if (! $this->tabelasDisponiveis() || ! $this->teamId || ! $this->userId) {
            Notification::make()->title('Selecione uma equipe e um usuário.')->warning()->send();
            return;
        }

        $exists = DB::table('prazzu_team_user')
            ->where('team_id', $this->teamId)
            ->where('user_id', $this->userId)
            ->exists();

        if (! $exists) {
            DB::table('prazzu_team_user')->insert([
                'team_id' => $this->teamId,
                'user_id' => $this->userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->teamId = null;
        $this->userId = null;

        Notification::make()->title('Usuário vinculado à equipe.')->success()->send();
    }

    public function removerVinculo(int $teamId, int $userId): void
    {
        if (! $this->podeEditar() || ! $this->tabelasDisponiveis()) {
            return;
        }

        DB::table('prazzu_team_user')->where('team_id', $teamId)->where('user_id', $userId)->delete();

        Notification::make()->title('Usuário removido da equipe.')->success()->send();
    }

    public function equipes(): array
    {
        if (! $this->tabelasDisponiveis()) {
            return [];
        }

        return DB::table('prazzu_teams')
            ->leftJoin('prazzu_team_user', 'prazzu_team_user.team_id', '=', 'prazzu_teams.id')
            ->select('prazzu_teams.id', 'prazzu_teams.name', 'prazzu_teams.description', 'prazzu_teams.active', DB::raw('COUNT(prazzu_team_user.user_id) as users_count'))
            ->groupBy('prazzu_teams.id', 'prazzu_teams.name', 'prazzu_teams.description', 'prazzu_teams.active')
            ->orderBy('prazzu_teams.name')
            ->get()
            ->map(fn ($team) => (array) $team)
            ->all();
    }

    public function membrosPorEquipe(): array
    {
        if (! $this->tabelasDisponiveis()) {
            return [];
        }

        return DB::table('prazzu_team_user')
            ->join('users', 'users.id', '=', 'prazzu_team_user.user_id')
            ->select('prazzu_team_user.team_id', 'users.id', 'users.name', 'users.email', 'users.role')
            ->orderBy('users.name')
            ->get()
            ->groupBy('team_id')
            ->map(fn ($rows) => $rows->map(fn ($row) => (array) $row)->all())
            ->all();
    }

    public function usuariosDisponiveis(): array
    {
        $user = Auth::user();
        $companyId = $user?->isSuperAdmin() ? null : $user?->empresa_id;

        return User::query()
            ->when($companyId, fn ($query) => $query->where('empresa_id', $companyId))
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'email'])
            ->mapWithKeys(fn (User $user) => [$user->id => $user->name . ' — ' . $user->email])
            ->all();
    }

    public function podeEditar(): bool
    {
        $user = Auth::user();

        return (bool) $user && ($user->isSuperAdmin() || static::canAdvancedPermission('governanca.edit'));
    }

    public function tabelasDisponiveis(): bool
    {
        return CachedSchema::hasTable('prazzu_teams') && CachedSchema::hasTable('prazzu_team_user');
    }
}
