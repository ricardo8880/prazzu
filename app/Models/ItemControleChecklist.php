<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemControleChecklist extends Model
{
    protected $table = 'item_controle_checklists';

    protected $fillable = [
        'item_controle_id',
        'titulo',
        'concluido',
        'concluido_em',
        'concluido_por',
        'ordem',
    ];

    protected $casts = [
        'item_controle_id' => 'integer',
        'concluido' => 'boolean',
        'concluido_em' => 'datetime',
        'concluido_por' => 'integer',
        'ordem' => 'integer',
    ];

    public function itemControle()
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function concluidoPor()
    {
        return $this->belongsTo(User::class, 'concluido_por');
    }

    public function marcarComoConcluido(?User $user = null): bool
    {
        return $this->update([
            'concluido' => true,
            'concluido_em' => now(),
            'concluido_por' => $user?->id,
        ]);
    }

    public function marcarComoPendente(): bool
    {
        return $this->update([
            'concluido' => false,
            'concluido_em' => null,
            'concluido_por' => null,
        ]);
    }
}
