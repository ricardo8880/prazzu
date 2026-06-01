<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProductImprovementResolution extends Model
{
    protected $table = 'ai_product_improvement_resolutions';

    protected $fillable = [
        'item_key',
        'item_type',
        'item_name',
        'resolved_by_user_id',
        'resolved_at',
        'notes',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }
}
