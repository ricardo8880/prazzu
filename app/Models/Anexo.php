<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anexo extends Model
{
    protected $table = 'anexos';

    protected $fillable = [
        'item_controle_id',
        'nome',
        'caminho',
    ];

    public function itemControle()
    {
        return $this->belongsTo(ItemControle::class);
    }
}
