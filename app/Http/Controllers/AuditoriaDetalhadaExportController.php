<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaDetalhada;
use App\Services\AuditoriaManualService;
use App\Services\AuditoriaAccessService;
use App\Support\AuditoriaFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditoriaDetalhadaExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $access = app(AuditoriaAccessService::class);
        $user = Auth::user();

        abort_unless($access->canExport($user), 403);

        $filename = 'auditoria-detalhada-' . now()->format('Y-m-d-His') . '.csv';
        $empresaId = $access->normalizeEmpresaFilter($user, $request->input('empresa_id'));

        AuditoriaManualService::registrarEvento('auditoria.exported', [
            'arquivo' => $filename,
            'filtros' => $request->only(['evento', 'user_id', 'empresa_id', 'modulo', 'periodo', 'suspeito']),
        ], null, empresaId: $empresaId, userId: Auth::id(), nivel: 'warning');

        return response()->streamDownload(function () use ($request, $empresaId): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Data',
                'Empresa',
                'Usuario',
                'Evento',
                'Modulo',
                'Registro',
                'Campo',
                'Valor anterior',
                'Valor novo',
                'IP',
                'Navegador',
                'Suspeito',
            ], ';');

            $this->query($request, $empresaId)
                ->with(['empresa:id,razao_social,nome_fantasia', 'user:id,name,email'])
                ->latest('created_at')
                ->chunk(300, function ($registros) use ($handle): void {
                    foreach ($registros as $registro) {
                        fputcsv($handle, [
                            optional($registro->created_at)->format('d/m/Y H:i:s'),
                            $registro->empresa?->razao_social ?: $registro->empresa?->nome_fantasia ?: '-',
                            $registro->user?->name ?: 'Sistema',
                            AuditoriaFormatter::evento($registro->evento),
                            AuditoriaFormatter::modulo($registro->auditable_type),
                            AuditoriaFormatter::registro($registro->auditable_type, $registro->auditable_id),
                            AuditoriaFormatter::campo($registro->campo),
                            AuditoriaFormatter::valor($registro->valor_anterior, $registro->campo),
                            AuditoriaFormatter::valor($registro->valor_novo, $registro->campo),
                            $registro->ip ?: '-',
                            $registro->user_agent ?: '-',
                            AuditoriaFormatter::isSuspeito($registro) ? 'Sim' : 'Não',
                        ], ';');
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function query(Request $request, ?int $empresaId = null): Builder
    {
        $query = AuditoriaDetalhada::query()
            ->visibleForUser(Auth::user());

        if ($request->filled('evento')) {
            $query->where('evento', $request->string('evento')->toString());
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        if ($request->filled('modulo')) {
            $query->where('auditable_type', $request->string('modulo')->toString());
        }

        if ($request->filled('periodo')) {
            match ($request->string('periodo')->toString()) {
                'hoje' => $query->whereDate('created_at', now()->toDateString()),
                '7' => $query->where('created_at', '>=', now()->subDays(7)),
                '30' => $query->where('created_at', '>=', now()->subDays(30)),
                default => null,
            };
        }

        if ($request->boolean('suspeito')) {
            $query->where(function (Builder $subQuery): void {
                $subQuery
                    ->where('evento', 'deleted')
                    ->orWhere('campo', 'like', '%password%')
                    ->orWhere('campo', 'like', '%senha%')
                    ->orWhere('campo', 'like', '%role%')
                    ->orWhere('campo', 'like', '%permiss%')
                    ->orWhere('campo', 'like', '%status%');
            });
        }

        return $query;
    }

    private function normalizarValor(?string $valor): string
    {
        return AuditoriaFormatter::valor($valor);
    }

    private function eventoLabel(?string $evento): string
    {
        return AuditoriaFormatter::evento($evento);
    }

    private function isSuspeito($registro): bool
    {
        $campo = mb_strtolower((string) $registro->campo);

        return AuditoriaFormatter::isSuspeito($registro);
    }
}
