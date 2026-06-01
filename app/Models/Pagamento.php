<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    protected $table = 'pagamentos';

    protected $fillable = [
        'empresa_id',
        'assinatura_id',
        'gateway_payment_id',
        'status',
        'billing_type',
        'valor',
        'vencimento',
        'pago_em',
        'invoice_url',
        'pix_qr_code',
        'payload_gateway',
    ];

    protected function casts(): array
    {
        return [
            'empresa_id' => 'integer',
            'assinatura_id' => 'integer',
            'valor' => 'decimal:2',
            'vencimento' => 'date',
            'pago_em' => 'datetime',
            'payload_gateway' => 'array',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(Assinatura::class, 'assinatura_id');
    }
}
