<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Concerns\BuildsHealthItems;
use App\Services\SystemHealth\HealthCheckContract;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class ApplicationHealthCheck implements HealthCheckContract
{
    use BuildsHealthItems;

    public function key(): string { return 'application'; }
    public function name(): string { return 'Arquivos críticos'; }
    public function description(): string { return 'Presença de controllers, services, views e rotas essenciais para os fluxos principais.'; }

    public function run(int $limit = 500): array
    {
        $items = [];

        foreach ([
            'app/Http/Controllers/PortalItemControleController.php',
            'app/Http/Controllers/PortalClientePublicoController.php',
            'app/Http/Controllers/AsaasWebhookController.php',
            'app/Http/Controllers/BillingController.php',
            'app/Services/AsaasService.php',
            'app/Services/GlobalSearchService.php',
            'resources/views/portal/item-controle-show.blade.php',
            'resources/views/components/global-search.blade.php',
        ] as $relativePath) {
            $items[] = File::exists(base_path($relativePath))
                ? $this->ok("Arquivo crítico presente: {$relativePath}.")
                : $this->error("Arquivo crítico ausente: {$relativePath}.", null, [], 'Restaure o arquivo antes de publicar.');
        }

        foreach (['admin.global-search', 'billing.cancelar', 'asaas.webhook'] as $routeName) {
            $items[] = Route::has($routeName)
                ? $this->ok("Rota {$routeName} disponível.")
                : $this->warning("Rota {$routeName} não registrada.", null, [], 'Registre a rota se o fluxo estiver ativo no sistema.');
        }

        $items[] = class_exists(\App\Filament\Pages\SystemHealthDashboard::class)
            ? $this->ok('Painel administrativo de saúde registrado no código.')
            : $this->error('Painel administrativo de saúde não encontrado.', null, [], 'Verifique o arquivo app/Filament/Pages/SystemHealthDashboard.php.');

        return $items;
    }
}
