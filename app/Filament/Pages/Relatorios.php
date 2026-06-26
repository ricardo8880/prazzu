<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\UsesAdvancedPermissions;

use App\Exports\PrazzuRelatorioOperacionalExport;
use App\Support\PrazzuRelatoriosData;
use App\Support\WhiteLabelSettings;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class Relatorios extends Page
{
    use UsesAdvancedPermissions;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string | UnitEnum | null $navigationGroup = 'Relatórios';
    protected static ?string $navigationLabel = 'Relatórios';
    protected static ?string $title = 'Relatórios';
    protected static ?int $navigationSort = 12;

    protected string $view = 'filament.pages.relatorios';


    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return static::canAdvancedPermission('relatorios.view');
    }

    public string $tipoRelatorio = 'documentos_vencidos';

    public function selecionarRelatorio(string $tipo): void
    {
        if (! array_key_exists($tipo, PrazzuRelatoriosData::TIPOS)) {
            Notification::make()
                ->title('Relatório não encontrado')
                ->body('Selecione um relatório disponível na tela.')
                ->danger()
                ->send();

            return;
        }

        $this->tipoRelatorio = $tipo;
    }

    public function exportarCsv(): StreamedResponse
    {
        if (! $this->ensureCanDo('relatorios.export')) {
            abort(403);
        }

        $rows = PrazzuRelatoriosData::exportRows($this->tipoRelatorio);
        $filename = $this->nomeArquivo('csv');
        $headings = PrazzuRelatoriosData::headings();

        return response()->streamDownload(function () use ($rows, $headings): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headings, ';');

            foreach ($rows as $row) {
                fputcsv($handle, array_values($row), ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportarExcel()
    {
        if (! $this->ensureCanDo('relatorios.export')) {
            return;
        }

        $rows = PrazzuRelatoriosData::exportRows($this->tipoRelatorio);

        return Excel::download(
            new PrazzuRelatorioOperacionalExport($rows, PrazzuRelatoriosData::headings()),
            $this->nomeArquivo('xlsx')
        );
    }

    public function exportarPdf()
    {
        if (! $this->ensureCanDo('relatorios.export')) {
            return;
        }

        $data = PrazzuRelatoriosData::dashboard($this->tipoRelatorio);

        return Pdf::loadView('exports.prazzu-relatorio-operacional-pdf', [
            'data' => $data,
            'titulo' => PrazzuRelatoriosData::TIPOS[$this->tipoRelatorio] ?? 'Relatório operacional',
            'geradoEm' => now()->format('d/m/Y H:i'),
            'whiteLabel' => WhiteLabelSettings::make(),
        ])->setPaper('a4', 'landscape')->download($this->nomeArquivo('pdf'));
    }

    protected function getViewData(): array
    {
        return PrazzuRelatoriosData::dashboard($this->tipoRelatorio);
    }

    private function nomeArquivo(string $extensao): string
    {
        $nome = PrazzuRelatoriosData::TIPOS[$this->tipoRelatorio] ?? 'relatorio-operacional';

        return Str::slug($nome).'-'.now()->format('Y-m-d-His').'.'.$extensao;
    }
}
