<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditoriaDetalhada extends Model
{
    protected $table = 'auditoria_detalhada';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'auditable_type',
        'auditable_id',
        'evento',
        'nivel',
        'campo',
        'valor_anterior',
        'valor_novo',
        'ip',
        'user_agent',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo(__FUNCTION__, 'auditable_type', 'auditable_id');
    }

    public function scopeVisibleForUser(Builder $query, ?User $user = null): Builder
    {
        $user ??= Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $query;
        }

        return $query->where('empresa_id', $user->empresa_id);
    }
}
