<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Concerns\BuildsHealthItems;
use App\Services\SystemHealth\HealthCheckContract;
use App\Support\CachedSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StorageHealthCheck implements HealthCheckContract
{
    use BuildsHealthItems;

    public function key(): string { return 'storage'; }
    public function name(): string { return 'Arquivos e uploads'; }
    public function description(): string { return 'Permissões de escrita, link público e existência física de anexos registrados.'; }

    public function run(int $limit = 500): array
    {
        $items = [];

        foreach ([storage_path(), storage_path('logs'), storage_path('framework'), storage_path('app/public'), app()->bootstrapPath('cache')] as $directory) {
            $items[] = is_dir($directory)
                ? $this->ok("Diretório existe: {$directory}")
                : $this->error("Diretório ausente: {$directory}", null, [], 'Crie o diretório e ajuste permissões.');

            $items[] = is_dir($directory) && is_writable($directory)
                ? $this->ok("Diretório gravável: {$directory}")
                : $this->error("Diretório sem escrita: {$directory}", null, [], 'Ajuste permissões do servidor para o usuário do PHP/webserver.');
        }

        $publicStorage = public_path('storage');
        $items[] = is_link($publicStorage) || is_dir($publicStorage)
            ? $this->ok('Link public/storage disponível.')
            : $this->warning('Link public/storage ausente.', null, [], 'Execute php artisan storage:link para publicar anexos e uploads.');

        if (CachedSchema::hasTable('item_controle_anexos')) {
            $missingFiles = $this->countMissingAttachmentFiles($limit);
            $items[] = $missingFiles > 0
                ? $this->warning('Anexos registrados sem arquivo físico encontrado.', "Total amostrado: {$missingFiles}.", ['count' => $missingFiles, 'limit' => $limit], 'Restaure arquivos do backup ou corrija registros órfãos.')
                : $this->ok('Amostra de anexos possui arquivo físico no disco public.');
        }

        return $items;
    }

    private function countMissingAttachmentFiles(int $limit): int
    {
        $pathColumn = CachedSchema::hasColumn('item_controle_anexos', 'caminho') ? 'caminho' : null;
        if (! $pathColumn) {
            return 0;
        }

        $attachments = DB::table('item_controle_anexos')
            ->select('id', $pathColumn)
            ->whereNotNull($pathColumn)
            ->limit($limit)
            ->get();

        $missing = 0;
        foreach ($attachments as $attachment) {
            $path = trim((string) $attachment->{$pathColumn});
            if ($path === '') {
                $missing++;
                continue;
            }

            $normalized = str_starts_with($path, 'public/') ? substr($path, 7) : $path;
            if (! Storage::disk('public')->exists($normalized) && ! file_exists(public_path($path))) {
                $missing++;
            }
        }

        return $missing;
    }
}
