<?php

namespace App\Models;

use App\Services\PrazzuTemplateApplicationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class PrazzuTemplate extends Model
{
    protected $table = 'prazzu_templates';

    protected $fillable = [
        'module',
        'name',
        'description',
        'payload',
        'active',
    ];

    protected $casts = [
        'payload' => 'array',
        'active' => 'boolean',
    ];

    public function getTasksCountAttribute(): int
    {
        return count($this->payloadValue('tasks', []));
    }

    public function getAutomationsCountAttribute(): int
    {
        return count($this->payloadValue('automations', []));
    }

    public function getCustomFieldsCountAttribute(): int
    {
        return count($this->payloadValue('custom_fields', []));
    }

    public function getViewsCountAttribute(): int
    {
        return count($this->payloadValue('views', []));
    }

    public function getChecklistCountAttribute(): int
    {
        $total = 0;

        foreach ($this->payloadValue('tasks', []) as $task) {
            $total += count(Arr::get($task, 'checklist', []));
        }

        return $total;
    }

    public function getBlueprintLabelAttribute(): string
    {
        $parts = [];

        if ($this->views_count > 0) {
            $parts[] = $this->views_count . ' visão(ões)';
        }

        if ($this->custom_fields_count > 0) {
            $parts[] = $this->custom_fields_count . ' campo(s)';
        }

        if ($this->automations_count > 0) {
            $parts[] = $this->automations_count . ' automação(ões)';
        }

        if ($this->tasks_count > 0) {
            $parts[] = $this->tasks_count . ' tarefa(s)';
        }

        return $parts ? implode(' • ', $parts) : 'Template sem estrutura definida';
    }

    public function payloadValue(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->payload ?? [], $key, $default);
    }

    public function instantiateFor(int $empresaId, ?int $responsavelId = null, ?Carbon $firstDueDate = null, ?int $userId = null): int
    {
        $summary = $this->instantiateDetailed([
            'empresa_id' => $empresaId,
            'responsavel_id' => $responsavelId,
            'data_inicio' => $firstDueDate ?: now(),
            'user_id' => $userId,
            'create_dependencies' => true,
            'create_documents' => true,
        ]);

        return (int) ($summary['items_created'] ?? 0);
    }

    public function instantiateDetailed(array $options): array
    {
        return app(PrazzuTemplateApplicationService::class)->apply($this, $options);
    }
}
