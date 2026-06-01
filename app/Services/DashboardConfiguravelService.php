<?php

namespace App\Services;


use App\Support\CachedSchema;
use App\Models\Comentario;
use App\Models\DashboardWidgetConfiguracao;
use App\Models\ItemControle;
use App\Models\ItemControleComentario;
use App\Models\Responsavel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardConfiguravelService
{
    public function valor(DashboardWidgetConfiguracao $widget): int|float|string
    {
        $cacheKey = 'dashboard_widget_valor_' . $widget->id . '_' . md5((string) $widget->updated_at);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($widget): int|float|string {
            if ($widget->fonte === 'valor_em_aberto') {
                return 'R$ ' . number_format((float) $this->queryValorEmAberto($widget)->sum($this->valorColumn()), 2, ',', '.');
            }

            if ($widget->fonte === 'comentarios_atribuidos') {
                return $this->countComentariosAtribuidos($widget);
            }

            return $this->aplicarFiltroFonte($this->queryBase($widget), $widget)->count();
        });
    }

    public function dadosTabela(DashboardWidgetConfiguracao $widget): Collection
    {
        $cacheKey = 'dashboard_widget_tabela_' . $widget->id . '_' . md5((string) $widget->updated_at);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($widget): Collection {
            if ($widget->fonte === 'comentarios_atribuidos') {
                return $this->comentariosAtribuidos($widget);
            }

            return $this->aplicarFiltroFonte($this->queryBase($widget), $widget)
                ->select($this->safeTableColumns())
                ->orderByRaw('CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END')
                ->orderBy('data_vencimento')
                ->orderByDesc('id')
                ->limit(8)
                ->get()
                ->map(fn (ItemControle $item): array => [
                    'id' => $item->id,
                    'titulo' => $item->titulo ?: '-',
                    'status' => $this->labelCampo($item->status),
                    'tipo' => $this->labelCampo($item->tipo),
                    'data_vencimento' => $item->data_vencimento?->format('d/m/Y') ?: '-',
                ]);
        });
    }

    public function dadosGrafico(DashboardWidgetConfiguracao $widget): Collection
    {
        $cacheKey = 'dashboard_widget_grafico_' . $widget->id . '_' . md5((string) $widget->updated_at);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($widget): Collection {
            if ($widget->fonte === 'carga_responsavel') {
                return $this->graficoCargaResponsavel($widget);
            }

            $campoAgrupamento = $this->campoAgrupamentoGrafico($widget);

            return $this->aplicarFiltroFonte($this->queryBase($widget), $widget)
                ->select([
                    DB::raw("COALESCE({$campoAgrupamento}, 'sem_informacao') as label"),
                    DB::raw('COUNT(id) as valor'),
                ])
                ->groupBy(DB::raw("COALESCE({$campoAgrupamento}, 'sem_informacao')"))
                ->orderByDesc('valor')
                ->limit(8)
                ->get()
                ->map(fn ($linha): array => [
                    'label' => $this->labelCampo((string) $linha->label),
                    'valor' => (int) $linha->valor,
                ]);
        });
    }

    protected function queryBase(DashboardWidgetConfiguracao $widget): Builder
    {
        return ItemControle::query()->where('empresa_id', $widget->empresa_id);
    }

    protected function aplicarFiltroFonte(Builder $query, DashboardWidgetConfiguracao $widget): Builder
    {
        return match ($widget->fonte) {
            'itens_abertos' => $query->whereNotIn('status', ['concluido', 'aprovado', 'cancelado']),
            'itens_vencidos' => $query->whereDate('data_vencimento', '<', now()->toDateString())->whereNotIn('status', ['concluido', 'aprovado', 'cancelado']),
            'vencendo_hoje' => $query->whereDate('data_vencimento', now()->toDateString())->whereNotIn('status', ['concluido', 'aprovado', 'cancelado']),
            'aprovacoes_pendentes' => $query->whereIn('status', ['aguardando_aprovacao', 'em_aprovacao']),
            'bloqueados' => $this->aplicarFiltroBloqueados($query),
            'valor_em_aberto', 'pendente_cobranca' => $this->queryValorEmAberto($widget, $query),
            'sla_vencido' => $query->where('sla_status', 'vencido'),
            'contratos_ativos' => $query->where('tipo', 'contrato')->where('contrato_status', 'ativo'),
            'total_itens', 'carga_responsavel', 'status_gargalo' => $query,
            default => $query,
        };
    }

    protected function queryValorEmAberto(DashboardWidgetConfiguracao $widget, ?Builder $query = null): Builder
    {
        $query ??= $this->queryBase($widget);

        $query->whereIn('status', ['concluido', 'aprovado', 'assinado']);

        if ($this->hasColumn('faturado_em')) {
            $query->whereNull('faturado_em');
        } elseif ($this->hasColumn('contrato_status')) {
            $query->where(function (Builder $builder): void {
                $builder->whereNull('contrato_status')
                    ->orWhereNotIn('contrato_status', ['faturado', 'pago']);
            });
        }

        if ($this->hasColumn('pago_em')) {
            $query->whereNull('pago_em');
        }

        return $query;
    }

    protected function aplicarFiltroBloqueados(Builder $query): Builder
    {
        $columns = array_values(array_filter([
            'bloqueado',
            'blocked_by_dependency',
            'bloqueado_por_dependencia',
        ], fn (string $column): bool => $this->hasColumn($column)));

        if (empty($columns)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $builder) use ($columns): void {
            foreach ($columns as $column) {
                $builder->orWhere($column, true);
            }
        });
    }

    protected function graficoCargaResponsavel(DashboardWidgetConfiguracao $widget): Collection
    {
        $rows = DB::table('item_controles')
            ->select([
                'responsavel_id',
                DB::raw('COUNT(id) as valor'),
            ])
            ->where('empresa_id', $widget->empresa_id)
            ->whereNotNull('responsavel_id')
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->groupBy('responsavel_id')
            ->orderByDesc('valor')
            ->limit(8)
            ->get();

        $responsaveis = Responsavel::query()
            ->whereIn('id', $rows->pluck('responsavel_id')->filter()->values())
            ->pluck('nome', 'id');

        return $rows->map(fn ($row): array => [
            'label' => $responsaveis[$row->responsavel_id] ?? 'Sem responsável',
            'valor' => (int) $row->valor,
        ]);
    }

    protected function comentariosAtribuidos(DashboardWidgetConfiguracao $widget): Collection
    {
        if (! $widget->user) {
            return collect();
        }

        $tokens = $this->mentionTokens($widget);

        if (empty($tokens)) {
            return collect();
        }

        $comentarios = collect();

        if (CachedSchema::hasTable('item_controle_comentarios')) {
            $comentarios = $comentarios->merge($this->comentarioQuery(ItemControleComentario::query(), $widget, $tokens)->get());
        }

        if (CachedSchema::hasTable('comentarios')) {
            $comentarios = $comentarios->merge($this->comentarioQuery(Comentario::query(), $widget, $tokens)->get());
        }

        return $comentarios
            ->sortByDesc('created_at')
            ->take(8)
            ->map(fn ($comentario): array => [
                'id' => $comentario->id,
                'titulo' => $comentario->itemControle?->titulo ?: 'Comentário atribuído',
                'status' => $comentario->user?->name ?: 'Usuário',
                'tipo' => 'Comentário',
                'data_vencimento' => $comentario->created_at?->format('d/m/Y') ?: '-',
            ])
            ->values();
    }

    protected function countComentariosAtribuidos(DashboardWidgetConfiguracao $widget): int
    {
        return $this->comentariosAtribuidos($widget)->count();
    }

    protected function comentarioQuery(Builder $query, DashboardWidgetConfiguracao $widget, array $tokens): Builder
    {
        return $query
            ->with(['user:id,name,email', 'itemControle:id,empresa_id,titulo,status,tipo,data_vencimento'])
            ->whereHas('itemControle', fn (Builder $builder): Builder => $builder->where('empresa_id', $widget->empresa_id))
            ->where(function (Builder $builder) use ($tokens): void {
                foreach ($tokens as $token) {
                    $builder->orWhere('comentario', 'like', '%' . $token . '%');
                }
            })
            ->latest('created_at')
            ->limit(8);
    }

    protected function mentionTokens(DashboardWidgetConfiguracao $widget): array
    {
        $user = $widget->user;

        if (! $user) {
            return [];
        }

        return array_values(array_filter(array_unique([
            '@' . trim((string) $user->name),
            '@' . trim((string) $user->email),
            $user->responsavel?->nome ? '@' . trim((string) $user->responsavel->nome) : null,
        ])));
    }

    protected function campoAgrupamentoGrafico(DashboardWidgetConfiguracao $widget): string
    {
        return match ($widget->fonte) {
            'sla_vencido' => 'sla_status',
            'contratos_ativos' => 'contrato_status',
            default => 'status',
        };
    }

    protected function safeTableColumns(): array
    {
        return array_values(array_filter([
            'id',
            'titulo',
            'status',
            CachedSchema::hasColumn('item_controles', 'tipo') ? 'tipo' : null,
            'data_vencimento',
        ]));
    }

    protected function valorColumn(): string
    {
        return $this->hasColumn('valor_tarefa') ? 'valor_tarefa' : 'contrato_valor';
    }

    protected function hasColumn(string $column): bool
    {
        static $cache = [];

        return $cache[$column] ??= CachedSchema::hasColumn('item_controles', $column);
    }

    protected function labelCampo(?string $valor): string
    {
        if (blank($valor)) {
            return 'Sem informação';
        }

        return ucfirst(str_replace('_', ' ', (string) $valor));
    }
}
