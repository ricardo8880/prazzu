<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaItemControleChecklistTemplate extends Model
{
    protected $table = 'categoria_item_controle_checklist_templates';

    protected $fillable = [
        'categoria_item_controle_id',
        'titulo',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'categoria_item_controle_id' => 'integer',
        'ordem' => 'integer',
        'ativo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaItemControle::class, 'categoria_item_controle_id');
    }

    public function isAtivo(): bool
    {
        return (bool) $this->ativo;
    }
}
