<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PrazzuCustomField extends Model
{
    protected $table = 'prazzu_custom_fields';

    protected $fillable = [
        'module',
        'field_key',
        'field_label',
        'field_type',
        'options',
        'required',
        'active',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'active' => 'boolean',
    ];

    public function getNameAttribute(): ?string
    {
        return $this->field_key;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['field_key'] = $value ? Str::slug($value, '_') : null;
    }

    public function getLabelAttribute(): ?string
    {
        return $this->field_label;
    }

    public function setLabelAttribute(?string $value): void
    {
        $this->attributes['field_label'] = $value;

        if (empty($this->attributes['field_key']) && $value) {
            $this->attributes['field_key'] = Str::slug($value, '_');
        }
    }

    public function getTypeAttribute(): ?string
    {
        return $this->field_type;
    }

    public function setTypeAttribute(?string $value): void
    {
        $this->attributes['field_type'] = $value ?: 'text';
    }
}
