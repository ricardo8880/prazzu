<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrazzuUserRole extends Model
{
    protected $table = 'prazzu_user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(PrazzuRole::class, 'role_id');
    }
}
