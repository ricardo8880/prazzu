<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatorioPersonalizadoColuna extends Model
{
    protected $table = 'relatorios_personalizados_colunas';

    protected $fillable = [
        'relatorio_id',
        'campo',
        'rotulo',
        'tipo',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    public function relatorio()
    {
        return $this->belongsTo(RelatorioPersonalizado::class, 'relatorio_id');
    }
}
