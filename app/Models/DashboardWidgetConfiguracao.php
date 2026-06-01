<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DashboardWidgetConfiguracao extends Model
{
    protected $table = 'dashboard_widget_configuracoes';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'titulo',
        'tipo',
        'fonte',
        'largura',
        'ordem',
        'configuracao',
        'ativo',
    ];

    protected $casts = [
        'configuracao' => 'array',
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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

        return $query->where('empresa_id', $user->empresa_id)
            ->where(function (Builder $builder) use ($user): void {
                $builder->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            });
    }
}
