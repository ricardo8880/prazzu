<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrazzuPermissionAudit extends Model
{
    protected $table = 'prazzu_permission_audits';

    protected $fillable = [
        'actor_user_id',
        'target_user_id',
        'role_id',
        'event',
        'module',
        'action',
        'scope',
        'allowed',
        'reason',
        'before_payload',
        'after_payload',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'allowed' => 'boolean',
        'before_payload' => 'array',
        'after_payload' => 'array',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function role()
    {
        return $this->belongsTo(PrazzuRole::class, 'role_id');
    }

    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'role.created' => 'Perfil criado/atualizado',
            'role.permissions.updated' => 'Matriz do perfil alterada',
            'role.status.updated' => 'Status do perfil alterado',
            'user.role.assigned' => 'Perfil vinculado ao usuário',
            'user.role.removed' => 'Perfil removido do usuário',
            'user.override.saved' => 'Exceção individual salva',
            'user.override.removed' => 'Exceção individual removida',
            default => $this->event,
        };
    }
}
