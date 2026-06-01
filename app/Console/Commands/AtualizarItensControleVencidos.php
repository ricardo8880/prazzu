<?php

namespace App\Console\Commands;

use App\Models\ItemControle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class AtualizarItensControleVencidos extends Command
{
    protected $signature = 'itens-controle:atualizar-vencidos';

    protected $description = 'Atualiza automaticamente o status dos itens vencidos, ignorando concluídos, cancelados e já vencidos.';

    public function handle(): int
    {
        $iniciouEm = now();
        $total = 0;
        $erros = 0;

        Log::info('Iniciando atualização automática de itens vencidos.', [
            'executado_em' => $iniciouEm->toDateTimeString(),
        ]);

        ItemControle::query()
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->whereNotIn('status', ['concluido', 'cancelado', 'vencido'])
            ->orderBy('id')
            ->chunkById(200, function ($itens) use (&$total, &$erros): void {
                foreach ($itens as $item) {
                    $statusAnterior = $item->status;

                    try {
                        activity()->disableLogging();

                        $item->update([
                            'status' => 'vencido',
                        ]);
                    } catch (Throwable $exception) {
                        $erros++;

                        Log::error('Falha ao atualizar item vencido automaticamente.', [
                            'item_controle_id' => $item->id,
                            'empresa_id' => $item->empresa_id ?? null,
                            'status_anterior' => $statusAnterior,
                            'data_vencimento' => optional($item->data_vencimento)->format('Y-m-d'),
                            'message' => $exception->getMessage(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine(),
                        ]);

                        $this->error("Falha ao atualizar item #{$item->id}: {$exception->getMessage()}");
                        continue;
                    } finally {
                        activity()->enableLogging();
                    }

                    activity('item_controle')
                        ->performedOn($item)
                        ->withProperties([
                            'old' => [
                                'status' => $statusAnterior,
                            ],
                            'attributes' => [
                                'status' => 'vencido',
                            ],
                        ])
                        ->event('status_automatico')
                        ->log('Status atualizado automaticamente para vencido');

                    $total++;
                }
            });

        Log::info('Atualização automática de itens vencidos finalizada.', [
            'atualizados' => $total,
            'erros' => $erros,
            'duracao_segundos' => now()->diffInSeconds($iniciouEm),
        ]);

        $this->info("Itens atualizados como vencidos: {$total}");

        if ($erros > 0) {
            $this->warn("Itens com falha: {$erros}. Consulte os logs para detalhes.");
        }

        return $erros > 0 ? self::FAILURE : self::SUCCESS;
    }
}
