<?php

namespace App\Filament\Pages;

use App\Exports\AuditoriaExport;
use App\Support\ComplianceModuleData;
use App\Services\AuditoriaAccessService;
use App\Filament\Resources\AuditoriaDetalhada\AuditoriaDetalhadaResource;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class Auditoria extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static string | UnitEnum | null $navigationGroup = 'Relatórios e Auditoria';

    protected static ?string $navigationLabel = 'Auditoria e Rastreabilidade';

    protected static ?string $title = 'Auditoria e Rastreabilidade';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.compliance-auditoria';

    public static function canAccess(): bool
    {
        return app(AuditoriaAccessService::class)->canView(auth()->user());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function exportAuditoriaCsv(): StreamedResponse
    {
        abort_unless(app(AuditoriaAccessService::class)->canExport(auth()->user()), 403);

        $filters = $this->resolveAuditFilters();
        $rows = ComplianceModuleData::auditoriaExportRows(auth()->user(), $filters);
        $headings = ComplianceModuleData::auditoriaExportHeadings();
        $filename = $this->exportFilename('csv');

        return response()->streamDownload(function () use ($rows, $headings): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headings, ';');

            foreach ($rows as $row) {
                fputcsv($output, array_values($row), ';');
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportAuditoriaExcel(): BinaryFileResponse
    {
        abort_unless(app(AuditoriaAccessService::class)->canExport(auth()->user()), 403);

        $filters = $this->resolveAuditFilters();
        $rows = ComplianceModuleData::auditoriaExportRows(auth()->user(), $filters);
        $headings = ComplianceModuleData::auditoriaExportHeadings();

        return Excel::download(
            new AuditoriaExport($rows, $headings),
            $this->exportFilename('xlsx')
        );
    }

    protected function getViewData(): array
    {
        $filters = $this->resolveAuditFilters();

        return [
            'data' => ComplianceModuleData::auditoria($filters),
            'filters' => $filters,
            'auditoriaDetalhadaUrl' => class_exists(AuditoriaDetalhadaResource::class)
                ? AuditoriaDetalhadaResource::getUrl('index')
                : url('/admin/auditoria-detalhada'),
        ];
    }

    public function canExportAuditoria(): bool
    {
        return app(AuditoriaAccessService::class)->canExport(auth()->user());
    }

    private function resolveAuditFilters(): array
    {
        $empresaId = app(AuditoriaAccessService::class)->normalizeEmpresaFilter(auth()->user(), request()->query('companyFilter', 'todas'));

        return [
            'dateFilter' => request()->query('dateFilter', '30'),
            'fromDate' => request()->query('fromDate'),
            'toDate' => request()->query('toDate'),
            'userFilter' => request()->query('userFilter', 'todos'),
            'companyFilter' => $empresaId ?: 'todas',
            'actionFilter' => request()->query('actionFilter', 'todas'),
            'searchFilter' => trim((string) request()->query('searchFilter', '')),
            'auditableType' => trim((string) request()->query('auditableType', '')),
            'auditableId' => trim((string) request()->query('auditableId', '')),
        ];
    }

    private function exportFilename(string $extension): string
    {
        return 'auditoria-' . now()->format('Y-m-d-His') . '-' . Str::random(6) . '.' . $extension;
    }
}
