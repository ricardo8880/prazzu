<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalDocumento extends Model
{
    protected $table = 'portal_documentos';

    protected $fillable = [
        'empresa_id',
        'item_controle_id',
        'titulo',
        'tipo',
        'conteudo',
        'url',
        'arquivo',
        'visivel_cliente',
        'criado_por',
    ];

    protected $casts = [
        'empresa_id' => 'integer',
        'item_controle_id' => 'integer',
        'visivel_cliente' => 'boolean',
        'criado_por' => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function itemControle(): BelongsTo
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }
}
