<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Concerns\BuildsHealthItems;
use App\Services\SystemHealth\HealthCheckContract;
use Illuminate\Support\Facades\File;

class LogsHealthCheck implements HealthCheckContract
{
    use BuildsHealthItems;

    public function key(): string { return 'logs'; }
    public function name(): string { return 'Logs recentes'; }
    public function description(): string { return 'Volume recente de erros nos logs principais e logs de diagnóstico.'; }

    public function run(int $limit = 500): array
    {
        $items = [];
        $logDirectory = storage_path('logs');

        if (! is_dir($logDirectory)) {
            return [$this->error('Diretório de logs ausente.', $logDirectory, [], 'Crie storage/logs e libere escrita para o usuário do PHP.')];
        }

        $items[] = is_writable($logDirectory)
            ? $this->ok('Diretório de logs gravável.')
            : $this->error('Diretório de logs sem escrita.', $logDirectory, [], 'Ajuste permissões de storage/logs.');

        $files = collect(File::files($logDirectory))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.log'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->take(5);

        if ($files->isEmpty()) {
            $items[] = $this->warning('Nenhum arquivo .log encontrado.', null, [], 'Confirme se o logging do Laravel está gravando corretamente.');
            return $items;
        }

        foreach ($files as $file) {
            $content = $this->tailFile($file->getPathname(), 200000);
            $errors = preg_match_all('/\.(ERROR|CRITICAL|ALERT|EMERGENCY):/i', $content) ?: 0;
            $warnings = preg_match_all('/\.(WARNING|WARN):/i', $content) ?: 0;
            $context = [
                'arquivo' => $file->getFilename(),
                'erros' => $errors,
                'avisos' => $warnings,
                'modificado_em' => date('d/m/Y H:i:s', $file->getMTime()),
            ];

            if ($errors > 0) {
                $items[] = $this->warning('Log recente contém erros.', "{$file->getFilename()} possui {$errors} erro(s) no trecho final analisado.", $context, 'Abra o arquivo de log e corrija os erros recorrentes.');
            } elseif ($warnings > 0) {
                $items[] = $this->warning('Log recente contém avisos.', "{$file->getFilename()} possui {$warnings} aviso(s) no trecho final analisado.", $context, 'Revise avisos recorrentes para evitar degradação silenciosa.');
            } else {
                $items[] = $this->ok('Log recente sem erros no trecho analisado.', $file->getFilename(), $context);
            }
        }

        return $items;
    }

    private function tailFile(string $path, int $bytes): string
    {
        $size = filesize($path);
        if ($size === false || $size <= $bytes) {
            return (string) file_get_contents($path);
        }

        $handle = fopen($path, 'rb');
        if (! $handle) {
            return '';
        }

        fseek($handle, -$bytes, SEEK_END);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $content;
    }
}
