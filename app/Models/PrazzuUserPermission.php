<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrazzuUserPermission extends Model
{
    protected $table = 'prazzu_user_permissions';

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'scope',
        'allowed',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'allowed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
