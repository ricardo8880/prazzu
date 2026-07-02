<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSidebarFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_key',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'position' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
