#!/usr/bin/env php
<?php

/**
 * Lote 2 - Banco oficial e consistência de schema.
 *
 * Este projeto usa SQL manual/dump oficial, não migrations.
 * O script valida o dump oficial em database/sql/prazzu_schema_oficial.sql
 * e, se houver PDO MySQL disponível + .env configurado, compara com o banco real.
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
        $value = trim($value);
        $value = trim($value, "\"'");
        $env[trim($key)] = $value;
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

$schemaPath = $basePath . '/database/sql/prazzu_schema_oficial.sql';
$schemaSql = is_file($schemaPath) ? (file_get_contents($schemaPath) ?: '') : '';
$tables = $schemaSql !== '' ? sql_tables($schemaSql) : [];

add_ok(is_file($schemaPath), 'Schema oficial encontrado: database/sql/prazzu_schema_oficial.sql', 'Schema oficial ausente: database/sql/prazzu_schema_oficial.sql', $ok, $errors);
add_ok(count($tables) >= 108, 'Schema oficial contém pelo menos 108 tabelas.', 'Schema oficial contém menos de 108 tabelas. Total encontrado: ' . count($tables), $ok, $errors);

$requiredSqlFiles = [
    'database/sql/README.md',
    'database/sql/lote_10_financeiro_asaas.sql',
    'database/sql/lote_13_performance.sql',
    'database/sql/lote_14_qualidade_testes.sql',
];

foreach ($requiredSqlFiles as $file) {
    add_ok(is_file($basePath . '/' . $file), "Arquivo SQL/documentação encontrado: {$file}", "Arquivo obrigatório ausente: {$file}", $ok, $errors);
}

$requiredTables = [
    'users',
    'empresas',
    'item_controles',
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
    'item_controles' => ['empresa_id', 'status', 'data_vencimento', 'portal_token', 'sla_limite_em', 'responsavel_id'],
    'asaas_webhook_events' => ['payload_hash', 'gateway_payment_id', 'gateway_subscription_id', 'status', 'payload', 'processed_at', 'failed_at'],
    'assinaturas' => ['empresa_id', 'gateway_customer_id', 'gateway_subscription_id', 'plano', 'status'],
    'pagamentos' => ['empresa_id', 'assinatura_id', 'gateway_payment_id', 'status', 'valor', 'vencimento'],
    'auditoria_detalhada' => ['empresa_id', 'user_id', 'auditable_type', 'auditable_id', 'evento', 'campo'],
];

foreach ($requiredColumns as $table => $columns) {
    $missing = sql_table_has_columns($schemaSql, $table, $columns);
    add_ok($missing === [], "Colunas críticas presentes em {$table}.", "Colunas ausentes em {$table}: " . implode(', ', $missing), $ok, $errors);
}

$hasMigrations = is_dir($basePath . '/database/migrations') && count(glob($basePath . '/database/migrations/*.php') ?: []) > 0;
add_warn(! $hasMigrations, 'Nenhuma migration detectada como fonte de schema.', 'Existem migrations em database/migrations; manter o padrão do projeto usando SQL manual/dump oficial.', $ok, $warnings);

$env = parse_env_file($basePath . '/.env');
$canTryDb = extension_loaded('pdo_mysql')
    && ($env['DB_CONNECTION'] ?? 'mysql') === 'mysql'
    && ! empty($env['DB_DATABASE'])
    && ! empty($env['DB_USERNAME']);

if (! $canTryDb) {
    $warnings[] = 'Comparação com banco real não executada. Para habilitar, instale pdo_mysql e configure DB_* no .env.';
} else {
    try {
        $host = $env['DB_HOST'] ?? '127.0.0.1';
        $port = $env['DB_PORT'] ?? '3306';
        $database = $env['DB_DATABASE'];
        $username = $env['DB_USERNAME'];
        $password = $env['DB_PASSWORD'] ?? '';
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $stmt = $pdo->query('SHOW TABLES');
        $liveTables = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        sort($liveTables);

        $missingLive = array_values(array_diff($requiredTables, $liveTables));
        add_ok($missingLive === [], 'Banco real contém todas as tabelas críticas.', 'Banco real não contém tabelas críticas: ' . implode(', ', $missingLive), $ok, $errors);
        add_warn(count($liveTables) >= 108, 'Banco real contém pelo menos 108 tabelas.', 'Banco real contém menos de 108 tabelas. Total encontrado: ' . count($liveTables), $ok, $warnings);
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
