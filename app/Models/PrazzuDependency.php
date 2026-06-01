<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrazzuDependency extends Model
{
    protected $table = 'prazzu_dependencies';

    protected $fillable = [
        'item_controle_id',
        'depends_on_item_controle_id',
        'type',
        'notes',
        'blocked_until_resolved',
    ];

    protected $casts = [
        'blocked_until_resolved' => 'boolean',
    ];

    public function itemControle(): BelongsTo
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function dependsOnItem(): BelongsTo
    {
        return $this->belongsTo(ItemControle::class, 'depends_on_item_controle_id');
    }
}
