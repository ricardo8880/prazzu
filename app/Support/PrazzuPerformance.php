<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PrazzuPerformance
{
    /**
     * Limite padrão para listagens operacionais que não precisam carregar a base inteira.
     */
    public const DEFAULT_OPERATIONAL_LIMIT = 500;

    /**
     * @return array{0:string,1:string}
     */
    public static function dayBounds(CarbonInterface|string $date): array
    {
        $carbon = is_string($date) ? Carbon::parse($date) : Carbon::instance($date->toDateTime());
        $start = $carbon->copy()->startOfDay();
        $end = $start->copy()->addDay();

        return [
            $start->toDateTimeString(),
            $end->toDateTimeString(),
        ];
    }

    /**
     * Aplica filtro de dia usando faixa >= / < para aproveitar índice da coluna.
     */
    public static function whereDay(Builder|QueryBuilder $query, string $column, CarbonInterface|string $date): Builder|QueryBuilder
    {
        [$start, $end] = self::dayBounds($date);

        return $query->where($column, '>=', $start)->where($column, '<', $end);
    }

    /**
     * Normaliza limites vindos de tela/comando para impedir cargas acidentais enormes.
     */
    public static function safeLimit(?int $limit, int $default = self::DEFAULT_OPERATIONAL_LIMIT, int $max = 5000): int
    {
        $limit = $limit ?: $default;

        return max(1, min($max, $limit));
    }
}
