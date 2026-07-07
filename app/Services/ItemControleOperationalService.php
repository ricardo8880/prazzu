<?php

namespace App\Services;

use App\Models\ItemControle;
use App\Models\Responsavel;
use App\Models\User;
use App\Support\CachedSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ItemControleOperationalService
{
    public function __construct(
        private readonly PrazzuSlaEngine $slaEngine = new PrazzuSlaEngine(),
    ) {
    }

    /** @param array<string, mixed> $dados */
    public function criarPendencia(array $dados, ?User $user = null): ItemControle
    {
        return DB::transaction(function () use ($dados, $user): ItemControle {
            $payload = $this->filterExistingColumns([
                'titulo' => trim((string) Arr::get($dados, 'titulo')),
                'descricao' => Arr::get($dados, 'descricao'),
                'tipo' => Arr::get($dados, 'tipo', 'pendencia_compliance'),
                'status' => ItemControleCoreService::STATUS_PENDENTE,
                'prioridade' => Arr::get($dados, 'prioridade', 'media'),
                'empresa_id' => Arr::get($dados, 'empresa_id'),
                'responsavel_id' => Arr::get($dados, 'responsavel_id'),
                'data_vencimento' => Arr::get($dados, 'data_vencimento'),
                'status_operacional_at' => now(),
            ]);

            $payload = $this->withSlaStatus($payload);
            $item = ItemControle::query()->create($payload);

            $this->timeline($item, 'criacao', 'Pendência criada', 'Pendência criada pelo fluxo operacional oficial.', [
                'origem' => Arr::get($dados, 'origem', 'operacional'),
                'responsavel_id' => $payload['responsavel_id'] ?? null,
                'data_vencimento' => $payload['data_vencimento'] ?? null,
            ], $user);

            return $item->refresh();
        });
    }

    public function concluir(ItemControle $item, ?User $user = null, ?string $origem = null, ?string $descricao = null): ItemControle
    {
        return DB::transaction(function () use ($item, $user, $origem, $descricao): ItemControle {
            $payload = $this->filterExistingColumns([
                'status' => ItemControleCoreService::STATUS_CONCLUIDO,
                'data_conclusao' => now(),
                'sla_concluido_em' => now(),
                'status_operacional_at' => now(),
            ]);

            $payload = $this->withSlaStatus($payload, $item);
            $this->safeUpdate($item, $payload);

            $this->timeline($item, 'conclusao', 'Item concluído', $descricao ?: 'Item concluído pelo fluxo operacional oficial.', [
                'origem' => $origem ?: 'operacional',
                'status_novo' => ItemControleCoreService::STATUS_CONCLUIDO,
            ], $user);

            return $item->refresh();
        });
    }


    public function alterarStatus(ItemControle $item, string $novoStatus, ?User $user = null, ?string $origem = null, ?string $motivo = null): ItemControle
    {
        $novoStatus = ItemControleCoreService::normalizeStatus($novoStatus);

        if (ItemControleCoreService::isFinalStatus($novoStatus)) {
            return $this->concluir($item, $user, $origem, $motivo ?: 'Status alterado para concluído pelo fluxo operacional oficial.');
        }

        return DB::transaction(function () use ($item, $novoStatus, $user, $origem, $motivo): ItemControle {
            $statusAnterior = ItemControleCoreService::normalizeStatus($item->status);
            $payload = $this->filterExistingColumns([
                'status' => $novoStatus,
                'data_conclusao' => null,
                'sla_concluido_em' => null,
                'status_operacional_at' => now(),
            ]);

            $payload = $this->withSlaStatus($payload, $item);
            $this->safeUpdate($item, $payload);

            $this->timeline($item, 'status_operacional', 'Status operacional atualizado', $motivo ?: 'Status alterado pelo fluxo operacional oficial.', [
                'origem' => $origem ?: 'operacional',
                'status_anterior' => $statusAnterior,
                'status_novo' => $novoStatus,
            ], $user);

            return $item->refresh();
        });
    }

    public function alterarPrazo(ItemControle $item, Carbon|string|null $novoPrazo, ?User $user = null, ?string $origem = null, ?string $motivo = null): ItemControle
    {
        return DB::transaction(function () use ($item, $novoPrazo, $user, $origem, $motivo): ItemControle {
            $prazo = $this->normalizeDate($novoPrazo);
            $prazoAnterior = $item->data_vencimento?->toDateString();

            $payload = $this->filterExistingColumns([
                'data_vencimento' => $prazo,
                'sla_prazo_alvo_em' => $prazo,
                'sla_limite_em' => $prazo,
                'status_operacional_at' => now(),
            ]);

            if (! ItemControleCoreService::isFinalStatus($item->status)) {
                $payload = $this->withSlaStatus($payload, $item);
            }

            $this->safeUpdate($item, $payload);

            $this->timeline($item, 'prazo_alterado', 'Prazo operacional alterado', $motivo ?: 'Prazo alterado pelo fluxo operacional oficial.', [
                'origem' => $origem ?: 'operacional',
                'prazo_anterior' => $prazoAnterior,
                'prazo_novo' => $prazo,
            ], $user);

            return $item->refresh();
        });
    }

    public function alterarResponsavel(ItemControle $item, Responsavel|int|null $novoResponsavel, ?User $user = null, ?string $origem = null, ?string $motivo = null): ItemControle
    {
        return DB::transaction(function () use ($item, $novoResponsavel, $user, $origem, $motivo): ItemControle {
            $responsavelId = $novoResponsavel instanceof Responsavel ? $novoResponsavel->id : $novoResponsavel;
            $responsavelAnteriorId = $item->responsavel_id ? (int) $item->responsavel_id : null;
            $responsavelAnteriorNome = $item->responsavel?->nome ?: 'Sem responsável';
            $responsavelNovoNome = $novoResponsavel instanceof Responsavel
                ? ($novoResponsavel->nome ?: 'Novo responsável')
                : (Responsavel::query()->whereKey($responsavelId)->value('nome') ?: 'Novo responsável');

            $payload = $this->filterExistingColumns([
                'responsavel_id' => $responsavelId,
                'status_operacional_at' => now(),
            ]);

            $this->safeUpdate($item, $payload);

            $this->timeline($item, 'responsavel_alterado', 'Responsável operacional alterado', $motivo ?: "Responsável alterado de {$responsavelAnteriorNome} para {$responsavelNovoNome}.", [
                'origem' => $origem ?: 'operacional',
                'responsavel_anterior_id' => $responsavelAnteriorId,
                'responsavel_novo_id' => $responsavelId,
            ], $user);

            return $item->refresh();
        });
    }

    /** @param array<string, mixed> $payload */
    public function atualizarSituacao(ItemControle $item, array $payload, string $tipoTimeline, string $tituloTimeline, ?string $descricao = null, array $dadosTimeline = [], ?User $user = null): ItemControle
    {
        return DB::transaction(function () use ($item, $payload, $tipoTimeline, $tituloTimeline, $descricao, $dadosTimeline, $user): ItemControle {
            $payload['status_operacional_at'] = now();
            $payload = $this->filterExistingColumns($payload);
            $this->safeUpdate($item, $payload);
            $this->timeline($item, $tipoTimeline, $tituloTimeline, $descricao, $dadosTimeline, $user);

            return $item->refresh();
        });
    }

    /** @param array<string, mixed> $payload */
    private function withSlaStatus(array $payload, ?ItemControle $item = null): array
    {
        if (! CachedSchema::hasColumn('item_controles', 'sla_status')) {
            return $payload;
        }

        $record = [
            'data_vencimento' => $payload['data_vencimento'] ?? $item?->data_vencimento,
            'data_conclusao' => $payload['data_conclusao'] ?? $item?->data_conclusao,
            'sla_limite_em' => $payload['sla_limite_em'] ?? $item?->sla_limite_em,
            'sla_concluido_em' => $payload['sla_concluido_em'] ?? $item?->sla_concluido_em,
        ];

        $payload['sla_status'] = $this->slaEngine->statusForRecord($record);

        return $payload;
    }

    /** @param array<string, mixed> $payload */
    private function filterExistingColumns(array $payload): array
    {
        return collect($payload)
            ->filter(fn ($value, string $column): bool => CachedSchema::hasColumn('item_controles', $column))
            ->all();
    }

    /** @param array<string, mixed> $payload */
    private function safeUpdate(ItemControle $item, array $payload): void
    {
        if ($payload === []) {
            return;
        }

        $item->forceFill($payload)->save();
    }

    private function normalizeDate(Carbon|string|null $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->toDateString();
        }

        return Carbon::parse($value)->toDateString();
    }

    /** @param array<string, mixed> $dados */
    private function timeline(ItemControle $item, string $tipo, string $titulo, ?string $descricao, array $dados = [], ?User $user = null): void
    {
        try {
            $item->registrarTimeline($tipo, $titulo, $descricao, array_filter($dados, static fn ($value) => $value !== null), $user);
        } catch (Throwable $exception) {
            Log::warning('Não foi possível registrar timeline operacional do item.', [
                'item_controle_id' => $item->id,
                'tipo' => $tipo,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
