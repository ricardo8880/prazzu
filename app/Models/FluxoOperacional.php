<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FluxoOperacional extends Model
{
    protected $table = 'fluxos_operacionais';

    protected $fillable = [
        'empresa_id',
        'nome',
        'descricao',
        'tipo_item',
        'padrao',
        'ativo',
    ];

    protected $casts = [
        'padrao' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function etapas()
    {
        return $this->hasMany(FluxoOperacionalEtapa::class, 'fluxo_operacional_id')
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function itens()
    {
        return $this->hasMany(ItemControle::class, 'fluxo_operacional_id');
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
