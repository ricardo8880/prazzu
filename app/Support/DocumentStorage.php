<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentStorage
{
    public const DISK = 'public';

    public const MAX_SIZE_KB = 10240;

    public const ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt',
        'jpg', 'jpeg', 'png', 'webp',
    ];

    public const BLOCKED_EXTENSIONS = [
        'php', 'phtml', 'phar', 'js', 'html', 'htm', 'svg', 'exe', 'bat', 'cmd', 'sh', 'com', 'scr', 'jar',
    ];

    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'text/plain',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public static function storeItemControlePrincipal(UploadedFile $file, ?int $empresaId = null, ?int $itemId = null): string
    {
        return self::storeUploadedFile($file, self::directory('item-controles', $empresaId, $itemId));
    }

    public static function storePortalDocumento(UploadedFile $file, ?int $empresaId = null, ?int $itemId = null): string
    {
        return self::storeUploadedFile($file, self::directory('portal-cliente/documentos', $empresaId, $itemId));
    }

    public static function storePortalChatAnexo(UploadedFile $file, ?int $empresaId = null, ?int $atendimentoId = null): string
    {
        return self::storeUploadedFile($file, self::directory('portal-cliente/chat', $empresaId, $atendimentoId));
    }

    public static function storeUploadedFile(UploadedFile $file, string $directory): string
    {
        self::assertValidUpload($file);

        $path = $file->storeAs(
            trim($directory, '/'),
            self::safeStoredFilename($file),
            self::DISK
        );

        if (! is_string($path) || $path === '') {
            throw new \RuntimeException('Falha ao gravar arquivo no disco público.');
        }

        return self::normalizePath($path);
    }

    public static function assertValidUpload(UploadedFile $file): void
    {
        self::assertSafeOriginalName($file);

        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'arquivo' => 'O arquivo enviado não pôde ser processado. Selecione o arquivo novamente.',
            ]);
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mimeType = strtolower((string) ($file->getMimeType() ?: $file->getClientMimeType()));
        $size = (int) $file->getSize();

        if ($size <= 0) {
            throw ValidationException::withMessages([
                'arquivo' => 'O arquivo enviado está vazio.',
            ]);
        }

        if (in_array($extension, self::BLOCKED_EXTENSIONS, true) || ! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'arquivo' => 'Extensão não permitida. Envie PDF, Word, Excel, CSV, TXT ou imagem.',
            ]);
        }

        if ($mimeType !== '' && ! in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw ValidationException::withMessages([
                'arquivo' => 'Tipo de arquivo não permitido. Envie um arquivo válido e seguro.',
            ]);
        }

        if ($size > (self::MAX_SIZE_KB * 1024)) {
            throw ValidationException::withMessages([
                'arquivo' => 'O arquivo ultrapassa o limite de 10 MB.',
            ]);
        }
    }

    public static function assertSafeOriginalName(UploadedFile $file): void
    {
        $originalName = strtolower(str_replace('\\', '/', $file->getClientOriginalName()));
        $parts = array_values(array_filter(explode('.', basename($originalName))));

        foreach ($parts as $part) {
            if (in_array($part, self::BLOCKED_EXTENSIONS, true)) {
                throw ValidationException::withMessages([
                    'arquivo' => 'Nome de arquivo não permitido por segurança.',
                ]);
            }
        }

        if (str_contains($originalName, "\0") || str_contains($originalName, '../')) {
            throw ValidationException::withMessages([
                'arquivo' => 'Nome de arquivo inválido.',
            ]);
        }
    }

    public static function safeStoredFilename(UploadedFile $file): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $base = Str::slug(Str::limit($base, 80, '')) ?: 'documento';

        return now()->format('YmdHis') . '-' . Str::random(12) . '-' . $base . ($extension ? '.' . $extension : '');
    }

    public static function normalizePath(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = str_replace('\\', '/', trim($path));
        $path = preg_replace('#/+#', '/', $path) ?: $path;
        $path = ltrim($path, '/');
        $path = preg_replace('#^(storage/|public/)#', '', $path) ?: $path;

        if (str_contains($path, '..')) {
            return null;
        }

        return $path;
    }

    public static function exists(?string $path): bool
    {
        $normalized = self::normalizePath($path);

        return $normalized !== null && Storage::disk(self::DISK)->exists($normalized);
    }

    public static function publicUrl(?string $path): ?string
    {
        $normalized = self::normalizePath($path);

        return $normalized ? Storage::disk(self::DISK)->url($normalized) : null;
    }

    public static function download(?string $path, ?string $downloadName = null): StreamedResponse
    {
        $normalized = self::normalizePath($path);

        abort_if($normalized === null || ! Storage::disk(self::DISK)->exists($normalized), 404, 'Arquivo não encontrado.');

        return Storage::disk(self::DISK)->download($normalized, self::safeDownloadName($downloadName ?: basename($normalized)));
    }

    public static function safeDownloadName(string $name): string
    {
        $name = basename(trim(str_replace(["\r", "\n", '/', '\\'], ' ', $name)));
        $name = trim(preg_replace('/[\x00-\x1F\x7F]+/', '', $name) ?: '');
        $name = Str::limit($name, 160, '');

        return $name !== '' ? $name : 'documento';
    }

    private static function directory(string $base, ?int $empresaId = null, ?int $subjectId = null): string
    {
        return collect([
            trim($base, '/'),
            $empresaId ? 'empresa-' . $empresaId : null,
            $subjectId ? 'registro-' . $subjectId : null,
            now()->format('Y/m'),
        ])->filter()->implode('/');
    }
}
