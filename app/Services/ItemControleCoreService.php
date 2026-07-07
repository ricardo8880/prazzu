<?php

namespace App\Services;

use App\Models\ItemControle;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ItemControleCoreService
{
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_EM_ANDAMENTO = 'em_andamento';
    public const STATUS_PRONTO = 'pronto';
    public const STATUS_EM_REVISAO = 'em_revisao';
    public const STATUS_AGUARDANDO_APROVACAO = 'aguardando_aprovacao';
    public const STATUS_EM_APROVACAO = 'em_aprovacao';
    public const STATUS_CORRECAO_NECESSARIA = 'correcao_necessaria';
    public const STATUS_APROVADO = 'aprovado';
    public const STATUS_REPROVADO = 'reprovado';
    public const STATUS_ASSINADO = 'assinado';
    public const STATUS_CONCLUIDO = 'concluido';
    public const STATUS_CANCELADO = 'cancelado';
    public const STATUS_VENCIDO = 'vencido';

    public const STATUS_FINAIS = [
        self::STATUS_APROVADO,
        self::STATUS_ASSINADO,
        self::STATUS_CONCLUIDO,
        self::STATUS_CANCELADO,
    ];

    public const STATUS_OPERACIONAIS_ATIVOS = [
        self::STATUS_PENDENTE,
        self::STATUS_EM_ANDAMENTO,
        self::STATUS_PRONTO,
        self::STATUS_EM_REVISAO,
        self::STATUS_AGUARDANDO_APROVACAO,
        self::STATUS_EM_APROVACAO,
        self::STATUS_CORRECAO_NECESSARIA,
        self::STATUS_REPROVADO,
        self::STATUS_VENCIDO,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDENTE => 'Pendente',
        self::STATUS_EM_ANDAMENTO => 'Em andamento',
        self::STATUS_PRONTO => 'Pronto',
        self::STATUS_EM_REVISAO => 'Em revisão',
        self::STATUS_AGUARDANDO_APROVACAO => 'Aguardando aprovação',
        self::STATUS_EM_APROVACAO => 'Em aprovação',
        self::STATUS_CORRECAO_NECESSARIA => 'Correção necessária',
        self::STATUS_APROVADO => 'Aprovado',
        self::STATUS_REPROVADO => 'Reprovado',
        self::STATUS_ASSINADO => 'Assinado',
        self::STATUS_CONCLUIDO => 'Concluído',
        self::STATUS_CANCELADO => 'Cancelado',
        self::STATUS_VENCIDO => 'Vencido',
    ];

    public const STATUS_COLORS = [
        self::STATUS_PENDENTE => 'warning',
        self::STATUS_EM_ANDAMENTO => 'info',
        self::STATUS_PRONTO => 'info',
        self::STATUS_EM_REVISAO => 'warning',
        self::STATUS_AGUARDANDO_APROVACAO => 'warning',
        self::STATUS_EM_APROVACAO => 'warning',
        self::STATUS_CORRECAO_NECESSARIA => 'danger',
        self::STATUS_APROVADO => 'success',
        self::STATUS_REPROVADO => 'danger',
        self::STATUS_ASSINADO => 'success',
        self::STATUS_CONCLUIDO => 'success',
        self::STATUS_CANCELADO => 'gray',
        self::STATUS_VENCIDO => 'danger',
    ];

    public const PRIORIDADE_LABELS = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
        'critica' => 'Crítica',
        'urgente' => 'Urgente',
    ];

    public const PRIORIDADE_COLORS = [
        'baixa' => 'gray',
        'media' => 'info',
        'alta' => 'warning',
        'critica' => 'danger',
        'urgente' => 'danger',
    ];

    /**
     * Aliases usados por telas antigas, portal, seeds e automações.
     * Mantém compatibilidade sem espalhar normalização pelo projeto.
     */
    public const STATUS_ALIASES = [
        'aberto' => self::STATUS_PENDENTE,
        'a_fazer' => self::STATUS_PENDENTE,
        'andamento' => self::STATUS_EM_ANDAMENTO,
        'em_execucao' => self::STATUS_EM_ANDAMENTO,
        'execucao' => self::STATUS_EM_ANDAMENTO,
        'revisao' => self::STATUS_EM_REVISAO,
        'aguardando_revisao' => self::STATUS_EM_REVISAO,
        'aprovacao' => self::STATUS_EM_APROVACAO,
        'aguardando_cliente' => self::STATUS_AGUARDANDO_APROVACAO,
        'correcao' => self::STATUS_CORRECAO_NECESSARIA,
        'finalizado' => self::STATUS_CONCLUIDO,
        'feito' => self::STATUS_CONCLUIDO,
        'aprovada' => self::STATUS_APROVADO,
        'reprovada' => self::STATUS_REPROVADO,
    ];

    public static function statuses(): array
    {
        return self::STATUS_LABELS;
    }

    public static function statusLabel(?string $status): string
    {
        $status = self::normalizeStatus($status);

        return self::STATUS_LABELS[$status] ?? ucfirst((string) $status);
    }

    public static function statusColor(?string $status): string
    {
        $status = self::normalizeStatus($status);

        return self::STATUS_COLORS[$status] ?? 'secondary';
    }

    public static function priorityLabel(?string $prioridade): string
    {
        $prioridade = self::normalizePriority($prioridade);

        return self::PRIORIDADE_LABELS[$prioridade] ?? self::PRIORIDADE_LABELS['media'];
    }

    public static function priorityColor(?string $prioridade): string
    {
        $prioridade = self::normalizePriority($prioridade);

        return self::PRIORIDADE_COLORS[$prioridade] ?? self::PRIORIDADE_COLORS['media'];
    }

    public static function normalizeStatus(?string $status): string
    {
        $status = trim((string) $status);

        if ($status === '') {
            return self::STATUS_PENDENTE;
        }

        return self::STATUS_ALIASES[$status] ?? $status;
    }

    public static function normalizePriority(?string $prioridade): string
    {
        $prioridade = trim((string) $prioridade);

        return array_key_exists($prioridade, self::PRIORIDADE_LABELS) ? $prioridade : 'media';
    }

    public static function urgencyFromPriority(?string $prioridade): string
    {
        return match (self::normalizePriority($prioridade)) {
            'urgente', 'critica' => 'critica',
            'alta' => 'alta',
            'baixa' => 'baixa',
            default => 'media',
        };
    }

    public static function isFinalStatus(?string $status): bool
    {
        return in_array(self::normalizeStatus($status), self::STATUS_FINAIS, true);
    }

    public static function isActiveStatus(?string $status): bool
    {
        return in_array(self::normalizeStatus($status), self::STATUS_OPERACIONAIS_ATIVOS, true);
    }

    public function normalizeBeforeSave(ItemControle $item): void
    {
        $item->status = self::normalizeStatus($item->status);
        $item->prioridade = self::normalizePriority($item->prioridade);

        if (blank($item->urgencia)) {
            $item->urgencia = self::urgencyFromPriority($item->prioridade);
        }

        if (blank($item->valor_tarefa) && filled($item->contrato_valor)) {
            $item->valor_tarefa = $item->contrato_valor;
        }

        if (self::isFinalStatus($item->status) && blank($item->data_conclusao)) {
            $item->data_conclusao = now()->toDateString();
        }
    }

    public function operationalPayload(ItemControle $item): array
    {
        $diasRestantes = $item->data_vencimento
            ? now()->startOfDay()->diffInDays($item->data_vencimento->copy()->startOfDay(), false)
            : null;

        return [
            'id' => $item->id,
            'empresa_id' => $item->empresa_id,
            'responsavel_id' => $item->responsavel_id,
            'titulo' => $item->titulo,
            'status' => self::normalizeStatus($item->status),
            'status_label' => self::statusLabel($item->status),
            'status_color' => self::statusColor($item->status),
            'prioridade' => self::normalizePriority($item->prioridade),
            'prioridade_label' => self::priorityLabel($item->prioridade),
            'prioridade_color' => self::priorityColor($item->prioridade),
            'data_vencimento' => $item->data_vencimento?->toDateString(),
            'dias_restantes' => $diasRestantes,
            'vencido' => $item->isVencido(),
            'bloqueado' => $item->estaBloqueadoOperacionalmente(),
            'valor_operacional' => $item->getValorOperacional(),
            'portal_ativo' => (bool) $item->portal_ativo,
            'sla_status' => $item->sla_status,
        ];
    }

    public function applyVisibility(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if (! $user->empresa_id) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdminEmpresa() || $user->isGestor()) {
            return $query->where('empresa_id', $user->empresa_id);
        }

        if ($user->isUser()) {
            return $query->where('responsavel_id', $user->responsavel?->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function transitionStatus(ItemControle $item, string $novoStatus, ?User $user = null, ?string $motivo = null): bool
    {
        app(ItemControleFluxoService::class)->atualizarStatus($item, $novoStatus, $user, $motivo);

        return true;
    }

}
