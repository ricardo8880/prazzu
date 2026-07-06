<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\DocumentStorage;

class ItemControleAnexo extends Model
{
    protected $table = 'item_controle_anexos';

    protected $fillable = [
        'item_controle_id',
        'user_id',
        'arquivo',
        'caminho',
        'categoria',
        'nome_original',
        'mime_type',
        'tamanho_bytes',
        'observacao',
    ];

    public function getCaminhoArquivo(): ?string
    {
        $caminho = $this->arquivo ?: $this->caminho;

        return filled($caminho) ? (string) $caminho : null;
    }

    public function getNomeExibicao(): string
    {
        $caminho = $this->getCaminhoArquivo();

        return $this->nome_original ?: ($caminho ? basename($caminho) : 'Anexo #'.$this->id);
    }

    public function getUrl(): ?string
    {
        $caminho = $this->getCaminhoArquivo();

        return DocumentStorage::publicUrl($caminho);
    }

    public function getTamanhoFormatado(): string
    {
        $bytes = (int) ($this->tamanho_bytes ?? 0);

        if ($bytes <= 0) {
            return '-';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }

    public function isPreviewable(): bool
    {
        $mime = strtolower((string) $this->mime_type);
        $nome = strtolower($this->getNomeExibicao());

        return str_starts_with($mime, 'image/')
            || $mime === 'application/pdf'
            || str_ends_with($nome, '.pdf')
            || preg_match('/\.(jpg|jpeg|png|webp)$/', $nome) === 1;
    }

    public function itemControle(): BelongsTo
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}