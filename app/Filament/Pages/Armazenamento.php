<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Empresas\EmpresaResource;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use App\Support\CachedSchema;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class Armazenamento extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';
    protected static string | UnitEnum | null $navigationGroup = 'Documentos';
    protected static ?string $navigationLabel = 'Armazenamento';
    protected static ?string $title = 'Armazenamento';
    protected static ?int $navigationSort = 2;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected string $view = 'filament.pages.armazenamento';

    public string $aba = 'visao-geral';
    public string $busca = '';
    public string $ordenarPor = 'uso_desc';

    public function mount(): void
    {
        $aba = request()->query('aba');

        if (is_string($aba) && collect($this->abas())->contains(fn (array $item): bool => $item['key'] === $aba)) {
            $this->aba = $aba;
        }
    }

    public function getSubNavigation(): array
    {
        return collect($this->abas())
            ->map(fn (array $item): NavigationItem => NavigationItem::make($item['label'])
                ->icon($item['icon'])
                ->url(static::getUrl(['aba' => $item['key']]))
                ->isActiveWhen(fn (): bool => $this->aba === $item['key'])
                ->sort($item['sort']))
            ->all();
    }

    protected function getViewData(): array
    {
        $arquivos = $this->arquivos();
        $porEmpresa = $this->porEmpresa($arquivos);
        $resumo = $this->resumo($arquivos, $porEmpresa);
        $arquivosPesados = $this->arquivosPesados($arquivos);
        $arquivosExpirados = $this->arquivosExpirados($arquivos);

        return [
            'abas' => $this->abas(),
            'aba' => $this->aba,
            'resumo' => $resumo,
            'porEmpresa' => $porEmpresa,
            'topConsumidores' => array_slice($porEmpresa, 0, 5),
            'arquivosPesados' => $arquivosPesados,
            'arquivosExpirados' => $arquivosExpirados,
            'limites' => $this->limites($porEmpresa),
            'alertas' => $this->alertas($resumo, $porEmpresa, $arquivosPesados, $arquivosExpirados),
            'insights' => $this->insights($resumo, $porEmpresa, $arquivos),
            'temColunaLimite' => CachedSchema::hasTable('empresas') && CachedSchema::hasColumn('empresas', 'limite_armazenamento_mb'),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function abas(): array
    {
        return [
            ['key' => 'visao-geral', 'label' => 'Visão Geral', 'icon' => 'heroicon-o-squares-2x2', 'sort' => 1],
            ['key' => 'por-empresa', 'label' => 'Por Cliente/Empresa', 'icon' => 'heroicon-o-building-office-2', 'sort' => 2],
            ['key' => 'arquivos-pesados', 'label' => 'Arquivos Pesados', 'icon' => 'heroicon-o-scale', 'sort' => 3],
            ['key' => 'expirados', 'label' => 'Expirados', 'icon' => 'heroicon-o-clock', 'sort' => 4],
            ['key' => 'limites', 'label' => 'Limites', 'icon' => 'heroicon-o-adjustments-horizontal', 'sort' => 5],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function arquivos(): array
    {
        $arquivos = collect();

        if (CachedSchema::hasTable('item_controle_anexos')) {
            $query = DB::table('item_controle_anexos as anexos')
                ->leftJoin('item_controles as itens', 'itens.id', '=', 'anexos.item_controle_id')
                ->leftJoin('empresas', 'empresas.id', '=', 'itens.empresa_id')
                ->select([
                    'anexos.id',
                    DB::raw("COALESCE(anexos.nome_original, anexos.arquivo, anexos.caminho, 'Arquivo sem nome') as nome"),
                    DB::raw("COALESCE(anexos.caminho, anexos.arquivo) as caminho"),
                    DB::raw('COALESCE(anexos.tamanho_bytes, 0) as tamanho_bytes'),
                    'anexos.mime_type',
                    'anexos.created_at',
                    'anexos.updated_at',
                    'itens.id as item_id',
                    'itens.titulo as item_titulo',
                    'itens.data_vencimento',
                    'itens.status',
                    'empresas.id as empresa_id',
                    DB::raw("COALESCE(empresas.nome_fantasia, empresas.razao_social, 'Sem empresa') as empresa_nome"),
                    'empresas.plano',
                ]);

            if (CachedSchema::hasTable('item_controles')) {
                $ids = ItemControle::query()->visibleForUser(auth()->user())->pluck('id');
                $query->whereIn('itens.id', $ids);
            }

            $arquivos = $arquivos->merge($query->limit(500)->get()->map(fn ($row): array => $this->normalizarArquivo((array) $row, 'Anexo')));
        }

        if (CachedSchema::hasTable('item_controles') && CachedSchema::hasColumn('item_controles', 'arquivo')) {
            $query = ItemControle::query()
                ->visibleForUser(auth()->user())
                ->select($this->itemControleColumns())
                ->with($this->itemControleRelations())
                ->whereNotNull('arquivo')
                ->where('arquivo', '<>', '')
                ->limit(500)
                ->get();

            $arquivos = $arquivos->merge($query->map(function (ItemControle $item): array {
                $empresa = $item->getRelationValue('empresa');

                return $this->normalizarArquivo([
                    'id' => $item->id,
                    'nome' => basename((string) $item->getAttribute('arquivo')),
                    'caminho' => (string) $item->getAttribute('arquivo'),
                    'tamanho_bytes' => 0,
                    'mime_type' => null,
                    'created_at' => $item->getAttribute('created_at'),
                    'updated_at' => $item->getAttribute('updated_at'),
                    'item_id' => $item->id,
                    'item_titulo' => $item->getAttribute('titulo'),
                    'data_vencimento' => $item->getAttribute('data_vencimento'),
                    'status' => $item->getAttribute('status'),
                    'empresa_id' => $empresa?->id,
                    'empresa_nome' => $empresa?->nome_fantasia ?: ($empresa?->razao_social ?: 'Sem empresa'),
                    'plano' => $empresa?->plano,
                ], 'Documento');
            }));
        }

        if (CachedSchema::hasTable('portal_documentos') && CachedSchema::hasColumn('portal_documentos', 'arquivo')) {
            $query = DB::table('portal_documentos')
                ->leftJoin('empresas', 'empresas.id', '=', 'portal_documentos.empresa_id')
                ->select([
                    'portal_documentos.id',
                    DB::raw("COALESCE(portal_documentos.titulo, portal_documentos.arquivo, 'Documento do portal') as nome"),
                    'portal_documentos.arquivo as caminho',
                    DB::raw('0 as tamanho_bytes'),
                    DB::raw('NULL as mime_type'),
                    'portal_documentos.created_at',
                    'portal_documentos.updated_at',
                    'portal_documentos.item_controle_id as item_id',
                    'portal_documentos.titulo as item_titulo',
                    DB::raw('NULL as data_vencimento'),
                    DB::raw('NULL as status'),
                    'empresas.id as empresa_id',
                    DB::raw("COALESCE(empresas.nome_fantasia, empresas.razao_social, 'Sem empresa') as empresa_nome"),
                    'empresas.plano',
                ])
                ->whereNotNull('portal_documentos.arquivo')
                ->where('portal_documentos.arquivo', '<>', '')
                ->limit(500)
                ->get();

            $arquivos = $arquivos->merge($query->map(fn ($row): array => $this->normalizarArquivo((array) $row, 'Portal')));
        }

        return $arquivos
            ->unique(fn (array $arquivo): string => ($arquivo['origem'] ?? '-') . ':' . ($arquivo['id'] ?? '-') . ':' . ($arquivo['caminho'] ?? '-'))
            ->sortByDesc('tamanho_bytes')
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function normalizarArquivo(array $arquivo, string $origem): array
    {
        $caminho = trim((string) ($arquivo['caminho'] ?? ''));
        $tamanho = (int) ($arquivo['tamanho_bytes'] ?? 0);

        if ($tamanho <= 0 && $caminho !== '') {
            $tamanho = $this->tamanhoRealArquivo($caminho);
        }

        return [
            'id' => $arquivo['id'] ?? null,
            'nome' => (string) ($arquivo['nome'] ?? basename($caminho) ?: 'Arquivo sem nome'),
            'caminho' => $caminho,
            'origem' => $origem,
            'tamanho_bytes' => max(0, $tamanho),
            'tamanho_formatado' => $this->formatBytes(max(0, $tamanho)),
            'mime_type' => $arquivo['mime_type'] ?? null,
            'created_at' => $arquivo['created_at'] ?? null,
            'updated_at' => $arquivo['updated_at'] ?? null,
            'item_id' => $arquivo['item_id'] ?? null,
            'item_titulo' => (string) ($arquivo['item_titulo'] ?? 'Sem item vinculado'),
            'data_vencimento' => $arquivo['data_vencimento'] ?? null,
            'status' => $arquivo['status'] ?? null,
            'empresa_id' => $arquivo['empresa_id'] ?? null,
            'empresa_nome' => (string) ($arquivo['empresa_nome'] ?? 'Sem empresa'),
            'plano' => (string) ($arquivo['plano'] ?? 'sem plano'),
            'expirado' => $this->arquivoExpirado($arquivo),
            'idade_dias' => $this->idadeDias($arquivo['created_at'] ?? $arquivo['updated_at'] ?? null),
        ];
    }

    private function tamanhoRealArquivo(string $caminho): int
    {
        $candidatos = [
            $caminho,
            ltrim($caminho, '/'),
            'public/' . ltrim($caminho, '/'),
            str_replace('storage/', 'public/', ltrim($caminho, '/')),
        ];

        foreach (array_unique($candidatos) as $path) {
            try {
                if (Storage::exists($path)) {
                    return (int) Storage::size($path);
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return 0;
    }

    private function arquivoExpirado(array $arquivo): bool
    {
        $dataVencimento = $arquivo['data_vencimento'] ?? null;
        $status = strtolower((string) ($arquivo['status'] ?? ''));

        if (filled($dataVencimento) && Carbon::parse($dataVencimento)->isPast() && ! in_array($status, ['concluido', 'concluído', 'finalizado', 'aprovado', 'cancelado'], true)) {
            return true;
        }

        return $this->idadeDias($arquivo['created_at'] ?? null) > 365;
    }

    private function idadeDias(mixed $data): int
    {
        if (! filled($data)) {
            return 0;
        }

        try {
            return max(0, (int) now()->diffInDays(Carbon::parse($data)));
        } catch (\Throwable) {
            return 0;
        }
    }

    /** @return array<string, mixed> */
    private function resumo(array $arquivos, array $porEmpresa): array
    {
        $totalBytes = array_sum(array_column($arquivos, 'tamanho_bytes'));
        $totalArquivos = count($arquivos);
        $expirados = count(array_filter($arquivos, fn (array $arquivo): bool => (bool) ($arquivo['expirado'] ?? false)));
        $semTamanho = count(array_filter($arquivos, fn (array $arquivo): bool => (int) ($arquivo['tamanho_bytes'] ?? 0) === 0));
        $maiorArquivo = collect($arquivos)->sortByDesc('tamanho_bytes')->first();
        $totalLimiteBytes = (int) collect($porEmpresa)->sum('limite_bytes');
        $percentualGlobal = $totalLimiteBytes > 0 ? min(999, (int) round(($totalBytes / $totalLimiteBytes) * 100)) : 0;
        $recuperavelBytes = (int) collect($arquivos)
            ->filter(fn (array $arquivo): bool => (bool) ($arquivo['expirado'] ?? false))
            ->sum('tamanho_bytes');

        return [
            'total_arquivos' => $totalArquivos,
            'total_bytes' => $totalBytes,
            'total_formatado' => $this->formatBytes($totalBytes),
            'total_limite_bytes' => $totalLimiteBytes,
            'total_limite_formatado' => $this->formatBytes($totalLimiteBytes),
            'percentual_global' => $percentualGlobal,
            'tom_global' => $percentualGlobal >= 95 ? 'danger' : ($percentualGlobal >= 80 ? 'warning' : 'success'),
            'recuperavel_bytes' => $recuperavelBytes,
            'recuperavel_formatado' => $this->formatBytes($recuperavelBytes),
            'expirados' => $expirados,
            'sem_tamanho' => $semTamanho,
            'maior_arquivo' => $maiorArquivo,
            'empresas' => collect($arquivos)->pluck('empresa_id')->filter()->unique()->count(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function porEmpresa(array $arquivos): array
    {
        return collect($arquivos)
            ->groupBy(fn (array $arquivo): string => (string) ($arquivo['empresa_id'] ?? 'sem_empresa'))
            ->map(function ($grupo): array {
                $primeiro = $grupo->first();
                $totalBytes = (int) $grupo->sum('tamanho_bytes');
                $limiteBytes = $this->limiteEmpresaBytes((int) ($primeiro['empresa_id'] ?? 0), (string) ($primeiro['plano'] ?? ''));
                $percentual = $limiteBytes > 0 ? min(999, (int) round(($totalBytes / $limiteBytes) * 100)) : 0;

                return [
                    'empresa_id' => $primeiro['empresa_id'] ?? null,
                    'empresa_nome' => $primeiro['empresa_nome'] ?? 'Sem empresa',
                    'plano' => $primeiro['plano'] ?? 'sem plano',
                    'arquivos' => $grupo->count(),
                    'total_bytes' => $totalBytes,
                    'total_formatado' => $this->formatBytes($totalBytes),
                    'limite_bytes' => $limiteBytes,
                    'limite_formatado' => $this->formatBytes($limiteBytes),
                    'percentual' => $percentual,
                    'expirados' => $grupo->where('expirado', true)->count(),
                    'maior_arquivo' => $grupo->sortByDesc('tamanho_bytes')->first(),
                    'tom' => $percentual >= 95 ? 'danger' : ($percentual >= 80 ? 'warning' : 'success'),
                ];
            })
            ->sortByDesc('total_bytes')
            ->values()
            ->all();
    }

    private function limiteEmpresaBytes(int $empresaId, string $plano): int
    {
        if ($empresaId > 0 && CachedSchema::hasTable('empresas') && CachedSchema::hasColumn('empresas', 'limite_armazenamento_mb')) {
            $limiteMb = DB::table('empresas')->where('id', $empresaId)->value('limite_armazenamento_mb');
            if ((int) $limiteMb > 0) {
                return (int) $limiteMb * 1024 * 1024;
            }
        }

        $limitesMb = [
            'starter' => 1024,
            'profissional' => 5120,
            'business' => 10240,
            'business_plus' => 20480,
            'enterprise' => 40960,
        ];

        return ($limitesMb[$plano] ?? 5120) * 1024 * 1024;
    }

    /** @return array<int, array<string, mixed>> */
    private function arquivosPesados(array $arquivos): array
    {
        return collect($arquivos)->sortByDesc('tamanho_bytes')->take(20)->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function arquivosExpirados(array $arquivos): array
    {
        return collect($arquivos)->where('expirado', true)->sortByDesc('idade_dias')->take(30)->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function limites(array $porEmpresa): array
    {
        return collect($porEmpresa)->sortByDesc('percentual')->values()->all();
    }


    /** @return array<int, array<string, string>> */
    private function alertas(array $resumo, array $porEmpresa, array $arquivosPesados, array $arquivosExpirados): array
    {
        $alertas = [];
        $clientesAcima90 = collect($porEmpresa)->filter(fn (array $empresa): bool => (int) ($empresa['percentual'] ?? 0) >= 90)->count();
        $arquivosMuitoPesados = collect($arquivosPesados)->filter(fn (array $arquivo): bool => (int) ($arquivo['tamanho_bytes'] ?? 0) >= 500 * 1024 * 1024)->count();

        if ($clientesAcima90 > 0) {
            $alertas[] = [
                'tom' => 'danger',
                'titulo' => $clientesAcima90 . ' cliente(s) acima de 90%',
                'texto' => 'Priorize revisão de limite ou limpeza antes de bloquear novos envios.',
                'aba' => 'limites',
                'acao' => 'Ver limites',
            ];
        }

        if (count($arquivosExpirados) > 0) {
            $alertas[] = [
                'tom' => 'warning',
                'titulo' => count($arquivosExpirados) . ' arquivo(s) expirado(s)',
                'texto' => 'Revise retenção, auditoria e descarte controlado para recuperar espaço.',
                'aba' => 'expirados',
                'acao' => 'Revisar expirados',
            ];
        }

        if ($arquivosMuitoPesados > 0) {
            $alertas[] = [
                'tom' => 'primary',
                'titulo' => $arquivosMuitoPesados . ' arquivo(s) acima de 500 MB',
                'texto' => 'Arquivos grandes são os melhores candidatos para compressão ou arquivamento.',
                'aba' => 'arquivos-pesados',
                'acao' => 'Ver pesados',
            ];
        }

        if (($resumo['sem_tamanho'] ?? 0) > 0) {
            $alertas[] = [
                'tom' => 'warning',
                'titulo' => number_format((int) $resumo['sem_tamanho'], 0, ',', '.') . ' arquivo(s) sem tamanho',
                'texto' => 'Alguns registros não possuem tamanho no banco ou não foram encontrados no disco.',
                'aba' => 'arquivos-pesados',
                'acao' => 'Auditar arquivos',
            ];
        }

        if ($alertas === []) {
            $alertas[] = [
                'tom' => 'success',
                'titulo' => 'Nenhum alerta crítico',
                'texto' => 'O armazenamento está saudável com os dados disponíveis agora.',
                'aba' => 'por-empresa',
                'acao' => 'Ver empresas',
            ];
        }

        return array_slice($alertas, 0, 4);
    }

    /** @return array<int, array<string, string>> */
    private function insights(array $resumo, array $porEmpresa, array $arquivos): array
    {
        $insights = [];
        $empresaCritica = collect($porEmpresa)->firstWhere('tom', 'danger') ?: collect($porEmpresa)->firstWhere('tom', 'warning');
        $maiorArquivo = collect($arquivos)->sortByDesc('tamanho_bytes')->first();

        if ($empresaCritica) {
            $insights[] = [
                'tom' => $empresaCritica['tom'],
                'titulo' => 'Empresa próxima do limite',
                'texto' => $empresaCritica['empresa_nome'] . ' está usando ' . $empresaCritica['percentual'] . '% do limite configurado.',
            ];
        }

        if (($resumo['expirados'] ?? 0) > 0) {
            $insights[] = [
                'tom' => 'warning',
                'titulo' => 'Limpeza recomendada',
                'texto' => number_format((int) $resumo['expirados'], 0, ',', '.') . ' arquivo(s) aparecem como expirados ou antigos.',
            ];
        }

        if ($maiorArquivo && (int) ($maiorArquivo['tamanho_bytes'] ?? 0) > 0) {
            $insights[] = [
                'tom' => 'primary',
                'titulo' => 'Maior arquivo encontrado',
                'texto' => ($maiorArquivo['nome'] ?? 'Arquivo') . ' ocupa ' . ($maiorArquivo['tamanho_formatado'] ?? '0 B') . '.',
            ];
        }

        if ($insights === []) {
            $insights[] = [
                'tom' => 'success',
                'titulo' => 'Armazenamento saudável',
                'texto' => 'Nenhum estouro de limite, acúmulo crítico ou arquivo pesado relevante foi detectado.',
            ];
        }

        return $insights;
    }


    public function verClienteAction(): Action
    {
        return Action::make('verCliente')
            ->label('Ver cliente')
            ->modalHeading(fn (Action $action): string => $this->clienteStorageDetail($action->getArguments())['empresa']['nome'] ?? 'Resumo do cliente')
            ->modalDescription('Resumo operacional ligado ao consumo de armazenamento, sem tirar você da tela atual.')
            ->modalWidth(Width::SevenExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(fn (Action $action) => view('filament.pages.partials.armazenamento-cliente-modal', [
                'detail' => $this->clienteStorageDetail($action->getArguments()),
            ]));
    }

    private function clienteStorageDetail(array $arguments = []): array
    {
        $empresaId = (int) ($arguments['empresaId'] ?? 0);
        $arquivos = collect($this->arquivos())->filter(fn (array $arquivo): bool => (int) ($arquivo['empresa_id'] ?? 0) === $empresaId);
        $porEmpresa = collect($this->porEmpresa($arquivos->all()))->first() ?? [];
        $empresa = $this->empresaResumo($empresaId, $porEmpresa);
        $totalBytes = (int) $arquivos->sum('tamanho_bytes');
        $limiteBytes = (int) ($porEmpresa['limite_bytes'] ?? $this->limiteEmpresaBytes($empresaId, (string) ($empresa['plano'] ?? '')));
        $percentual = $limiteBytes > 0 ? min(999, (int) round(($totalBytes / $limiteBytes) * 100)) : 0;
        $expirados = $arquivos->where('expirado', true);
        $pesados = $arquivos->sortByDesc('tamanho_bytes')->take(5)->values();
        $tarefas = $this->metricasTarefasEmpresa($empresaId);
        $atendimentos = $this->metricasAtendimentosEmpresa($empresaId);
        $financeiro = $this->metricasFinanceiroEmpresa($empresaId);

        return [
            'empresa' => $empresa,
            'armazenamento' => [
                'total_formatado' => $this->formatBytes($totalBytes),
                'limite_formatado' => $this->formatBytes($limiteBytes),
                'percentual' => $percentual,
                'tom' => $percentual >= 95 ? 'danger' : ($percentual >= 80 ? 'warning' : 'success'),
                'arquivos' => $arquivos->count(),
                'expirados' => $expirados->count(),
                'recuperavel_formatado' => $this->formatBytes((int) $expirados->sum('tamanho_bytes')),
                'maior_arquivo' => $pesados->first(),
                'pesados' => $pesados->all(),
            ],
            'tarefas' => $tarefas,
            'atendimentos' => $atendimentos,
            'portal' => $this->metricasPortalEmpresa($empresaId),
            'financeiro' => $financeiro,
            'contratos' => $this->metricasContratosEmpresa($empresaId),
            'validade' => $this->metricasValidadeEmpresa($empresaId),
            'governanca' => $this->metricasGovernancaEmpresa($empresaId),
            'acoes' => $this->acoesClienteModal($empresaId),
            'recomendacoes' => $this->recomendacoesClienteModal($percentual, $expirados->count(), (int) ($tarefas['atrasadas'] ?? 0), (int) ($atendimentos['criticos'] ?? 0), (float) ($financeiro['vencido_valor'] ?? 0)),
        ];
    }

    private function empresaResumo(int $empresaId, array $porEmpresa): array
    {
        $empresa = [];
        if ($empresaId > 0 && CachedSchema::hasTable('empresas')) {
            $select = ['id'];
            foreach (['razao_social', 'nome_fantasia', 'cnpj', 'email', 'telefone', 'responsavel_nome', 'status', 'plano', 'created_at', 'portal_ativo'] as $column) {
                if (CachedSchema::hasColumn('empresas', $column)) { $select[] = $column; }
            }
            $empresa = (array) DB::table('empresas')->select($select)->where('id', $empresaId)->first();
        }
        $nome = $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? $porEmpresa['empresa_nome'] ?? 'Cliente não identificado';
        return [
            'id' => $empresaId,
            'nome' => $nome,
            'razao_social' => $empresa['razao_social'] ?? $nome,
            'cnpj' => $empresa['cnpj'] ?? null,
            'email' => $empresa['email'] ?? null,
            'telefone' => $empresa['telefone'] ?? null,
            'responsavel' => $empresa['responsavel_nome'] ?? null,
            'status' => $empresa['status'] ?? 'não informado',
            'plano' => $empresa['plano'] ?? ($porEmpresa['plano'] ?? 'sem plano'),
            'portal_ativo' => (bool) ($empresa['portal_ativo'] ?? false),
            'desde' => $this->formatDate($empresa['created_at'] ?? null),
        ];
    }

    private function metricasTarefasEmpresa(int $empresaId): array
    {
        if ($empresaId <= 0 || ! CachedSchema::hasTable('item_controles')) { return $this->emptyMetrics(); }
        $query = DB::table('item_controles')->where('empresa_id', $empresaId);
        $abertas = (clone $query)->whereNotIn('status', $this->statusFinalizados())->count();
        $atrasadas = CachedSchema::hasColumn('item_controles', 'data_vencimento') ? (clone $query)->whereNotIn('status', $this->statusFinalizados())->whereDate('data_vencimento', '<', now()->toDateString())->count() : 0;
        $criticas = CachedSchema::hasColumn('item_controles', 'prioridade') ? (clone $query)->whereNotIn('status', $this->statusFinalizados())->whereIn('prioridade', ['alta', 'urgente', 'critica', 'crítica'])->count() : 0;
        $concluidasMes = CachedSchema::hasColumn('item_controles', 'data_conclusao') ? (clone $query)->whereIn('status', $this->statusFinalizados())->whereDate('data_conclusao', '>=', now()->startOfMonth()->toDateString())->count() : (clone $query)->whereIn('status', $this->statusFinalizados())->whereDate('updated_at', '>=', now()->startOfMonth()->toDateString())->count();
        $slaVencido = CachedSchema::hasColumn('item_controles', 'sla_limite_em') ? (clone $query)->whereNotIn('status', $this->statusFinalizados())->whereNotNull('sla_limite_em')->where('sla_limite_em', '<', now())->count() : 0;
        return compact('abertas', 'atrasadas', 'criticas', 'concluidasMes', 'slaVencido');
    }

    private function metricasAtendimentosEmpresa(int $empresaId): array
    {
        if ($empresaId <= 0 || ! CachedSchema::hasTable('atendimentos')) { return $this->emptyMetrics(); }
        $query = DB::table('atendimentos')->where('empresa_id', $empresaId);
        $abertos = (clone $query)->whereNotIn('status', ['resolvido', 'fechado', 'cancelado'])->count();
        $aguardandoCliente = (clone $query)->where('status', 'aguardando_cliente')->count();
        $criticos = (clone $query)->whereNotIn('status', ['resolvido', 'fechado', 'cancelado'])->whereIn('prioridade', ['alta', 'urgente'])->count();
        $slaVencido = CachedSchema::hasColumn('atendimentos', 'sla_limite_em') ? (clone $query)->whereNotIn('status', ['resolvido', 'fechado', 'cancelado'])->whereNotNull('sla_limite_em')->where('sla_limite_em', '<', now())->count() : 0;
        return ['abertos' => $abertos, 'aguardando_cliente' => $aguardandoCliente, 'criticos' => $criticos, 'sla_vencido' => $slaVencido, 'ultimo_contato' => $this->formatDateTime((clone $query)->max('updated_at'))];
    }

    private function metricasPortalEmpresa(int $empresaId): array
    {
        $data = ['solicitacoes_abertas' => 0, 'mensagens_abertas' => 0, 'documentos' => 0, 'ultima_mensagem' => 'Não informado'];
        if ($empresaId > 0 && CachedSchema::hasTable('portal_solicitacoes')) { $data['solicitacoes_abertas'] = DB::table('portal_solicitacoes')->where('empresa_id', $empresaId)->whereNotIn('status', ['concluido', 'concluida', 'finalizado', 'finalizada', 'cancelado', 'cancelada'])->count(); }
        if ($empresaId > 0 && CachedSchema::hasTable('portal_mensagens')) { $query = DB::table('portal_mensagens')->where('empresa_id', $empresaId); $data['mensagens_abertas'] = (clone $query)->where('conversa_status', 'aberta')->count(); $data['ultima_mensagem'] = $this->formatDateTime((clone $query)->max('created_at')); }
        if ($empresaId > 0 && CachedSchema::hasTable('portal_documentos')) { $data['documentos'] = DB::table('portal_documentos')->where('empresa_id', $empresaId)->count(); }
        return $data;
    }

    private function metricasFinanceiroEmpresa(int $empresaId): array
    {
        if ($empresaId <= 0 || ! CachedSchema::hasTable('financeiro_cobrancas')) { return ['abertas' => 0, 'vencidas' => 0, 'vencido_valor' => 0, 'vencido_formatado' => 'R$ 0,00', 'proximo_vencimento' => 'Sem vencimento']; }
        $query = DB::table('financeiro_cobrancas')->where('empresa_id', $empresaId);
        $abertas = (clone $query)->whereNotIn('status', ['paga', 'pago', 'cancelada', 'cancelado'])->count();
        $vencidasQuery = (clone $query)->whereNotIn('status', ['paga', 'pago', 'cancelada', 'cancelado'])->whereDate('vencimento', '<', now()->toDateString());
        $vencidas = (clone $vencidasQuery)->count();
        $vencidoValor = (float) (clone $vencidasQuery)->sum('valor');
        $proximoVencimento = (clone $query)->whereNotIn('status', ['paga', 'pago', 'cancelada', 'cancelado'])->whereDate('vencimento', '>=', now()->toDateString())->min('vencimento');
        return ['abertas' => $abertas, 'vencidas' => $vencidas, 'vencido_valor' => $vencidoValor, 'vencido_formatado' => $this->formatCurrency($vencidoValor), 'proximo_vencimento' => $this->formatDate($proximoVencimento)];
    }

    private function metricasContratosEmpresa(int $empresaId): array
    {
        if ($empresaId <= 0 || ! CachedSchema::hasTable('item_controles')) { return $this->emptyMetrics(); }
        $query = DB::table('item_controles')->where('empresa_id', $empresaId);
        $contratos = CachedSchema::hasColumn('item_controles', 'contrato_numero') ? (clone $query)->whereNotNull('contrato_numero')->where('contrato_numero', '<>', '')->count() : 0;
        $ativos = CachedSchema::hasColumn('item_controles', 'contrato_status') ? (clone $query)->whereNotNull('contrato_numero')->whereIn('contrato_status', ['ativo', 'vigente', 'assinado'])->count() : 0;
        $vencendo = CachedSchema::hasColumn('item_controles', 'contrato_fim_em') ? (clone $query)->whereNotNull('contrato_numero')->whereBetween('contrato_fim_em', [now()->toDateString(), now()->addDays(30)->toDateString()])->count() : 0;
        return compact('contratos', 'ativos', 'vencendo');
    }

    private function metricasValidadeEmpresa(int $empresaId): array
    {
        if ($empresaId <= 0 || ! CachedSchema::hasTable('item_controles') || ! CachedSchema::hasColumn('item_controles', 'data_vencimento')) { return $this->emptyMetrics(); }
        $query = DB::table('item_controles')->where('empresa_id', $empresaId)->whereNotIn('status', $this->statusFinalizados());
        return ['vencidos' => (clone $query)->whereDate('data_vencimento', '<', now()->toDateString())->count(), 'proximos_7' => (clone $query)->whereBetween('data_vencimento', [now()->toDateString(), now()->addDays(7)->toDateString()])->count(), 'proximos_30' => (clone $query)->whereBetween('data_vencimento', [now()->toDateString(), now()->addDays(30)->toDateString()])->count()];
    }

    private function metricasGovernancaEmpresa(int $empresaId): array
    {
        $data = ['aprovacoes_pendentes' => 0, 'assinaturas_pendentes' => 0, 'checklists_pendentes' => 0];
        if ($empresaId > 0 && CachedSchema::hasTable('item_controle_aprovacoes')) { $data['aprovacoes_pendentes'] = DB::table('item_controle_aprovacoes')->where('empresa_id', $empresaId)->where('status', 'pendente')->count(); }
        if ($empresaId > 0 && CachedSchema::hasTable('item_controle_assinaturas')) { $data['assinaturas_pendentes'] = DB::table('item_controle_assinaturas')->where('empresa_id', $empresaId)->whereNull('assinado_em')->count(); }
        if ($empresaId > 0 && CachedSchema::hasTable('item_controle_checklists') && CachedSchema::hasTable('item_controles')) { $data['checklists_pendentes'] = DB::table('item_controle_checklists')->join('item_controles', 'item_controles.id', '=', 'item_controle_checklists.item_controle_id')->where('item_controles.empresa_id', $empresaId)->where('item_controle_checklists.concluido', false)->count(); }
        return $data;
    }

    private function acoesClienteModal(int $empresaId): array
    {
        if ($empresaId <= 0) { return []; }
        return [
            ['label' => 'Abrir ficha do cliente', 'url' => EmpresaResource::getUrl('edit', ['record' => $empresaId]), 'style' => 'primary'],
            ['label' => 'Ver tarefas', 'url' => ItemControleResource::getUrl('index') . '?tableFilters[empresa_id][value]=' . $empresaId, 'style' => 'secondary'],
            ['label' => 'Ver documentos', 'url' => ItemControleResource::getUrl('anexos') . '?tableFilters[empresa_id][value]=' . $empresaId, 'style' => 'secondary'],
            ['label' => 'Ver aprovações', 'url' => ItemControleResource::getUrl('aprovacoes') . '?tableFilters[empresa_id][value]=' . $empresaId, 'style' => 'secondary'],
        ];
    }

    private function recomendacoesClienteModal(int $percentual, int $expirados, int $tarefasAtrasadas, int $atendimentosCriticos, float $valorVencido): array
    {
        $recomendacoes = [];
        if ($percentual >= 90) { $recomendacoes[] = ['tom' => 'danger', 'texto' => 'Cliente acima de 90% do limite: revisar limite contratado ou iniciar limpeza de arquivos grandes.']; }
        elseif ($percentual >= 80) { $recomendacoes[] = ['tom' => 'warning', 'texto' => 'Cliente próximo do limite: acompanhar crescimento antes de novos envios em massa.']; }
        if ($expirados > 0) { $recomendacoes[] = ['tom' => 'warning', 'texto' => 'Há arquivos expirados/antigos: validar retenção antes de excluir e priorizar os maiores.']; }
        if ($tarefasAtrasadas > 0) { $recomendacoes[] = ['tom' => 'danger', 'texto' => 'Existem tarefas atrasadas: o consumo pode estar ligado a pendências documentais.']; }
        if ($atendimentosCriticos > 0) { $recomendacoes[] = ['tom' => 'warning', 'texto' => 'Atendimentos críticos abertos: conferir se há solicitação de documentos pendente.']; }
        if ($valorVencido > 0) { $recomendacoes[] = ['tom' => 'danger', 'texto' => 'Cliente com cobrança vencida: avaliar regra comercial antes de ampliar limite de armazenamento.']; }
        if ($recomendacoes === []) { $recomendacoes[] = ['tom' => 'success', 'texto' => 'Cliente sem alerta crítico combinado. Manter monitoramento padrão.']; }
        return $recomendacoes;
    }

    private function emptyMetrics(): array
    {
        return ['abertas' => 0, 'atrasadas' => 0, 'criticas' => 0];
    }

    private function statusFinalizados(): array
    {
        return ['concluido', 'concluído', 'finalizado', 'aprovado', 'cancelado', 'cancelada', 'fechado', 'resolvido'];
    }

    private function formatDate(mixed $date): string
    {
        if (! filled($date)) { return 'Não informado'; }
        try { return Carbon::parse($date)->format('d/m/Y'); } catch (\Throwable) { return 'Não informado'; }
    }

    private function formatDateTime(mixed $date): string
    {
        if (! filled($date)) { return 'Não informado'; }
        try { return Carbon::parse($date)->format('d/m/Y H:i'); } catch (\Throwable) { return 'Não informado'; }
    }

    private function formatCurrency(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    /** @return array<int, string> */
    private function itemControleColumns(): array
    {
        $columns = ['id'];

        foreach (['titulo', 'arquivo', 'data_vencimento', 'status', 'empresa_id', 'created_at', 'updated_at'] as $column) {
            if (CachedSchema::hasColumn('item_controles', $column)) {
                $columns[] = $column;
            }
        }

        return array_values(array_unique($columns));
    }

    /** @return array<int, string> */
    private function itemControleRelations(): array
    {
        return CachedSchema::hasColumn('item_controles', 'empresa_id') && CachedSchema::hasTable('empresas')
            ? ['empresa:id,razao_social,nome_fantasia,plano']
            : [];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 1, ',', '.') . ' ' . $units[$power];
    }
}
