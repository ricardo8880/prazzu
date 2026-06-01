<?php

namespace App\Exports;

use App\Models\ItemControle;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ItemControlesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected Collection $items
    ) {
    }

    public function collection(): Collection
    {
        return $this->items->map(function (ItemControle $item) {
            $statusExibicao = $item->status;

            if (
                $item->data_vencimento &&
                $item->data_vencimento->copy()->startOfDay()->isPast() &&
                ! in_array($item->status, ['concluido', 'cancelado', 'vencido'], true)
            ) {
                $statusExibicao = 'vencido';
            }

            $situacaoPrazo = '-';

            if ($item->data_vencimento) {
                if (in_array($item->status, ['concluido', 'cancelado'], true)) {
                    $situacaoPrazo = ucfirst(str_replace('_', ' ', $item->status));
                } else {
                    $dias = now()->startOfDay()->diffInDays($item->data_vencimento->copy()->startOfDay(), false);

                    $situacaoPrazo = match (true) {
                        $dias < 0 => 'Vencido',
                        $dias === 0 => 'Vence hoje',
                        $dias <= 7 => 'Proximo do vencimento',
                        default => 'No prazo',
                    };
                }
            }

            return [
                'ID' => $item->id,
                'Titulo' => $item->titulo,
                'Tipo' => $this->traduzirTipo($item->tipo),
                'Status' => $this->traduzirStatus($statusExibicao),
                'Empresa' => $item->empresa?->razao_social,
                'Responsavel' => $item->responsavel?->nome,
                'Vencimento' => optional($item->data_vencimento)?->format('d/m/Y'),
                'Conclusao' => optional($item->data_conclusao)?->format('d/m/Y'),
                'Situacao do prazo' => $situacaoPrazo,
                'Anexo principal' => filled($item->arquivo) ? 'Sim' : 'Nao',
                'Qtd. anexos complementares' => $item->anexos_count,
                'Qtd. comentarios' => $item->comentarios_count,
                'Observacao' => $item->observacao,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Titulo',
            'Tipo',
            'Status',
            'Empresa',
            'Responsavel',
            'Vencimento',
            'Conclusao',
            'Situacao do prazo',
            'Anexo principal',
            'Qtd. anexos complementares',
            'Qtd. comentarios',
            'Observacao',
        ];
    }

    protected function traduzirTipo(?string $tipo): string
    {
        return match ($tipo) {
            'contrato' => 'Contrato',
            'documento' => 'Documento',
            'licenca' => 'Licenca',
            'acordo' => 'Acordo',
            default => (string) $tipo,
        };
    }

    protected function traduzirStatus(?string $status): string
    {
        return match ($status) {
            'pendente' => 'Pendente',
            'em_andamento' => 'Em andamento',
            'concluido' => 'Concluido',
            'cancelado' => 'Cancelado',
            'vencido' => 'Vencido',
            default => (string) $status,
        };
    }
}
