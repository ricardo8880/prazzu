<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $table = 'comentarios';

    protected $fillable = [
        'item_controle_id',
        'user_id',
        'comentario',
    ];

    public function itemControle()
    {
        return $this->belongsTo(ItemControle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
