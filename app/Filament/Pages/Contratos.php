<?php

namespace App\Filament\Pages;


use App\Support\CachedSchema;
use App\Filament\Resources\ItemControles\ItemControleResource;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class Contratos extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    protected static string | UnitEnum | null $navigationGroup = 'Contratos e Financeiro';
    protected static ?string $navigationLabel = 'Contratos e Financeiro';
    protected static ?string $title = 'Contratos e Financeiro';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.contratos';

    public function getHeading(): string
    {
        return 'Contratos e Financeiro';
    }

    public function getSubheading(): ?string
    {
        return 'Fonte oficial da gestão contratual: vigência, partes, valores, status e assinaturas vinculadas.';
    }


    public static function shouldRegisterNavigation(): bool { return true; }
    public static function canAccess(): bool { return true; }

    protected function getViewData(): array
    {
        return ['resumo' => $this->resumo(), 'contratos' => $this->contratos()];
    }

    private function resumo(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return ['total' => 0, 'ativos' => 0, 'vencendo' => 0, 'valor' => 0, 'vencidos' => 0, 'semVigencia' => 0];
        }

        $base = DB::table('item_controles')->where(function ($query) {
            $query->where('tipo', 'like', '%contrato%')->orWhereNotNull('contrato_numero');
        });

        return [
            'total' => (clone $base)->count(),
            'ativos' => (clone $base)->whereIn('contrato_status', ['ativo', 'vigente', 'em_vigor'])->count(),
            'vencendo' => (clone $base)->whereBetween('contrato_fim_em', [now()->toDateString(), now()->addDays(30)->toDateString()])->count(),
            'vencidos' => (clone $base)->whereNotNull('contrato_fim_em')->whereDate('contrato_fim_em', '<', now()->toDateString())->count(),
            'semVigencia' => (clone $base)->whereNull('contrato_fim_em')->count(),
            'valor' => (clone $base)->sum('contrato_valor'),
        ];
    }

    private function contratos(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return DB::table('item_controles')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->select('item_controles.id', 'item_controles.titulo', 'item_controles.descricao', 'item_controles.arquivo', 'item_controles.contrato_numero', 'item_controles.contrato_parte_nome', 'item_controles.contrato_parte_documento', 'item_controles.contrato_valor', 'item_controles.contrato_inicio_em', 'item_controles.contrato_fim_em', 'item_controles.contrato_status', 'item_controles.status', 'item_controles.updated_at', 'empresas.nome_fantasia', 'empresas.razao_social')
            ->where(function ($query) {
                $query->where('item_controles.tipo', 'like', '%contrato%')->orWhereNotNull('item_controles.contrato_numero');
            })
            ->orderByRaw('CASE WHEN item_controles.contrato_fim_em IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.contrato_fim_em')
            ->limit(24)
            ->get()
            ->map(function ($item) {
                $item = (array) $item;
                $item['edit_url'] = ItemControleResource::getUrl('edit', ['record' => $item['id']]);
                $item['arquivo_url'] = ! empty($item['arquivo']) ? asset('storage/' . $item['arquivo']) : null;
                return $item;
            })
            ->all();
    }
}
