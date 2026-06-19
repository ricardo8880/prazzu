<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrazzuRole extends Model
{
    protected $table = 'prazzu_roles';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    public function permissions()
    {
        return $this->hasMany(PrazzuPermission::class, 'role_id');
    }

    public function userRoles()
    {
        return $this->hasMany(PrazzuUserRole::class, 'role_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'prazzu_user_roles', 'role_id', 'user_id')->withTimestamps();
    }
}
