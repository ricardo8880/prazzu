<?php

namespace App\Services;

use App\Models\User;
use App\Support\CachedSchema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class AccountIndicatorsService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(?User $user): array
    {
        $cacheKey = $this->cacheKey($user);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user): array {
            return $this->buildDashboard($user);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDashboard(?User $user): array
    {
        $clientes = $this->clientesCount($user);
        $usuarios = $this->usuariosCount($user);
        $documentos = $this->documentosCount($user);
        $databaseBytes = $this->databaseSizeBytes();
        $storageBytes = $this->storageSizeBytes();

        $storageLimitBytes = $this->storageLimitBytes();
        $storagePercent = $storageLimitBytes > 0
            ? min(100, round(($storageBytes / $storageLimitBytes) * 100, 1))
            : null;

        return [
            'cards' => [
                [
                    'label' => 'Clientes',
                    'value' => $this->formatNumber($clientes['value']),
                    'raw_value' => $clientes['value'],
                    'description' => 'Clientes cadastrados na conta',
                    'icon' => 'bi-buildings',
                    'tone' => 'info',
                ],
                [
                    'label' => 'Usuários',
                    'value' => $this->formatNumber($usuarios['value']),
                    'raw_value' => $usuarios['value'],
                    'description' => 'Pessoas com acesso ao sistema',
                    'icon' => 'bi-people',
                    'tone' => 'primary',
                ],
                [
                    'label' => 'Documentos',
                    'value' => $this->formatNumber($documentos['value']),
                    'raw_value' => $documentos['value'],
                    'description' => 'Documentos e anexos cadastrados',
                    'icon' => 'bi-file-earmark-text',
                    'tone' => 'success',
                ],
                [
                    'label' => 'Banco de dados',
                    'value' => $databaseBytes === null ? 'Indisponível' : $this->formatBytes($databaseBytes),
                    'raw_value' => $databaseBytes,
                    'description' => $databaseBytes === null
                        ? 'Não foi possível calcular agora'
                        : 'Volume atual das informações',
                    'icon' => 'bi-database',
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Espaço utilizado',
                    'value' => $this->formatBytes($storageBytes),
                    'raw_value' => $storageBytes,
                    'description' => 'Espaço ocupado pelos arquivos',
                    'icon' => 'bi-hdd-stack',
                    'tone' => 'gray',
                ],
            ],
            'summary' => [
                'updated_at' => now()->format('d/m/Y H:i'),
                'cache_hint' => 'Atualização automática a cada 5 minutos',
                'scope' => $user?->isSuperAdmin()
                    ? 'Visão geral da plataforma'
                    : 'Visão da conta da empresa',
            ],
            'usage' => [
                'storage_used' => $this->formatBytes($storageBytes),
                'storage_limit' => $storageLimitBytes > 0 ? $this->formatBytes($storageLimitBytes) : 'Sem limite definido',
                'storage_percent' => $storagePercent,
                'storage_bar_width' => $storagePercent === null ? 0 : max(4, min(100, (float) $storagePercent)),
                'database_size' => $databaseBytes === null ? 'Indisponível' : $this->formatBytes($databaseBytes),
            ],
            'summaryItems' => $this->summaryItems($clientes['value'], $usuarios['value'], $documentos['value'], $storageBytes),
            'managerNotes' => $this->managerNotes($clientes['value'], $usuarios['value'], $documentos['value'], $storageBytes, $storageLimitBytes, $storagePercent, $databaseBytes),
        ];
    }


    /**
     * @return array<int, array{title:string, description:string, tone:string}>
     */
    private function summaryItems(int $clientes, int $usuarios, int $documentos, int $storageBytes): array
    {
        $documentosPorCliente = $clientes > 0
            ? number_format($documentos / max(1, $clientes), 1, ',', '.')
            : '0';

        return [
            [
                'title' => 'Carteira atendida',
                'description' => $clientes > 0
                    ? 'A conta possui ' . $this->formatNumber($clientes) . ' clientes cadastrados para acompanhamento.'
                    : 'Ainda não há clientes cadastrados nesta conta.',
                'tone' => $clientes > 0 ? 'success' : 'warning',
            ],
            [
                'title' => 'Equipe com acesso',
                'description' => $usuarios > 0
                    ? $this->formatNumber($usuarios) . ' usuários possuem acesso ao ambiente.'
                    : 'Nenhum usuário foi encontrado para esta conta.',
                'tone' => $usuarios > 0 ? 'info' : 'warning',
            ],
            [
                'title' => 'Volume documental',
                'description' => $clientes > 0
                    ? 'Média aproximada de ' . $documentosPorCliente . ' documentos por cliente.'
                    : 'Os documentos aparecerão aqui conforme forem cadastrados.',
                'tone' => $documentos > 0 ? 'success' : 'gray',
            ],
            [
                'title' => 'Espaço ocupado',
                'description' => 'A conta utiliza atualmente ' . $this->formatBytes($storageBytes) . ' em arquivos.',
                'tone' => 'primary',
            ],
        ];
    }

    /**
     * @return array<int, array{title:string, description:string, tone:string}>
     */
    private function managerNotes(int $clientes, int $usuarios, int $documentos, int $storageBytes, int $storageLimitBytes, ?float $storagePercent, ?int $databaseBytes): array
    {
        $notes = [];

        if ($storageLimitBytes > 0) {
            if (($storagePercent ?? 0) >= 90) {
                $notes[] = [
                    'title' => 'Armazenamento quase cheio',
                    'description' => 'O ambiente já passou de 90% do limite configurado. Vale revisar documentos antigos ou ampliar o limite.',
                    'tone' => 'warning',
                ];
            } elseif (($storagePercent ?? 0) >= 75) {
                $notes[] = [
                    'title' => 'Uso de armazenamento em crescimento',
                    'description' => 'O ambiente está acima de 75% do limite. É um bom momento para acompanhar a evolução.',
                    'tone' => 'warning',
                ];
            } else {
                $notes[] = [
                    'title' => 'Armazenamento saudável',
                    'description' => 'O uso atual está dentro de uma faixa confortável para a operação.',
                    'tone' => 'success',
                ];
            }
        } else {
            $notes[] = [
                'title' => 'Uso atual registrado',
                'description' => 'O sistema mostra o espaço utilizado hoje. Um limite pode ser definido depois, caso você queira acompanhar percentual de uso.',
                'tone' => 'info',
            ];
        }

        if ($clientes > 0 && $usuarios > 0) {
            $clientesPorUsuario = number_format($clientes / max(1, $usuarios), 1, ',', '.');

            $notes[] = [
                'title' => 'Carga por usuário',
                'description' => 'Hoje há cerca de ' . $clientesPorUsuario . ' clientes para cada usuário com acesso.',
                'tone' => 'primary',
            ];
        }

        if ($documentos === 0 && $clientes > 0) {
            $notes[] = [
                'title' => 'Documentos ainda não cadastrados',
                'description' => 'Existem clientes cadastrados, mas nenhum documento foi localizado para a conta.',
                'tone' => 'warning',
            ];
        }

        if ($databaseBytes === null) {
            $notes[] = [
                'title' => 'Banco indisponível no momento',
                'description' => 'O tamanho do banco não pôde ser calculado agora, mas os demais indicadores seguem disponíveis.',
                'tone' => 'gray',
            ];
        }

        return array_slice($notes, 0, 3);
    }

    private function cacheKey(?User $user): string
    {
        if (! $user) {
            return 'account-indicators:v2:guest';
        }

        if ($user->isSuperAdmin()) {
            return 'account-indicators:v2:global';
        }

        return 'account-indicators:v2:empresa:' . ((int) ($user->empresa_id ?? 0)) . ':user:' . ((int) $user->id);
    }

    private function storageLimitBytes(): int
    {
        $configured = config('prazzu.storage_limit_bytes')
            ?? config('filesystems.storage_limit_bytes')
            ?? env('PRAZZU_STORAGE_LIMIT_BYTES');

        return max(0, (int) $configured);
    }

    /** @return array{value:int, description:string} */
    private function clientesCount(?User $user): array
    {
        $value = 0;
        $source = null;

        if (CachedSchema::hasTable('crm_clientes')) {
            $query = DB::table('crm_clientes');
            $this->scopeByEmpresa($query, $user, 'empresa_id');
            $value = (int) $query->count();
            $source = 'crm_clientes';
        }

        if ($value === 0 && CachedSchema::hasTable('financeiro_clientes')) {
            $query = DB::table('financeiro_clientes');
            $this->scopeByEmpresa($query, $user, 'empresa_id');
            $value = (int) $query->count();
            $source = 'financeiro_clientes';
        }

        return [
            'value' => $value,
            'description' => $source ? 'Total identificado em ' . $source : 'Nenhuma tabela de clientes encontrada',
        ];
    }

    /** @return array{value:int, description:string} */
    private function usuariosCount(?User $user): array
    {
        if (! CachedSchema::hasTable('users')) {
            return ['value' => 0, 'description' => 'Tabela users não encontrada'];
        }

        $query = DB::table('users');
        $this->scopeByEmpresa($query, $user, 'empresa_id');

        return [
            'value' => (int) $query->count(),
            'description' => 'Pessoas com acesso ao ambiente',
        ];
    }

    /** @return array{value:int, description:string} */
    private function documentosCount(?User $user): array
    {
        $total = 0;
        $sources = [];

        if (CachedSchema::hasTable('portal_documentos')) {
            $query = DB::table('portal_documentos');
            $this->scopeByEmpresa($query, $user, 'empresa_id');
            $count = (int) $query->count();
            $total += $count;
            $sources[] = 'portal_documentos';
        }

        if (CachedSchema::hasTable('item_controles') && CachedSchema::hasColumn('item_controles', 'arquivo')) {
            $query = DB::table('item_controles')
                ->whereNotNull('arquivo')
                ->where('arquivo', '<>', '');
            $this->scopeByEmpresa($query, $user, 'empresa_id');
            $count = (int) $query->count();
            $total += $count;
            $sources[] = 'item_controles.arquivo';
        }

        if (CachedSchema::hasTable('anexos')) {
            $query = DB::table('anexos');

            if (CachedSchema::hasColumn('anexos', 'item_controle_id') && CachedSchema::hasTable('item_controles')) {
                $query->leftJoin('item_controles', 'item_controles.id', '=', 'anexos.item_controle_id');
                $this->scopeByEmpresa($query, $user, 'item_controles.empresa_id');
            }

            $total += (int) $query->count();
            $sources[] = 'anexos';
        }

        if (CachedSchema::hasTable('item_controle_anexos')) {
            $query = DB::table('item_controle_anexos');

            if (CachedSchema::hasColumn('item_controle_anexos', 'item_controle_id') && CachedSchema::hasTable('item_controles')) {
                $query->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_anexos.item_controle_id');
                $this->scopeByEmpresa($query, $user, 'item_controles.empresa_id');
            }

            $total += (int) $query->count();
            $sources[] = 'item_controle_anexos';
        }

        return [
            'value' => $total,
            'description' => $sources ? 'Soma local: ' . implode(', ', $sources) : 'Nenhuma tabela documental encontrada',
        ];
    }

    private function databaseSizeBytes(): ?int
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();

            if ($driver === 'mysql') {
                $row = $connection->selectOne(
                    'SELECT COALESCE(SUM(data_length + index_length), 0) AS bytes FROM information_schema.tables WHERE table_schema = DATABASE()'
                );

                return isset($row->bytes) ? (int) $row->bytes : null;
            }

            if ($driver === 'sqlite') {
                $database = (string) config('database.connections.sqlite.database');
                $path = $database === ':memory:' ? '' : $database;

                return $path !== '' && is_file($path) ? (int) filesize($path) : null;
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function storageSizeBytes(): int
    {
        $total = 0;

        foreach ($this->storagePaths() as $path) {
            $total += $this->directorySize($path);
        }

        return $total;
    }

    /** @return array<int, string> */
    private function storagePaths(): array
    {
        return array_values(array_filter([
            storage_path('app'),
            public_path('storage'),
            public_path('uploads'),
        ], fn (string $path): bool => is_dir($path)));
    }

    /** @return array<int, string> */
    private function storagePathsLabels(): array
    {
        return array_map(function (string $path): string {
            return str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
        }, $this->storagePaths());
    }

    private function directorySize(string $path): int
    {
        if (! is_dir($path) || is_link($path)) {
            return 0;
        }

        $size = 0;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->isLink()) {
                    continue;
                }

                $pathname = $file->getPathname();

                if (str_contains($pathname, DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR)
                    || str_contains($pathname, DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'sessions' . DIRECTORY_SEPARATOR)
                    || str_contains($pathname, DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR)) {
                    continue;
                }

                $size += (int) $file->getSize();
            }
        } catch (Throwable) {
            return $size;
        }

        return $size;
    }

    private function scopeByEmpresa($query, ?User $user, string $column): void
    {
        if (! $user || $user->isSuperAdmin()) {
            return;
        }

        $empresaId = (int) ($user->empresa_id ?? 0);

        if ($empresaId <= 0) {
            return;
        }

        $table = null;
        $columnName = $column;

        if (str_contains($column, '.')) {
            [$table, $columnName] = explode('.', $column, 2);
        } elseif (isset($query->from) && is_string($query->from)) {
            $table = $query->from;
        }

        if ($table && ! CachedSchema::hasColumn($table, $columnName)) {
            return;
        }

        $query->where($column, $empresaId);
    }

    private function formatNumber(int $number): string
    {
        return number_format($number, 0, ',', '.');
    }

    private function formatBytes(?int $bytes): string
    {
        $bytes = max(0, (int) $bytes);

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024;

        foreach ($units as $unit) {
            if ($value < 1024 || $unit === 'TB') {
                return number_format($value, $value >= 10 ? 1 : 2, ',', '.') . ' ' . $unit;
            }

            $value /= 1024;
        }

        return number_format($value, 1, ',', '.') . ' TB';
    }
}
