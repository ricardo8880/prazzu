<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Concerns\BuildsHealthItems;
use App\Services\SystemHealth\HealthCheckContract;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class SchedulerHealthCheck implements HealthCheckContract
{
    use BuildsHealthItems;

    public function key(): string { return 'scheduler'; }
    public function name(): string { return 'Comandos e scheduler'; }
    public function description(): string { return 'Comandos críticos, configuração de filas e presença dos agendamentos operacionais.'; }

    public function run(int $limit = 500): array
    {
        $items = [];
        $commands = array_keys(Artisan::all());

        foreach (['itens-controle:atualizar-vencidos', 'item-controle:notificar-vencimentos', 'asaas:reconciliar-assinaturas', 'sistemrh:diagnostico'] as $command) {
            $items[] = in_array($command, $commands, true)
                ? $this->ok("Comando {$command} registrado.")
                : $this->warning("Comando {$command} não encontrado.", null, [], 'Confirme se o arquivo do command existe e foi carregado pelo Laravel.');
        }

        $consoleRoutes = base_path('routes/console.php');
        $kernel = app_path('Console/Kernel.php');
        $items[] = File::exists($consoleRoutes) ? $this->ok('routes/console.php existe.') : $this->warning('routes/console.php ausente.');
        $items[] = File::exists($kernel) ? $this->ok('app/Console/Kernel.php existe.') : $this->warning('app/Console/Kernel.php ausente.');

        $schedulerSource = '';
        if (File::exists($consoleRoutes)) {
            $schedulerSource .= File::get($consoleRoutes);
        }
        if (File::exists($kernel)) {
            $schedulerSource .= "\n".File::get($kernel);
        }

        foreach (['item-controle:notificar-vencimentos', 'itens-controle:atualizar-vencidos', 'asaas:reconciliar-assinaturas'] as $scheduledCommand) {
            $items[] = str_contains($schedulerSource, $scheduledCommand)
                ? $this->ok("Scheduler contém {$scheduledCommand}.")
                : $this->warning("Scheduler não contém {$scheduledCommand}.", null, [], 'Adicione o comando ao scheduler se ele precisar rodar automaticamente.');
        }

        $queue = (string) config('queue.default');
        $items[] = $queue !== ''
            ? $this->ok('QUEUE_CONNECTION configurada.', $queue)
            : $this->warning('QUEUE_CONNECTION ausente.', null, [], 'Configure a conexão de filas no .env.');

        return $items;
    }
}
