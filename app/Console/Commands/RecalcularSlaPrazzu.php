<?php

namespace App\Console\Commands;

use App\Services\PrazzuSlaService;
use Illuminate\Console\Command;

class RecalcularSlaPrazzu extends Command
{
    protected $signature = 'prazzu:sla-recalcular {--empresa-id= : Recalcula somente uma empresa} {--limit=1000 : Quantidade máxima de itens avaliados}';

    protected $description = 'Recalcula o status de SLA dos itens de controle pela regra oficial do Prazzu.';

    public function handle(PrazzuSlaService $slaService): int
    {
        $empresaId = $this->option('empresa-id') ? (int) $this->option('empresa-id') : null;
        $limit = max(1, (int) $this->option('limit'));

        $resultado = $slaService->recalcularItensControle($empresaId, $limit);

        $this->info('SLA recalculado.');
        $this->line('Itens avaliados: ' . $resultado['avaliados']);
        $this->line('Itens atualizados: ' . $resultado['atualizados']);

        foreach ($resultado['por_status'] as $status => $total) {
            $this->line("- {$status}: {$total}");
        }

        return self::SUCCESS;
    }
}
