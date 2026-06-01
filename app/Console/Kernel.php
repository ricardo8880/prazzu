<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Os agendamentos oficiais ficam em routes/console.php.
        // Mantemos este Kernel sem tarefas para evitar execução duplicada quando o projeto
        // for carregado em ambientes que ainda consultem app/Console/Kernel.php.
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
