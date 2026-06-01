<?php

namespace App\Console\Commands;

use App\Services\AsaasService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class ReconciliarAssinaturasAsaas extends Command
{
    protected $signature = 'asaas:reconciliar-assinaturas {--limit=50 : Quantidade máxima de assinaturas sincronizadas nesta execução}';

    protected $description = 'Reconcilia assinaturas e cobranças locais com o estado atual retornado pelo Asaas.';

    public function handle(AsaasService $asaas): int
    {
        $limit = (int) $this->option('limit');
        $limit = max(1, min($limit, 200));

        try {
            $resultado = $asaas->reconciliarAssinaturasPendentes($limit);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            Log::channel('asaas')->error('Reconciliação Asaas interrompida por erro operacional.', [
                'message' => $exception->getMessage(),
                'limit' => $limit,
            ]);

            return self::FAILURE;
        } catch (Throwable $exception) {
            $this->error('Falha inesperada ao reconciliar assinaturas Asaas. Consulte o log do canal asaas.');

            Log::channel('asaas')->error('Reconciliação Asaas falhou inesperadamente.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'limit' => $limit,
            ]);

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Reconciliação concluída. Total: %d | Sincronizadas: %d | Ignoradas: %d | Falhas: %d',
            $resultado['total'],
            $resultado['sincronizadas'],
            $resultado['ignoradas'],
            $resultado['falhas']
        ));

        return ($resultado['falhas'] ?? 0) > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
