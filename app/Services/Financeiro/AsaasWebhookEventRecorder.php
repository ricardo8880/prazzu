<?php

namespace App\Services\Financeiro;

use App\Models\AsaasWebhookEvent;
use App\Support\CachedSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AsaasWebhookEventRecorder
{
    public function registrarRecebimento(array $payload, ?string $ip = null): ?AsaasWebhookEvent
    {
        if (! CachedSchema::hasTable('asaas_webhook_events')) {
            return null;
        }

        $hash = $this->payloadHash($payload);
        $dados = $this->extrairIdentificadores($payload);

        try {
            $evento = AsaasWebhookEvent::query()->firstOrNew([
                'payload_hash' => $hash,
            ]);

            if (! $evento->exists) {
                $evento->fill([
                    'event' => $dados['event'],
                    'gateway_payment_id' => $dados['payment_id'],
                    'gateway_subscription_id' => $dados['subscription_id'],
                    'payload_hash' => $hash,
                    'status' => 'received',
                    'attempts' => 0,
                    'ip' => $ip,
                    'payload' => $this->sanitizarPayload($payload),
                    'received_at' => now(),
                ]);
            }

            if (! $evento->estaProcessado()) {
                $evento->forceFill([
                    'attempts' => ((int) $evento->attempts) + 1,
                    'status' => $evento->status === 'failed' ? 'retrying' : $evento->status,
                    'ip' => $ip ?: $evento->ip,
                    'last_error' => null,
                ]);
            }

            $evento->save();

            return $evento;
        } catch (Throwable $exception) {
            Log::channel('asaas')->warning('Não foi possível registrar evento de webhook Asaas para idempotência.', [
                'message' => $exception->getMessage(),
                'event' => $dados['event'],
                'payment_id' => $dados['payment_id'],
                'subscription_id' => $dados['subscription_id'],
            ]);

            return null;
        }
    }

    public function marcarProcessado(?AsaasWebhookEvent $evento): void
    {
        if (! $evento) {
            return;
        }

        try {
            $evento->forceFill([
                'status' => 'processed',
                'processed_at' => now(),
                'failed_at' => null,
                'last_error' => null,
            ])->save();
        } catch (Throwable $exception) {
            Log::channel('asaas')->warning('Webhook Asaas processado, mas não foi possível atualizar registro de idempotência.', [
                'asaas_webhook_event_id' => $evento->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function marcarFalha(?AsaasWebhookEvent $evento, Throwable $exception): void
    {
        if (! $evento) {
            return;
        }

        try {
            $evento->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'last_error' => Str::limit($exception->getMessage(), 1000),
            ])->save();
        } catch (Throwable $innerException) {
            Log::channel('asaas')->warning('Falha ao persistir erro do webhook Asaas.', [
                'asaas_webhook_event_id' => $evento->id,
                'message' => $innerException->getMessage(),
            ]);
        }
    }

    public function payloadHash(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION) ?: serialize($payload));
    }

    /**
     * @return array{event:?string,payment_id:?string,subscription_id:?string}
     */
    protected function extrairIdentificadores(array $payload): array
    {
        return [
            'event' => $payload['event'] ?? null,
            'payment_id' => Arr::get($payload, 'payment.id'),
            'subscription_id' => Arr::get($payload, 'subscription.id') ?: Arr::get($payload, 'payment.subscription'),
        ];
    }

    protected function sanitizarPayload(array $payload): array
    {
        foreach ($payload as $campo => $valor) {
            if (is_array($valor)) {
                $payload[$campo] = $this->sanitizarPayload($valor);
                continue;
            }

            $campoNormalizado = Str::lower((string) $campo);

            if (str_contains($campoNormalizado, 'token') || str_contains($campoNormalizado, 'key') || str_contains($campoNormalizado, 'password')) {
                $payload[$campo] = '[redacted]';
            }
        }

        return $payload;
    }
}
