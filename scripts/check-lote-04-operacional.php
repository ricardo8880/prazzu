<?php

$base = dirname(__DIR__);
$requiredFiles = [
    'app/Services/ItemControleOperationalService.php',
    'app/Filament/Pages/Pendencias.php',
    'app/Filament/Pages/CentroOperacional.php',
    'app/Filament/Pages/Calendario.php',
    'app/Filament/Pages/Kanban.php',
    'app/Filament/Pages/Projetos.php',
    'app/Filament/Pages/SlaPrazos.php',
    'app/Filament/Resources/ItemControles/Tables/ItemControlesTable.php',
];

$errors = [];
foreach ($requiredFiles as $file) {
    $path = $base . DIRECTORY_SEPARATOR . $file;
    if (! is_file($path)) {
        $errors[] = "Arquivo obrigatório ausente: {$file}";
        continue;
    }

    $output = [];
    $code = 0;
    exec('php -l ' . escapeshellarg($path) . ' 2>&1', $output, $code);
    if ($code !== 0) {
        $errors[] = "php -l falhou em {$file}: " . implode(' | ', $output);
    }
}

$service = file_get_contents($base . '/app/Services/ItemControleOperationalService.php');
foreach (['criarPendencia', 'concluir', 'alterarStatus', 'alterarPrazo', 'alterarResponsavel', 'atualizarSituacao', 'withSlaStatus'] as $method) {
    if (! str_contains($service, 'function ' . $method . '(')) {
        $errors[] = "Método obrigatório ausente no serviço operacional: {$method}";
    }
}

$integrationChecks = [
    'app/Filament/Pages/Pendencias.php' => ['criarPendencia(', '->concluir('],
    'app/Filament/Pages/CentroOperacional.php' => ['->concluir(', '->alterarPrazo(', '->alterarResponsavel(', '->atualizarSituacao('],
    'app/Filament/Pages/Calendario.php' => ['->alterarStatus('],
    'app/Filament/Pages/Kanban.php' => ['->alterarStatus('],
    'app/Filament/Pages/Projetos.php' => ['->alterarStatus('],
    'app/Filament/Resources/ItemControles/Tables/ItemControlesTable.php' => ['->concluir(', '->alterarResponsavel('],
    'app/Filament/Pages/SlaPrazos.php' => ['PrazzuSlaEngine::statusAbertos()', 'data_vencimento'],
];

foreach ($integrationChecks as $file => $needles) {
    $contents = file_get_contents($base . '/' . $file);
    foreach ($needles as $needle) {
        if (! str_contains($contents, $needle)) {
            $errors[] = "Integração esperada não encontrada em {$file}: {$needle}";
        }
    }
}

if ($errors) {
    echo json_encode(['ok' => false, 'errors' => $errors], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(1);
}

echo json_encode([
    'ok' => true,
    'checked_files' => count($requiredFiles),
    'domain' => 'lote_04_sla_pendencias_responsaveis',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
