<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CategoriaItemControle extends Model
{
    protected $table = 'categorias_item_controle';

    protected $fillable = [
        'empresa_id',
        'nome',
        'cor',
        'ativo',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'ativo' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function itens()
    {
        return $this->hasMany(ItemControle::class, 'categoria_id');
    }

    public function checklistTemplates()
    {
        return $this->hasMany(
            CategoriaItemControleChecklistTemplate::class,
            'categoria_item_controle_id'
        )
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function checklistTemplatesAtivos()
    {
        return $this->hasMany(
            CategoriaItemControleChecklistTemplate::class,
            'categoria_item_controle_id'
        )
            ->where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function scopeVisibleForUser(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if (! $user->empresa_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('empresa_id', $user->empresa_id);
    }

    public function isAtiva(): bool
    {
        return (bool) $this->ativo;
    }
}
