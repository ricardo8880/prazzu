<?php

namespace App\Support;

use Carbon\Carbon;

class PortalChatMessageContract
{
    public static function fromArray(array $mensagem, array $context = []): array
    {
        $id = (int) ($mensagem['message_id'] ?? $mensagem['id'] ?? 0);
        $empresaId = (int) ($context['empresa_id'] ?? $mensagem['empresa_id'] ?? $mensagem['empresaId'] ?? 0);
        $room = trim((string) ($context['room'] ?? $mensagem['room'] ?? ($empresaId > 0 ? 'empresa:' . $empresaId . ':portal' : '')));
        $roomScope = trim((string) ($context['room_scope'] ?? $mensagem['room_scope'] ?? $mensagem['roomScope'] ?? 'portal'));

        $origem = self::normalizarOrigem((string) ($mensagem['origem'] ?? $mensagem['actor'] ?? $mensagem['class'] ?? 'cliente'));
        $classe = self::classeDaOrigem($origem);
        $actor = $classe === 'cliente' ? 'cliente' : 'suporte';

        $textoOriginal = trim((string) ($mensagem['mensagem_texto'] ?? $mensagem['text'] ?? $mensagem['mensagem'] ?? ''));
        $attachments = self::normalizarAnexos($mensagem['attachments'] ?? $mensagem['anexos'] ?? []);

        if ($attachments === []) {
            $attachments = self::extrairAnexosMensagem($textoOriginal);
        }

        $texto = self::removerBlocoAnexosMensagem($textoOriginal);
        $nome = trim((string) ($mensagem['author'] ?? $mensagem['nome'] ?? $mensagem['usuario_nome'] ?? $mensagem['autor_label'] ?? ''));
        $nome = $nome !== '' ? $nome : ($classe === 'cliente' ? 'Cliente' : 'Equipe');
        $createdAtLabel = (string) ($mensagem['created_at_label'] ?? $mensagem['time'] ?? '');

        if ($createdAtLabel === '' && ! empty($mensagem['created_at'])) {
            $createdAtLabel = self::formatarDataHora($mensagem['created_at']);
        }

        $payload = array_merge($mensagem, [
            'id' => $id,
            'message_id' => $id,
            'empresa_id' => $empresaId ?: null,
            'room' => $room,
            'room_scope' => $roomScope,
            'class' => $classe,
            'css_class' => $classe,
            'actor' => $actor,
            'origem' => $classe === 'cliente' ? 'cliente' : 'interno',
            'author' => $nome,
            'nome' => $nome,
            'usuario_nome' => $nome,
            'text' => $texto,
            'mensagem' => $texto,
            'mensagem_texto' => $texto,
            'time' => $createdAtLabel !== '' ? $createdAtLabel : 'agora',
            'created_at_label' => $createdAtLabel !== '' ? $createdAtLabel : 'agora',
            'attachments' => $attachments,
            'anexos' => $attachments,
            'has_attachments' => $attachments !== [],
        ]);

        if ($empresaId > 0 && $room !== '' && $id > 0) {
            $payload['server_signature'] = self::messageSignature($empresaId, $room, $actor, $id);
        }

        return $payload;
    }

    public static function messageSignature(int $empresaId, string $room, string $actor, int $messageId): string
    {
        return hash_hmac('sha256', $empresaId . '|' . $room . '|' . $actor . '|' . $messageId, (string) config('app.key'));
    }

    public static function classeDaOrigem(string $origem): string
    {
        return in_array(self::normalizarOrigem($origem), ['cliente', 'portal_cliente', 'client', 'portal', 'publico'], true)
            ? 'cliente'
            : 'equipe';
    }

    public static function normalizarOrigem(string $origem): string
    {
        $origem = strtolower(trim($origem));

        return match ($origem) {
            'support', 'suporte', 'equipe', 'interno', 'admin', 'user' => 'interno',
            'portal', 'publico', 'public', 'client', 'portal_cliente' => 'cliente',
            default => $origem !== '' ? $origem : 'cliente',
        };
    }

    public static function normalizarAnexos(mixed $anexos): array
    {
        if (! is_iterable($anexos)) {
            return [];
        }

        return collect($anexos)
            ->map(function (mixed $anexo): array {
                $item = is_array($anexo) ? $anexo : [];
                $url = trim((string) ($item['url'] ?? $item['download_url'] ?? $item['preview_url'] ?? ''));
                $mime = trim((string) ($item['mime_type'] ?? $item['mime'] ?? ''));
                $size = $item['size'] ?? $item['size_label'] ?? $item['tamanho_label'] ?? null;

                return [
                    'url' => $url,
                    'preview_url' => (string) ($item['preview_url'] ?? ''),
                    'name' => trim((string) ($item['name'] ?? $item['nome'] ?? $item['nome_original'] ?? 'Anexo')) ?: 'Anexo',
                    'size' => is_numeric($size) ? self::formatarBytes((int) $size) : (string) ($size ?? ($mime !== '' ? $mime : 'arquivo')),
                    'mime_type' => $mime,
                    'mime' => $mime,
                    'is_image' => (bool) ($item['is_image'] ?? $item['is_imagem'] ?? str_starts_with($mime, 'image/')),
                ];
            })
            ->filter(fn (array $anexo): bool => $anexo['url'] !== '')
            ->values()
            ->all();
    }

    public static function removerBlocoAnexosMensagem(string $texto): string
    {
        if ($texto === '' || ! str_contains($texto, 'Anexos enviados:')) {
            return $texto;
        }

        $limpo = preg_replace('/\n?Anexos enviados:\s*(?:\n-\s*.+?(?:\r?\n|$))+/si', '', $texto) ?? $texto;

        return trim($limpo) !== '' ? trim($limpo) : 'Arquivo(s) enviado(s).';
    }

    public static function extrairAnexosMensagem(string $texto): array
    {
        if ($texto === '' || ! str_contains($texto, 'Anexos enviados:')) {
            return [];
        }

        preg_match_all('/^-\s*(.+?)\s*\|\s*(https?:\/\/\S+)(?:\s*\|\s*([^|\r\n]+))?(?:\s*\|\s*([^\r\n]+))?/mi', $texto, $matches, PREG_SET_ORDER);

        return collect($matches)
            ->map(function (array $match): array {
                $mime = trim((string) ($match[3] ?? ''));
                $size = (int) trim((string) ($match[4] ?? '0'));

                return [
                    'url' => trim((string) ($match[2] ?? '')),
                    'name' => trim((string) ($match[1] ?? 'Anexo')) ?: 'Anexo',
                    'size' => $size > 0 ? self::formatarBytes($size) : ($mime !== '' ? $mime : 'arquivo'),
                    'mime_type' => $mime,
                    'mime' => $mime,
                    'is_image' => str_starts_with($mime, 'image/'),
                ];
            })
            ->filter(fn (array $anexo): bool => $anexo['url'] !== '')
            ->values()
            ->all();
    }

    private static function formatarBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }

    private static function formatarDataHora(mixed $data): string
    {
        try {
            return Carbon::parse($data)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return 'agora';
        }
    }
}
