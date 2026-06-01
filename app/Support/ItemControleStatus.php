<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ItemControleStatus
{
    public const PENDENTE = 'pendente';
    public const EM_ANDAMENTO = 'em_andamento';
    public const CONCLUIDO = 'concluido';

    public const DONE_STATUSES = [
        'concluido',
        'concluida',
        'concluído',
        'finalizado',
        'finalizada',
        'cancelado',
        'cancelada',
    ];

    public const ACTIVE_STATUSES = [
        'pendente',
        'em_andamento',
        'andamento',
        'em_execucao',
        'em_progresso',
        'em_aprovacao',
        'aprovacao',
        'em_analise',
    ];

    public const KANBAN_STATUSES = [
        self::PENDENTE,
        self::EM_ANDAMENTO,
        self::CONCLUIDO,
    ];

    public static function normalize(?string $status): string
    {
        $status = Str::of((string) $status)
            ->trim()
            ->lower()
            ->ascii()
            ->replace('-', '_')
            ->replace(' ', '_')
            ->toString();

        return match ($status) {
            'andamento', 'em_execucao', 'em_progresso' => self::EM_ANDAMENTO,
            'aprovacao', 'em_analise' => 'em_aprovacao',
            'concluida', 'concluido', 'concluído', 'finalizado', 'finalizada' => self::CONCLUIDO,
            'cancelada' => 'cancelado',
            default => $status ?: self::PENDENTE,
        };
    }

    public static function label(?string $status): string
    {
        return match (self::normalize($status)) {
            self::PENDENTE => 'Pendente',
            self::EM_ANDAMENTO => 'Em andamento',
            self::CONCLUIDO => 'Concluído',
            'cancelado' => 'Cancelado',
            'em_aprovacao' => 'Em aprovação',
            'reprovado' => 'Reprovado',
            default => Str::of((string) $status)->replace('_', ' ')->headline()->toString(),
        };
    }

    public static function isDone(?string $status): bool
    {
        return in_array(self::normalize($status), [self::CONCLUIDO, 'cancelado'], true)
            || in_array((string) $status, self::DONE_STATUSES, true);
    }

    public static function isLate(?CarbonInterface $dueDate, ?string $status): bool
    {
        return $dueDate !== null
            && ! self::isDone($status)
            && $dueDate->lt(now()->startOfDay());
    }

    public static function applyKanbanColumn(Builder $query, string $column): Builder
    {
        return match ($column) {
            'vencido' => $query
                ->whereNotIn('status', self::DONE_STATUSES)
                ->whereDate('data_vencimento', '<', now()->toDateString()),
            self::EM_ANDAMENTO => $query->whereIn('status', ['em_andamento', 'andamento', 'em_execucao', 'em_progresso']),
            self::CONCLUIDO => $query->whereIn('status', ['concluido', 'concluida', 'finalizado', 'finalizada']),
            default => $query->where('status', self::PENDENTE),
        };
    }

    public static function doneStatusesSql(): array
    {
        return self::DONE_STATUSES;
    }
}
