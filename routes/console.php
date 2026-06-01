<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

// 🔔 NOTIFICAÇÕES
Schedule::command('item-controle:notificar-vencimentos')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();

// 🔥 ATUALIZA STATUS VENCIDO (CORRIGIDO AQUI)
Schedule::command('itens-controle:atualizar-vencidos')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground();


// 💳 ASAAS: reconciliação de assinaturas/cobranças
Schedule::command('asaas:reconciliar-assinaturas --limit=100')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// ⚙️ PROCESSAMENTO INTERNO PRAZZU ENTERPRISE
Schedule::command('prazzu:processar-roadmap-interno --silent-notifications')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// 🧭 CENTRO OPERACIONAL
Schedule::command('centro-operacional:processar --silent-notifications')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// 🧪 DIAGNÓSTICO FINAL / COMPATIBILIDADE
Artisan::command('prazzu:diagnostico-producao {--limite=500} {--arquivo=} {--sem-arquivos} {--somente-erros}', function () {
    $options = [
        '--limite' => (int) $this->option('limite'),
    ];

    if ($this->option('arquivo')) {
        $options['--arquivo'] = (string) $this->option('arquivo');
    }

    if ($this->option('sem-arquivos')) {
        $options['--sem-arquivos'] = true;
    }

    if ($this->option('somente-erros')) {
        $options['--somente-erros'] = true;
    }

    return Artisan::call('sistemrh:diagnostico', $options, $this->getOutput());
})->purpose('Alias de compatibilidade para o diagnóstico profundo do SistemRH/Prazzu.');
