<?php

namespace App\Filament\Resources\ItemControles\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Services\PlanoService;
use App\Models\ItemControle;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RelatoriosInternos extends Page
{
    public static function canAccess(array $parameters = []): bool
    {
        return PlanoService::usuarioPossuiFeature(
            \Filament\Facades\Filament::auth()->user(),
            PlanoService::FEATURE_RELATORIOS_INTERNOS
        );
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return static::canAccess($parameters);
    }

    protected static string $resource = ItemControleResource::class;

    protected string $view = 'filament.resources.item-controles.pages.relatorios-internos';

    protected static ?string $title = 'Relatórios';

    public function getVencimentosProperty(): Collection
    {
        $user = Filament::auth()->user();

        return ItemControle::query()
            ->select([
                'id',
                'titulo',
                'status',
                'data_vencimento',
                'empresa_id',
                'responsavel_id',
            ])
            ->with([
                'empresa:id,razao_social',
                'responsavel:id,nome',
            ])
            ->visibleForUser($user)
            ->whereDate('data_vencimento', '<=', now()->addDays(30))
            ->whereNotIn('status', ['concluido', 'cancelado'])
            ->orderBy('data_vencimento')
            ->limit(100)
            ->get();
    }

    public function getSlaAtrasadosProperty(): Collection
    {
        $user = Filament::auth()->user();

        return ItemControle::query()
            ->select([
                'id',
                'titulo',
                'sla_status',
                'sla_limite_em',
                'empresa_id',
                'responsavel_id',
            ])
            ->with([
                'empresa:id,razao_social',
                'responsavel:id,nome',
            ])
            ->visibleForUser($user)
            ->where('sla_status', 'atrasado')
            ->orderBy('sla_limite_em')
            ->limit(100)
            ->get();
    }

    public function getAprovacoesPendentesProperty(): Collection
    {
        $user = Filament::auth()->user();

        return ItemControle::query()
            ->select([
                'id',
                'titulo',
                'status',
                'empresa_id',
                'responsavel_id',
            ])
            ->with([
                'empresa:id,razao_social',
                'responsavel:id,nome',
            ])
            ->visibleForUser($user)
            ->where('status', 'em_aprovacao')
            ->limit(100)
            ->get();
    }

    public function getProdutividadeProperty(): Collection
    {
        $user = Filament::auth()->user();

        return ItemControle::query()
            ->visibleForUser($user)
            ->select([
                'responsavel_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos"),
            ])
            ->with([
                'responsavel:id,nome',
            ])
            ->groupBy('responsavel_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get();
    }

    public function getContratosVencendoProperty(): Collection
    {
        $user = Filament::auth()->user();

        return ItemControle::query()
            ->select([
                'id',
                'titulo',
                'contrato_status',
                'contrato_fim_em',
                'empresa_id',
            ])
            ->with([
                'empresa:id,razao_social',
            ])
            ->visibleForUser($user)
            ->whereNotNull('contrato_fim_em')
            ->whereDate('contrato_fim_em', '<=', now()->addDays(30))
            ->orderBy('contrato_fim_em')
            ->limit(100)
            ->get();
    }
}
