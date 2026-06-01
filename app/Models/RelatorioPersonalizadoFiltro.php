<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelatorioPersonalizadoFiltro extends Model
{
    protected $table = 'relatorios_personalizados_filtros';

    protected $fillable = [
        'relatorio_id',
        'campo',
        'operador',
        'valor_padrao',
        'rotulo',
        'ordem',
        'ativo',
        'obrigatorio',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'obrigatorio' => 'boolean',
        'ordem' => 'integer',
    ];

    public function relatorio()
    {
        return $this->belongsTo(RelatorioPersonalizado::class, 'relatorio_id');
    }
}
