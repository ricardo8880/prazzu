<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmCliente extends Model
{
    protected $table = 'crm_clientes';

    protected $fillable = [
        'empresa_id',
        'situacao',
        'proxima_acao',
        'ultimo_contato_em',
        'proximo_followup_em',
        'risco_churn',
        'responsavel_user_id',
        'valor_contrato',
        'valor_mensal',
        'proxima_entrega_em',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'responsavel_user_id' => 'integer',
        'ultimo_contato_em' => 'datetime',
        'proximo_followup_em' => 'datetime',
        'proxima_entrega_em' => 'datetime',
        'valor_contrato' => 'decimal:2',
        'valor_mensal' => 'decimal:2',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_user_id');
    }

    public function atendimentos(): HasMany
    {
        return $this->hasMany(Atendimento::class, 'crm_cliente_id');
    }
}
