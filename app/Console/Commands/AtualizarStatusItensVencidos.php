<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class AtualizarStatusItensVencidos extends Command
{
    protected $signature = 'itens:atualizar-vencidos';

    protected $description = 'Comando legado: redireciona para o fluxo oficial de vencidos e notificações sem duplicar regras.';

    public function handle(): int
    {
        $this->warn('Comando legado detectado. Usando o fluxo oficial: itens-controle:atualizar-vencidos + item-controle:notificar-vencimentos.');

        Log::notice('Comando legado itens:atualizar-vencidos executado; redirecionando para comandos oficiais.', [
            'command' => $this->signature,
        ]);

        try {
            $statusExitCode = $this->call('itens-controle:atualizar-vencidos');

            if ($statusExitCode !== self::SUCCESS) {
                Log::warning('Comando oficial de atualização de vencidos retornou falha via comando legado.', [
                    'exit_code' => $statusExitCode,
                ]);

                return (int) $statusExitCode;
            }

            $notificationExitCode = $this->call('item-controle:notificar-vencimentos');

            if ($notificationExitCode !== self::SUCCESS) {
                Log::warning('Comando oficial de notificação de vencimentos retornou falha via comando legado.', [
                    'exit_code' => $notificationExitCode,
                ]);

                return (int) $notificationExitCode;
            }
        } catch (Throwable $exception) {
            Log::error('Falha ao executar fluxo legado de atualização de vencidos.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            $this->error('Falha ao executar fluxo oficial de vencidos: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Fluxo oficial de vencidos e notificações executado com sucesso.');

        return self::SUCCESS;
    }
}
