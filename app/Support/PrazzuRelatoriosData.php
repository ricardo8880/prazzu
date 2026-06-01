<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PrazzuRelatoriosData
{
    public const TIPOS = [
        'documentos_vencidos' => 'Documentos vencidos',
        'documentos_vencendo' => 'Documentos vencendo',
        'aprovacoes' => 'Aprovações',
        'assinaturas' => 'Assinaturas',
        'produtividade' => 'Produtividade',
        'por_cliente' => 'Por cliente',
    ];

    public static function dashboard(string $tipo = 'documentos_vencidos'): array
    {
        $tipo = array_key_exists($tipo, self::TIPOS) ? $tipo : 'documentos_vencidos';

        return [
            'tipoAtual' => $tipo,
            'tipos' => self::TIPOS,
            'resumo' => self::resumoGeral(),
            'cards' => self::cardsDoTipo($tipo),
            'linhas' => self::linhas($tipo, 80),
            'clientesCriticos' => self::clientesCriticos(),
            'seguranca' => PrazzuSecurityChecklist::avaliar(),
            'validacao' => PrazzuValidationChecklist::avaliar(),
        ];
    }

    public static function linhas(string $tipo, int $limit = 500): Collection
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return collect();
        }

        return match ($tipo) {
            'documentos_vencendo' => self::itensBase()
                ->whereNotNull('item_controles.data_vencimento')
                ->whereDate('item_controles.data_vencimento', '>=', now()->toDateString())
                ->whereDate('item_controles.data_vencimento', '<=', now()->addDays(30)->toDateString())
                ->whereNotIn('item_controles.status', self::statusFinalizados())
                ->orderBy('item_controles.data_vencimento')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => self::formatarItem((array) $row, 'Vencendo')),
            'aprovacoes' => self::itensBase()
                ->where(function ($query) {
                    if (CachedSchema::hasColumn('item_controles', 'approval_status')) {
                        $query->whereIn('item_controles.approval_status', ['pendente', 'em_aprovacao', 'em_aprovação', 'aguardando']);
                    }
                    $query->orWhere('item_controles.status', 'em_aprovacao');
                })
                ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
                ->orderBy('item_controles.data_vencimento')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => self::formatarItem((array) $row, 'Aprovação')),
            'assinaturas' => self::assinaturas($limit),
            'produtividade' => self::produtividade($limit),
            'por_cliente' => self::porCliente($limit),
            default => self::itensBase()
                ->whereNotNull('item_controles.data_vencimento')
                ->whereDate('item_controles.data_vencimento', '<', now()->toDateString())
                ->whereNotIn('item_controles.status', self::statusFinalizados())
                ->orderBy('item_controles.data_vencimento')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => self::formatarItem((array) $row, 'Vencido')),
        };
    }

    public static function exportRows(string $tipo): Collection
    {
        return self::linhas($tipo, 2000)->map(function (array $row): array {
            return [
                'Relatório' => $row['relatorio'] ?? '-',
                'Cliente' => $row['cliente'] ?? '-',
                'Título' => $row['titulo'] ?? '-',
                'Status' => $row['status'] ?? '-',
                'Responsável' => $row['responsavel'] ?? '-',
                'Vencimento' => $row['vencimento'] ?? '-',
                'Dias' => $row['dias'] ?? '-',
                'Prioridade' => $row['prioridade'] ?? '-',
                'Indicador' => $row['indicador'] ?? '-',
                'Observação' => $row['observacao'] ?? '-',
            ];
        });
    }

    public static function headings(): array
    {
        return ['Relatório', 'Cliente', 'Título', 'Status', 'Responsável', 'Vencimento', 'Dias', 'Prioridade', 'Indicador', 'Observação'];
    }

    private static function resumoGeral(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return self::zeros();
        }

        $hoje = now()->toDateString();
        $em30 = now()->addDays(30)->toDateString();
        $base = DB::table('item_controles');
        $statusFinalizados = self::statusFinalizados();

        $vencidos = (clone $base)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', $hoje)
            ->whereNotIn('status', $statusFinalizados)
            ->count();

        $vencendo = (clone $base)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '>=', $hoje)
            ->whereDate('data_vencimento', '<=', $em30)
            ->whereNotIn('status', $statusFinalizados)
            ->count();

        $aprovacoes = (clone $base)
            ->where(function ($query) {
                if (CachedSchema::hasColumn('item_controles', 'approval_status')) {
                    $query->whereIn('approval_status', ['pendente', 'em_aprovacao', 'em_aprovação', 'aguardando']);
                }
                $query->orWhere('status', 'em_aprovacao');
            })
            ->count();

        $assinaturas = CachedSchema::hasTable('item_controle_assinaturas')
            ? DB::table('item_controle_assinaturas')->whereNull('assinado_em')->count()
            : 0;

        return [
            ['label' => 'Vencidos', 'value' => $vencidos, 'hint' => 'Exigem ação imediata', 'tone' => $vencidos > 0 ? 'danger' : 'ok'],
            ['label' => 'Vencem em 30 dias', 'value' => $vencendo, 'hint' => 'Acompanhar preventivamente', 'tone' => $vencendo > 0 ? 'warning' : 'ok'],
            ['label' => 'Aprovações pendentes', 'value' => $aprovacoes, 'hint' => 'Fluxos aguardando decisão', 'tone' => $aprovacoes > 0 ? 'warning' : 'ok'],
            ['label' => 'Assinaturas pendentes', 'value' => $assinaturas, 'hint' => 'Documentos aguardando aceite', 'tone' => $assinaturas > 0 ? 'warning' : 'ok'],
        ];
    }

    private static function cardsDoTipo(string $tipo): array
    {
        $rows = self::linhas($tipo, 1000);
        $total = $rows->count();
        $clientes = $rows->pluck('cliente')->filter()->unique()->count();
        $criticos = $rows->filter(fn ($row) => in_array($row['prioridade'] ?? '', ['alta', 'critica', 'crítica', 'urgente'], true))->count();
        $responsaveis = $rows->pluck('responsavel')->filter(fn ($value) => filled($value) && $value !== 'Sem responsável')->unique()->count();

        return [
            ['label' => 'Registros', 'value' => $total, 'hint' => 'Itens encontrados no relatório', 'tone' => $total > 0 ? 'info' : 'ok'],
            ['label' => 'Clientes impactados', 'value' => $clientes, 'hint' => 'Carteira afetada', 'tone' => $clientes > 0 ? 'warning' : 'ok'],
            ['label' => 'Prioridade alta', 'value' => $criticos, 'hint' => 'Requerem atenção primeiro', 'tone' => $criticos > 0 ? 'danger' : 'ok'],
            ['label' => 'Responsáveis', 'value' => $responsaveis, 'hint' => 'Pessoas acionáveis', 'tone' => 'info'],
        ];
    }

    private static function clientesCriticos(): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return [];
        }

        return self::porCliente(10)
            ->filter(fn ($row) => ($row['indicador_numero'] ?? 0) > 0)
            ->values()
            ->all();
    }

    private static function itensBase()
    {
        $select = [
            'item_controles.id',
            'item_controles.titulo',
            'item_controles.status',
            'item_controles.prioridade',
            'item_controles.data_vencimento',
            'item_controles.created_at',
            'empresas.razao_social',
            'empresas.nome_fantasia',
            'responsaveis.nome as responsavel_nome',
        ];

        foreach (['approval_status', 'document_status', 'tipo'] as $column) {
            if (CachedSchema::hasColumn('item_controles', $column)) {
                $select[] = 'item_controles.'.$column;
            }
        }

        return DB::table('item_controles')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
            ->select($select);
    }

    private static function assinaturas(int $limit): Collection
    {
        if (! CachedSchema::hasTable('item_controle_assinaturas')) {
            return collect();
        }

        return DB::table('item_controle_assinaturas')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_assinaturas.item_controle_id')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controle_assinaturas.empresa_id')
            ->select([
                'item_controle_assinaturas.id',
                'item_controle_assinaturas.nome',
                'item_controle_assinaturas.email',
                'item_controle_assinaturas.assinado_em',
                'item_controle_assinaturas.created_at',
                'item_controles.titulo',
                'item_controles.status',
                'item_controles.data_vencimento',
                'empresas.razao_social',
                'empresas.nome_fantasia',
            ])
            ->whereNull('item_controle_assinaturas.assinado_em')
            ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.data_vencimento')
            ->limit($limit)
            ->get()
            ->map(function ($row): array {
                $data = ! empty($row->data_vencimento) ? Carbon::parse($row->data_vencimento) : null;

                return [
                    'relatorio' => 'Assinatura',
                    'cliente' => self::cliente($row),
                    'titulo' => $row->titulo ?: 'Assinatura sem item vinculado',
                    'status' => 'Pendente',
                    'responsavel' => $row->nome ?: 'Signatário não informado',
                    'vencimento' => $data?->format('d/m/Y') ?: '-',
                    'dias' => $data ? now()->startOfDay()->diffInDays($data->copy()->startOfDay(), false) : '-',
                    'prioridade' => 'media',
                    'indicador' => $row->email ?: 'Sem e-mail',
                    'indicador_numero' => 1,
                    'observacao' => 'Assinatura criada em '.optional($row->created_at ? Carbon::parse($row->created_at) : null)->format('d/m/Y H:i'),
                ];
            });
    }

    private static function produtividade(int $limit): Collection
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return collect();
        }

        return DB::table('item_controles')
            ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
            ->selectRaw('COALESCE(responsaveis.nome, "Sem responsável") as responsavel_nome')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN item_controles.status IN ("concluido", "concluído", "finalizado") THEN 1 ELSE 0 END) as concluidos')
            ->selectRaw('SUM(CASE WHEN item_controles.data_vencimento IS NOT NULL AND item_controles.data_vencimento < ? AND item_controles.status NOT IN ("concluido", "concluído", "cancelado", "finalizado") THEN 1 ELSE 0 END) as atrasados', [now()->toDateString()])
            ->groupBy('responsavel_nome')
            ->orderByDesc('atrasados')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($row): array {
                $total = (int) $row->total;
                $concluidos = (int) $row->concluidos;
                $atrasados = (int) $row->atrasados;
                $taxa = $total > 0 ? round(($concluidos / $total) * 100) : 0;

                return [
                    'relatorio' => 'Produtividade',
                    'cliente' => '-',
                    'titulo' => $row->responsavel_nome,
                    'status' => $taxa.'% concluído',
                    'responsavel' => $row->responsavel_nome,
                    'vencimento' => '-',
                    'dias' => '-',
                    'prioridade' => $atrasados > 0 ? 'alta' : 'media',
                    'indicador' => $total.' itens · '.$concluidos.' concluídos · '.$atrasados.' atrasados',
                    'indicador_numero' => $atrasados,
                    'observacao' => 'Taxa calculada a partir dos itens vinculados ao responsável.',
                ];
            });
    }

    private static function porCliente(int $limit): Collection
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return collect();
        }

        return DB::table('item_controles')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->selectRaw('COALESCE(empresas.nome_fantasia, empresas.razao_social, "Sem cliente") as cliente_nome')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN item_controles.status IN ("concluido", "concluído", "finalizado") THEN 1 ELSE 0 END) as concluidos')
            ->selectRaw('SUM(CASE WHEN item_controles.data_vencimento IS NOT NULL AND item_controles.data_vencimento < ? AND item_controles.status NOT IN ("concluido", "concluído", "cancelado", "finalizado") THEN 1 ELSE 0 END) as atrasados', [now()->toDateString()])
            ->groupBy('cliente_nome')
            ->orderByDesc('atrasados')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($row): array {
                $total = (int) $row->total;
                $atrasados = (int) $row->atrasados;
                $concluidos = (int) $row->concluidos;

                return [
                    'relatorio' => 'Por cliente',
                    'cliente' => $row->cliente_nome,
                    'titulo' => 'Resumo operacional do cliente',
                    'status' => $atrasados > 0 ? 'Atenção' : 'Em dia',
                    'responsavel' => '-',
                    'vencimento' => '-',
                    'dias' => '-',
                    'prioridade' => $atrasados > 0 ? 'alta' : 'media',
                    'indicador' => $total.' itens · '.$concluidos.' concluídos · '.$atrasados.' atrasados',
                    'indicador_numero' => $atrasados,
                    'observacao' => 'Visão consolidada por empresa/cliente.',
                ];
            });
    }

    private static function formatarItem(array $row, string $relatorio): array
    {
        $data = ! empty($row['data_vencimento']) ? Carbon::parse($row['data_vencimento']) : null;
        $dias = $data ? now()->startOfDay()->diffInDays($data->copy()->startOfDay(), false) : null;

        return [
            'relatorio' => $relatorio,
            'cliente' => self::cliente((object) $row),
            'titulo' => $row['titulo'] ?? 'Sem título',
            'status' => self::label($row['status'] ?? 'pendente'),
            'responsavel' => $row['responsavel_nome'] ?? 'Sem responsável',
            'vencimento' => $data?->format('d/m/Y') ?: '-',
            'dias' => $dias ?? '-',
            'prioridade' => $row['prioridade'] ?? 'media',
            'indicador' => self::indicadorItem($row, $dias),
            'indicador_numero' => is_numeric($dias) ? abs((int) $dias) : 0,
            'observacao' => self::observacaoItem($row),
        ];
    }

    private static function indicadorItem(array $row, ?int $dias): string
    {
        if ($dias === null) {
            return 'Sem prazo definido';
        }

        if ($dias < 0) {
            return abs($dias).' dia(s) em atraso';
        }

        if ($dias === 0) {
            return 'Vence hoje';
        }

        return 'Vence em '.$dias.' dia(s)';
    }

    private static function observacaoItem(array $row): string
    {
        $parts = [];

        foreach (['tipo' => 'Tipo', 'approval_status' => 'Aprovação', 'document_status' => 'Documento'] as $key => $label) {
            if (filled($row[$key] ?? null)) {
                $parts[] = $label.': '.self::label((string) $row[$key]);
            }
        }

        return $parts ? implode(' · ', $parts) : 'Sem observação adicional.';
    }

    private static function cliente(object $row): string
    {
        return $row->nome_fantasia ?: ($row->razao_social ?: 'Sem cliente');
    }

    private static function label(?string $value): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', (string) $value));
    }

    private static function statusFinalizados(): array
    {
        return ['concluido', 'concluído', 'cancelado', 'finalizado', 'encerrado'];
    }

    private static function zeros(): array
    {
        return [
            ['label' => 'Vencidos', 'value' => 0, 'hint' => 'Tabela item_controles não encontrada', 'tone' => 'muted'],
            ['label' => 'Vencem em 30 dias', 'value' => 0, 'hint' => 'Tabela item_controles não encontrada', 'tone' => 'muted'],
            ['label' => 'Aprovações pendentes', 'value' => 0, 'hint' => 'Tabela item_controles não encontrada', 'tone' => 'muted'],
            ['label' => 'Assinaturas pendentes', 'value' => 0, 'hint' => 'Tabela item_controles não encontrada', 'tone' => 'muted'],
        ];
    }
}
