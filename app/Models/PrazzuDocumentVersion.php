<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrazzuDocumentVersion extends Model
{
    protected $table = 'prazzu_document_versions';

    protected $fillable = [
        'item_controle_id',
        'document_type',
        'version_number',
        'file_path',
        'uploaded_by',
        'status',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'item_controle_id' => 'integer',
        'version_number' => 'integer',
        'uploaded_by' => 'integer',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function itemControle(): BelongsTo
    {
        return $this->belongsTo(ItemControle::class, 'item_controle_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
