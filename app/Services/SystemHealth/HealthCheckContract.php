<?php

namespace App\Services\SystemHealth;

interface HealthCheckContract
{
    public function key(): string;

    public function name(): string;

    public function description(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(int $limit = 500): array;
}
