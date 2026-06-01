<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ItemControleTag extends Model
{
    protected $table = 'item_controle_tags';

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
        return $this->belongsToMany(
            ItemControle::class,
            'item_controle_tag_relations',
            'item_controle_tag_id',
            'item_controle_id'
        )->withTimestamps();
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
