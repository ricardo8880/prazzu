<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemControleNotificacaoLog extends Model
{
    protected $table = 'item_controle_notificacao_logs';

    protected $fillable = [
        'item_controle_id',
        'responsavel_id',
        'user_id',
        'tipo_notificacao',
        'canal',
        'mensagem',
        'status',
        'enviado_em',
    ];

    protected $casts = [
        'item_controle_id' => 'integer',
        'responsavel_id' => 'integer',
        'user_id' => 'integer',
        'enviado_em' => 'datetime',
    ];

    public function itemControle(): BelongsTo
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Responsavel::class, 'responsavel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
