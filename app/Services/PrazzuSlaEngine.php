<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class PrazzuSlaEngine
{
    public const STATUS_SEM_SLA = 'sem_sla';
    public const STATUS_EM_ANDAMENTO = 'em_andamento';
    public const STATUS_RISCO = 'risco';
    public const STATUS_VENCIDO = 'vencido';
    public const STATUS_CONCLUIDO_NO_PRAZO = 'concluido_no_prazo';
    public const STATUS_CONCLUIDO_ATRASADO = 'concluido_atrasado';

    /** @return array<int, string> */
    public static function statusAbertos(): array
    {
        return [self::STATUS_EM_ANDAMENTO, self::STATUS_RISCO, self::STATUS_VENCIDO];
    }

    /** @return array<int, string> */
    public static function statusFinalizados(): array
    {
        return [self::STATUS_CONCLUIDO_NO_PRAZO, self::STATUS_CONCLUIDO_ATRASADO];
    }

    public function status(null|string|CarbonInterface $limit, null|string|CarbonInterface $completed = null, int $warningHours = 8, null|string|CarbonInterface $reference = null): string
    {
        $limite = $this->toCarbon($limit);

        if (! $limite) {
            return self::STATUS_SEM_SLA;
        }

        $concluido = $this->toCarbon($completed);

        if ($concluido) {
            return $concluido->lte($limite)
                ? self::STATUS_CONCLUIDO_NO_PRAZO
                : self::STATUS_CONCLUIDO_ATRASADO;
        }

        $agora = $this->toCarbon($reference) ?: now();

        if ($limite->lt($agora)) {
            return self::STATUS_VENCIDO;
        }

        if ($limite->diffInHours($agora, true) <= max(0, $warningHours)) {
            return self::STATUS_RISCO;
        }

        return self::STATUS_EM_ANDAMENTO;
    }

    /** @param object|array<string, mixed> $record */
    public function statusForRecord(object|array $record, int $warningHours = 8, null|string|CarbonInterface $reference = null): string
    {
        return $this->status(
            $this->value($record, 'sla_limite_em') ?: $this->value($record, 'data_vencimento'),
            $this->value($record, 'sla_concluido_em') ?: $this->value($record, 'data_conclusao'),
            $warningHours,
            $reference,
        );
    }

    /** @param object|array<string, mixed> $record */
    public function remainingLabel(object|array $record, int $warningHours = 8, null|string|CarbonInterface $reference = null): string
    {
        $limite = $this->toCarbon($this->value($record, 'sla_limite_em') ?: $this->value($record, 'data_vencimento'));

        if (! $limite) {
            return 'Sem SLA definido';
        }

        $concluido = $this->toCarbon($this->value($record, 'sla_concluido_em') ?: $this->value($record, 'data_conclusao'));

        if ($concluido) {
            return $concluido->lte($limite)
                ? 'Concluído no prazo'
                : 'Concluído com atraso';
        }

        $agora = $this->toCarbon($reference) ?: now();
        $status = $this->status($limite, null, $warningHours, $agora);

        if ($status === self::STATUS_VENCIDO) {
            return 'Vencido há ' . $limite->diffForHumans($agora, true);
        }

        if ($status === self::STATUS_RISCO) {
            return 'Em risco: vence em ' . $agora->diffForHumans($limite, true);
        }

        return 'Vence em ' . $agora->diffForHumans($limite, true);
    }

    public function normalizeStatus(?string $status): string
    {
        return match (strtolower(trim((string) $status))) {
            'ok', 'no_prazo', 'em andamento', 'em_andamento' => self::STATUS_EM_ANDAMENTO,
            'em_risco', 'risco', 'atenção', 'atencao' => self::STATUS_RISCO,
            'atrasado', 'vencido' => self::STATUS_VENCIDO,
            'concluido', 'concluído', 'finalizado', 'concluido_no_prazo' => self::STATUS_CONCLUIDO_NO_PRAZO,
            'concluido_atrasado', 'concluído_atrasado', 'finalizado_atrasado' => self::STATUS_CONCLUIDO_ATRASADO,
            default => self::STATUS_SEM_SLA,
        };
    }

    private function toCarbon(mixed $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value->toDateTime());
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /** @param object|array<string, mixed> $record */
    private function value(object|array $record, string $key): mixed
    {
        if (is_array($record)) {
            return $record[$key] ?? null;
        }

        return $record->{$key} ?? null;
    }
}
