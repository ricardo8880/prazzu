<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AuditoriaExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(
        private readonly array $rows,
        private readonly array $headings,
    ) {}

    public function headings(): array
    {
        return $this->headings;
    }

    public function array(): array
    {
        return array_map(fn (array $row): array => array_values($row), $this->rows);
    }
}
