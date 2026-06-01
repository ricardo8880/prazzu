<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiMarketSource extends Model
{
    protected $table = 'ai_market_sources';

    protected $fillable = [
        'name',
        'competitor_name',
        'source_type',
        'source_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(AiMarketComment::class, 'source_id');
    }
}
