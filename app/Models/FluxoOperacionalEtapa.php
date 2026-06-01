<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FluxoOperacionalEtapa extends Model
{
    protected $table = 'fluxos_operacionais_etapas';

    protected $fillable = [
        'fluxo_operacional_id',
        'nome',
        'descricao',
        'ordem',
        'prazo_horas',
        'responsavel_padrao_id',
        'exige_aprovacao',
        'ativo',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'prazo_horas' => 'integer',
        'exige_aprovacao' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function fluxo()
    {
        return $this->belongsTo(FluxoOperacional::class, 'fluxo_operacional_id');
    }

    public function responsavelPadrao()
    {
        return $this->belongsTo(Responsavel::class, 'responsavel_padrao_id');
    }
}
