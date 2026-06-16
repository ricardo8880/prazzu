<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\AtendimentoStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Atendimento extends Model
{
    protected $table = 'atendimentos';

    protected $fillable = [
        'empresa_id',
        'crm_cliente_id',
        'portal_solicitacao_id',
        'portal_mensagem_id',
        'item_controle_id',
        'responsavel_id',
        'criado_por',
        'titulo',
        'descricao',
        'status',
        'prioridade',
        'origem',
        'canal',
        'sla_horas',
        'sla_limite_em',
        'primeira_resposta_em',
        'resolvido_em',
        'fechado_em',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'crm_cliente_id' => 'integer',
        'portal_solicitacao_id' => 'integer',
        'portal_mensagem_id' => 'integer',
        'item_controle_id' => 'integer',
        'responsavel_id' => 'integer',
        'criado_por' => 'integer',
        'sla_horas' => 'integer',
        'sla_limite_em' => 'datetime',
        'primeira_resposta_em' => 'datetime',
        'resolvido_em' => 'datetime',
        'fechado_em' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(CrmCliente::class, 'crm_cliente_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function portalSolicitacao(): BelongsTo
    {
        return $this->belongsTo(PortalSolicitacao::class, 'portal_solicitacao_id');
    }

    public function portalMensagem(): BelongsTo
    {
        return $this->belongsTo(PortalMensagem::class, 'portal_mensagem_id');
    }

    public function itemControle(): BelongsTo
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function interacoes(): HasMany
    {
        return $this->hasMany(AtendimentoInteracao::class)->latest();
    }

    public function isAberto(): bool
    {
        return AtendimentoStatus::isActive((string) $this->status);
    }
}
