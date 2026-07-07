<?php

namespace App\Console\Commands;

use App\Support\CachedSchema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitorarFilasPrazzu extends Command
{
    protected $signature = 'prazzu:filas-monitorar
        {--queue= : Nome da fila para filtrar}
        {--alert-threshold=100 : Quantidade de jobs pendentes para registrar alerta}
        {--failed-threshold=1 : Quantidade de jobs falhos para registrar alerta}';

    protected $description = 'Monitora filas, jobs pendentes e jobs falhos do Prazzu sem processar dados.';

    public function handle(): int
    {
        if (! $this->schemaPronto()) {
            $this->error('Tabelas de fila ausentes. Rode database/sql/lote_07_filas_scheduler_notificacoes.sql.');

            return self::FAILURE;
        }

        $queue = $this->option('queue') ?: null;
        $alertThreshold = max(1, (int) $this->option('alert-threshold'));
        $failedThreshold = max(0, (int) $this->option('failed-threshold'));

        $pendentesQuery = DB::table('jobs');
        $falhosQuery = DB::table('failed_jobs');

        if ($queue) {
            $pendentesQuery->where('queue', $queue);
            $falhosQuery->where('queue', $queue);
        }

        $pendentes = (int) $pendentesQuery->count();
        $reservados = (int) DB::table('jobs')
            ->when($queue, fn ($query) => $query->where('queue', $queue))
            ->whereNotNull('reserved_at')
            ->count();
        $falhos = (int) $falhosQuery->count();

        $this->info('Monitoramento de filas Prazzu');
        $this->line('Fila: ' . ($queue ?: 'todas'));
        $this->line("Pendentes: {$pendentes}");
        $this->line("Reservados/em execução: {$reservados}");
        $this->line("Falhos: {$falhos}");

        if ($pendentes >= $alertThreshold || $falhos >= $failedThreshold) {
            Log::warning('Fila Prazzu acima do limite operacional.', [
                'queue' => $queue ?: 'todas',
                'pendentes' => $pendentes,
                'reservados' => $reservados,
                'falhos' => $falhos,
                'alert_threshold' => $alertThreshold,
                'failed_threshold' => $failedThreshold,
            ]);
        }

        return self::SUCCESS;
    }

    private function schemaPronto(): bool
    {
        return CachedSchema::hasTable('jobs') && CachedSchema::hasTable('failed_jobs');
    }
}
