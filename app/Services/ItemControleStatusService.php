<?php

namespace App\Services;

use App\Models\ItemControle;
use App\Models\User;
use App\Support\CachedSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ItemControleStatusService
{
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_EM_ANDAMENTO = 'em_andamento';
    public const STATUS_ASSINADO = 'assinado';
    public const STATUS_CONCLUIDO = 'concluido';

    public const PORTAL_AGUARDANDO_CLIENTE = 'aguardando_cliente';
    public const PORTAL_CLIENTE_RESPONDEU = 'cliente_respondeu';
    public const PORTAL_DOCUMENTO_ENVIADO = 'documento_enviado';
    public const PORTAL_ASSINADO = 'assinado';
    public const PORTAL_SOLICITACAO_ABERTA = 'solicitacao_aberta';

    public const DOCUMENTO_AGUARDANDO = 'aguardando_documento';
    public const DOCUMENTO_RECEBIDO_PORTAL = 'recebido_pelo_portal';
    public const DOCUMENTO_ASSINADO = 'assinado';

    public const APROVACAO_PENDENTE = 'pendente';
    public const APROVACAO_APROVADO = 'aprovado';
    public const APROVACAO_AGUARDANDO_CLIENTE = 'aguardando_cliente';

    private const STATUS_FINAIS = [
        self::STATUS_CONCLUIDO,
        'concluida',
        'finalizado',
        'finalizada',
        'cancelado',
        'cancelada',
    ];

    public function registrarAssinaturaPortal(ItemControle $item, array $assinatura, ?User $user = null): ItemControle
    {
        $payload = $this->payloadAtual($item);
        $payload['assinatura'] = array_merge((array) ($payload['assinatura'] ?? []), [
            'status' => 'concluida',
            'concluido_em' => $assinatura['concluido_em'] ?? now()->toDateTimeString(),
            'ultimo_assinante_nome' => $assinatura['nome'] ?? null,
            'ultimo_assinante_email' => $assinatura['email'] ?? null,
            'hash_assinatura' => $assinatura['hash_assinatura'] ?? null,
            'canal' => $assinatura['canal'] ?? 'portal_cliente',
        ]);

        return $this->atualizar($item, [
            'status' => $this->statusOperacionalSeguro($item, self::STATUS_ASSINADO),
            'portal_status' => self::PORTAL_ASSINADO,
            'document_status' => self::DOCUMENTO_ASSINADO,
            'approval_status' => self::APROVACAO_APROVADO,
            'ultima_interacao_cliente_em' => now(),
            'custom_payload' => $payload,
        ], 'assinatura', 'Status atualizado após assinatura do portal', [
            'origem' => 'portal_cliente',
            'nome' => $assinatura['nome'] ?? null,
            'email' => $assinatura['email'] ?? null,
        ], $user);
    }

    public function registrarDocumentoPortal(ItemControle $item, array $documento, ?User $user = null): ItemControle
    {
        $payload = $this->payloadAtual($item);
        $documentos = (array) ($payload['documentos_portal'] ?? []);
        $documentos[] = [
            'nome_original' => $documento['nome_original'] ?? null,
            'arquivo' => $documento['arquivo'] ?? null,
            'enviado_em' => now()->toDateTimeString(),
            'client_name' => $documento['client_name'] ?? null,
            'client_email' => $documento['client_email'] ?? null,
        ];
        $payload['documentos_portal'] = $documentos;

        return $this->atualizar($item, [
            'portal_status' => self::PORTAL_DOCUMENTO_ENVIADO,
            'document_status' => self::DOCUMENTO_RECEBIDO_PORTAL,
            'approval_status' => self::APROVACAO_PENDENTE,
            'ultima_interacao_cliente_em' => now(),
            'custom_payload' => $payload,
        ], 'atualizacao', 'Status atualizado após envio de documento pelo portal', [
            'origem' => 'portal_cliente',
            'arquivo' => $documento['arquivo'] ?? null,
            'nome_original' => $documento['nome_original'] ?? null,
        ], $user);
    }

    public function registrarMensagemPortal(ItemControle $item, array $mensagem = [], ?User $user = null): ItemControle
    {
        return $this->atualizar($item, [
            'portal_status' => self::PORTAL_CLIENTE_RESPONDEU,
            'ultima_interacao_cliente_em' => now(),
        ], 'atualizacao', 'Status atualizado após mensagem do cliente', [
            'origem' => 'portal_cliente',
            'client_name' => $mensagem['client_name'] ?? null,
            'client_email' => $mensagem['client_email'] ?? null,
        ], $user);
    }

    public function registrarSolicitacaoCliente(ItemControle $item, array $solicitacao = [], ?User $user = null): ItemControle
    {
        return $this->atualizar($item, [
            'portal_status' => self::PORTAL_SOLICITACAO_ABERTA,
            'approval_status' => self::APROVACAO_AGUARDANDO_CLIENTE,
            'ultima_interacao_cliente_em' => now(),
        ], 'solicitacao', 'Solicitação aberta pelo portal do cliente', [
            'origem' => 'portal_cliente',
            'solicitacao_id' => $solicitacao['solicitacao_id'] ?? null,
            'prioridade' => $solicitacao['prioridade'] ?? null,
        ], $user);
    }

    public function registrarRespostaPendencia(ItemControle $item, array $resposta = [], ?User $user = null): ItemControle
    {
        return $this->atualizar($item, [
            'portal_status' => self::PORTAL_CLIENTE_RESPONDEU,
            'approval_status' => self::APROVACAO_PENDENTE,
            'ultima_interacao_cliente_em' => now(),
        ], 'atualizacao', 'Pendência respondida pelo portal do cliente', [
            'origem' => 'portal_cliente',
            'solicitacao_id' => $resposta['solicitacao_id'] ?? null,
            'nome' => $resposta['nome'] ?? null,
            'email' => $resposta['email'] ?? null,
        ], $user);
    }

    public function atualizar(ItemControle $item, array $dados, string $tipoTimeline, string $tituloTimeline, array $dadosTimeline = [], ?User $user = null): ItemControle
    {
        $payload = [];

        foreach ($dados as $coluna => $valor) {
            if ($coluna === 'status' && $this->statusFinal($item->status) && ! $this->statusFinal($valor)) {
                continue;
            }

            if (CachedSchema::hasColumn($item->getTable(), $coluna)) {
                $payload[$coluna] = $valor;
            }
        }

        if ($payload !== []) {
            $item->forceFill($payload)->save();
        }

        $this->registrarTimelineSegura($item, $tipoTimeline, $tituloTimeline, null, array_filter($dadosTimeline, static fn ($value) => $value !== null), $user);

        return $item->refresh();
    }

    public function statusOperacionalSeguro(ItemControle $item, string $statusDesejado): string
    {
        if ($this->statusFinal($item->status)) {
            return (string) $item->status;
        }

        return $statusDesejado;
    }

    public function statusFinal(?string $status): bool
    {
        return in_array($this->normalizar($status), self::STATUS_FINAIS, true);
    }

    public function normalizar(?string $status): string
    {
        return str((string) $status)
            ->trim()
            ->lower()
            ->ascii()
            ->replace([' ', '-'], '_')
            ->toString();
    }

    private function payloadAtual(ItemControle $item): array
    {
        $payload = $item->custom_payload;

        if (is_array($payload)) {
            return $payload;
        }

        if (is_string($payload) && trim($payload) !== '') {
            $decoded = json_decode($payload, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function registrarTimelineSegura(ItemControle $item, string $tipo, string $titulo, ?string $descricao, array $dados, ?User $user = null): void
    {
        try {
            $item->registrarTimeline($tipo, $titulo, $descricao, $dados, $user);
        } catch (\Throwable $exception) {
            Log::warning('Não foi possível registrar timeline de status do item.', [
                'item_controle_id' => $item->id,
                'empresa_id' => $item->empresa_id,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
