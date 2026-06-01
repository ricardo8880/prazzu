<?php

namespace App\Support;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItemControleRelatorioExportador
{
    public static function exportarCsv(?User $user, array $filters = [], string $nomeArquivo = 'relatorio-itens-controle.csv'): StreamedResponse
    {
        $registros = self::buscarRegistros($user, $filters);

        return response()->streamDownload(function () use ($registros): void {
            $saida = fopen('php://output', 'w');

            fprintf($saida, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($saida, [
                'ID',
                'Título',
                'Tipo',
                'Status',
                'Empresa',
                'Responsável',
                'Vencimento',
                'Conclusão',
                'Situação do Prazo',
                'Observação',
            ], ';');

            foreach ($registros as $item) {
                fputcsv($saida, [
                    $item->id,
                    $item->titulo,
                    self::traduzirTipo($item->tipo),
                    self::traduzirStatus($item->status),
                    $item->empresa?->razao_social,
                    $item->responsavel?->nome,
                    optional($item->data_vencimento)?->format('d/m/Y'),
                    optional($item->data_conclusao)?->format('d/m/Y'),
                    self::situacaoPrazo($item),
                    $item->observacao,
                ], ';');
            }

            fclose($saida);
        }, $nomeArquivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public static function exportarPdf(?User $user, array $filters = [], string $nomeArquivo = 'relatorio-itens-controle.pdf')
    {
        $registros = self::buscarRegistros($user, $filters);

        $pdf = Pdf::loadView('relatorios.item-controles-pdf', [
            'registros' => $registros,
            'filtros' => $filters,
            'geradoEm' => now(),
            'whiteLabel' => WhiteLabelSettings::make(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf->output();
        }, $nomeArquivo);
    }

    public static function buscarRegistros(?User $user, array $filters = []): Collection
    {
        return ItemControleQuery::applyFilters(
            ItemControleQuery::scoped($user),
            $filters
        )
            ->orderBy('data_vencimento')
            ->get();
    }

    public static function traduzirTipo(?string $tipo): string
    {
        return match ($tipo) {
            'contrato' => 'Contrato',
            'documento' => 'Documento',
            'licenca' => 'Licença',
            'acordo' => 'Acordo',
            default => (string) $tipo,
        };
    }

    public static function traduzirStatus(?string $status): string
    {
        return match ($status) {
            'pendente' => 'Pendente',
            'em_andamento' => 'Em andamento',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'vencido' => 'Vencido',
            default => (string) $status,
        };
    }

    public static function situacaoPrazo($item): string
    {
        if (! $item->data_vencimento) {
            return '-';
        }

        if (in_array($item->status, ['concluido', 'cancelado'], true)) {
            return self::traduzirStatus($item->status);
        }

        $dias = now()->startOfDay()->diffInDays($item->data_vencimento->copy()->startOfDay(), false);

        return match (true) {
            $dias < 0 => 'Vencido',
            $dias === 0 => 'Vence hoje',
            $dias <= 7 => 'Próximo do vencimento',
            default => 'No prazo',
        };
    }
}