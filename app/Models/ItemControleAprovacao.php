<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemControleAprovacao extends Model
{
    protected $table = 'item_controle_aprovacoes';

    protected $fillable = [
        'item_controle_id',
        'empresa_id',
        'solicitante_id',
        'aprovador_id',
        'status',
        'observacao_solicitacao',
        'observacao_resposta',
        'motivo_reprovacao',
        'solicitado_em',
        'respondido_em',
    ];

    protected $casts = [
        'item_controle_id' => 'integer',
        'empresa_id' => 'integer',
        'solicitante_id' => 'integer',
        'aprovador_id' => 'integer',
        'solicitado_em' => 'datetime',
        'respondido_em' => 'datetime',
    ];

    public function itemControle()
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function aprovador()
    {
        return $this->belongsTo(User::class, 'aprovador_id');
    }

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isAprovado(): bool
    {
        return $this->status === 'aprovado';
    }

    public function isReprovado(): bool
    {
        return $this->status === 'reprovado';
    }

    public function getStatusExibicao(): string
    {
        return match ($this->status) {
            'pendente' => 'Pendente',
            'aprovado' => 'Aprovado',
            'reprovado' => 'Reprovado',
            default => ucfirst((string) $this->status),
        };
    }

    public function getMotivoReprovacao(): ?string
    {
        return $this->motivo_reprovacao ?: $this->observacao_resposta;
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'pendente' => 'warning',
            'aprovado' => 'success',
            'reprovado' => 'danger',
            default => 'gray',
        };
    }
}
