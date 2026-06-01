<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assinatura extends Model
{
    protected $table = 'assinaturas';

    protected $fillable = [
        'empresa_id',
        'gateway',
        'gateway_customer_id',
        'gateway_subscription_id',
        'plano',
        'valor',
        'ciclo',
        'status',
        'proximo_vencimento',
        'cancelado_em',
    ];

    protected function casts(): array
    {
        return [
            'empresa_id' => 'integer',
            'valor' => 'decimal:2',
            'proximo_vencimento' => 'date',
            'cancelado_em' => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class, 'assinatura_id');
    }

    public function estaAtiva(): bool
    {
        return in_array($this->status, ['ACTIVE', 'RECEIVED', 'CONFIRMED'], true);
    }
}
