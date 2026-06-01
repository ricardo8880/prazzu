<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

class PrazzuAutomationService
{
    public function __construct(
        private readonly PrazzuAutomationEngine $engine,
    ) {}

    /**
     * Mantém compatibilidade com chamadas antigas, mas usa o motor real de automação.
     *
     * O service anterior alterava campos dinamicamente sem validar tabela/coluna e podia
     * deixar botões ou regras visíveis sem efeito. Agora a execução passa pelo engine
     * central, que valida schema, evita duplicidade diária e registra ações internas.
     */
    public function process(object $record, string $module = 'item_controles'): void
    {
        try {
            $payload = method_exists($record, 'toArray') ? $record->toArray() : get_object_vars($record);
            $payload['tipo'] = $payload['tipo'] ?? $module;

            $this->engine->runForItem($payload);
        } catch (Throwable $exception) {
            Log::warning('Falha ao processar automação operacional.', [
                'module' => $module,
                'record_id' => $record->id ?? null,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
