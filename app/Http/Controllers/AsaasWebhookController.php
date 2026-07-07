<?php

namespace App\Http\Controllers;

use App\Models\AsaasWebhookEvent;
use App\Services\AsaasService;
use App\Services\AuditoriaTrailService;
use App\Services\Financeiro\AsaasWebhookEventRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AsaasWebhookController extends Controller
{
    public function __invoke(Request $request, AsaasService $asaas, AsaasWebhookEventRecorder $eventRecorder): JsonResponse
    {
        $this->recusarPayloadGrande($request);

        $configuredToken = config('services.asaas.webhook_token');
        $receivedToken = $request->header('asaas-access-token')
            ?: $request->header('access_token');

        if (blank($receivedToken) && (bool) config('services.asaas.webhook_allow_token_input', false)) {
            $receivedToken = $request->input('token');
        }

        if (blank($configuredToken)) {
            Log::channel('asaas')->critical('Webhook Asaas recusado: ASAAS_WEBHOOK_TOKEN não está configurado.', [
                'ip' => $request->ip(),
                'event' => $request->input('event'),
            ]);

            AuditoriaTrailService::financeiro('asaas.webhook.rejected', [
                'motivo' => 'webhook_token_nao_configurado',
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ], null, nivel: 'critical');

            return response()->json(['message' => 'Webhook não configurado.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        if (! hash_equals((string) $configuredToken, (string) $receivedToken)) {
            Log::channel('asaas')->warning('Webhook Asaas recusado por token inválido.', [
                'ip' => $request->ip(),
                'event' => $request->input('event'),
            ]);

            AuditoriaTrailService::financeiro('asaas.webhook.rejected', [
                'motivo' => 'token_invalido',
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ], null, nivel: 'critical');

            return response()->json(['message' => 'Token inválido.'], Response::HTTP_UNAUTHORIZED);
        }

        $webhookEvent = $eventRecorder->registrarRecebimento($request->all(), $request->ip());

        if ($webhookEvent instanceof AsaasWebhookEvent && $webhookEvent->estaProcessado()) {
            Log::channel('asaas')->info('Webhook Asaas duplicado ignorado com segurança.', [
                'asaas_webhook_event_id' => $webhookEvent->id,
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ]);

            AuditoriaTrailService::financeiro('asaas.webhook.duplicate_ignored', [
                'asaas_webhook_event_id' => $webhookEvent->id,
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ], null, nivel: 'info');

            return response()->json(['message' => 'Webhook duplicado já processado.']);
        }

        try {
            Log::channel('asaas')->info('Webhook Asaas recebido para processamento.', [
                'asaas_webhook_event_id' => $webhookEvent?->id,
                'ip' => $request->ip(),
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ]);

            AuditoriaTrailService::financeiro('asaas.webhook.received', [
                'asaas_webhook_event_id' => $webhookEvent?->id,
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ], null, nivel: 'info');

            $lockKey = $this->lockKey($request->all());
            $lock = Cache::lock($lockKey, 30);

            if (! $lock->get()) {
                Log::channel('asaas')->warning('Webhook Asaas recusado temporariamente por processamento concorrente.', [
                    'asaas_webhook_event_id' => $webhookEvent?->id,
                    'event' => $request->input('event'),
                    'payment_id' => data_get($request->all(), 'payment.id'),
                    'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
                ]);

                return response()->json(['message' => 'Webhook em processamento.'], Response::HTTP_CONFLICT);
            }

            try {
                $asaas->processarWebhook($request->all());
                $eventRecorder->marcarProcessado($webhookEvent);
            } finally {
                optional($lock)->release();
            }

            AuditoriaTrailService::financeiro('asaas.webhook.processed', [
                'asaas_webhook_event_id' => $webhookEvent?->id,
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ], null, nivel: 'info');

            return response()->json(['message' => 'Webhook processado.']);
        } catch (InvalidArgumentException $exception) {
            $eventRecorder->marcarFalha($webhookEvent, $exception);
            Log::channel('asaas')->warning('Webhook Asaas inválido recusado durante processamento.', [
                'message' => $exception->getMessage(),
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ]);

            AuditoriaTrailService::financeiro('asaas.webhook.rejected', [
                'asaas_webhook_event_id' => $webhookEvent?->id,
                'motivo' => 'payload_invalido',
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
                'erro' => $exception->getMessage(),
            ], null, nivel: 'warning');

            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $exception) {
            $eventRecorder->marcarFalha($webhookEvent, $exception);
            Log::channel('asaas')->error('Erro ao processar webhook Asaas.', [
                'message' => $exception->getMessage(),
                'payload' => $request->all(),
            ]);

            AuditoriaTrailService::financeiro('asaas.webhook.failed', [
                'asaas_webhook_event_id' => $webhookEvent?->id,
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
                'erro' => $exception->getMessage(),
            ], null, nivel: 'error');

            return response()->json(['message' => 'Erro ao processar webhook.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    private function recusarPayloadGrande(Request $request): void
    {
        $maxKb = max(16, (int) config('services.asaas.webhook_max_payload_kb', 256));
        $contentLength = (int) ($request->server('CONTENT_LENGTH') ?: strlen((string) $request->getContent()));

        abort_if($contentLength > ($maxKb * 1024), Response::HTTP_REQUEST_ENTITY_TOO_LARGE, 'Payload do webhook excede o limite permitido.');
    }

    private function lockKey(array $payload): string
    {
        $event = (string) data_get($payload, 'event', 'unknown');
        $paymentId = (string) data_get($payload, 'payment.id', '');
        $subscriptionId = (string) (data_get($payload, 'subscription.id') ?: data_get($payload, 'payment.subscription', ''));
        $fingerprint = $paymentId !== '' || $subscriptionId !== ''
            ? implode('|', [$event, $paymentId, $subscriptionId])
            : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return 'asaas:webhook:lock:' . hash('sha256', (string) $fingerprint);
    }

}
