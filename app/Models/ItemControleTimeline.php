<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemControleTimeline extends Model
{
    protected $table = 'item_controle_timeline';

    protected $fillable = [
        'item_controle_id',
        'empresa_id',
        'user_id',
        'tipo',
        'titulo',
        'descricao',
        'dados',
    ];

    protected $casts = [
        'item_controle_id' => 'integer',
        'empresa_id' => 'integer',
        'user_id' => 'integer',
        'dados' => 'array',
    ];

    public function itemControle()
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTipoExibicao(): string
    {
        return match ($this->tipo) {
            'criacao' => 'Criação',
            'atualizacao' => 'Atualização',
            'comentario' => 'Comentário',
            'anexo' => 'Anexo',
            'checklist' => 'Checklist',
            'aprovacao_solicitada' => 'Aprovação solicitada',
            'aprovacao_aprovada' => 'Aprovação aprovada',
            'aprovacao_reprovada' => 'Aprovação reprovada',
            'notificacao' => 'Notificação',
            'assinatura' => 'Assinatura',
            'sla' => 'SLA',
            'contrato' => 'Contrato',
            'etapa' => 'Etapa operacional',
            'auditoria' => 'Auditoria',
            default => ucfirst(str_replace('_', ' ', (string) $this->tipo)),
        };
    }

    public function getTipoIcone(): string
    {
        return match ($this->tipo) {
            'criacao' => '🆕',
            'atualizacao' => '✏️',
            'comentario' => '💬',
            'anexo' => '📎',
            'checklist' => '✅',
            'aprovacao_solicitada' => '📨',
            'aprovacao_aprovada' => '🟢',
            'aprovacao_reprovada' => '🔴',
            'notificacao' => '🔔',
            'assinatura' => '🖊️',
            'sla' => '⏱️',
            'contrato' => '📄',
            'etapa' => '🧩',
            'auditoria' => '🛡️',
            default => '•',
        };
    }

    public function getTipoColor(): string
    {
        return match ($this->tipo) {
            'aprovacao_aprovada', 'assinatura' => 'success',
            'aprovacao_reprovada' => 'danger',
            'aprovacao_solicitada', 'notificacao' => 'warning',
            'comentario' => 'info',
            'sla', 'contrato', 'etapa', 'auditoria' => 'info',
            default => 'gray',
        };
    }
}
