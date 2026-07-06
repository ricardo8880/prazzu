<?php

namespace App\Support;

use App\Models\ItemControle;
use App\Models\ItemControleAnexo;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ItemControleAnexoUploader
{
    public const MAX_SIZE_KB = DocumentStorage::MAX_SIZE_KB;

    public const ALLOWED_EXTENSIONS = DocumentStorage::ALLOWED_EXTENSIONS;

    public const ALLOWED_MIME_TYPES = DocumentStorage::ALLOWED_MIME_TYPES;

    /**
     * @param  array<int, string>  $arquivos
     * @return array<int, ItemControleAnexo>
     */
    public static function storeComplementares(ItemControle $item, User $user, array $arquivos, ?string $observacao = null): array
    {
        $criados = [];
        $arquivos = array_values(array_filter($arquivos));

        if ($arquivos === []) {
            throw ValidationException::withMessages([
                'arquivos' => 'Selecione ao menos um arquivo para anexar.',
            ]);
        }

        foreach ($arquivos as $arquivoPath) {
            $arquivoPath = DocumentStorage::normalizePath((string) $arquivoPath) ?: '';
            $nomeOriginal = basename($arquivoPath);
            $extension = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

            if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                self::safeDelete($arquivoPath);

                throw ValidationException::withMessages([
                    'arquivos' => 'O arquivo "'.$nomeOriginal.'" possui extensão inválida. Envie PDF, Word, Excel, CSV, TXT ou imagem.',
                ]);
            }

            if (! DocumentStorage::exists($arquivoPath)) {
                throw ValidationException::withMessages([
                    'arquivos' => 'Não foi possível localizar o arquivo enviado. Tente fazer o upload novamente.',
                ]);
            }

            $mimeType = null;
            $tamanho = null;

            try {
                $mimeType = Storage::disk(DocumentStorage::DISK)->mimeType($arquivoPath);
                $tamanho = Storage::disk(DocumentStorage::DISK)->size($arquivoPath);
            } catch (\Throwable) {
                $mimeType = null;
                $tamanho = null;
            }

            if ($tamanho !== null && $tamanho > (self::MAX_SIZE_KB * 1024)) {
                self::safeDelete($arquivoPath);

                throw ValidationException::withMessages([
                    'arquivos' => 'O arquivo "'.$nomeOriginal.'" ultrapassa o limite de 10 MB.',
                ]);
            }

            $dados = [
                'item_controle_id' => $item->id,
                'user_id' => $user->id,
                'arquivo' => $arquivoPath,
                'nome_original' => $nomeOriginal,
                'mime_type' => $mimeType,
                'tamanho_bytes' => $tamanho,
                'observacao' => $observacao,
            ];

            if (CachedSchema::hasColumn('item_controle_anexos', 'categoria')) {
                $dados['categoria'] = 'complementar';
            }

            if (CachedSchema::hasColumn('item_controle_anexos', 'caminho')) {
                $dados['caminho'] = $arquivoPath;
            }

            $anexo = ItemControleAnexo::query()->create($dados);
            $criados[] = $anexo;

            activity('item_controle')
                ->performedOn($item)
                ->causedBy($user)
                ->withProperties([
                    'attributes' => [
                        'arquivo' => $nomeOriginal,
                        'observacao' => $observacao,
                        'tipo' => 'anexo_complementar',
                    ],
                ])
                ->event('anexo_adicionado')
                ->log('Anexo complementar adicionado ao item');
        }

        return $criados;
    }

    public static function safeDelete(string $arquivoPath): void
    {
        try {
            if (DocumentStorage::exists($arquivoPath)) {
                Storage::disk(DocumentStorage::DISK)->delete(DocumentStorage::normalizePath($arquivoPath));
            }
        } catch (\Throwable) {
            // Evita quebrar a tela por falha ao remover arquivo temporário inválido.
        }
    }
}
