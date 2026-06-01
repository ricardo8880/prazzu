<?php

namespace App\Services;

use App\Models\ItemControle;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ItemControleAssinaturaService
{
    public function status(ItemControle $item): array
    {
        $item->loadMissing(['assinaturas.user', 'empresa', 'responsavel']);

        $assinaturas = $item->assinaturas;
        $payload = $this->assinaturaPayload($item);
        $clicksign = $this->clicksignPayload($item);
        $assinantesPendentes = $this->assinantesPendentes($item);
        $assinantesConcluidos = $assinaturas->map(fn ($assinatura): array => [
            'nome' => $assinatura->nome ?: 'Assinante não identificado',
            'email' => $assinatura->email ?: null,
            'documento' => $assinatura->documento ?: null,
            'assinado_em' => $assinatura->assinado_em,
            'origem' => $assinatura->user_id ? 'Usuário interno' : 'Portal externo',
            'hash' => $assinatura->hash_assinatura,
        ])->values()->all();

        $status = $assinaturas->isNotEmpty() ? 'concluida' : ($item->portal_ativo ? 'aguardando' : 'nao_enviada');

        if (($clicksign['status'] ?? null) && $status !== 'concluida') {
            $status = (string) $clicksign['status'];
        }

        return [
            'status' => $status,
            'label' => $this->labelStatus($status),
            'tone' => $this->toneStatus($status),
            'assinantes_concluidos' => $assinantesConcluidos,
            'assinantes_pendentes' => $assinantesPendentes,
            'total_assinados' => count($assinantesConcluidos),
            'total_pendentes' => count($assinantesPendentes),
            'enviado_em' => $this->parseDate($payload['enviado_em'] ?? null),
            'ultimo_reenvio_em' => $this->parseDate($payload['ultimo_reenvio_em'] ?? null),
            'ultima_consulta_em' => $this->parseDate($payload['ultima_consulta_em'] ?? null),
            'concluido_em' => $this->parseDate($assinaturas->max('assinado_em')),
            'portal_url' => $item->getPortalUrl(),
            'portal_ativo' => (bool) $item->portal_ativo,
            'clicksign' => [
                'habilitado' => $this->clicksignConfigurado(),
                'document_key' => $clicksign['document_key'] ?? $clicksign['documento_key'] ?? null,
                'request_key' => $clicksign['request_key'] ?? null,
                'status' => $clicksign['status'] ?? null,
                'ultima_sincronizacao_em' => $this->parseDate($clicksign['ultima_sincronizacao_em'] ?? null),
                'mensagem' => $clicksign['mensagem'] ?? null,
            ],
        ];
    }

    public function reenviar(ItemControle $item): array
    {
        $item->gerarPortalTokenSeNecessario();

        if (! $item->portal_ativo) {
            $item->forceFill(['portal_ativo' => true])->save();
        }

        $customPayload = $this->customPayload($item);
        $assinaturaPayload = (array) ($customPayload['assinatura'] ?? []);
        $agora = now();

        $assinaturaPayload['enviado_em'] = $assinaturaPayload['enviado_em'] ?? $agora->toDateTimeString();
        $assinaturaPayload['ultimo_reenvio_em'] = $agora->toDateTimeString();
        $assinaturaPayload['canal'] = 'portal_cliente';
        $assinaturaPayload['status'] = $item->foiAssinado() ? 'concluida' : 'aguardando';
        $assinaturaPayload['total_reenvios'] = ((int) ($assinaturaPayload['total_reenvios'] ?? 0)) + 1;

        $customPayload['assinatura'] = $assinaturaPayload;

        $item->forceFill(['custom_payload' => $customPayload])->save();

        $item->registrarTimeline(
            'assinatura',
            'Solicitação de assinatura reenviada',
            'O portal externo foi ativado e o link de assinatura ficou disponível para envio ao cliente.'
        );

        return $this->status($item->refresh());
    }

    public function consultar(ItemControle $item): array
    {
        $customPayload = $this->customPayload($item);
        $assinaturaPayload = (array) ($customPayload['assinatura'] ?? []);
        $clicksignPayload = (array) ($customPayload['clicksign'] ?? []);

        $assinaturaPayload['ultima_consulta_em'] = now()->toDateTimeString();
        $assinaturaPayload['status'] = $item->foiAssinado() ? 'concluida' : ($item->portal_ativo ? 'aguardando' : 'nao_enviada');

        if ($this->clicksignConfigurado() && filled($clicksignPayload['document_key'] ?? null)) {
            $clicksignPayload = $this->consultarClicksign($clicksignPayload);
        } elseif (filled($clicksignPayload)) {
            $clicksignPayload['mensagem'] = 'Clicksign identificado no item, mas token/base URL não configurados no ambiente.';
        }

        $customPayload['assinatura'] = $assinaturaPayload;
        if (filled($clicksignPayload)) {
            $customPayload['clicksign'] = $clicksignPayload;
        }

        $item->forceFill(['custom_payload' => $customPayload])->save();

        $item->registrarTimeline(
            'assinatura',
            'Status de assinatura consultado',
            'A situação da assinatura foi atualizada com base nos registros existentes do item.'
        );

        return $this->status($item->refresh());
    }

    private function consultarClicksign(array $clicksignPayload): array
    {
        $baseUrl = rtrim((string) config('services.clicksign.base_url', env('CLICKSIGN_BASE_URL', '')), '/');
        $token = (string) config('services.clicksign.access_token', env('CLICKSIGN_ACCESS_TOKEN', ''));
        $documentKey = (string) ($clicksignPayload['document_key'] ?? '');

        if ($baseUrl === '' || $token === '' || $documentKey === '') {
            $clicksignPayload['mensagem'] = 'Configuração Clicksign incompleta.';
            return $clicksignPayload;
        }

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->get($baseUrl . '/api/v1/documents/' . $documentKey, [
                    'access_token' => $token,
                ]);

            $clicksignPayload['ultima_sincronizacao_em'] = now()->toDateTimeString();
            $clicksignPayload['http_status'] = $response->status();

            if (! $response->successful()) {
                $clicksignPayload['mensagem'] = 'Não foi possível consultar a Clicksign agora. Código HTTP: ' . $response->status();
                return $clicksignPayload;
            }

            $json = $response->json();
            $document = Arr::get($json, 'document', $json);

            $clicksignPayload['status'] = (string) (Arr::get($document, 'status') ?: Arr::get($document, 'document.status') ?: ($clicksignPayload['status'] ?? 'aguardando'));
            $clicksignPayload['finished_at'] = Arr::get($document, 'finished_at') ?: Arr::get($document, 'deadline_at') ?: ($clicksignPayload['finished_at'] ?? null);
            $clicksignPayload['mensagem'] = 'Status sincronizado com a Clicksign.';
        } catch (\Throwable $exception) {
            $clicksignPayload['ultima_sincronizacao_em'] = now()->toDateTimeString();
            $clicksignPayload['mensagem'] = 'Falha ao consultar Clicksign: ' . Str::limit($exception->getMessage(), 180);
        }

        return $clicksignPayload;
    }

    private function assinantesPendentes(ItemControle $item): array
    {
        if ($item->assinaturas->isNotEmpty()) {
            return [];
        }

        $pendentes = [];

        if (filled($item->portal_cliente_nome) || filled($item->portal_cliente_email)) {
            $pendentes[] = [
                'nome' => $item->portal_cliente_nome ?: 'Cliente do portal',
                'email' => $item->portal_cliente_email ?: null,
                'origem' => 'Portal externo',
            ];
        }

        if (filled($item->contrato_parte_nome) || filled($item->contrato_parte_documento)) {
            $pendentes[] = [
                'nome' => $item->contrato_parte_nome ?: 'Parte do contrato',
                'email' => null,
                'documento' => $item->contrato_parte_documento ?: null,
                'origem' => 'Contrato',
            ];
        }

        if (empty($pendentes)) {
            $pendentes[] = [
                'nome' => 'Aguardando definição do assinante',
                'email' => null,
                'origem' => 'Configuração do item',
            ];
        }

        return collect($pendentes)->unique(fn (array $row): string => strtolower(($row['nome'] ?? '') . '|' . ($row['email'] ?? '') . '|' . ($row['documento'] ?? '')))->values()->all();
    }

    private function assinaturaPayload(ItemControle $item): array
    {
        return (array) ($this->customPayload($item)['assinatura'] ?? []);
    }

    private function clicksignPayload(ItemControle $item): array
    {
        return (array) ($this->customPayload($item)['clicksign'] ?? []);
    }

    private function customPayload(ItemControle $item): array
    {
        return is_array($item->custom_payload) ? $item->custom_payload : [];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function labelStatus(string $status): string
    {
        return match ($status) {
            'concluida', 'closed', 'finished', 'signed' => 'Concluída',
            'aguardando', 'running', 'pending', 'waiting_signature' => 'Aguardando assinatura',
            'cancelada', 'canceled', 'cancelled' => 'Cancelada',
            'recusada', 'refused', 'rejected' => 'Recusada',
            default => 'Não enviada',
        };
    }

    private function toneStatus(string $status): string
    {
        return match ($status) {
            'concluida', 'closed', 'finished', 'signed' => 'success',
            'aguardando', 'running', 'pending', 'waiting_signature' => 'warning',
            'cancelada', 'canceled', 'cancelled', 'recusada', 'refused', 'rejected' => 'danger',
            default => 'gray',
        };
    }

    private function clicksignConfigurado(): bool
    {
        return filled(config('services.clicksign.access_token', env('CLICKSIGN_ACCESS_TOKEN')))
            && filled(config('services.clicksign.base_url', env('CLICKSIGN_BASE_URL')));
    }
}
