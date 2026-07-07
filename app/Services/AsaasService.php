<?php

namespace App\Services;

use App\Models\Assinatura;
use App\Models\Empresa;
use App\Models\Pagamento;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class AsaasService
{
    public function criarAssinaturaParaEmpresa(Empresa $empresa, string $billingType = 'UNDEFINED'): Assinatura
    {
        $plano = PlanoService::normalizarPlano($empresa->plano);

        $customer = $this->criarCliente($empresa);

        $subscription = $this->criarAssinatura(
            $empresa,
            $customer['id'],
            $plano,
            $billingType
        );

        $assinatura = Assinatura::query()->updateOrCreate(
            [
                'gateway' => 'asaas',
                'gateway_subscription_id' => $subscription['id'],
            ],
            [
                'empresa_id' => $empresa->id,
                'gateway_customer_id' => $customer['id'],
                'plano' => $plano,
                'valor' => PlanoService::valorMensal($plano),
                'ciclo' => 'MONTHLY',
                'status' => $subscription['status'] ?? 'PENDING',
                'proximo_vencimento' => $subscription['nextDueDate'] ?? now()->toDateString(),
            ]
        );

        try {
            $payments = $this->listarPagamentosDaAssinatura($subscription['id']);
        } catch (Throwable $exception) {
            $payments = [];

            Log::channel('asaas')->warning('Assinatura criada, mas a cobrança inicial não pôde ser consultada no Asaas.', [
                'empresa_id' => $empresa->id,
                'assinatura_id' => $assinatura->id,
                'gateway_subscription_id' => $subscription['id'],
                'message' => $exception->getMessage(),
            ]);
        }

        $primeiroPagamento = Arr::first($payments['data'] ?? []);

        if ($primeiroPagamento) {
            $this->salvarPagamento($assinatura, $primeiroPagamento);

            $this->sincronizarEmpresaPorPagamento(
                $assinatura->refresh(),
                $primeiroPagamento['status'] ?? null,
                $primeiroPagamento
            );
        } else {
            $this->marcarEmpresaPendentePagamento(
                $empresa,
                'Assinatura criada no Asaas sem cobrança inicial retornada.'
            );
        }

        return $assinatura->refresh();
    }

    public function criarCliente(Empresa $empresa): array
    {
        $payload = array_filter([
            'name' => $empresa->razao_social ?: $empresa->nome_fantasia,
            'email' => $empresa->email,
            'cpfCnpj' => $this->somenteNumeros($empresa->cnpj),
            'phone' => $this->somenteNumeros($empresa->telefone),
            'mobilePhone' => $this->somenteNumeros($empresa->telefone),
            'externalReference' => (string) $empresa->id,
            'notificationDisabled' => false,
        ], fn ($value) => filled($value));

        return $this->post('/customers', $payload);
    }

    public function criarAssinatura(
        Empresa $empresa,
        string $customerId,
        string $plano,
        string $billingType = 'UNDEFINED'
    ): array {
        return $this->post('/subscriptions', [
            'customer' => $customerId,
            'billingType' => $this->normalizarBillingType($billingType),
            'nextDueDate' => now()->toDateString(),
            'value' => PlanoService::valorMensal($plano),
            'cycle' => 'MONTHLY',
            'description' => PlanoService::descricaoAssinatura($plano),
            'externalReference' => (string) $empresa->id,
        ]);
    }

    public function listarPagamentosDaAssinatura(string $subscriptionId): array
    {
        return $this->get("/subscriptions/{$subscriptionId}/payments");
    }

    public function consultarAssinatura(string $subscriptionId): array
    {
        return $this->get("/subscriptions/{$subscriptionId}");
    }

    public function consultarPagamento(string $paymentId): array
    {
        return $this->get("/payments/{$paymentId}");
    }

    public function cancelarAssinatura(Assinatura $assinatura): array
    {
        $assinatura->loadMissing('empresa');

        if (! $assinatura->gateway_subscription_id) {
            throw new RuntimeException('Esta assinatura não possui ID de assinatura no gateway. Nada foi cancelado no Asaas.');
        }

        if ($this->assinaturaCanceladaLocalmente($assinatura)) {
            $this->bloquearEmpresaPorAssinaturaCancelada($assinatura, 'Cancelamento solicitado para assinatura que já estava cancelada localmente.');

            return [
                'status' => $assinatura->status,
                'already_cancelled' => true,
            ];
        }

        $response = $this->delete("/subscriptions/{$assinatura->gateway_subscription_id}");

        $this->bloquearEmpresaPorAssinaturaCancelada($assinatura, 'Assinatura cancelada com sucesso no Asaas.');

        Log::channel('asaas')->info('Assinatura cancelada no Asaas e bloqueada localmente.', [
            'assinatura_id' => $assinatura->id,
            'empresa_id' => $assinatura->empresa_id,
            'gateway_subscription_id' => $assinatura->gateway_subscription_id,
            'response' => $response,
        ]);

        return $response;
    }

    public function salvarPagamento(Assinatura $assinatura, array $payment): Pagamento
    {
        $paymentId = $payment['id'] ?? null;

        if (! $paymentId) {
            throw new RuntimeException('Pagamento recebido do Asaas sem ID. O registro local foi recusado para evitar inconsistência.');
        }

        $status = $payment['status'] ?? null;
        $billingType = $payment['billingType'] ?? null;
        $valor = $payment['value'] ?? $assinatura->valor;
        $vencimento = $payment['dueDate'] ?? null;
        $pagoEm = $payment['paymentDate'] ?? $payment['clientPaymentDate'] ?? null;
        $invoiceUrl = $payment['invoiceUrl'] ?? $payment['bankSlipUrl'] ?? null;

        return Pagamento::query()->updateOrCreate(
            [
                'gateway_payment_id' => $paymentId,
            ],
            [
                'empresa_id' => $assinatura->empresa_id,
                'assinatura_id' => $assinatura->id,
                'status' => $status,
                'billing_type' => $billingType,
                'valor' => $valor,
                'vencimento' => $vencimento,
                'pago_em' => $pagoEm ? Carbon::parse($pagoEm) : null,
                'invoice_url' => $invoiceUrl,
                'pix_qr_code' => $payment['pixQrCode'] ?? null,
                'payload_gateway' => $payment,
            ]
        );
    }

    public function processarWebhook(array $payload): void
    {
        $event = $payload['event'] ?? null;

        $this->validarEventoWebhook($event, $payload);

        $payment = $payload['payment'] ?? null;
        $subscriptionPayload = $payload['subscription'] ?? null;

        if (is_array($payment)) {
            $this->processarWebhookPagamento($event, $payment);
            return;
        }

        if (is_array($subscriptionPayload)) {
            $this->processarWebhookAssinatura($event, $subscriptionPayload);
            return;
        }

        Log::channel('asaas')->warning('Webhook Asaas recusado: payload sem payment/subscription reconhecível.', [
            'event' => $event,
            'payload_keys' => array_keys($payload),
        ]);

        throw new InvalidArgumentException('Webhook Asaas inválido: payload sem payment/subscription reconhecível.');
    }

    public function reconciliarAssinatura(Assinatura $assinatura): array
    {
        $assinatura->loadMissing('empresa');

        if (! $assinatura->gateway_subscription_id) {
            Log::channel('asaas')->warning('Reconciliação Asaas ignorada: assinatura sem ID no gateway.', [
                'assinatura_id' => $assinatura->id,
                'empresa_id' => $assinatura->empresa_id,
            ]);

            return [
                'assinatura_id' => $assinatura->id,
                'sincronizada' => false,
                'motivo' => 'sem_gateway_subscription_id',
            ];
        }

        $subscriptionPayload = $this->consultarAssinatura($assinatura->gateway_subscription_id);

        $assinatura->forceFill([
            'gateway_customer_id' => $subscriptionPayload['customer'] ?? $assinatura->gateway_customer_id,
            'status' => $subscriptionPayload['status'] ?? $assinatura->status,
            'proximo_vencimento' => $subscriptionPayload['nextDueDate'] ?? $assinatura->proximo_vencimento,
        ])->save();

        $payments = $this->listarPagamentosDaAssinatura($assinatura->gateway_subscription_id);
        $pagamentosSincronizados = 0;
        $pagamentoMaisRelevante = null;

        foreach ($payments['data'] ?? [] as $payment) {
            if (! is_array($payment) || ! filled($payment['id'] ?? null)) {
                continue;
            }

            $this->salvarPagamento($assinatura->refresh(), $payment);
            $pagamentosSincronizados++;

            if (! $pagamentoMaisRelevante || $this->pagamentoConfirmaAcesso($payment['status'] ?? null)) {
                $pagamentoMaisRelevante = $payment;
            }
        }

        if ($pagamentoMaisRelevante) {
            $this->sincronizarEmpresaPorPagamento(
                $assinatura->refresh(),
                $pagamentoMaisRelevante['status'] ?? null,
                $pagamentoMaisRelevante
            );
        } elseif ($this->assinaturaJaCancelada($subscriptionPayload['status'] ?? null)) {
            $this->bloquearEmpresaPorAssinaturaCancelada($assinatura->refresh(), 'Reconciliação encontrou assinatura cancelada/inativa no Asaas.');
        } elseif ($this->assinaturaAtivaNoGateway($subscriptionPayload['status'] ?? null)) {
            $this->ativarEmpresaPorAssinatura($assinatura->refresh(), $subscriptionPayload);
        }

        Log::channel('asaas')->info('Reconciliação de assinatura Asaas concluída.', [
            'assinatura_id' => $assinatura->id,
            'empresa_id' => $assinatura->empresa_id,
            'gateway_subscription_id' => $assinatura->gateway_subscription_id,
            'status_gateway' => $subscriptionPayload['status'] ?? null,
            'pagamentos_sincronizados' => $pagamentosSincronizados,
        ]);

        return [
            'assinatura_id' => $assinatura->id,
            'sincronizada' => true,
            'status_gateway' => $subscriptionPayload['status'] ?? null,
            'pagamentos_sincronizados' => $pagamentosSincronizados,
        ];
    }

    public function reconciliarAssinaturasPendentes(int $limit = 50): array
    {
        $assinaturas = Assinatura::query()
            ->where('gateway', 'asaas')
            ->whereNotNull('gateway_subscription_id')
            ->whereIn('status', [
                'PENDING',
                'OVERDUE',
                'INACTIVE',
                'ACTIVE',
                'RECEIVED',
                'CONFIRMED',
            ])
            ->orderByDesc('updated_at')
            ->limit(max(1, min($limit, 200)))
            ->get();

        $resultado = [
            'total' => $assinaturas->count(),
            'sincronizadas' => 0,
            'falhas' => 0,
            'ignoradas' => 0,
        ];

        foreach ($assinaturas as $assinatura) {
            try {
                $retorno = $this->reconciliarAssinatura($assinatura);

                if ($retorno['sincronizada'] ?? false) {
                    $resultado['sincronizadas']++;
                } else {
                    $resultado['ignoradas']++;
                }
            } catch (Throwable $exception) {
                $resultado['falhas']++;

                Log::channel('asaas')->error('Falha ao reconciliar assinatura Asaas.', [
                    'assinatura_id' => $assinatura->id,
                    'empresa_id' => $assinatura->empresa_id,
                    'gateway_subscription_id' => $assinatura->gateway_subscription_id,
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ]);
            }
        }

        return $resultado;
    }

    protected function validarEventoWebhook(?string $event, array $payload): void
    {
        if (blank($event)) {
            Log::channel('asaas')->warning('Webhook Asaas recusado: evento ausente.', [
                'payload_keys' => array_keys($payload),
            ]);

            throw new InvalidArgumentException('Webhook Asaas inválido: evento ausente.');
        }

        $event = Str::upper((string) $event);
        $eventosPermitidos = config('services.asaas.webhook_events', []);

        if (! in_array($event, $eventosPermitidos, true)) {
            Log::channel('asaas')->warning('Webhook Asaas recusado: evento não homologado no projeto.', [
                'event' => $event,
                'payment_id' => data_get($payload, 'payment.id'),
                'subscription_id' => data_get($payload, 'subscription.id') ?: data_get($payload, 'payment.subscription'),
            ]);

            throw new InvalidArgumentException("Webhook Asaas inválido: evento {$event} não homologado.");
        }
    }

    protected function processarWebhookPagamento(?string $event, array $payment): void
    {
        $subscriptionId = $payment['subscription'] ?? null;
        $paymentId = $payment['id'] ?? null;
        $assinatura = $this->resolverAssinaturaDoPagamento($payment);

        if (! $paymentId) {
            Log::channel('asaas')->warning('Webhook de pagamento Asaas recusado: pagamento sem ID.', [
                'event' => $event,
                'gateway_subscription_id' => $subscriptionId,
                'status' => $payment['status'] ?? null,
            ]);

            throw new InvalidArgumentException('Webhook Asaas inválido: pagamento sem ID.');
        }

        if (! $assinatura) {
            $assinatura = $this->tentarReconciliarAssinaturaDoPagamento($payment);
        }

        if (! $assinatura) {
            Log::channel('asaas')->warning('Webhook de pagamento Asaas aguardando reconciliação: assinatura local não encontrada.', [
                'event' => $event,
                'gateway_subscription_id' => $subscriptionId,
                'gateway_payment_id' => $paymentId,
                'external_reference' => $payment['externalReference'] ?? null,
                'customer' => $payment['customer'] ?? null,
                'status' => $payment['status'] ?? null,
            ]);

            throw new RuntimeException('Webhook Asaas não reconciliado: assinatura local não encontrada.');
        }

        if ($subscriptionId && ! $assinatura->gateway_subscription_id) {
            $assinatura->forceFill(['gateway_subscription_id' => $subscriptionId])->save();
        }

        $pagamento = $this->salvarPagamento($assinatura->refresh(), $payment);

        $this->sincronizarEmpresaPorPagamento(
            $assinatura->refresh(),
            $payment['status'] ?? $event,
            $payment
        );

        Log::channel('asaas')->info('Webhook de pagamento Asaas processado.', [
            'event' => $event,
            'assinatura_id' => $assinatura->id,
            'empresa_id' => $assinatura->empresa_id,
            'pagamento_id' => $pagamento->id,
            'gateway_payment_id' => $paymentId,
            'gateway_subscription_id' => $subscriptionId,
            'status' => $payment['status'] ?? null,
        ]);
    }

    protected function processarWebhookAssinatura(?string $event, array $subscriptionPayload): void
    {
        $subscriptionId = $subscriptionPayload['id'] ?? null;

        if (! $subscriptionId) {
            Log::channel('asaas')->warning('Webhook de assinatura Asaas ignorado: ID da assinatura ausente.', [
                'event' => $event,
            ]);

            return;
        }

        $assinatura = Assinatura::query()
            ->where('gateway', 'asaas')
            ->where('gateway_subscription_id', $subscriptionId)
            ->first();

        if (! $assinatura) {
            $assinatura = $this->resolverAssinaturaPorExternalReference($subscriptionPayload, $subscriptionId);
        }

        if (! $assinatura) {
            Log::channel('asaas')->warning('Webhook de assinatura Asaas aguardando reconciliação: assinatura local não encontrada.', [
                'event' => $event,
                'gateway_subscription_id' => $subscriptionId,
                'external_reference' => $subscriptionPayload['externalReference'] ?? null,
                'customer' => $subscriptionPayload['customer'] ?? null,
                'status' => $subscriptionPayload['status'] ?? null,
            ]);

            return;
        }

        if (in_array($event, ['SUBSCRIPTION_DELETED', 'SUBSCRIPTION_INACTIVATED'], true)) {
            $this->bloquearEmpresaPorAssinaturaCancelada($assinatura, 'Webhook Asaas informou assinatura cancelada/inativa.');

            return;
        }

        if (in_array($event, ['SUBSCRIPTION_CREATED', 'SUBSCRIPTION_UPDATED'], true)) {
            $assinatura->forceFill([
                'gateway_customer_id' => $subscriptionPayload['customer'] ?? $assinatura->gateway_customer_id,
                'gateway_subscription_id' => $subscriptionId,
                'status' => $subscriptionPayload['status'] ?? $assinatura->status,
                'proximo_vencimento' => $subscriptionPayload['nextDueDate'] ?? $assinatura->proximo_vencimento,
            ])->save();

            if ($this->assinaturaAtivaNoGateway($subscriptionPayload['status'] ?? null)) {
                $this->ativarEmpresaPorAssinatura($assinatura->refresh(), $subscriptionPayload);
            }

            Log::channel('asaas')->info('Webhook de assinatura Asaas atualizado localmente.', [
                'event' => $event,
                'assinatura_id' => $assinatura->id,
                'empresa_id' => $assinatura->empresa_id,
                'gateway_subscription_id' => $subscriptionId,
                'status' => $subscriptionPayload['status'] ?? null,
            ]);

            return;
        }

        Log::channel('asaas')->info('Webhook de assinatura Asaas recebido sem transição local configurada.', [
            'event' => $event,
            'assinatura_id' => $assinatura->id,
            'empresa_id' => $assinatura->empresa_id,
            'gateway_subscription_id' => $subscriptionId,
            'status' => $subscriptionPayload['status'] ?? null,
        ]);
    }

    protected function tentarReconciliarAssinaturaDoPagamento(array $payment): ?Assinatura
    {
        $subscriptionId = $payment['subscription'] ?? null;

        if (! $subscriptionId) {
            return null;
        }

        try {
            $subscriptionPayload = $this->consultarAssinatura($subscriptionId);
        } catch (Throwable $exception) {
            Log::channel('asaas')->warning('Não foi possível consultar assinatura Asaas durante reconciliação de pagamento.', [
                'gateway_subscription_id' => $subscriptionId,
                'gateway_payment_id' => $payment['id'] ?? null,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        $assinatura = $this->resolverAssinaturaPorExternalReference(
            array_merge($subscriptionPayload, [
                'status' => $subscriptionPayload['status'] ?? $payment['status'] ?? null,
                'customer' => $subscriptionPayload['customer'] ?? $payment['customer'] ?? null,
                'nextDueDate' => $subscriptionPayload['nextDueDate'] ?? $payment['dueDate'] ?? null,
            ]),
            $subscriptionId
        );

        if ($assinatura) {
            Log::channel('asaas')->info('Assinatura local reconciliada automaticamente antes de processar pagamento Asaas.', [
                'assinatura_id' => $assinatura->id,
                'empresa_id' => $assinatura->empresa_id,
                'gateway_subscription_id' => $subscriptionId,
                'gateway_payment_id' => $payment['id'] ?? null,
            ]);
        }

        return $assinatura;
    }

    protected function resolverAssinaturaDoPagamento(array $payment): ?Assinatura
    {
        $subscriptionId = $payment['subscription'] ?? null;
        $paymentId = $payment['id'] ?? null;

        if ($subscriptionId) {
            $assinatura = Assinatura::query()
                ->where('gateway', 'asaas')
                ->where('gateway_subscription_id', $subscriptionId)
                ->first();

            if ($assinatura) {
                return $assinatura;
            }
        }

        if ($paymentId) {
            $pagamento = Pagamento::query()
                ->where('gateway_payment_id', $paymentId)
                ->with('assinatura')
                ->first();

            if ($pagamento?->assinatura) {
                return $pagamento->assinatura;
            }
        }

        if ($subscriptionId) {
            return $this->resolverAssinaturaPorExternalReference($payment, $subscriptionId);
        }

        return null;
    }

    protected function resolverAssinaturaPorExternalReference(array $payload, string $subscriptionId): ?Assinatura
    {
        $empresaId = $payload['externalReference'] ?? null;

        if (! $empresaId || ! is_numeric($empresaId)) {
            return null;
        }

        $empresa = Empresa::query()->find((int) $empresaId);

        if (! $empresa) {
            return null;
        }

        $assinatura = Assinatura::query()
            ->where('gateway', 'asaas')
            ->where('empresa_id', $empresa->id)
            ->where(function ($query) use ($subscriptionId): void {
                $query->whereNull('gateway_subscription_id')
                    ->orWhere('gateway_subscription_id', '')
                    ->orWhere('gateway_subscription_id', $subscriptionId);
            })
            ->latest('id')
            ->first();

        if (! $assinatura) {
            Log::channel('asaas')->warning('Asaas enviou externalReference de empresa válida, mas não há assinatura local para vincular automaticamente.', [
                'empresa_id' => $empresa->id,
                'gateway_subscription_id' => $subscriptionId,
                'status' => $payload['status'] ?? null,
            ]);

            return null;
        }

        $assinatura->forceFill([
            'gateway_customer_id' => $payload['customer'] ?? $assinatura->gateway_customer_id,
            'gateway_subscription_id' => $subscriptionId,
            'status' => $payload['status'] ?? $assinatura->status,
            'proximo_vencimento' => $payload['nextDueDate'] ?? $payload['dueDate'] ?? $assinatura->proximo_vencimento,
        ])->save();

        Log::channel('asaas')->info('Assinatura local reconciliada por externalReference do Asaas.', [
            'assinatura_id' => $assinatura->id,
            'empresa_id' => $empresa->id,
            'gateway_subscription_id' => $subscriptionId,
        ]);

        return $assinatura->refresh();
    }

    protected function sincronizarEmpresaPorPagamento(Assinatura $assinatura, ?string $statusPagamento, array $payment = []): void
    {
        $empresa = $assinatura->empresa;

        if (! $empresa) {
            Log::channel('asaas')->warning('Pagamento Asaas não sincronizou empresa: assinatura sem empresa vinculada.', [
                'assinatura_id' => $assinatura->id,
                'gateway_subscription_id' => $assinatura->gateway_subscription_id,
                'gateway_payment_id' => $payment['id'] ?? null,
                'status_pagamento' => $statusPagamento,
            ]);

            return;
        }

        if ($this->pagamentoConfirmaAcesso($statusPagamento)) {
            $assinatura->forceFill([
                'status' => 'ACTIVE',
                'proximo_vencimento' => $payment['dueDate'] ?? $assinatura->proximo_vencimento,
            ])->save();

            $empresa->forceFill([
                'status' => 'ativo',
                'ativo' => true,
            ])->save();

            Log::channel('asaas')->info('Empresa ativada por pagamento confirmado no Asaas.', [
                'empresa_id' => $empresa->id,
                'assinatura_id' => $assinatura->id,
                'gateway_payment_id' => $payment['id'] ?? null,
                'status_pagamento' => $statusPagamento,
            ]);

            return;
        }

        if ($this->pagamentoBloqueiaAcesso($statusPagamento)) {
            $assinatura->forceFill([
                'status' => 'INACTIVE',
            ])->save();

            $empresa->forceFill([
                'status' => 'bloqueado_pagamento',
                'ativo' => false,
            ])->save();

            Log::channel('asaas')->warning('Empresa bloqueada por status de pagamento no Asaas.', [
                'empresa_id' => $empresa->id,
                'assinatura_id' => $assinatura->id,
                'gateway_payment_id' => $payment['id'] ?? null,
                'status_pagamento' => $statusPagamento,
            ]);

            return;
        }

        $this->marcarEmpresaPendentePagamento(
            $empresa,
            'Pagamento Asaas ainda não confirmou acesso.',
            [
                'assinatura_id' => $assinatura->id,
                'gateway_subscription_id' => $assinatura->gateway_subscription_id,
                'gateway_payment_id' => $payment['id'] ?? null,
                'status_pagamento' => $statusPagamento,
            ]
        );
    }

    protected function marcarEmpresaPendentePagamento(Empresa $empresa, string $motivo, array $contexto = []): void
    {
        if ((bool) $empresa->ativo === true && (string) $empresa->status === 'ativo') {
            Log::channel('asaas')->info('Empresa já está ativa; status pendente do Asaas não rebaixou acesso.', array_merge([
                'empresa_id' => $empresa->id,
                'motivo' => $motivo,
            ], $contexto));

            return;
        }

        $empresa->forceFill([
            'status' => 'pendente_pagamento',
            'ativo' => false,
        ])->save();

        Log::channel('asaas')->info('Empresa mantida pendente até confirmação de pagamento.', array_merge([
            'empresa_id' => $empresa->id,
            'motivo' => $motivo,
        ], $contexto));
    }

    protected function ativarEmpresaPorAssinatura(Assinatura $assinatura, array $subscriptionPayload = []): void
    {
        $empresa = $assinatura->empresa;

        if (! $empresa) {
            Log::channel('asaas')->warning('Assinatura ativa no Asaas não ativou empresa: assinatura sem empresa vinculada.', [
                'assinatura_id' => $assinatura->id,
                'gateway_subscription_id' => $assinatura->gateway_subscription_id,
                'status' => $subscriptionPayload['status'] ?? null,
            ]);

            return;
        }

        $assinatura->forceFill([
            'status' => 'ACTIVE',
            'proximo_vencimento' => $subscriptionPayload['nextDueDate'] ?? $assinatura->proximo_vencimento,
        ])->save();

        $empresa->forceFill([
            'status' => 'ativo',
            'ativo' => true,
        ])->save();

        Log::channel('asaas')->info('Empresa ativada por assinatura ativa no Asaas.', [
            'empresa_id' => $empresa->id,
            'assinatura_id' => $assinatura->id,
            'gateway_subscription_id' => $assinatura->gateway_subscription_id,
        ]);
    }

    protected function bloquearEmpresaPorAssinaturaCancelada(Assinatura $assinatura, string $motivo): void
    {
        $assinatura->forceFill([
            'status' => 'CANCELLED',
            'cancelado_em' => $assinatura->cancelado_em ?: now(),
        ])->save();

        $assinatura->empresa?->forceFill([
            'status' => 'bloqueado_pagamento',
            'ativo' => false,
        ])->save();

        Log::channel('asaas')->warning('Empresa bloqueada por assinatura cancelada/inativa.', [
            'assinatura_id' => $assinatura->id,
            'empresa_id' => $assinatura->empresa_id,
            'gateway_subscription_id' => $assinatura->gateway_subscription_id,
            'motivo' => $motivo,
        ]);
    }

    protected function pagamentoConfirmaAcesso(?string $status): bool
    {
        return in_array(Str::upper((string) $status), [
            'RECEIVED',
            'CONFIRMED',
            'PAYMENT_RECEIVED',
            'PAYMENT_CONFIRMED',
        ], true);
    }

    protected function pagamentoBloqueiaAcesso(?string $status): bool
    {
        return in_array(Str::upper((string) $status), [
            'OVERDUE',
            'DELETED',
            'REFUNDED',
            'CHARGEBACK_REQUESTED',
            'CHARGEBACK_DISPUTE',
            'AWAITING_CHARGEBACK_REVERSAL',
            'PAYMENT_OVERDUE',
            'PAYMENT_DELETED',
            'PAYMENT_REFUNDED',
            'PAYMENT_CHARGEBACK_REQUESTED',
            'PAYMENT_CHARGEBACK_DISPUTE',
            'PAYMENT_AWAITING_CHARGEBACK_REVERSAL',
        ], true);
    }


    protected function assinaturaCanceladaLocalmente(Assinatura $assinatura): bool
    {
        return filled($assinatura->cancelado_em)
            || in_array(Str::upper((string) $assinatura->status), [
                'CANCELLED',
                'CANCELED',
                'DELETED',
                'EXPIRED',
            ], true);
    }

    protected function assinaturaJaCancelada(?string $status): bool
    {
        return in_array(Str::upper((string) $status), [
            'CANCELLED',
            'CANCELED',
            'INACTIVE',
            'DELETED',
            'EXPIRED',
        ], true);
    }

    protected function assinaturaAtivaNoGateway(?string $status): bool
    {
        return in_array(Str::upper((string) $status), [
            'ACTIVE',
            'RECEIVED',
            'CONFIRMED',
        ], true);
    }

    protected function get(string $uri): array
    {
        $response = $this->client()->get($uri);

        if ($response->failed()) {
            Log::channel('asaas')->error('Falha em requisição GET para o Asaas.', [
                'uri' => $uri,
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);

            throw new RuntimeException($this->formatarErro($response->status(), $response->json(), $response->body()));
        }

        return $response->json() ?? [];
    }

    protected function post(string $uri, array $payload): array
    {
        $response = $this->client()->post($uri, $payload);

        if ($response->failed()) {
            Log::channel('asaas')->error('Falha em requisição POST para o Asaas.', [
                'uri' => $uri,
                'status' => $response->status(),
                'payload' => $this->sanitizarPayloadLog($payload),
                'response' => $response->json() ?? $response->body(),
            ]);

            throw new RuntimeException($this->formatarErro($response->status(), $response->json(), $response->body()));
        }

        return $response->json() ?? [];
    }

    protected function delete(string $uri): array
    {
        $response = $this->client()->delete($uri);

        if ($response->failed()) {
            Log::channel('asaas')->error('Falha em requisição DELETE para o Asaas.', [
                'uri' => $uri,
                'status' => $response->status(),
                'response' => $response->json() ?? $response->body(),
            ]);

            throw new RuntimeException($this->formatarErro($response->status(), $response->json(), $response->body()));
        }

        return $response->json() ?? [];
    }

    protected function client(): PendingRequest
    {
        $apiKey = config('services.asaas.api_key');

        if (! $apiKey) {
            throw new RuntimeException('Token do Asaas não configurado.');
        }

        return Http::baseUrl(rtrim((string) config('services.asaas.base_url'), '/'))
            ->timeout((int) config('services.asaas.timeout', 30))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'access_token' => $apiKey,
            ]);
    }

    protected function sanitizarPayloadLog(array $payload): array
    {
        return collect($payload)
            ->mapWithKeys(function ($value, string $key): array {
                if (in_array(Str::lower($key), ['cpfcnpj', 'cpf_cnpj', 'password', 'token', 'api_key', 'access_token'], true)) {
                    return [$key => '[redacted]'];
                }

                return [$key => $value];
            })
            ->all();
    }

    protected function formatarErro(int $status, ?array $json, string $body): string
    {
        $errors = collect($json['errors'] ?? [])
            ->map(fn ($error): string => trim(($error['code'] ?? '') . ' ' . ($error['description'] ?? '')))
            ->filter()
            ->implode(' | ');

        return 'Erro ao comunicar com Asaas. HTTP ' . $status . ($errors ? ': ' . $errors : ': ' . Str::limit($body, 500));
    }

    protected function somenteNumeros(?string $valor): ?string
    {
        if (! $valor) {
            return null;
        }

        $numeros = preg_replace('/\D+/', '', $valor);

        return $numeros !== '' ? $numeros : null;
    }

    protected function normalizarBillingType(?string $billingType): string
    {
        $billingType = strtoupper((string) $billingType);

        return in_array($billingType, ['UNDEFINED', 'BOLETO', 'CREDIT_CARD', 'PIX'], true)
            ? $billingType
            : 'UNDEFINED';
    }
}
