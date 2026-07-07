<?php

$root = dirname(__DIR__);
$errors = [];

$requiredFiles = [
    'app/Services/ItemControleFluxoService.php',
    'app/Services/ItemControleCoreService.php',
    'app/Models/ItemControleTimeline.php',
    'docs/lotes/lote-04-fluxo-operacional.md',
];

foreach ($requiredFiles as $file) {
    if (! is_file($root . DIRECTORY_SEPARATOR . $file)) {
        $errors[] = "Arquivo obrigatório ausente: {$file}";
    }
}

$fluxo = file_get_contents($root . '/app/Services/ItemControleFluxoService.php');
$core = file_get_contents($root . '/app/Services/ItemControleCoreService.php');
$timeline = file_get_contents($root . '/app/Models/ItemControleTimeline.php');

$requiredMethods = [
    'atualizarStatus',
    'atualizarPrazo',
    'atribuirResponsavel',
    'registrarEvidencia',
    'solicitarAprovacao',
    'aprovar',
    'reprovar',
    'concluir',
    'reabrir',
];

foreach ($requiredMethods as $method) {
    if (! str_contains($fluxo, "function {$method}(")) {
        $errors[] = "Método obrigatório ausente no ItemControleFluxoService: {$method}";
    }
}

$requiredFragments = [
    'DB::transaction',
    'validarTransicao',
    'status_operacional_at',
    'possuiAprovacaoPendente',
    'getChecklistPercentual',
    'hasAnexoPrincipal',
    'canBeApprovedBy',
    'canBeModifiedBy',
];

foreach ($requiredFragments as $fragment) {
    if (! str_contains($fluxo, $fragment)) {
        $errors[] = "Fragmento esperado ausente no fluxo operacional: {$fragment}";
    }
}

if (! str_contains($core, 'app(ItemControleFluxoService::class)->atualizarStatus')) {
    $errors[] = 'ItemControleCoreService::transitionStatus não está delegando para ItemControleFluxoService.';
}

foreach (['status_operacional', 'prazo', 'responsavel', 'evidencia', 'reabertura'] as $timelineType) {
    if (! str_contains($timeline, "'{$timelineType}'")) {
        $errors[] = "Tipo de timeline não registrado: {$timelineType}";
    }
}

$phpFiles = [
    'app/Services/ItemControleFluxoService.php',
    'app/Services/ItemControleCoreService.php',
    'app/Models/ItemControleTimeline.php',
    'scripts/check-lote-04-fluxo-operacional.php',
];

foreach ($phpFiles as $file) {
    $cmd = 'php -l ' . escapeshellarg($root . DIRECTORY_SEPARATOR . $file) . ' 2>&1';
    exec($cmd, $output, $code);

    if ($code !== 0) {
        $errors[] = "PHP lint falhou em {$file}: " . implode(' ', $output);
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Lote 04 falhou:\n- " . implode("\n- ", $errors) . "\n");
    exit(1);
}

echo "Lote 04 OK: fluxo operacional validado.\n";
