<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogSistema extends Model
{
    protected $table = 'logs_sistema';

    protected $fillable = [
        'user_id',
        'acao',
        'descricao',
        'ip',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
