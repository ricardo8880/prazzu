<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Responsavel extends Model
{
    protected $table = 'responsaveis';

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'cargo',
        'empresa_id',
        'user_id',
        'gestor_user_id',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->empresa_id && auth()->check()) {
                $model->empresa_id = auth()->user()->empresa_id;
            }
        });
    }

    protected $casts = [
        'empresa_id' => 'integer',
        'user_id' => 'integer',
        'gestor_user_id' => 'integer',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function itemControles(): HasMany
    {
        return $this->hasMany(ItemControle::class, 'responsavel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestor_user_id');
    }
}
