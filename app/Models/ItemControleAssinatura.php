<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemControleAssinatura extends Model
{
    protected $table = 'item_controle_assinaturas';

    protected $fillable = [
        'item_controle_id',
        'empresa_id',
        'user_id',
        'nome',
        'email',
        'documento',
        'ip',
        'user_agent',
        'observacao',
        'hash_assinatura',
        'aceite_texto',
        'assinado_em',
    ];

    protected $casts = [
        'item_controle_id' => 'integer',
        'empresa_id' => 'integer',
        'user_id' => 'integer',
        'assinado_em' => 'datetime',
    ];

    public function itemControle()
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
