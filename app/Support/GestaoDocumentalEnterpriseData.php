<?php

namespace App\Support;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GestaoDocumentalEnterpriseData
{
    public function dados(?User $user, array $filtros = []): array
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return $this->vazio($filtros);
        }

        $query = $this->baseQuery($user);
        $this->aplicarFiltros($query, $filtros);
        $this->aplicarOrdenacao($query, (string) ($filtros['ordenacao'] ?? 'prioridade'));

        $documentos = $query->limit(100)->get();
        $documentosFormatados = $documentos->map(fn (ItemControle $documento): array => $this->formatarDocumento($documento));

        return [
            'filtros' => $filtros,
            'opcoes' => $this->opcoes($user),
            'resumo' => $this->resumo($user),
            'documentos' => $documentosFormatados,
            'porPrioridade' => $this->porPrioridade($documentosFormatados),
            'porEmpresa' => $documentosFormatados->groupBy('empresa_nome')->map(fn (Collection $items): array => [
                'empresa' => $items->first()['empresa_nome'],
                'total' => $items->count(),
                'criticos' => $items->whereIn('tom', ['danger', 'warning'])->count(),
                'score' => $this->scoreColecao($items),
                'semResponsavel' => $items->where('sem_responsavel', true)->count(),
                'itens' => $items->take(4)->values(),
            ])->sortBy('score')->values(),
            'semResponsavel' => $documentosFormatados->where('sem_responsavel', true)->values(),
            'vencimentos' => $documentosFormatados->whereIn('situacao_chave', ['vencido', 'vence_hoje', 'vence_7', 'vence_30'])->values(),
            'acaoRapida' => $documentosFormatados->sortByDesc('peso_prioridade')->take(8)->values(),
            'scoreGeral' => $this->scoreColecao($documentosFormatados),
        ];
    }

    private function vazio(array $filtros): array
    {
        return [
            'filtros' => $filtros,
            'opcoes' => ['empresas' => [], 'responsaveis' => [], 'status' => [], 'situacoes' => $this->situacoes()],
            'resumo' => [
                'total' => 0,
                'vencidos' => 0,
                'vencem7' => 0,
                'vencem30' => 0,
                'semResponsavel' => 0,
                'semArquivo' => 0,
                'assinados' => 0,
                'aprovacaoPendente' => 0,
                'comVersao' => 0,
                'portalAtivo' => 0,
            ],
            'documentos' => collect(),
            'porEmpresa' => collect(),
            'porPrioridade' => collect(),
            'semResponsavel' => collect(),
            'vencimentos' => collect(),
            'acaoRapida' => collect(),
            'scoreGeral' => 100,
        ];
    }

    private function baseQuery(?User $user): Builder
    {
        $query = ItemControle::query()->visibleForUser($user);

        $with = [];
        $withCount = [];

        if (CachedSchema::hasTable('empresas')) {
            $with[] = 'empresa:id,razao_social,nome_fantasia';
        }

        if (CachedSchema::hasTable('responsaveis')) {
            $with[] = 'responsavel:id,nome,email,empresa_id';
        }

        if (CachedSchema::hasTable('item_controle_anexos')) {
            $withCount[] = 'anexos';
        }

        if (CachedSchema::hasTable('item_controle_assinaturas')) {
            $with[] = 'ultimaAssinatura';
            $withCount[] = 'assinaturas';
        }

        if (CachedSchema::hasTable('item_controle_aprovacoes')) {
            $with[] = 'ultimaAprovacao';
            $withCount[] = 'aprovacoes';
        }

        if (CachedSchema::hasTable('prazzu_document_versions')) {
            $with[] = 'documentVersions';
            $withCount[] = 'documentVersions';
        }

        if (CachedSchema::hasTable('item_controle_timeline')) {
            $with[] = 'timelines';
        }

        if ($with !== []) {
            $query->with($with);
        }

        if ($withCount !== []) {
            $query->withCount($withCount);
        }

        return $query;
    }

    private function aplicarFiltros(Builder $query, array $filtros): void
    {
        $busca = trim((string) ($filtros['busca'] ?? ''));
        $empresaId = $filtros['empresa_id'] ?? null;
        $responsavelId = $filtros['responsavel_id'] ?? null;
        $status = $filtros['status'] ?? null;
        $situacao = $filtros['situacao'] ?? null;
        $prioridade = $filtros['prioridade'] ?? null;
        $tipo = $filtros['tipo'] ?? null;

        if ($busca !== '') {
            $query->where(function (Builder $subQuery) use ($busca): void {
                foreach (['titulo', 'descricao', 'tipo', 'status', 'prioridade'] as $coluna) {
                    if (CachedSchema::hasColumn('item_controles', $coluna)) {
                        $subQuery->orWhere($coluna, 'like', "%{$busca}%");
                    }
                }

                foreach (['contrato_numero', 'contrato_parte_nome', 'portal_cliente_nome', 'portal_cliente_email', 'document_status', 'approval_status'] as $coluna) {
                    if (CachedSchema::hasColumn('item_controles', $coluna)) {
                        $subQuery->orWhere($coluna, 'like', "%{$busca}%");
                    }
                }
            });
        }

        if (filled($empresaId) && CachedSchema::hasColumn('item_controles', 'empresa_id')) {
            $query->where('empresa_id', $empresaId);
        }

        if (filled($responsavelId) && CachedSchema::hasColumn('item_controles', 'responsavel_id')) {
            $query->where('responsavel_id', $responsavelId);
        }

        if (filled($status) && CachedSchema::hasColumn('item_controles', 'status')) {
            $query->where('status', $status);
        }

        if (filled($prioridade) && CachedSchema::hasColumn('item_controles', 'prioridade')) {
            $query->where('prioridade', $prioridade);
        }

        if (filled($tipo) && CachedSchema::hasColumn('item_controles', 'tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($situacao === 'vencido') {
            $query->whereNotNull('data_vencimento')
                ->whereDate('data_vencimento', '<', now()->toDateString())
                ->whereNotIn('status', $this->statusFinalizados());
        }

        if ($situacao === 'vence_7') {
            $query->whereBetween('data_vencimento', [now()->toDateString(), now()->addDays(7)->toDateString()]);
        }

        if ($situacao === 'vence_30') {
            $query->whereBetween('data_vencimento', [now()->toDateString(), now()->addDays(30)->toDateString()]);
        }

        if ($situacao === 'sem_responsavel' && CachedSchema::hasColumn('item_controles', 'responsavel_id')) {
            $query->whereNull('responsavel_id');
        }

        if ($situacao === 'sem_arquivo' && CachedSchema::hasColumn('item_controles', 'arquivo')) {
            $query->where(function (Builder $subQuery): void {
                $subQuery->whereNull('arquivo')->orWhere('arquivo', '');
            });
        }

        if ($situacao === 'aprovacao_pendente' && CachedSchema::hasTable('item_controle_aprovacoes')) {
            $query->whereHas('aprovacoes', fn (Builder $aprovacoes): Builder => $aprovacoes->where('status', 'pendente'));
        }

        if ($situacao === 'assinatura_pendente' && CachedSchema::hasTable('item_controle_assinaturas')) {
            $query->whereDoesntHave('assinaturas');
        }

        if ($situacao === 'sem_versao' && CachedSchema::hasTable('prazzu_document_versions')) {
            $query->whereDoesntHave('documentVersions');
        }
    }

    private function aplicarOrdenacao(Builder $query, string $ordenacao): void
    {
        if ($ordenacao === 'atualizacao') {
            $query->orderByDesc('updated_at');
            return;
        }

        if ($ordenacao === 'empresa' && CachedSchema::hasColumn('item_controles', 'empresa_id')) {
            $query->orderBy('empresa_id')->orderByRaw('CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END')->orderBy('data_vencimento');
            return;
        }

        if ($ordenacao === 'vencimento') {
            $query->orderByRaw('CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END')->orderBy('data_vencimento')->orderByDesc('updated_at');
            return;
        }

        $query
            ->orderByRaw("CASE WHEN data_vencimento IS NOT NULL AND data_vencimento < ? AND status NOT IN ('concluido','concluído','finalizado','cancelado','aprovado') THEN 0 ELSE 1 END", [now()->toDateString()])
            ->orderByRaw("CASE prioridade WHEN 'urgente' THEN 0 WHEN 'critica' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 WHEN 'baixa' THEN 4 ELSE 5 END")
            ->orderByRaw('CASE WHEN data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('data_vencimento')
            ->orderByDesc('updated_at');
    }

    private function resumo(?User $user): array
    {
        $query = ItemControle::query()->visibleForUser($user);

        $total = (clone $query)->count();
        $vencidos = (clone $query)->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<', now()->toDateString())->whereNotIn('status', $this->statusFinalizados())->count();
        $vencem7 = (clone $query)->whereBetween('data_vencimento', [now()->toDateString(), now()->addDays(7)->toDateString()])->count();
        $vencem30 = (clone $query)->whereBetween('data_vencimento', [now()->toDateString(), now()->addDays(30)->toDateString()])->count();
        $semResponsavel = CachedSchema::hasColumn('item_controles', 'responsavel_id') ? (clone $query)->whereNull('responsavel_id')->count() : 0;
        $semArquivo = CachedSchema::hasColumn('item_controles', 'arquivo') ? (clone $query)->where(function (Builder $subQuery): void {
            $subQuery->whereNull('arquivo')->orWhere('arquivo', '');
        })->count() : 0;

        return [
            'total' => $total,
            'vencidos' => $vencidos,
            'vencem7' => $vencem7,
            'vencem30' => $vencem30,
            'semResponsavel' => $semResponsavel,
            'semArquivo' => $semArquivo,
            'assinados' => CachedSchema::hasTable('item_controle_assinaturas') ? $this->countDistinct('item_controle_assinaturas', 'item_controle_id', $user) : 0,
            'aprovacaoPendente' => CachedSchema::hasTable('item_controle_aprovacoes') ? $this->countAprovacoesPendentes($user) : 0,
            'comVersao' => CachedSchema::hasTable('prazzu_document_versions') ? $this->countDistinct('prazzu_document_versions', 'item_controle_id', $user) : 0,
            'portalAtivo' => CachedSchema::hasColumn('item_controles', 'portal_ativo') ? (clone $query)->where('portal_ativo', true)->count() : 0,
        ];
    }

    private function countDistinct(string $table, string $column, ?User $user): int
    {
        if (! CachedSchema::hasTable($table) || ! CachedSchema::hasColumn($table, $column)) {
            return 0;
        }

        $visibleItems = ItemControle::query()
            ->visibleForUser($user)
            ->select('item_controles.id');

        return DB::table($table)
            ->whereIn($column, $visibleItems)
            ->whereNotNull($column)
            ->distinct()
            ->count($column);
    }

    private function countAprovacoesPendentes(?User $user): int
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes') || ! CachedSchema::hasColumn('item_controle_aprovacoes', 'item_controle_id')) {
            return 0;
        }

        $visibleItems = ItemControle::query()
            ->visibleForUser($user)
            ->select('item_controles.id');

        $query = DB::table('item_controle_aprovacoes')
            ->whereIn('item_controle_id', $visibleItems);

        if (CachedSchema::hasColumn('item_controle_aprovacoes', 'status')) {
            $query->where('status', 'pendente');
        }

        return $query->count();
    }

    private function opcoes(?User $user): array
    {
        $base = ItemControle::query()->visibleForUser($user);

        return [
            'empresas' => CachedSchema::hasTable('empresas') && CachedSchema::hasColumn('item_controles', 'empresa_id')
                ? (clone $base)->join('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
                    ->select('empresas.id', DB::raw("COALESCE(NULLIF(empresas.nome_fantasia, ''), empresas.razao_social) as nome"))
                    ->distinct()->orderBy('nome')->limit(100)->pluck('nome', 'id')->toArray()
                : [],
            'responsaveis' => CachedSchema::hasTable('responsaveis') && CachedSchema::hasColumn('item_controles', 'responsavel_id')
                ? (clone $base)->join('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
                    ->select('responsaveis.id', 'responsaveis.nome')->distinct()->orderBy('responsaveis.nome')->limit(100)->pluck('responsaveis.nome', 'responsaveis.id')->toArray()
                : [],
            'status' => CachedSchema::hasColumn('item_controles', 'status')
                ? (clone $base)->whereNotNull('status')->distinct()->orderBy('status')->pluck('status')->mapWithKeys(fn ($status): array => [$status => $this->label($status)])->toArray()
                : [],
            'prioridades' => CachedSchema::hasColumn('item_controles', 'prioridade')
                ? (clone $base)->whereNotNull('prioridade')->distinct()->pluck('prioridade')->sortBy(fn ($prioridade) => $this->ordemPrioridade((string) $prioridade))->mapWithKeys(fn ($prioridade): array => [$prioridade => $this->label($prioridade)])->toArray()
                : [],
            'tipos' => CachedSchema::hasColumn('item_controles', 'tipo')
                ? (clone $base)->whereNotNull('tipo')->distinct()->orderBy('tipo')->pluck('tipo')->mapWithKeys(fn ($tipo): array => [$tipo => $this->label($tipo)])->toArray()
                : [],
            'situacoes' => $this->situacoes(),
            'ordenacoes' => [
                'prioridade' => 'Prioridade operacional',
                'vencimento' => 'Vencimento mais próximo',
                'atualizacao' => 'Última atualização',
                'empresa' => 'Agrupar por empresa',
            ],
        ];
    }


    private function porPrioridade(Collection $documentos): Collection
    {
        $ordem = ['Urgente', 'Critica', 'Crítica', 'Alta', 'Media', 'Média', 'Baixa', '-'];

        return $documentos
            ->groupBy('prioridade')
            ->map(fn (Collection $items, string $prioridade): array => [
                'prioridade' => $prioridade,
                'total' => $items->count(),
                'criticos' => $items->whereIn('tom', ['danger', 'warning'])->count(),
                'sem_anexo' => $items->where('tem_arquivo', false)->count(),
                'sem_responsavel' => $items->where('sem_responsavel', true)->count(),
                'itens' => $items->sortByDesc('peso_prioridade')->take(5)->values(),
            ])
            ->sortBy(fn (array $grupo): int => array_search($grupo['prioridade'], $ordem, true) === false ? 99 : (int) array_search($grupo['prioridade'], $ordem, true))
            ->values();
    }

    private function ordemPrioridade(string $prioridade): int
    {
        return match ($prioridade) {
            'urgente' => 0,
            'critica' => 1,
            'alta' => 2,
            'media' => 3,
            'baixa' => 4,
            default => 5,
        };
    }

    private function situacoes(): array
    {
        return [
            'vencido' => 'Vencidos',
            'vence_7' => 'Vencem em 7 dias',
            'vence_30' => 'Vencem em 30 dias',
            'sem_responsavel' => 'Sem responsável',
            'sem_arquivo' => 'Sem anexo principal',
            'aprovacao_pendente' => 'Aprovação pendente',
            'assinatura_pendente' => 'Assinatura pendente',
            'sem_versao' => 'Sem versionamento',
        ];
    }

    private function formatarDocumento(ItemControle $documento): array
    {
        $vencimento = $documento->data_vencimento ? Carbon::parse($documento->data_vencimento) : null;
        $dias = $vencimento ? now()->startOfDay()->diffInDays($vencimento->copy()->startOfDay(), false) : null;
        $finalizado = in_array((string) $documento->status, $this->statusFinalizados(), true);
        $vencido = $vencimento && $vencimento->isPast() && ! $vencimento->isToday() && ! $finalizado;
        $venceHoje = $vencimento && $vencimento->isToday() && ! $finalizado;
        $vence7 = $dias !== null && $dias >= 0 && $dias <= 7 && ! $finalizado;
        $vence30 = $dias !== null && $dias >= 0 && $dias <= 30 && ! $finalizado;

        $anexosRelacionados = (int) ($documento->anexos_count ?? 0);
        $temArquivoPrincipal = filled($this->coluna($documento, 'arquivo'));
        $anexosCount = $anexosRelacionados + ($temArquivoPrincipal ? 1 : 0);
        $assinaturasCount = (int) ($documento->assinaturas_count ?? 0);
        $aprovacoesCount = (int) ($documento->aprovacoes_count ?? 0);
        $versoesCount = (int) ($documento->document_versions_count ?? 0);

        $ultimaVersao = $documento->relationLoaded('documentVersions') ? $documento->documentVersions->first() : null;
        $aprovacaoStatus = $documento->relationLoaded('ultimaAprovacao') && $documento->ultimaAprovacao
            ? $documento->ultimaAprovacao->getStatusExibicao()
            : ($this->coluna($documento, 'approval_status') ? $this->label($this->coluna($documento, 'approval_status')) : 'Sem aprovação');
        $assinaturaStatus = $assinaturasCount > 0 || ($documento->relationLoaded('ultimaAssinatura') && $documento->ultimaAssinatura) ? 'Assinado' : 'Pendente';

        $statusDocumental = $this->statusDocumental($documento, $vencido, $venceHoje, $anexosCount, $aprovacaoStatus, $assinaturaStatus);
        $tom = $this->tom($statusDocumental['chave']);
        $empresaNome = $documento->empresa?->nome_fantasia ?: ($documento->empresa?->razao_social ?: 'Sem empresa');
        $arquivoUrl = $temArquivoPrincipal ? asset('storage/' . ltrim((string) $documento->arquivo, '/')) : null;
        $pendencias = $this->pendencias($documento, $vencido, $venceHoje, $vence7, $anexosCount, $assinaturaStatus, $aprovacaoStatus, $versoesCount);
        $peso = $this->pesoPrioridade($documento, $vencido, $venceHoje, $vence7, $anexosCount, $assinaturaStatus, $aprovacaoStatus, $versoesCount);

        return [
            'id' => $documento->id,
            'titulo' => $documento->titulo ?: 'Documento sem título',
            'descricao' => Str::limit((string) ($documento->descricao ?: 'Sem descrição cadastrada.'), 140),
            'tipo' => $this->label($documento->tipo ?: 'documento'),
            'empresa_nome' => $empresaNome,
            'responsavel_nome' => $documento->responsavel?->nome ?: 'Sem responsável',
            'responsavel_email' => $documento->responsavel?->email,
            'sem_responsavel' => blank($documento->responsavel_id),
            'status' => $this->label($documento->status ?: 'pendente'),
            'status_documental' => $statusDocumental['label'],
            'status_documental_chave' => $statusDocumental['chave'],
            'tom' => $tom,
            'prioridade' => $this->label($documento->prioridade ?: 'media'),
            'vencimento' => $vencimento?->format('d/m/Y') ?: 'Sem vencimento',
            'dias' => $dias,
            'situacao_prazo' => $this->situacaoPrazo($vencimento, $dias, $finalizado),
            'situacao_chave' => $vencido ? 'vencido' : ($venceHoje ? 'vence_hoje' : ($vence7 ? 'vence_7' : ($vence30 ? 'vence_30' : 'normal'))),
            'arquivo_url' => $arquivoUrl,
            'tem_arquivo' => $anexosCount > 0,
            'anexos_count' => $anexosCount,
            'assinatura' => $assinaturaStatus,
            'assinaturas_count' => $assinaturasCount,
            'aprovacao' => $aprovacaoStatus,
            'aprovacoes_count' => $aprovacoesCount,
            'versao' => $ultimaVersao ? ('v' . $ultimaVersao->version_number) : ($versoesCount > 0 ? $versoesCount . ' versões' : 'Sem versão'),
            'versoes_count' => $versoesCount,
            'document_status' => $this->label($this->coluna($documento, 'document_status') ?: ''),
            'portal' => (bool) ($this->coluna($documento, 'portal_ativo') ?? false),
            'cliente_portal' => $this->coluna($documento, 'portal_cliente_nome') ?: $this->coluna($documento, 'portal_cliente_email'),
            'edit_url' => ItemControleResource::getUrl('edit', ['record' => $documento->id]),
            'portal_url' => method_exists($documento, 'getPortalUrl') && filled($this->coluna($documento, 'portal_token')) ? $documento->getPortalUrl() : null,
            'updated_at' => $documento->updated_at?->format('d/m/Y H:i'),
            'pendencias' => $pendencias,
            'pendencias_count' => count($pendencias),
            'peso_prioridade' => $peso,
            'score' => max(0, 100 - ($peso * 8)),
            'timeline' => $this->timeline($documento),
            'workflow' => $this->workflow($anexosCount, $aprovacaoStatus, $assinaturaStatus, $finalizado, $vencido),
        ];
    }

    private function statusDocumental(ItemControle $documento, bool $vencido, bool $venceHoje, int $anexosCount, string $aprovacaoStatus, string $assinaturaStatus): array
    {
        $documentStatus = $this->coluna($documento, 'document_status');

        if (filled($documentStatus)) {
            return ['chave' => (string) $documentStatus, 'label' => $this->label($documentStatus)];
        }

        if ($vencido) {
            return ['chave' => 'vencido', 'label' => 'Vencido'];
        }

        if ($venceHoje) {
            return ['chave' => 'vence_hoje', 'label' => 'Vence hoje'];
        }

        if ($anexosCount === 0) {
            return ['chave' => 'sem_anexo', 'label' => 'Aguardando anexo'];
        }

        if (Str::contains(Str::lower($aprovacaoStatus), ['pendente', 'aguardando'])) {
            return ['chave' => 'aprovacao_pendente', 'label' => 'Em aprovação'];
        }

        if ($assinaturaStatus === 'Pendente') {
            return ['chave' => 'assinatura_pendente', 'label' => 'Aguardando assinatura'];
        }

        if ($assinaturaStatus === 'Assinado') {
            return ['chave' => 'assinado', 'label' => 'Assinado'];
        }

        return ['chave' => 'em_dia', 'label' => 'Em dia'];
    }

    private function pendencias(ItemControle $documento, bool $vencido, bool $venceHoje, bool $vence7, int $anexosCount, string $assinaturaStatus, string $aprovacaoStatus, int $versoesCount): array
    {
        $pendencias = [];

        if ($vencido) {
            $pendencias[] = 'Documento vencido';
        } elseif ($venceHoje) {
            $pendencias[] = 'Vence hoje';
        } elseif ($vence7) {
            $pendencias[] = 'Vence nos próximos 7 dias';
        }

        if (blank($documento->responsavel_id)) {
            $pendencias[] = 'Sem responsável';
        }

        if ($anexosCount === 0) {
            $pendencias[] = 'Sem anexo principal';
        }

        if (Str::contains(Str::lower($aprovacaoStatus), ['pendente', 'aguardando'])) {
            $pendencias[] = 'Aprovação pendente';
        }

        if ($assinaturaStatus === 'Pendente') {
            $pendencias[] = 'Assinatura pendente';
        }

        if (CachedSchema::hasTable('prazzu_document_versions') && $versoesCount === 0) {
            $pendencias[] = 'Sem versionamento';
        }

        return $pendencias;
    }

    private function pesoPrioridade(ItemControle $documento, bool $vencido, bool $venceHoje, bool $vence7, int $anexosCount, string $assinaturaStatus, string $aprovacaoStatus, int $versoesCount): int
    {
        $peso = match ((string) $documento->prioridade) {
            'urgente' => 5,
            'critica' => 4,
            'alta' => 3,
            'media' => 2,
            'baixa' => 1,
            default => 2,
        };

        if ($vencido) {
            $peso += 5;
        } elseif ($venceHoje) {
            $peso += 4;
        } elseif ($vence7) {
            $peso += 3;
        }

        if (blank($documento->responsavel_id)) {
            $peso += 3;
        }

        if ($anexosCount === 0) {
            $peso += 3;
        }

        if (Str::contains(Str::lower($aprovacaoStatus), ['pendente', 'aguardando'])) {
            $peso += 2;
        }

        if ($assinaturaStatus === 'Pendente') {
            $peso += 1;
        }

        if (CachedSchema::hasTable('prazzu_document_versions') && $versoesCount === 0) {
            $peso += 1;
        }

        return $peso;
    }

    private function timeline(ItemControle $documento): array
    {
        if (! $documento->relationLoaded('timelines')) {
            return [];
        }

        return $documento->timelines->take(3)->map(fn ($timeline): array => [
            'tipo' => method_exists($timeline, 'getTipoExibicao') ? $timeline->getTipoExibicao() : $this->label($timeline->tipo),
            'titulo' => $timeline->titulo,
            'descricao' => $timeline->descricao,
            'data' => $timeline->created_at?->format('d/m/Y H:i'),
        ])->values()->toArray();
    }

    private function workflow(int $anexosCount, string $aprovacaoStatus, string $assinaturaStatus, bool $finalizado, bool $vencido): array
    {
        return [
            ['label' => 'Anexo', 'ok' => $anexosCount > 0],
            ['label' => 'Aprovação', 'ok' => ! Str::contains(Str::lower($aprovacaoStatus), ['pendente', 'aguardando']) && $aprovacaoStatus !== 'Sem aprovação'],
            ['label' => 'Assinatura', 'ok' => $assinaturaStatus === 'Assinado'],
            ['label' => 'Vigência', 'ok' => ! $vencido],
            ['label' => 'Conclusão', 'ok' => $finalizado],
        ];
    }

    private function scoreColecao(Collection $documentos): int
    {
        if ($documentos->isEmpty()) {
            return 100;
        }

        return (int) max(0, min(100, round($documentos->avg('score'))));
    }

    private function situacaoPrazo(?Carbon $vencimento, ?int $dias, bool $finalizado): string
    {
        if (! $vencimento) {
            return 'Sem prazo definido';
        }

        if ($finalizado) {
            return 'Finalizado';
        }

        if ($vencimento->isPast() && ! $vencimento->isToday()) {
            return 'Vencido há ' . abs((int) $dias) . ' dia(s)';
        }

        if ($vencimento->isToday()) {
            return 'Vence hoje';
        }

        return 'Vence em ' . max((int) $dias, 0) . ' dia(s)';
    }

    private function tom(string $status): string
    {
        return match ($status) {
            'vencido', 'sem_anexo', 'reprovado' => 'danger',
            'vence_hoje', 'aprovacao_pendente', 'assinatura_pendente', 'em_revisao', 'pendente' => 'warning',
            'assinado', 'aprovado', 'em_dia', 'concluido', 'concluído' => 'success',
            default => 'info',
        };
    }

    private function label(?string $valor): string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return '-';
        }

        return Str::of($valor)->replace(['_', '-'], ' ')->title()->toString();
    }

    private function coluna(ItemControle $documento, string $coluna): mixed
    {
        return CachedSchema::hasColumn('item_controles', $coluna) ? $documento->{$coluna} : null;
    }

    private function statusFinalizados(): array
    {
        return ['concluido', 'concluído', 'finalizado', 'cancelado', 'aprovado'];
    }
}
