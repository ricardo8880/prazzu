<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RelatorioPersonalizado extends Model
{
    protected $table = 'relatorios_personalizados';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'nome',
        'descricao',
        'fonte',
        'formato_padrao',
        'ativo',
        'publico',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'publico' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function colunas()
    {
        return $this->hasMany(RelatorioPersonalizadoColuna::class, 'relatorio_id')
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function filtros()
    {
        return $this->hasMany(RelatorioPersonalizadoFiltro::class, 'relatorio_id')
            ->orderBy('ordem')
            ->orderBy('id');
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
                $builder->where('publico', true)
                    ->orWhere('user_id', $user->id);
            });
    }
}
