<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class PortalClienteToken extends Model
{
    protected $table = 'portal_cliente_tokens';

    protected $fillable = [
        'cliente_portal_user_id',
        'email',
        'tipo',
        'token_hash',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'cliente_portal_user_id' => 'integer',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(ClientePortalUser::class, 'cliente_portal_user_id');
    }

    public function estaValido(): bool
    {
        return $this->used_at === null && $this->expires_at !== null && $this->expires_at->isFuture();
    }

    public function confereToken(string $token): bool
    {
        return Hash::check($token, (string) $this->token_hash);
    }
}
