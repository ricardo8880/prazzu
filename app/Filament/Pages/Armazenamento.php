<?php

namespace App\Filament\Pages;

use App\Models\ItemControle;
use App\Support\CachedSchema;
use BackedEnum;
use Carbon\Carbon;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
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
