<?php

/**
 * Lote 02 - Verificador de aderência banco x Models.
 * Uso:
 *   php scripts/check-database.php /caminho/para/dump.sql
 *
 * Este script não conecta no banco, não altera dados e não executa migrations.
 */

$dumpPath = $argv[1] ?? __DIR__ . '/../prazzu-03-07-26.sql';
$basePath = dirname(__DIR__);

if (! is_file($dumpPath)) {
    fwrite(STDERR, "Dump SQL não encontrado: {$dumpPath}\n");
    exit(1);
}

$sql = file_get_contents($dumpPath);
preg_match_all('/CREATE TABLE `([^`]+)`/i', $sql, $tableMatches);
preg_match_all('/CREATE\s+(?:ALGORITHM=.*?\s+)?(?:DEFINER=.*?\s+)?(?:SQL SECURITY .*?\s+)?VIEW `([^`]+)`/i', $sql, $viewMatches);
$schemaObjects = array_values(array_unique(array_merge($tableMatches[1] ?? [], $viewMatches[1] ?? [])));
sort($schemaObjects);

$modelDir = $basePath . '/app/Models';
$modelFiles = glob($modelDir . '/*.php') ?: [];

$snakePlural = static function (string $class): string {
    $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class));
    return str_ends_with($snake, 'y') ? substr($snake, 0, -1) . 'ies' : $snake . 's';
};

$models = [];
$missing = [];
foreach ($modelFiles as $file) {
    $content = file_get_contents($file);
    $class = basename($file, '.php');
    $table = null;
    if (preg_match('/protected\s+\$table\s*=\s*[\'\"]([^\'\"]+)/', $content, $m)) {
        $table = $m[1];
    } else {
        $table = $snakePlural($class);
    }

    $exists = in_array($table, $schemaObjects, true);
    $row = [
        'model' => $class,
        'table' => $table,
        'table_declared' => str_contains($content, 'protected $table'),
        'exists_in_dump' => $exists,
    ];
    $models[] = $row;
    if (! $exists) {
        $missing[] = $row;
    }
}

$result = [
    'dump' => basename($dumpPath),
    'tables_or_views_found' => count($schemaObjects),
    'models_found' => count($models),
    'missing_model_tables' => $missing,
    'status' => count($missing) === 0 ? 'ok' : 'attention_required',
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
exit(count($missing) === 0 ? 0 : 2);
