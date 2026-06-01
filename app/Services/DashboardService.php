<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\ItemControle;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public static function getData(): array
    {
        $user = Filament::auth()->user();

        if ($user?->isSuperAdmin()) {
            return self::superAdmin();
        }

        return self::empresa($user);
    }

    protected static function superAdmin(): array
    {
        return Cache::remember('dashboard.superadmin', 60, function (): array {
            $crescimento = Empresa::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mes, COUNT(*) as total")
                ->whereNotNull('created_at')
                ->groupBy('mes')
                ->orderBy('mes')
                ->get();

            $ranking = Empresa::query()
                ->withCount('itemControles')
                ->orderByDesc('item_controles_count')
                ->limit(10)
                ->get()
                ->map(function (Empresa $empresa): array {
                    $itens = (int) $empresa->item_controles_count;

                    return [
                        'id' => $empresa->id,
                        'nome' => $empresa->razao_social,
                        'itens' => $itens,
                        'score' => min(100, $itens),
                    ];
                })
                ->values();

            $churn = Empresa::query()
                ->doesntHave('itemControles')
                ->orderBy('razao_social')
                ->limit(10)
                ->get();

            $atividade = ItemControle::query()
                ->with(['empresa:id,razao_social'])
                ->latest('created_at')
                ->limit(10)
                ->get();

            $empresasComVencidos = Empresa::query()
                ->withCount([
                    'itemControles as vencidos_count' => function ($query): void {
                        $query->where('status', 'vencido');
                    },
                ])
                ->having('vencidos_count', '>', 0)
                ->orderByDesc('vencidos_count')
                ->limit(10)
                ->get();

            $empresasSemUsuarios = Empresa::query()
                ->doesntHave('users')
                ->orderBy('razao_social')
                ->limit(10)
                ->get();

            return [
                'tipo' => 'superadmin',

                'totalEmpresas' => Empresa::query()->count(),
                'empresasAtivas' => Empresa::query()->where('status', 'ativo')->count(),
                'empresasInativas' => Empresa::query()->where('status', 'inativo')->count(),
                'totalUsuarios' => User::query()->count(),
                'totalItens' => ItemControle::query()->count(),
                'totalVencidos' => ItemControle::query()->where('status', 'vencido')->count(),
                'totalPendentes' => ItemControle::query()->whereIn('status', ['pendente', 'em_andamento'])->count(),
                'totalConcluidos' => ItemControle::query()->where('status', 'concluido')->count(),

                'crescimento' => $crescimento,
                'ranking' => $ranking,
                'churn' => $churn,
                'atividade' => $atividade,
                'empresasComVencidos' => $empresasComVencidos,
                'empresasSemUsuarios' => $empresasSemUsuarios,
            ];
        });
    }

    protected static function empresa($user): array
    {
        $empresaId = $user?->empresa_id;

        return Cache::remember("dashboard.empresa.{$empresaId}", 60, function () use ($empresaId): array {
            return [
                'tipo' => 'empresa',

                'totalItens' => ItemControle::query()
                    ->where('empresa_id', $empresaId)
                    ->count(),

                'itensVencidos' => ItemControle::query()
                    ->where('empresa_id', $empresaId)
                    ->where('status', 'vencido')
                    ->count(),

                'itensPendentes' => ItemControle::query()
                    ->where('empresa_id', $empresaId)
                    ->whereIn('status', ['pendente', 'em_andamento'])
                    ->count(),

                'itensConcluidos' => ItemControle::query()
                    ->where('empresa_id', $empresaId)
                    ->where('status', 'concluido')
                    ->count(),

                'proximosVencimentos' => ItemControle::query()
                    ->with(['responsavel:id,nome'])
                    ->where('empresa_id', $empresaId)
                    ->whereNotIn('status', ['concluido', 'cancelado'])
                    ->whereDate('data_vencimento', '>=', now()->toDateString())
                    ->orderBy('data_vencimento')
                    ->limit(10)
                    ->get(),

                'ultimosItens' => ItemControle::query()
                    ->with(['responsavel:id,nome'])
                    ->where('empresa_id', $empresaId)
                    ->latest('created_at')
                    ->limit(10)
                    ->get(),
            ];
        });
    }
}
