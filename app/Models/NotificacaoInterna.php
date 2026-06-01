<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacaoInterna extends Model
{
    protected $table = 'notificacoes_internas';

    protected $fillable = [
        'empresa_id',
        'item_controle_id',
        'user_id',
        'tipo',
        'titulo',
        'mensagem',
        'lida',
        'lida_em',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'item_controle_id' => 'integer',
        'user_id' => 'integer',
        'lida' => 'boolean',
        'lida_em' => 'datetime',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function itemControle()
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function marcarComoLida(): bool
    {
        return $this->update([
            'lida' => true,
            'lida_em' => now(),
        ]);
    }

    public function getTipoExibicao(): string
    {
        return match ($this->tipo) {
            'manual' => 'Manual',
            'aprovacao' => 'Aprovação',
            'prazo' => 'Prazo',
            'sistema' => 'Sistema',
            'sla' => 'SLA',
            'contrato' => 'Contrato',
            default => ucfirst((string) $this->tipo),
        };
    }

    public function getTipoColor(): string
    {
        return match ($this->tipo) {
            'aprovacao' => 'warning',
            'prazo' => 'danger',
            'sistema' => 'info',
            'sla' => 'warning',
            'contrato' => 'info',
            default => 'gray',
        };
    }
}
