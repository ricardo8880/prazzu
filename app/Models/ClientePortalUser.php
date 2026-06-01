<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ClientePortalUser extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $table = 'cliente_portal_users';

    protected $fillable = [
        'empresa_id',
        'nome',
        'email',
        'password',
        'telefone',
        'cargo',
        'ativo',
        'ultimo_acesso_em',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'empresa_id' => 'integer',
            'ativo' => 'boolean',
            'ultimo_acesso_em' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function estaAtivo(): bool
    {
        return (bool) $this->ativo;
    }
}
