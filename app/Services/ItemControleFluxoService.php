<?php

namespace App\Services;

use App\Models\ItemControle;
use App\Models\ItemControleAnexo;
use App\Models\ItemControleAprovacao;
use App\Models\ItemControleChecklist;
use App\Models\ItemControleComentario;
use App\Models\Responsavel;
use App\Models\User;
use App\Support\CachedSchema;
use App\Support\ItemControleStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ItemControleFluxoService
{
    /**
     * Transições permitidas para impedir saltos ambíguos no fluxo operacional.
     * Métodos dedicados abaixo tratam exceções como aprovação, reprovação e conclusão validada.
     */
    private const TRANSICOES = [
        'pendente' => ['em_andamento', 'cancelado'],
        'em_andamento' => ['pronto', 'em_revisao', 'aguardando_aprovacao', 'em_aprovacao', 'concluido', 'cancelado'],
        'pronto' => ['em_revisao', 'aguardando_aprovacao', 'em_aprovacao', 'concluido', 'cancelado'],
        'em_revisao' => ['em_andamento', 'correcao_necessaria', 'aguardando_aprovacao', 'em_aprovacao', 'concluido', 'cancelado'],
        'correcao_necessaria' => ['em_andamento', 'em_revisao', 'cancelado'],
        'aguardando_aprovacao' => ['em_aprovacao', 'aprovado', 'reprovado', 'cancelado'],
        'em_aprovacao' => ['aprovado', 'reprovado', 'cancelado'],
        'reprovado' => ['correcao_necessaria', 'em_andamento', 'cancelado'],
        'aprovado' => ['assinado', 'concluido', 'cancelado'],
        'assinado' => ['concluido', 'cancelado'],
        'vencido' => ['em_andamento', 'correcao_necessaria', 'cancelado'],
    ];

    public function atualizarStatus(ItemControle $item, string $status, ?User $user = null, ?string $motivo = null): ItemControle
    {
        $this->garantirPodeModificar($item, $user);

        $statusAnterior = ItemControleCoreService::normalizeStatus($item->status);
        $statusNovo = ItemControleCoreService::normalizeStatus($status);

        if ($statusAnterior === $statusNovo) {
            return $item->refresh();
        }

        $this->validarTransicao($item, $statusAnterior, $statusNovo);

        return DB::transaction(function () use ($item, $statusAnterior, $statusNovo, $motivo, $user): ItemControle {
            $payload = $this->payloadStatus($item, $statusNovo);
            $item->forceFill($payload)->save();

            $item->registrarTimeline(
                'status_operacional',
                $statusNovo === ItemControleCoreService::STATUS_CONCLUIDO ? 'Item concluído' : 'Status atualizado',
                'Status alterado de "' . ItemControleCoreService::statusLabel($statusAnterior) . '" para "' . ItemControleCoreService::statusLabel($statusNovo) . '".',
                [
                    'status_anterior' => $statusAnterior,
                    'status_novo' => $statusNovo,
                    'motivo' => $motivo,
                ],
                $user
            );

            return $item->refresh();
        });
    }

    public function atualizarPrazo(ItemControle $item, string|\DateTimeInterface|null $dataVencimento, ?User $user = null, ?string $motivo = null): ItemControle
    {
        $this->garantirPodeModificar($item, $user);

        if (ItemControleCoreService::isFinalStatus($item->status)) {
            throw ValidationException::withMessages(['data_vencimento' => 'Não é possível alterar prazo de item finalizado. Reabra o item antes.']);
        }

        $prazoAnterior = $item->data_vencimento?->toDateString();
        $novoPrazo = $dataVencimento ? \Illuminate\Support\Carbon::parse($dataVencimento)->toDateString() : null;

        return DB::transaction(function () use ($item, $prazoAnterior, $novoPrazo, $motivo, $user): ItemControle {
            $item->forceFill(['data_vencimento' => $novoPrazo])->save();

            $item->registrarTimeline(
                'prazo',
                'Prazo atualizado',
                'Prazo alterado de ' . ($prazoAnterior ?: 'sem prazo') . ' para ' . ($novoPrazo ?: 'sem prazo') . '.',
                [
                    'prazo_anterior' => $prazoAnterior,
                    'prazo_novo' => $novoPrazo,
                    'motivo' => $motivo,
                ],
                $user
            );

            return $item->refresh();
        });
    }

    public function atribuirResponsavel(ItemControle $item, int $responsavelId, ?User $user = null, ?string $motivo = null): ItemControle
    {
        $this->garantirPodeModificar($item, $user);

        $responsavel = Responsavel::query()
            ->whereKey($responsavelId)
            ->where('empresa_id', $item->empresa_id)
            ->first();

        if (! $responsavel) {
            throw ValidationException::withMessages(['responsavel_id' => 'Responsável inválido para a empresa deste item.']);
        }

        $responsavelAnterior = $item->responsavel_id;

        return DB::transaction(function () use ($item, $responsavelAnterior, $responsavelId, $motivo, $user): ItemControle {
            $item->forceFill(['responsavel_id' => $responsavelId])->save();

            $item->registrarTimeline(
                'responsavel',
                'Responsável atualizado',
                'Responsável operacional do item alterado.',
                [
                    'responsavel_anterior_id' => $responsavelAnterior,
                    'responsavel_novo_id' => $responsavelId,
                    'motivo' => $motivo,
                ],
                $user
            );

            return $item->refresh();
        });
    }

    public function registrarEvidencia(ItemControle $item, string $arquivoPath, ?string $observacao = null, ?User $user = null): ItemControleAnexo
    {
        return $this->adicionarAnexo($item, $arquivoPath, $observacao, $user, 'evidencia');
    }

    public function solicitarAprovacao(ItemControle $item, ?User $user = null, ?string $observacao = null): ItemControleAprovacao
    {
        $this->garantirPodeModificar($item, $user);

        if (! $item->podeSolicitarAprovacao()) {
            throw ValidationException::withMessages(['approval_status' => 'Este item não aceita nova solicitação de aprovação no status atual.']);
        }

        return DB::transaction(function () use ($item, $user, $observacao): ItemControleAprovacao {
            $aprovacao = $item->aprovacoes()->create([
                'empresa_id' => $item->empresa_id,
                'solicitante_id' => $user?->id,
                'status' => 'pendente',
                'observacao_solicitacao' => $observacao,
                'solicitado_em' => now(),
            ]);

            $payload = $this->payloadStatus($item, ItemControleCoreService::STATUS_EM_APROVACAO);
            $payload['approval_status'] = 'pendente';
            $payload['approval_required'] = true;
            $item->forceFill($this->filtrarColunasExistentes($item, $payload))->save();

            $item->registrarTimeline('aprovacao_solicitada', 'Aprovação solicitada', $observacao ?: 'O item foi enviado para aprovação.', [
                'aprovacao_id' => $aprovacao->id,
                'solicitante_id' => $user?->id,
            ], $user);

            return $aprovacao;
        });
    }

    public function aprovar(ItemControle $item, ?User $user = null, ?string $observacao = null, ?int $aprovacaoId = null): ItemControle
    {
        $this->garantirPodeAprovar($item, $user);

        return DB::transaction(function () use ($item, $user, $observacao, $aprovacaoId): ItemControle {
            $aprovacao = $this->obterAprovacaoPendente($item, $aprovacaoId);

            $aprovacao->update([
                'aprovador_id' => $user?->id,
                'status' => 'aprovado',
                'observacao_resposta' => $observacao,
                'respondido_em' => now(),
            ]);

            $payload = $this->payloadStatus($item, ItemControleCoreService::STATUS_APROVADO);
            $payload['approval_status'] = 'aprovado';
            $payload['approval_required'] = false;
            $item->forceFill($this->filtrarColunasExistentes($item, $payload))->save();

            $item->registrarTimeline('aprovacao_aprovada', 'Item aprovado', $observacao ?: 'O item foi aprovado.', [
                'aprovacao_id' => $aprovacao->id,
                'aprovador_id' => $user?->id,
            ], $user);

            return $item->refresh();
        });
    }

    public function reprovar(ItemControle $item, ?User $user = null, ?string $observacao = null, ?int $aprovacaoId = null): ItemControle
    {
        $this->garantirPodeAprovar($item, $user);

        return DB::transaction(function () use ($item, $user, $observacao, $aprovacaoId): ItemControle {
            $aprovacao = $this->obterAprovacaoPendente($item, $aprovacaoId);

            $aprovacao->update([
                'aprovador_id' => $user?->id,
                'status' => 'reprovado',
                'observacao_resposta' => $observacao,
                'motivo_reprovacao' => $observacao,
                'respondido_em' => now(),
            ]);

            $payload = $this->payloadStatus($item, ItemControleCoreService::STATUS_REPROVADO);
            $payload['approval_status'] = 'reprovado';
            $payload['approval_required'] = true;
            $item->forceFill($this->filtrarColunasExistentes($item, $payload))->save();

            $item->registrarTimeline('aprovacao_reprovada', 'Item reprovado', $observacao ?: 'O item foi reprovado.', [
                'aprovacao_id' => $aprovacao->id,
                'aprovador_id' => $user?->id,
            ], $user);

            return $item->refresh();
        });
    }

    public function concluir(ItemControle $item, ?User $user = null, ?string $motivo = null, bool $exigirChecklist = true, bool $exigirEvidencia = false): ItemControle
    {
        $this->garantirPodeModificar($item, $user);

        if ($exigirChecklist && $item->getTotalChecklist() > 0 && $item->getChecklistPercentual() < 100) {
            throw ValidationException::withMessages(['checklist' => 'Conclua todas as etapas do checklist antes de finalizar o item.']);
        }

        if ($exigirEvidencia && ! $item->hasAnexoPrincipal()) {
            throw ValidationException::withMessages(['evidencia' => 'Adicione uma evidência/anexo antes de finalizar o item.']);
        }

        if ($item->possuiAprovacaoPendente()) {
            throw ValidationException::withMessages(['approval_status' => 'Resolva a aprovação pendente antes de finalizar o item.']);
        }

        return $this->atualizarStatus($item, ItemControleCoreService::STATUS_CONCLUIDO, $user, $motivo);
    }

    public function reabrir(ItemControle $item, ?User $user = null, ?string $motivo = null): ItemControle
    {
        $this->garantirPodeModificar($item, $user);

        if (! ItemControleCoreService::isFinalStatus($item->status)) {
            return $item->refresh();
        }

        if (blank($motivo)) {
            throw ValidationException::withMessages(['motivo' => 'Informe o motivo da reabertura.']);
        }

        return DB::transaction(function () use ($item, $motivo, $user): ItemControle {
            $statusAnterior = ItemControleCoreService::normalizeStatus($item->status);
            $payload = $this->payloadStatus($item, ItemControleCoreService::STATUS_EM_ANDAMENTO);
            $payload['data_conclusao'] = null;
            $payload['sla_concluido_em'] = null;
            $item->forceFill($this->filtrarColunasExistentes($item, $payload))->save();

            $item->registrarTimeline('reabertura', 'Item reaberto', 'Item reaberto para execução.', [
                'status_anterior' => $statusAnterior,
                'status_novo' => ItemControleCoreService::STATUS_EM_ANDAMENTO,
                'motivo' => $motivo,
            ], $user);

            return $item->refresh();
        });
    }

    public function adicionarComentario(ItemControle $item, string $comentario, ?User $user = null): ItemControleComentario
    {
        $this->garantirPodeModificar($item, $user);
        $comentario = trim($comentario);

        if ($comentario === '') {
            throw ValidationException::withMessages(['comentario' => 'Informe um comentário antes de salvar.']);
        }

        $registro = ItemControleComentario::query()->create([
            'item_controle_id' => $item->id,
            'user_id' => $user?->id,
            'comentario' => $comentario,
        ]);

        $item->registrarTimeline('comentario', 'Comentário adicionado', $comentario, ['comentario_id' => $registro->id], $user);

        return $registro;
    }

    public function adicionarChecklist(ItemControle $item, string $titulo, ?User $user = null): ItemControleChecklist
    {
        $this->garantirPodeModificar($item, $user);
        $titulo = trim($titulo);

        if ($titulo === '') {
            throw ValidationException::withMessages(['checklist' => 'Informe o título da etapa antes de adicionar.']);
        }

        $checklist = ItemControleChecklist::query()->create([
            'item_controle_id' => $item->id,
            'titulo' => $titulo,
            'concluido' => false,
            'ordem' => ((int) $item->checklists()->max('ordem')) + 1,
        ]);

        $item->registrarTimeline('checklist', 'Etapa adicionada ao checklist', $titulo, ['checklist_id' => $checklist->id], $user);

        return $checklist;
    }

    public function alternarChecklist(ItemControleChecklist $checklist, ?User $user = null): ItemControleChecklist
    {
        $item = $checklist->itemControle;
        $this->garantirPodeModificar($item, $user);

        if ($checklist->concluido) {
            $checklist->marcarComoPendente();
            $tituloTimeline = 'Etapa reaberta';
        } else {
            $checklist->marcarComoConcluido($user);
            $tituloTimeline = 'Etapa concluída';
        }

        $item->registrarTimeline('checklist', $tituloTimeline, $checklist->titulo, ['checklist_id' => $checklist->id], $user);

        return $checklist->refresh();
    }

    public function adicionarAnexo(ItemControle $item, string $arquivoPath, ?string $observacao = null, ?User $user = null, string $tipoTimeline = 'anexo'): ItemControleAnexo
    {
        $this->garantirPodeModificar($item, $user);

        $nomeOriginal = basename($arquivoPath);
        $mimeType = null;
        $tamanho = null;

        try {
            if (Storage::disk('public')->exists($arquivoPath)) {
                $mimeType = Storage::disk('public')->mimeType($arquivoPath);
                $tamanho = Storage::disk('public')->size($arquivoPath);
            }
        } catch (\Throwable) {
            $mimeType = null;
            $tamanho = null;
        }

        $anexo = ItemControleAnexo::query()->create([
            'item_controle_id' => $item->id,
            'user_id' => $user?->id,
            'arquivo' => $arquivoPath,
            'nome_original' => $nomeOriginal,
            'mime_type' => $mimeType,
            'tamanho_bytes' => $tamanho,
            'observacao' => $observacao,
        ]);

        $item->registrarTimeline($tipoTimeline, $tipoTimeline === 'evidencia' ? 'Evidência adicionada' : 'Anexo adicionado', $nomeOriginal, [
            'anexo_id' => $anexo->id,
            'observacao' => $observacao,
        ], $user);

        return $anexo;
    }

    private function validarTransicao(ItemControle $item, string $statusAnterior, string $statusNovo): void
    {
        if (ItemControleCoreService::isFinalStatus($statusAnterior) && ! ItemControleCoreService::isFinalStatus($statusNovo)) {
            throw ValidationException::withMessages(['status' => 'Use a ação de reabertura para reabrir itens finalizados.']);
        }

        if ($item->estaBloqueadoOperacionalmente() && $statusNovo !== 'cancelado') {
            throw ValidationException::withMessages(['status' => 'Item bloqueado por dependência ou regra operacional. Remova o bloqueio antes de avançar.']);
        }

        $permitidos = self::TRANSICOES[$statusAnterior] ?? ItemControleStatus::KANBAN_STATUSES;

        if (! in_array($statusNovo, $permitidos, true) && ! in_array($statusNovo, ItemControleStatus::KANBAN_STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => 'Transição inválida de ' . ItemControleCoreService::statusLabel($statusAnterior) . ' para ' . ItemControleCoreService::statusLabel($statusNovo) . '.',
            ]);
        }
    }

    private function payloadStatus(ItemControle $item, string $status): array
    {
        $agora = now();
        $payload = [
            'status' => $status,
            'status_operacional_at' => $agora,
        ];

        if (ItemControleCoreService::isFinalStatus($status)) {
            $payload['data_conclusao'] = $item->data_conclusao ?: $agora->toDateString();
            $payload['sla_concluido_em'] = $item->sla_concluido_em ?: $agora;
            $payload['sla_status'] = $item->sla_limite_em && $agora->greaterThan($item->sla_limite_em)
                ? 'concluido_atrasado'
                : 'concluido_no_prazo';
        } elseif ($status === ItemControleCoreService::STATUS_EM_ANDAMENTO && blank($item->sla_inicio_em)) {
            $payload['sla_inicio_em'] = $agora;
            $payload['sla_horas'] = $item->sla_horas ?: $item->getSlaHorasPadrao();
            $payload['sla_limite_em'] = $agora->copy()->addHours($payload['sla_horas']);
            $payload['sla_prazo_alvo_em'] = $payload['sla_limite_em'];
            $payload['sla_status'] = 'em_andamento';
        }

        return $this->filtrarColunasExistentes($item, $payload);
    }

    private function filtrarColunasExistentes(ItemControle $item, array $payload): array
    {
        return array_filter(
            $payload,
            static fn (mixed $valor, string $coluna): bool => CachedSchema::hasColumn($item->getTable(), $coluna),
            ARRAY_FILTER_USE_BOTH
        );
    }

    private function obterAprovacaoPendente(ItemControle $item, ?int $aprovacaoId = null): ItemControleAprovacao
    {
        $query = $item->aprovacoes()->where('status', 'pendente');

        if ($aprovacaoId) {
            $query->whereKey($aprovacaoId);
        }

        $aprovacao = $query->latest('id')->first();

        if (! $aprovacao) {
            throw ValidationException::withMessages(['approval_status' => 'Não existe aprovação pendente para este item.']);
        }

        return $aprovacao;
    }

    private function garantirPodeModificar(?ItemControle $item, ?User $user): void
    {
        if (! $item || ! $item->exists || ! $item->canBeModifiedBy($user)) {
            abort(403, 'Você não tem permissão para alterar este item.');
        }
    }

    private function garantirPodeAprovar(?ItemControle $item, ?User $user): void
    {
        if (! $item || ! $item->exists || ! $item->canBeApprovedBy($user)) {
            abort(403, 'Você não tem permissão para aprovar este item.');
        }
    }
}
