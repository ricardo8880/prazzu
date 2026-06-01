<?php

namespace App\Models;

use App\Services\PlanoService;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'empresa_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'empresa_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $authUser = auth()->user();

            if (! $authUser) {
                return;
            }

            if ($authUser->isSuperAdmin()) {
                if ($user->role === 'super_admin') {
                    $user->empresa_id = null;
                    return;
                }

                $empresa = $user->empresa_id ? Empresa::query()->find($user->empresa_id) : null;

                if (! $empresa || ! $empresa->isAtivo()) {
                    throw ValidationException::withMessages([
                        'empresa_id' => 'Empresa inativa ou não vinculada. Não é possível criar novos usuários para este plano.',
                    ]);
                }

                if ($empresa->atingiuLimiteUsuarios()) {
                    throw ValidationException::withMessages([
                        'email' => 'Limite de usuários do plano da empresa atingido. Altere o plano antes de cadastrar mais usuários.',
                    ]);
                }

                return;
            }

            $empresa = $authUser->empresa;

            if (! $empresa || ! $empresa->isAtivo()) {
                throw ValidationException::withMessages([
                    'empresa_id' => 'Empresa inativa. Não é possível criar novos usuários.',
                ]);
            }

            if ($empresa->atingiuLimiteUsuarios()) {
                throw ValidationException::withMessages([
                    'email' => 'Limite de usuários do plano atingido. Para cadastrar mais usuários, altere o plano da empresa.',
                ]);
            }

            $user->empresa_id = $authUser->empresa_id;

            if ($user->role === 'super_admin') {
                throw ValidationException::withMessages([
                    'role' => 'Você não tem permissão para criar super admin.',
                ]);
            }
        });
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function responsavel()
    {
        return $this->hasOne(Responsavel::class, 'user_id');
    }

    public function equipeResponsaveis()
    {
        return $this->hasMany(Responsavel::class, 'gestor_user_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'super_admin';
    }

    public function isAdminEmpresa(): bool
    {
        return $this->role === 'admin';
    }

    public function isGestor(): bool
    {
        return $this->role === 'gestor';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function hasEmpresaVinculada(): bool
    {
        return filled($this->empresa_id);
    }

    public function possuiFeaturePlano(string $feature): bool
    {
        return PlanoService::usuarioPossuiFeature($this, $feature);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
