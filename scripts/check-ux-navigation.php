<?php

$root = dirname(__DIR__);
$requiredFiles = [
    'app/Support/PrazzuUxNavigation.php',
    'app/Support/PrazzuTopNavigation.php',
    'app/Filament/Pages/Home.php',
    'resources/views/components/top-navigation.blade.php',
    'resources/views/filament/pages/home.blade.php',
    'public/css/style.css',
];

$missing = [];
foreach ($requiredFiles as $file) {
    if (! file_exists($root . DIRECTORY_SEPARATOR . $file)) {
        $missing[] = $file;
    }
}

$checks = [
    'mapa_ux_centralizado' => str_contains(file_get_contents($root . '/app/Support/PrazzuUxNavigation.php'), 'class PrazzuUxNavigation'),
    'topo_usa_mapa_ux' => str_contains(file_get_contents($root . '/app/Support/PrazzuTopNavigation.php'), 'PrazzuUxNavigation::topSections'),
    'home_recebe_jornada_ux' => str_contains(file_get_contents($root . '/app/Filament/Pages/Home.php'), "'uxNavigation'"),
    'home_renderiza_mapa_rapido' => str_contains(file_get_contents($root . '/resources/views/filament/pages/home.blade.php'), 'Mapa rápido do sistema'),
    'css_lote_12_presente' => str_contains(file_get_contents($root . '/public/css/style.css'), 'Lote 12 - UX e navegação'),
];

$result = [
    'lote' => '12 - UX e navegação',
    'missing_files' => $missing,
    'checks' => $checks,
    'ok' => empty($missing) && ! in_array(false, $checks, true),
    'observacao' => 'Não executa Artisan; valida presença de arquivos e amarrações de UX centralizadas.',
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
exit($result['ok'] ? 0 : 1);
