<?php

namespace App\Http\Controllers;

use App\Models\AsaasWebhookEvent;
use App\Services\AsaasService;
use App\Services\AuditoriaTrailService;
use App\Services\Financeiro\AsaasWebhookEventRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AsaasWebhookController extends Controller
{
    public function __invoke(Request $request, AsaasService $asaas, AsaasWebhookEventRecorder $eventRecorder): JsonResponse
    {
        $configuredToken = config('services.asaas.webhook_token');
        $receivedToken = $request->header('asaas-access-token')
            ?: $request->header('access_token')
            ?: $request->input('token');

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

            $asaas->processarWebhook($request->all());

            $eventRecorder->marcarProcessado($webhookEvent);

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
}
