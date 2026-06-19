<?php

namespace App\Console\Commands;

use App\Services\StorageRetentionService;
use Illuminate\Console\Command;

class ProcessarRetencaoArquivos extends Command
{
    protected $signature = 'storage:processar-retencao {--limit=100 : Quantidade máxima de arquivos processados por execução}';

    protected $description = 'Processa políticas de retenção de arquivos, arquivando ou excluindo candidatos vencidos com histórico.';

    public function handle(StorageRetentionService $retention): int
    {
        if (! $retention->ready()) {
            $this->warn('As tabelas de retenção ainda não existem. Execute php artisan migrate.');
            return self::FAILURE;
        }

        $result = $retention->process(null, max(1, (int) $this->option('limit')));

        $this->info("Retenção processada: {$result['arquivados']} arquivado(s), {$result['excluidos']} excluído(s), {$result['erros']} erro(s).");

        return $result['erros'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
