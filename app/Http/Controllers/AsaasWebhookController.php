<?php

namespace App\Http\Controllers;

use App\Services\AsaasService;
use App\Services\AuditoriaManualService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AsaasWebhookController extends Controller
{
    public function __invoke(Request $request, AsaasService $asaas): JsonResponse
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

            AuditoriaManualService::registrarEvento('asaas.webhook.rejected', [
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

            AuditoriaManualService::registrarEvento('asaas.webhook.rejected', [
                'motivo' => 'token_invalido',
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ], null, nivel: 'critical');

            return response()->json(['message' => 'Token inválido.'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            Log::channel('asaas')->info('Webhook Asaas recebido para processamento.', [
                'ip' => $request->ip(),
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ]);

            AuditoriaManualService::registrarEvento('asaas.webhook.received', [
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ], null, nivel: 'info');

            $asaas->processarWebhook($request->all());

            AuditoriaManualService::registrarEvento('asaas.webhook.processed', [
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
            ], null, nivel: 'info');

            return response()->json(['message' => 'Webhook processado.']);
        } catch (Throwable $exception) {
            Log::channel('asaas')->error('Erro ao processar webhook Asaas.', [
                'message' => $exception->getMessage(),
                'payload' => $request->all(),
            ]);

            AuditoriaManualService::registrarEvento('asaas.webhook.failed', [
                'event' => $request->input('event'),
                'payment_id' => data_get($request->all(), 'payment.id'),
                'subscription_id' => data_get($request->all(), 'subscription.id') ?: data_get($request->all(), 'payment.subscription'),
                'erro' => $exception->getMessage(),
            ], null, nivel: 'error');

            return response()->json(['message' => 'Erro ao processar webhook.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
