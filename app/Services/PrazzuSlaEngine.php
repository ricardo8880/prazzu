<?php

namespace App\Services;

use Carbon\Carbon;

class PrazzuSlaEngine
{
    public function status(?string $limit, ?string $completed = null): string
    {
        if (! $limit) return 'sem_sla';
        if ($completed) return Carbon::parse($completed)->lte(Carbon::parse($limit)) ? 'concluido_no_prazo' : 'concluido_atrasado';
        if (Carbon::parse($limit)->isPast()) return 'vencido';
        if (Carbon::parse($limit)->diffInHours(now()) <= 8) return 'risco';
        return 'ok';
    }
}
