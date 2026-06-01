<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtendimentoInteracao extends Model
{
    protected $table = 'atendimento_interacoes';

    protected $fillable = [
        'atendimento_id',
        'user_id',
        'origem',
        'tipo',
        'mensagem',
        'metadata',
    ];

    protected $casts = [
        'atendimento_id' => 'integer',
        'user_id' => 'integer',
        'metadata' => 'array',
    ];

    public function atendimento(): BelongsTo
    {
        return $this->belongsTo(Atendimento::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
