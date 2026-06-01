<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FluxoOperacionalExecucao extends Model
{
    protected $table = 'fluxos_operacionais_execucoes';

    protected $fillable = [
        'empresa_id',
        'item_controle_id',
        'fluxo_operacional_id',
        'fluxo_operacional_etapa_id',
        'responsavel_id',
        'status',
        'iniciado_em',
        'prazo_em',
        'concluido_em',
        'observacao',
    ];

    protected $casts = [
        'iniciado_em' => 'datetime',
        'prazo_em' => 'datetime',
        'concluido_em' => 'datetime',
    ];


    public function getOrdemAttribute(): int|string
    {
        return $this->etapa?->ordem ?? $this->id;
    }

    public function getTituloAttribute(): string
    {
        return $this->etapa?->nome ?? 'Etapa operacional';
    }

    public function getDescricaoAttribute(): ?string
    {
        return $this->etapa?->descricao ?: $this->observacao;
    }

    public function getStatusExibicao(): string
    {
        return match ($this->status) {
            'pendente' => 'Pendente',
            'em_andamento' => 'Em andamento',
            'concluida', 'concluido' => 'Concluída',
            'cancelada', 'cancelado' => 'Cancelada',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getTempoResumo(): string
    {
        if ($this->concluido_em && $this->iniciado_em) {
            return $this->iniciado_em->diffForHumans($this->concluido_em, true);
        }

        if ($this->prazo_em) {
            return $this->prazo_em->isPast()
                ? 'Vencida ' . $this->prazo_em->diffForHumans()
                : 'Vence ' . $this->prazo_em->diffForHumans();
        }

        if ($this->iniciado_em) {
            return 'Iniciada ' . $this->iniciado_em->diffForHumans();
        }

        return 'Sem prazo';
    }

    public function itemControle()
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function fluxo()
    {
        return $this->belongsTo(FluxoOperacional::class, 'fluxo_operacional_id');
    }

    public function etapa()
    {
        return $this->belongsTo(FluxoOperacionalEtapa::class, 'fluxo_operacional_etapa_id');
    }

    public function responsavel()
    {
        return $this->belongsTo(Responsavel::class);
    }
}
