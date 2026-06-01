<?php

namespace App\Services;


use App\Support\CachedSchema;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PrazzuAutomationEngine
{
    private ?Collection $rules = null;

    /**
     * Carrega as regras uma única vez por execução do comando para evitar consulta repetida em cada item.
     */
    public function primeRules(): void
    {
        $this->rules = $this->loadRules();
    }

    public function runForItem(object|array $item): void
    {
        if (! CachedSchema::hasTable('prazzu_automation_rules') || ! CachedSchema::hasTable('item_controles')) {
            return;
        }

        $item = (array) $item;
        $rules = $this->rules ??= $this->loadRules();

        if ($rules->isEmpty()) {
            return;
        }

        foreach ($rules as $rule) {
            if (! $this->ruleAppliesToItem($rule, $item)) {
                continue;
            }

            $field = $rule->condition_field ?: $this->defaultConditionField($rule);
            $current = $item[$field] ?? null;

            if (! $this->matches($current, $rule->condition_operator, $rule->condition_value, $item, $rule)) {
                continue;
            }

            $this->executeAction($item, $rule);
        }
    }

    private function loadRules(): Collection
    {
        return DB::table('prazzu_automation_rules')
            ->where('active', 1)
            ->orderBy('id')
            ->get();
    }

    private function ruleAppliesToItem(object $rule, array $item): bool
    {
        $module = trim((string) ($rule->module ?? ''));

        if ($module === '' || in_array($module, ['global', 'item_controles', 'tarefas', 'pendencias'], true)) {
            return true;
        }

        return Str::lower((string) ($item['tipo'] ?? '')) === Str::lower($module);
    }

    private function defaultConditionField(object $rule): string
    {
        return match ((string) ($rule->trigger_type ?? '')) {
            'documento_vencendo', 'documento_vencido' => 'data_vencimento',
            'aprovacao_pendente' => 'approval_status',
            'assinatura_pendente' => 'signature_status',
            default => 'status',
        };
    }

    private function executeAction(array $item, object $rule): void
    {
        $itemId = $item['id'] ?? null;

        if (! $itemId) {
            return;
        }

        $action = (string) ($rule->action_type ?? '');
        $value = (string) ($rule->action_value ?? '');

        match ($action) {
            'status' => $this->updateItem($itemId, ['status' => $value]),
            'prioridade' => $this->updateItem($itemId, ['prioridade' => $value]),
            'responsavel', 'responsavel_id' => $this->updateItem($itemId, ['responsavel_id' => is_numeric($value) ? (int) $value : null]),
            'sla_status' => $this->updateItem($itemId, ['sla_status' => $value]),
            'portal_status' => $this->updateItem($itemId, ['portal_status' => $value]),
            'document_status' => $this->updateItem($itemId, ['document_status' => $value]),
            'approval_status' => $this->updateItem($itemId, ['approval_status' => $value]),
            'signature_status' => $this->updateItem($itemId, ['signature_status' => $value]),
            'notificacao', 'notificar' => $this->notify($item, $rule, $value),
            'timeline', 'historico' => $this->timeline($item, $rule, $value),
            'criar_pendencia' => $this->createPendingTask($item, $rule, $value),
            'cobrar_responsavel' => $this->chargeResponsible($item, $rule, $value),
            default => null,
        };
    }

    private function updateItem(int $itemId, array $payload): void
    {
        $payload = array_filter(
            $payload,
            fn (mixed $value, string $column): bool => filled($column) && CachedSchema::hasColumn('item_controles', $column),
            ARRAY_FILTER_USE_BOTH
        );

        if ($payload === []) {
            return;
        }

        $payload['updated_at'] = now();
        DB::table('item_controles')->where('id', $itemId)->update($payload);
    }

    private function matches(mixed $current, ?string $operator, mixed $expected, array $item = [], ?object $rule = null): bool
    {
        $operator = strtolower((string) ($operator ?: '='));
        $currentString = trim((string) $current);
        $expectedString = trim((string) $expected);

        if ($this->matchesBusinessTrigger($current, $operator, $expectedString, $item, $rule)) {
            return true;
        }

        return match ($operator) {
            '=', '==' => $currentString === $expectedString,
            '!=', '<>' => $currentString !== $expectedString,
            'contains' => Str::contains(Str::lower($currentString), Str::lower($expectedString)),
            'not_contains' => ! Str::contains(Str::lower($currentString), Str::lower($expectedString)),
            'empty', 'is_empty' => blank($currentString),
            'not_empty', 'filled' => filled($currentString),
            '>', 'greater_than' => is_numeric($current) && is_numeric($expected) && (float) $current > (float) $expected,
            '>=', 'greater_or_equal' => is_numeric($current) && is_numeric($expected) && (float) $current >= (float) $expected,
            '<', 'less_than' => is_numeric($current) && is_numeric($expected) && (float) $current < (float) $expected,
            '<=', 'less_or_equal' => is_numeric($current) && is_numeric($expected) && (float) $current <= (float) $expected,
            'date_before' => $this->isDate($currentString) && Carbon::parse($currentString)->lt($this->parseExpectedDate($expectedString)),
            'date_after' => $this->isDate($currentString) && Carbon::parse($currentString)->gt($this->parseExpectedDate($expectedString)),
            'date_until' => $this->isDate($currentString) && Carbon::parse($currentString)->betweenIncluded(now()->startOfDay(), now()->addDays((int) $expectedString)->endOfDay()),
            'date_overdue' => $this->isDate($currentString) && Carbon::parse($currentString)->lt(now()->startOfDay()),
            default => false,
        };
    }

    private function matchesBusinessTrigger(mixed $current, string $operator, string $expected, array $item, ?object $rule): bool
    {
        $trigger = (string) ($rule->trigger_type ?? '');
        $status = Str::lower((string) ($item['status'] ?? ''));
        $done = in_array($status, ['concluido', 'concluído', 'concluida', 'finalizado', 'cancelado'], true);

        if ($trigger === 'documento_vencendo') {
            $days = is_numeric($expected) ? (int) $expected : 30;

            return $this->isDate((string) $current)
                && ! $done
                && Carbon::parse((string) $current)->betweenIncluded(now()->startOfDay(), now()->addDays($days)->endOfDay());
        }

        if ($trigger === 'documento_vencido') {
            return $this->isDate((string) $current)
                && ! $done
                && Carbon::parse((string) $current)->lt(now()->startOfDay());
        }

        if ($trigger === 'aprovacao_pendente') {
            $approval = Str::lower((string) ($item['approval_status'] ?? $item['status'] ?? ''));

            return in_array($approval, ['pendente', 'em_aprovacao', 'aprovação', 'aprovacao'], true);
        }

        if ($trigger === 'assinatura_pendente') {
            $signature = Str::lower((string) ($item['signature_status'] ?? $item['status'] ?? ''));

            return in_array($signature, ['pendente', 'aguardando_assinatura', 'assinatura_pendente'], true);
        }

        return false;
    }

    private function parseExpectedDate(string $expected): Carbon
    {
        return match ($expected) {
            'today', 'hoje' => now()->startOfDay(),
            'tomorrow', 'amanha', 'amanhã' => now()->addDay()->startOfDay(),
            'yesterday', 'ontem' => now()->subDay()->startOfDay(),
            default => Carbon::parse($expected),
        };
    }

    private function isDate(string $value): bool
    {
        try {
            Carbon::parse($value);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function notify(array $item, object $rule, string $message): void
    {
        if (! CachedSchema::hasTable('notificacoes_internas')) {
            return;
        }

        $message = filled($message) ? $message : 'Existe uma ação automática pendente para este item.';
        $fingerprint = $this->fingerprint($item, $rule, 'notificacao');

        if ($this->alreadyExecutedToday($fingerprint)) {
            return;
        }

        $notificationId = DB::table('notificacoes_internas')->insertGetId([
            'item_controle_id' => $item['id'] ?? null,
            'empresa_id' => $item['empresa_id'] ?? null,
            'user_id' => $item['responsavel_id'] ?? null,
            'tipo' => 'automacao',
            'titulo' => $rule->name ?: 'Automação Prazzu',
            'mensagem' => $message,
            'lida' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logExecution($item, $rule, 'notificacao', $message, ['notificacao_id' => $notificationId, 'fingerprint' => $fingerprint]);
    }

    private function timeline(array $item, object $rule, string $message): void
    {
        if (! CachedSchema::hasTable('item_controle_timeline')) {
            return;
        }

        $message = filled($message) ? $message : 'Automação executada para este item.';
        $fingerprint = $this->fingerprint($item, $rule, 'timeline');

        if ($this->alreadyExecutedToday($fingerprint)) {
            return;
        }

        DB::table('item_controle_timeline')->insert([
            'item_controle_id' => $item['id'] ?? null,
            'empresa_id' => $item['empresa_id'] ?? null,
            'user_id' => null,
            'tipo' => 'automacao',
            'titulo' => $rule->name ?: 'Automação executada',
            'descricao' => $message,
            'dados' => json_encode(['origem' => 'prazzu_automation_rules', 'rule_id' => $rule->id ?? null], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->logExecution($item, $rule, 'timeline', $message, ['fingerprint' => $fingerprint]);
    }

    private function chargeResponsible(array $item, object $rule, string $message): void
    {
        $message = filled($message) ? $message : 'Responsável cobrado automaticamente por pendência operacional.';
        $this->notify($item, $rule, $message);
        $this->timeline($item, $rule, $message);
    }

    private function createPendingTask(array $item, object $rule, string $message): void
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return;
        }

        $fingerprint = $this->fingerprint($item, $rule, 'criar_pendencia');

        if ($this->alreadyExecutedToday($fingerprint)) {
            return;
        }

        $payload = [
            'empresa_id' => $item['empresa_id'] ?? null,
            'responsavel_id' => $item['responsavel_id'] ?? null,
            'titulo' => filled($message) ? $message : 'Pendência criada por automação',
            'descricao' => 'Criada automaticamente a partir do item #' . ($item['id'] ?? '-'),
            'status' => 'pendente',
            'prioridade' => $item['prioridade'] ?? 'media',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $payload = array_filter(
            $payload,
            fn (mixed $value, string $column): bool => CachedSchema::hasColumn('item_controles', $column),
            ARRAY_FILTER_USE_BOTH
        );

        DB::table('item_controles')->insert($payload);
        $this->logExecution($item, $rule, 'criar_pendencia', (string) ($payload['titulo'] ?? 'Pendência criada'), ['fingerprint' => $fingerprint]);
    }

    private function fingerprint(array $item, object $rule, string $action): string
    {
        return sha1(implode('|', [
            $item['id'] ?? 'sem-item',
            $rule->id ?? 'sem-regra',
            $action,
            now()->toDateString(),
        ]));
    }

    private function alreadyExecutedToday(string $fingerprint): bool
    {
        if (! CachedSchema::hasTable('prazzu_automation_executions')) {
            return false;
        }

        return DB::table('prazzu_automation_executions')
            ->where('fingerprint', $fingerprint)
            ->whereDate('created_at', now()->toDateString())
            ->exists();
    }

    private function logExecution(array $item, object $rule, string $action, string $message, array $payload = []): void
    {
        if (! CachedSchema::hasTable('prazzu_automation_executions')) {
            return;
        }

        DB::table('prazzu_automation_executions')->insert([
            'automation_rule_id' => $rule->id ?? null,
            'item_controle_id' => $item['id'] ?? null,
            'empresa_id' => $item['empresa_id'] ?? null,
            'action_type' => $action,
            'message' => mb_substr($message, 0, 1000),
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'fingerprint' => $payload['fingerprint'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
