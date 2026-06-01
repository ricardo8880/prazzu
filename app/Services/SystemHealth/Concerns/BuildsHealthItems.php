<?php

namespace App\Services\SystemHealth\Concerns;

trait BuildsHealthItems
{
    protected function ok(string $title, ?string $detail = null, array $context = [], ?string $action = null): array
    {
        return $this->item('ok', $title, $detail, $context, $action);
    }

    protected function warning(string $title, ?string $detail = null, array $context = [], ?string $action = null): array
    {
        return $this->item('warning', $title, $detail, $context, $action);
    }

    protected function error(string $title, ?string $detail = null, array $context = [], ?string $action = null): array
    {
        return $this->item('error', $title, $detail, $context, $action);
    }

    protected function item(string $status, string $title, ?string $detail = null, array $context = [], ?string $action = null): array
    {
        return [
            'status' => $status,
            'title' => $title,
            'detail' => $detail,
            'context' => $context,
            'action' => $action,
        ];
    }
}
