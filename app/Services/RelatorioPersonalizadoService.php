<?php

namespace App\Services;

use App\Models\ItemControle;
use App\Models\RelatorioPersonalizado;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RelatorioPersonalizadoService
{
    public const CAMPOS_ITEM_CONTROLE = [
        'id' => 'Código',
        'titulo' => 'Título',
        'tipo' => 'Tipo',
        'status' => 'Status',
        'prioridade' => 'Prioridade',
        'empresa.razao_social' => 'Empresa',
        'responsavel.nome' => 'Responsável',
        'categoria.nome' => 'Categoria',
        'data_vencimento' => 'Vencimento',
        'data_conclusao' => 'Conclusão',
        'sla_status' => 'Status SLA',
        'sla_limite_em' => 'Limite SLA',
        'contrato_numero' => 'Nº Contrato',
        'contrato_status' => 'Status Contrato',
        'contrato_valor' => 'Valor Contrato',
        'created_at' => 'Criado em',
    ];

    public const CAMPOS_FILTRO_ITEM_CONTROLE = [
        'titulo' => 'Título',
        'tipo' => 'Tipo',
        'status' => 'Status',
        'prioridade' => 'Prioridade',
        'categoria_id' => 'Categoria',
        'responsavel_id' => 'Responsável',
        'data_vencimento' => 'Vencimento',
        'data_conclusao' => 'Conclusão',
        'sla_status' => 'Status SLA',
        'contrato_status' => 'Status Contrato',
        'contrato_inicio_em' => 'Início Contrato',
        'contrato_fim_em' => 'Fim Contrato',
        'created_at' => 'Criado em',
    ];

    public const OPERADORES = [
        'igual' => 'Igual',
        'diferente' => 'Diferente',
        'contem' => 'Contém',
        'maior_igual' => 'Maior ou igual',
        'menor_igual' => 'Menor ou igual',
    ];

    public const TIPOS_COLUNA = [
        'texto' => 'Texto',
        'numero' => 'Número',
        'data' => 'Data',
        'data_hora' => 'Data e hora',
        'moeda' => 'Moeda',
        'badge' => 'Badge',
    ];

    protected const COLUNAS_SELECT_ITEM_CONTROLE = [
        'id',
        'empresa_id',
        'responsavel_id',
        'categoria_id',
        'titulo',
        'tipo',
        'status',
        'prioridade',
        'data_vencimento',
        'data_conclusao',
        'sla_status',
        'sla_limite_em',
        'contrato_numero',
        'contrato_status',
        'contrato_valor',
        'created_at',
    ];

    public function dados(RelatorioPersonalizado $relatorio, array $filtros = [], int $limite = 5000): Collection
    {
        $relatorio->loadMissing(['colunas', 'filtros']);

        return $this->query($relatorio, $filtros)
            ->limit($limite)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarLinha($item, $relatorio));
    }

    public function registrosPreview(RelatorioPersonalizado $relatorio, int $limite = 8): Collection
    {
        return $this->query($relatorio)
            ->limit($limite)
            ->get()
            ->map(fn (ItemControle $item): array => $this->formatarLinha($item, $relatorio));
    }

    public function total(RelatorioPersonalizado $relatorio): int
    {
        return (clone $this->query($relatorio))->count();
    }

    public function pendentes(RelatorioPersonalizado $relatorio): int
    {
        return (clone $this->query($relatorio))
            ->whereNotIn('status', ['concluido', 'cancelado'])
            ->count();
    }

    public function vencidos(RelatorioPersonalizado $relatorio): int
    {
        return (clone $this->query($relatorio))
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->whereNotIn('status', ['concluido', 'cancelado'])
            ->count();
    }

    public function concluidos(RelatorioPersonalizado $relatorio): int
    {
        return (clone $this->query($relatorio))
            ->where('status', 'concluido')
            ->count();
    }

    public function agrupadoPor(RelatorioPersonalizado $relatorio, string $campo = 'status', int $limite = 6): Collection
    {
        if (! in_array($campo, ['status', 'tipo', 'prioridade', 'sla_status', 'contrato_status'], true)) {
            $campo = 'status';
        }

        return (clone $this->query($relatorio))
            ->select([
                DB::raw("COALESCE({$campo}, 'sem_informacao') as label"),
                DB::raw('COUNT(*) as valor'),
            ])
            ->groupBy(DB::raw("COALESCE({$campo}, 'sem_informacao')"))
            ->orderByDesc('valor')
            ->limit($limite)
            ->get()
            ->map(fn ($linha): array => [
                'label' => $this->labelCampo((string) $linha->label),
                'valor' => (int) $linha->valor,
            ]);
    }

    public function query(RelatorioPersonalizado $relatorio, array $filtros = []): Builder
    {
        $relatorio->loadMissing(['filtros']);

        $query = ItemControle::query()
            ->select(self::COLUNAS_SELECT_ITEM_CONTROLE)
            ->with([
                'empresa:id,razao_social,nome_fantasia',
                'responsavel:id,nome',
                'categoria:id,nome',
            ])
            ->where('empresa_id', $relatorio->empresa_id);

        foreach ($relatorio->filtros as $filtro) {
            if (! $filtro->ativo || ! array_key_exists($filtro->campo, self::CAMPOS_FILTRO_ITEM_CONTROLE)) {
                continue;
            }

            $valor = $filtros[$filtro->campo] ?? $filtro->valor_padrao;

            if (blank($valor)) {
                continue;
            }

            match ($filtro->operador) {
                'igual' => $query->where($filtro->campo, $valor),
                'diferente' => $query->where($filtro->campo, '!=', $valor),
                'contem' => $query->where($filtro->campo, 'like', '%' . $valor . '%'),
                'maior_igual' => $query->where($filtro->campo, '>=', $valor),
                'menor_igual' => $query->where($filtro->campo, '<=', $valor),
                default => null,
            };
        }

        return $query->latest('id');
    }

    public function colunasAtivas(RelatorioPersonalizado $relatorio): Collection
    {
        $relatorio->loadMissing(['colunas']);

        $colunas = $relatorio->colunas->where('ativo', true)->values();

        if ($colunas->isNotEmpty()) {
            return $colunas;
        }

        return collect([
            'id',
            'titulo',
            'tipo',
            'status',
            'prioridade',
            'responsavel.nome',
            'data_vencimento',
        ])->map(fn (string $campo, int $index): object => (object) [
            'campo' => $campo,
            'rotulo' => self::CAMPOS_ITEM_CONTROLE[$campo] ?? $campo,
            'tipo' => $this->tipoPadraoCampo($campo),
            'ordem' => $index + 1,
            'ativo' => true,
        ]);
    }

    public function formatarLinha(ItemControle $item, RelatorioPersonalizado $relatorio): array
    {
        $linha = [];

        foreach ($this->colunasAtivas($relatorio) as $coluna) {
            $rotulo = $coluna->rotulo ?: (self::CAMPOS_ITEM_CONTROLE[$coluna->campo] ?? $coluna->campo);
            $linha[$rotulo] = $this->formatarValor(data_get($item, $coluna->campo), $coluna->tipo ?: $this->tipoPadraoCampo($coluna->campo));
        }

        return $linha;
    }

    public function tipoPadraoCampo(string $campo): string
    {
        return match ($campo) {
            'id', 'contrato_valor' => 'numero',
            'data_vencimento', 'data_conclusao', 'contrato_inicio_em', 'contrato_fim_em' => 'data',
            'created_at', 'updated_at', 'sla_limite_em' => 'data_hora',
            'status', 'tipo', 'prioridade', 'sla_status', 'contrato_status' => 'badge',
            default => 'texto',
        };
    }

    public function formatarValor(mixed $valor, string $tipo = 'texto'): string
    {
        if ($valor === null || $valor === '') {
            return '-';
        }

        if ($valor instanceof CarbonInterface) {
            return $tipo === 'data' ? $valor->format('d/m/Y') : $valor->format('d/m/Y H:i');
        }

        return match ($tipo) {
            'data' => $this->formatarData((string) $valor),
            'data_hora' => $this->formatarDataHora((string) $valor),
            'moeda' => 'R$ ' . number_format((float) $valor, 2, ',', '.'),
            'numero' => is_numeric($valor) ? number_format((float) $valor, 0, ',', '.') : (string) $valor,
            'badge' => $this->labelCampo((string) $valor),
            default => (string) $valor,
        };
    }

    public function labelCampo(?string $valor): string
    {
        if (blank($valor)) {
            return 'Sem informação';
        }

        return ucfirst(str_replace('_', ' ', (string) $valor));
    }

    protected function formatarData(string $valor): string
    {
        try {
            return \Carbon\Carbon::parse($valor)->format('d/m/Y');
        } catch (\Throwable) {
            return $valor;
        }
    }

    protected function formatarDataHora(string $valor): string
    {
        try {
            return \Carbon\Carbon::parse($valor)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return $valor;
        }
    }
}
