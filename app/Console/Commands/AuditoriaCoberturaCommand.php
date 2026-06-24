<?php

namespace App\Console\Commands;

use App\Support\AuditoriaModelInspector;
use Illuminate\Console\Command;

class AuditoriaCoberturaCommand extends Command
{
    protected $signature = 'auditoria:cobertura {--somente-problemas : Mostra apenas models obrigatórios sem cobertura}';

    protected $description = 'Mostra a cobertura de auditoria dos models do sistema.';

    public function handle(): int
    {
        $rows = [];
        $faltandoObrigatorios = [];

        foreach (AuditoriaModelInspector::modelClasses() as $modelClass) {
            $status = AuditoriaModelInspector::status($modelClass);

            if ($status['required'] && ! $status['covered']) {
                $faltandoObrigatorios[] = $status;
            }

            if ($this->option('somente-problemas') && (! $status['required'] || $status['covered'])) {
                continue;
            }

            $rows[] = [
                str_replace('App\\Models\\', '', $status['model']),
                $status['table'] ?? '-',
                $status['required'] ? 'sim' : 'não',
                $status['covered'] ? 'sim' : 'não',
                $status['global'] ? 'global' : ($status['trait'] ? 'trait' : '-'),
                $status['excluded'] ? 'sim' : 'não',
                $status['reason'],
            ];
        }

        $this->table(
            ['Model', 'Tabela', 'Obrigatório', 'Coberto', 'Origem', 'Excluído', 'Motivo'],
            $rows
        );

        $this->newLine();
        $this->info('Resumo:');
        $this->line('Models encontrados: ' . count(AuditoriaModelInspector::modelClasses()));
        $this->line('Obrigatórios sem cobertura: ' . count($faltandoObrigatorios));

        if ($faltandoObrigatorios !== []) {
            $this->error('Existem models obrigatórios sem cobertura de auditoria. Revise config/auditoria.php.');

            return self::FAILURE;
        }

        $this->info('Cobertura mínima obrigatória OK.');

        return self::SUCCESS;
    }
}
