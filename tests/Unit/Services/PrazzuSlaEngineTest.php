<?php

namespace Tests\Unit\Services;

use App\Services\PrazzuSlaEngine;
use PHPUnit\Framework\TestCase;

class PrazzuSlaEngineTest extends TestCase
{
    public function test_status_sem_sla_quando_nao_existe_limite(): void
    {
        $engine = new PrazzuSlaEngine();

        $this->assertSame(PrazzuSlaEngine::STATUS_SEM_SLA, $engine->status(null));
    }

    public function test_status_aberto_em_andamento_risco_e_vencido(): void
    {
        $engine = new PrazzuSlaEngine();
        $reference = '2026-07-06 10:00:00';

        $this->assertSame(PrazzuSlaEngine::STATUS_EM_ANDAMENTO, $engine->status('2026-07-07 10:00:00', null, 8, $reference));
        $this->assertSame(PrazzuSlaEngine::STATUS_RISCO, $engine->status('2026-07-06 16:00:00', null, 8, $reference));
        $this->assertSame(PrazzuSlaEngine::STATUS_VENCIDO, $engine->status('2026-07-06 09:59:59', null, 8, $reference));
    }

    public function test_status_finalizado_no_prazo_ou_atrasado(): void
    {
        $engine = new PrazzuSlaEngine();

        $this->assertSame(PrazzuSlaEngine::STATUS_CONCLUIDO_NO_PRAZO, $engine->status('2026-07-06 18:00:00', '2026-07-06 17:59:59'));
        $this->assertSame(PrazzuSlaEngine::STATUS_CONCLUIDO_ATRASADO, $engine->status('2026-07-06 18:00:00', '2026-07-06 18:00:01'));
    }

    public function test_status_for_record_prioriza_campos_de_sla(): void
    {
        $engine = new PrazzuSlaEngine();
        $record = [
            'sla_limite_em' => '2026-07-06 12:00:00',
            'data_vencimento' => '2026-07-10 12:00:00',
            'sla_concluido_em' => null,
            'data_conclusao' => null,
        ];

        $this->assertSame(PrazzuSlaEngine::STATUS_RISCO, $engine->statusForRecord($record, 8, '2026-07-06 10:00:00'));
    }

    public function test_normaliza_status_legados(): void
    {
        $engine = new PrazzuSlaEngine();

        $this->assertSame(PrazzuSlaEngine::STATUS_EM_ANDAMENTO, $engine->normalizeStatus('em andamento'));
        $this->assertSame(PrazzuSlaEngine::STATUS_RISCO, $engine->normalizeStatus('atenção'));
        $this->assertSame(PrazzuSlaEngine::STATUS_VENCIDO, $engine->normalizeStatus('atrasado'));
        $this->assertSame(PrazzuSlaEngine::STATUS_SEM_SLA, $engine->normalizeStatus('desconhecido'));
    }
}
