<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsaasWebhookEvent extends Model
{
    protected $table = 'asaas_webhook_events';

    protected $fillable = [
        'event',
        'gateway_payment_id',
        'gateway_subscription_id',
        'payload_hash',
        'status',
        'attempts',
        'ip',
        'payload',
        'last_error',
        'received_at',
        'processed_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function estaProcessado(): bool
    {
        return $this->status === 'processed';
    }
}
