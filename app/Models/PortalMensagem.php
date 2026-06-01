<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalMensagem extends Model
{
    protected $table = 'portal_mensagens';

    protected $fillable = [
        'empresa_id',
        'item_controle_id',
        'user_id',
        'nome',
        'email',
        'mensagem',
        'origem',
        'conversa_status',
        'visualizada_em',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'item_controle_id' => 'integer',
        'user_id' => 'integer',
        'visualizada_em' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function itemControle(): BelongsTo
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function atendimento(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Atendimento::class, 'portal_mensagem_id');
    }
}
