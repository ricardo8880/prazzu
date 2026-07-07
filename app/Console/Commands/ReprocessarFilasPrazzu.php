<?php

namespace App\Console\Commands;

use App\Support\CachedSchema;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReprocessarFilasPrazzu extends Command
{
    protected $signature = 'prazzu:filas-reprocessar
        {--queue= : Reprocessa somente uma fila específica}
        {--limit=25 : Quantidade máxima de jobs falhos reprocessados por execução}
        {--dry-run : Lista os jobs que seriam reprocessados, sem executar queue:retry}';

    protected $description = 'Reprocessa jobs falhos de forma limitada e segura.';

    public function handle(): int
    {
        if (! CachedSchema::hasTable('failed_jobs')) {
            $this->error('Tabela failed_jobs ausente. Rode database/sql/lote_07_filas_scheduler_notificacoes.sql.');

            return self::FAILURE;
        }

        $queue = $this->option('queue') ?: null;
        $limit = max(1, min((int) $this->option('limit'), 100));
        $dryRun = (bool) $this->option('dry-run');

        $jobs = DB::table('failed_jobs')
            ->select(['id', 'uuid', 'connection', 'queue', 'failed_at'])
            ->when($queue, fn ($query) => $query->where('queue', $queue))
            ->orderBy('failed_at')
            ->limit($limit)
            ->get();

        if ($jobs->isEmpty()) {
            $this->info('Nenhum job falho encontrado para reprocessar.');

            return self::SUCCESS;
        }

        $reprocessados = 0;
        $falhas = 0;

        foreach ($jobs as $job) {
            $id = $job->uuid ?: (string) $job->id;
            $this->line(sprintf(
                '%s job %s | conexão=%s | fila=%s | falhou_em=%s',
                $dryRun ? '[DRY-RUN]' : 'Reprocessando',
                $id,
                $job->connection,
                $job->queue,
                $job->failed_at
            ));

            if ($dryRun) {
                continue;
            }

            try {
                $exitCode = Artisan::call('queue:retry', ['id' => [$id]]);

                if ($exitCode === 0) {
                    $reprocessados++;
                } else {
                    $falhas++;
                }
            } catch (\Throwable $exception) {
                $falhas++;

                Log::error('Falha ao reprocessar job da fila Prazzu.', [
                    'failed_job_id' => $job->id,
                    'uuid' => $job->uuid,
                    'queue' => $job->queue,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        if ($dryRun) {
            $this->info('Dry-run concluído. Nenhum job foi reprocessado.');

            return self::SUCCESS;
        }

        $this->info("Jobs reprocessados: {$reprocessados}");
        $this->info("Falhas no reprocessamento: {$falhas}");

        return $falhas > 0 ? self::FAILURE : self::SUCCESS;
    }
}
