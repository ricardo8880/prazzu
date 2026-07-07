#!/usr/bin/env php
<?php

/**
 * Lote 2 - Banco oficial e consistência de schema.
 *
 * Este projeto usa SQL manual/dump oficial, não migrations Laravel.
 * O script valida o baseline em database/sql/prazzu_schema_oficial.sql,
 * os arquivos operacionais do lote e, quando possível, compara com o banco real.
 */

$basePath = dirname(__DIR__);
$errors = [];
$warnings = [];
$ok = [];

function add_ok(bool $condition, string $success, string $failure, array &$ok, array &$errors): void
{
    if ($condition) {
        $ok[] = $success;
        return;
    }

    $errors[] = $failure;
}

function add_warn(bool $condition, string $success, string $failure, array &$ok, array &$warnings): void
{
    if ($condition) {
        $ok[] = $success;
        return;
    }

    $warnings[] = $failure;
}

function parse_env_file(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $env[trim($key)] = trim(trim($value), "\"'");
    }

    return $env;
}

function sql_tables(string $sql): array
{
    preg_match_all('/CREATE\s+TABLE\s+`([^`]+)`/i', $sql, $matches);
    $tables = array_values(array_unique($matches[1] ?? []));
    sort($tables);
    return $tables;
}

function sql_table_block(string $sql, string $table): string
{
    if (preg_match('/CREATE\s+TABLE\s+`' . preg_quote($table, '/') . '`\s*\((.*?)\)\s*ENGINE/is', $sql, $match)) {
        return $match[1];
    }

    return '';
}

function sql_table_has_columns(string $sql, string $table, array $columns): array
{
    $block = sql_table_block($sql, $table);
    $missing = [];

    foreach ($columns as $column) {
        if ($block === '' || ! preg_match('/`' . preg_quote($column, '/') . '`/i', $block)) {
            $missing[] = $column;
        }
    }

    return $missing;
}

function sql_indexes_for_table(string $sql, string $table): array
{
    $block = sql_table_block($sql, $table);
    $indexes = [];

    foreach (preg_split('/,\s*\n/', $block) ?: [] as $line) {
        $line = trim($line);
        if (preg_match('/^(UNIQUE\s+)?KEY\s+`([^`]+)`\s+\(([^)]+)\)/i', $line, $match)) {
            $columns = preg_replace('/\s+/', '', $match[3]);
            $indexes[$match[2]] = $columns;
        }
    }

    return $indexes;
}

function find_duplicate_tables(array $tables): array
{
    $duplicates = [];
    $groups = [
        ['automation_rules', 'prazzu_automation_rules'],
        ['sla_rules', 'prazzu_sla_rules'],
        ['document_versions', 'prazzu_document_versions'],
        ['task_comments', 'prazzu_task_comments'],
        ['task_dependencies', 'prazzu_task_dependencies'],
        ['task_subtasks', 'prazzu_task_subtasks'],
        ['item_controle_timeline', 'item_controle_timelines'],
        ['backup_client_portal_messages', 'backup_prazzu_client_messages'],
    ];

    foreach ($groups as $group) {
        $present = array_values(array_intersect($group, $tables));
        if (count($present) > 1) {
            $duplicates[] = implode(' / ', $present);
        }
    }

    return $duplicates;
}

$schemaPath = $basePath . '/database/sql/prazzu_schema_oficial.sql';
$schemaSql = is_file($schemaPath) ? (file_get_contents($schemaPath) ?: '') : '';
$tables = $schemaSql !== '' ? sql_tables($schemaSql) : [];

add_ok(is_file($schemaPath), 'Schema oficial encontrado: database/sql/prazzu_schema_oficial.sql', 'Schema oficial ausente: database/sql/prazzu_schema_oficial.sql', $ok, $errors);
add_ok(count($tables) >= 108, 'Schema oficial contém pelo menos 108 tabelas.', 'Schema oficial contém menos de 108 tabelas. Total encontrado: ' . count($tables), $ok, $errors);

$requiredFiles = [
    'database/sql/README.md',
    'database/sql/changelog.md',
    'database/sql/lote_02_integridade_banco.sql',
    'scripts/backup-database.php',
    'scripts/restore-database.php',
    'docs/lotes/lote-02-banco.md',
];

foreach ($requiredFiles as $file) {
    add_ok(is_file($basePath . '/' . $file), "Arquivo do Lote 2 encontrado: {$file}", "Arquivo obrigatório ausente: {$file}", $ok, $errors);
}

$requiredTables = [
    'users',
    'empresas',
    'responsaveis',
    'item_controles',
    'portal_mensagens',
    'portal_documentos',
    'asaas_webhook_events',
    'assinaturas',
    'pagamentos',
    'auditoria_detalhada',
    'activity_log',
    'sessions',
    'jobs',
    'failed_jobs',
];

foreach ($requiredTables as $table) {
    add_ok(in_array($table, $tables, true), "Tabela crítica presente no schema oficial: {$table}", "Tabela crítica ausente no schema oficial: {$table}", $ok, $errors);
}

$requiredColumns = [
    'users' => ['id', 'name', 'email', 'empresa_id', 'password'],
    'empresas' => ['id', 'razao_social', 'status', 'created_at', 'updated_at'],
    'item_controles' => ['id', 'empresa_id', 'status', 'data_vencimento', 'portal_token', 'sla_limite_em', 'responsavel_id'],
    'portal_mensagens' => ['id', 'empresa_id', 'item_controle_id', 'created_at'],
    'portal_documentos' => ['id', 'empresa_id', 'item_controle_id', 'created_at'],
    'asaas_webhook_events' => ['id', 'payload_hash', 'gateway_payment_id', 'gateway_subscription_id', 'status', 'payload', 'processed_at', 'failed_at'],
    'assinaturas' => ['id', 'empresa_id', 'gateway_customer_id', 'gateway_subscription_id', 'plano', 'status'],
    'pagamentos' => ['id', 'empresa_id', 'assinatura_id', 'gateway_payment_id', 'status', 'valor', 'vencimento'],
    'auditoria_detalhada' => ['id', 'empresa_id', 'user_id', 'auditable_type', 'auditable_id', 'evento', 'campo'],
];

foreach ($requiredColumns as $table => $columns) {
    $missing = sql_table_has_columns($schemaSql, $table, $columns);
    add_ok($missing === [], "Colunas críticas presentes em {$table}.", "Colunas ausentes em {$table}: " . implode(', ', $missing), $ok, $errors);
}

$itemIndexes = sql_indexes_for_table($schemaSql, 'item_controles');
add_warn(count($itemIndexes) <= 40, 'Quantidade de índices em item_controles está dentro do limite de atenção.', 'item_controles possui muitos índices no baseline: ' . count($itemIndexes) . '. Revisar antes de alto volume.', $ok, $warnings);

$duplicateTables = find_duplicate_tables($tables);
add_warn($duplicateTables === [], 'Nenhuma família de tabelas duplicadas/legadas crítica detectada.', 'Possíveis tabelas duplicadas/legadas para revisão manual: ' . implode('; ', $duplicateTables), $ok, $warnings);

$hasMigrations = is_dir($basePath . '/database/migrations') && count(glob($basePath . '/database/migrations/*.php') ?: []) > 0;
add_warn(! $hasMigrations, 'Nenhuma migration detectada como fonte de schema, conforme padrão atual do projeto.', 'Existem migrations em database/migrations; manter padrão SQL manual ou documentar exceção.', $ok, $warnings);

$env = parse_env_file($basePath . '/.env');
$canTryDb = extension_loaded('pdo_mysql')
    && ($env['DB_CONNECTION'] ?? 'mysql') === 'mysql'
    && ! empty($env['DB_DATABASE'])
    && ! empty($env['DB_USERNAME']);

if (! $canTryDb) {
    $warnings[] = 'Comparação com banco real não executada. Para habilitar, instale pdo_mysql e configure DB_* no .env.';
} else {
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $env['DB_HOST'] ?? '127.0.0.1',
            $env['DB_PORT'] ?? '3306',
            $env['DB_DATABASE']
        );
        $pdo = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query('SHOW TABLES');
        $liveTables = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        sort($liveTables);

        $missingLive = array_values(array_diff($requiredTables, $liveTables));
        add_ok($missingLive === [], 'Banco real contém todas as tabelas críticas.', 'Banco real não contém tabelas críticas: ' . implode(', ', $missingLive), $ok, $errors);
        add_warn(count($liveTables) >= 108, 'Banco real contém pelo menos 108 tabelas.', 'Banco real contém menos de 108 tabelas. Total encontrado: ' . count($liveTables), $ok, $warnings);

        foreach ($requiredColumns as $table => $columns) {
            if (! in_array($table, $liveTables, true)) {
                continue;
            }
            $placeholders = implode(',', array_fill(0, count($columns), '?'));
            $query = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME IN ({$placeholders})";
            $columnStmt = $pdo->prepare($query);
            $columnStmt->execute(array_merge([$table], $columns));
            $present = $columnStmt->fetchAll(PDO::FETCH_COLUMN);
            $missing = array_values(array_diff($columns, $present));
            add_ok($missing === [], "Banco real contém colunas críticas em {$table}.", "Banco real com colunas ausentes em {$table}: " . implode(', ', $missing), $ok, $errors);
        }
    } catch (Throwable $e) {
        $warnings[] = 'Não foi possível comparar com banco real: ' . $e->getMessage();
    }
}

$result = [
    'lote' => 2,
    'area' => 'banco_schema_oficial',
    'status' => empty($errors) ? 'ok' : 'erro',
    'schema_tables' => count($tables),
    'errors' => $errors,
    'warnings' => $warnings,
    'ok' => $ok,
    'checked_at' => date(DATE_ATOM),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit(empty($errors) ? 0 : 1);
