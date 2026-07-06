<?php

namespace Tests\Unit\Support;

use App\Support\PrazzuPerformance;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class PrazzuPerformanceTest extends TestCase
{
    public function test_day_bounds_retorna_intervalo_meio_aberto_do_dia(): void
    {
        $bounds = PrazzuPerformance::dayBounds('2026-07-06 15:34:22');

        $this->assertSame(['2026-07-06 00:00:00', '2026-07-07 00:00:00'], $bounds);
    }

    public function test_day_bounds_aceita_carbon_interface(): void
    {
        $date = CarbonImmutable::parse('2026-12-31 23:59:59');

        $this->assertSame(['2026-12-31 00:00:00', '2027-01-01 00:00:00'], PrazzuPerformance::dayBounds($date));
    }

    public function test_safe_limit_impede_cargas_acidentais_grandes_ou_invalidas(): void
    {
        $this->assertSame(500, PrazzuPerformance::safeLimit(null));
        $this->assertSame(1, PrazzuPerformance::safeLimit(-50));
        $this->assertSame(123, PrazzuPerformance::safeLimit(123));
        $this->assertSame(5000, PrazzuPerformance::safeLimit(999999));
        $this->assertSame(250, PrazzuPerformance::safeLimit(null, 250, 1000));
    }
}
