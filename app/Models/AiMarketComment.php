<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMarketComment extends Model
{
    protected $table = 'ai_market_comments';

    protected $fillable = [
        'source_id',
        'competitor_name',
        'rating',
        'language',
        'original_text',
        'detected_sentiment',
        'detected_category',
        'detected_problem',
        'detected_opportunity',
        'detected_real_pain',
        'detected_impact',
        'recommended_action',
        'metadata',
    ];

    protected $casts = [
        'rating' => 'integer',
        'detected_impact' => 'integer',
        'metadata' => 'array',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(AiMarketSource::class, 'source_id');
    }
}
