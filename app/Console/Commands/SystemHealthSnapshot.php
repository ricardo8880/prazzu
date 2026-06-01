<?php

namespace App\Console\Commands;

use App\Services\SystemHealth\SystemHealthService;
use Illuminate\Console\Command;

class SystemHealthSnapshot extends Command
{
    protected $signature = 'sistemrh:saude
        {--limite=500 : Quantidade máxima de registros analisados em consultas pesadas}
        {--json= : Caminho opcional para salvar o relatório JSON}';

    protected $description = 'Executa o painel administrativo de saúde do sistema e atualiza o último snapshot exibido no admin.';

    public function handle(SystemHealthService $health): int
    {
        $limit = max(10, min(5000, (int) $this->option('limite')));
        $report = $health->run($limit);

        $this->newLine();
        $this->line('Saúde do Sistema');
        $this->line('Status: '.$report['status']);
        $this->line('OK: '.$report['summary']['ok'].' | Avisos: '.$report['summary']['warning'].' | Erros: '.$report['summary']['error'].' | Total: '.$report['summary']['total']);
        $this->line('Duração: '.$report['duration_ms'].'ms');

        if ($path = $this->option('json')) {
            $fullPath = base_path((string) $path);
            $directory = dirname($fullPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
            file_put_contents($fullPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->info('Relatório JSON salvo em: '.$fullPath);
        }

        if (($report['summary']['error'] ?? 0) > 0) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
