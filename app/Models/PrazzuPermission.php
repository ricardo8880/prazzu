<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrazzuPermission extends Model
{
    protected $table = 'prazzu_permissions';

    protected $fillable = [
        'role_id',
        'name',
        'module',
        'action',
        'scope',
    ];

    public function role()
    {
        return $this->belongsTo(PrazzuRole::class, 'role_id');
    }

    public function getCodeAttribute(): string
    {
        return trim((string) $this->module) . '.' . trim((string) $this->action);
    }
}
