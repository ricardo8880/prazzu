<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SugestaoMelhoria extends Model
{
    protected $table = 'sugestoes_melhorias';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'tipo',
        'prioridade',
        'status',
        'titulo',
        'descricao',
        'resposta_admin',
        'analisado_por',
        'analisado_em',
    ];

    protected $casts = [
        'analisado_em' => 'datetime',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function analisador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'analisado_por');
    }

    public function scopeVisibleForUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->hasEmpresaVinculada()) {
            return $query->where('empresa_id', $user->empresa_id);
        }

        return $query->where('user_id', $user->id);
    }

    public function getTipoFormatado(): string
    {
        return match ($this->tipo) {
            'bug' => 'Dor ou problema',
            'melhoria' => 'Melhoria',
            'funcionalidade' => 'Ideia de funcionalidade',
            'duvida' => 'Dúvida de uso',
            'outro' => 'Outro',
            default => (string) $this->tipo,
        };
    }

    public function getPrioridadeFormatada(): string
    {
        return match ($this->prioridade) {
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            default => (string) $this->prioridade,
        };
    }

    public function getStatusFormatado(): string
    {
        return match ($this->status) {
            'aberta' => 'Recebida',
            'em_analise' => 'Em análise',
            'aceita' => 'Planejada',
            'recusada' => 'Não seguirá agora',
            'implementada' => 'Implementada',
            default => (string) $this->status,
        };
    }
}