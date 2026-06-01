<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Concerns\BuildsHealthItems;
use App\Services\SystemHealth\HealthCheckContract;

class EnvironmentHealthCheck implements HealthCheckContract
{
    use BuildsHealthItems;

    public function key(): string { return 'environment'; }
    public function name(): string { return 'Ambiente'; }
    public function description(): string { return 'PHP, Laravel, chave da aplicação, URL e configurações sensíveis de produção.'; }

    public function run(int $limit = 500): array
    {
        $items = [];
        $items[] = version_compare(PHP_VERSION, '8.2.0', '>=')
            ? $this->ok('PHP compatível.', 'Versão atual: '.PHP_VERSION)
            : $this->error('PHP abaixo do recomendado.', 'Versão atual: '.PHP_VERSION, [], 'Atualize o servidor para PHP 8.2 ou superior.');

        foreach (['bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml'] as $extension) {
            $loaded = extension_loaded($extension);
            $critical = in_array($extension, ['mbstring', 'openssl', 'pdo', 'fileinfo'], true);
            $items[] = $loaded
                ? $this->ok("Extensão {$extension} carregada.")
                : ($critical
                    ? $this->error("Extensão {$extension} ausente.", null, [], "Ative a extensão PHP {$extension} no php.ini/servidor.")
                    : $this->warning("Extensão {$extension} ausente.", null, [], "Ative a extensão PHP {$extension} para evitar limitações em recursos específicos."));
        }

        $items[] = config('app.key')
            ? $this->ok('APP_KEY configurada.')
            : $this->error('APP_KEY ausente.', null, [], 'Execute php artisan key:generate e atualize o .env.');

        $items[] = config('app.url')
            ? $this->ok('APP_URL configurada.', (string) config('app.url'))
            : $this->error('APP_URL ausente.', null, [], 'Configure APP_URL com o domínio real do sistema.');

        if (app()->environment('production')) {
            $items[] = ! config('app.debug')
                ? $this->ok('APP_DEBUG desativado em produção.')
                : $this->error('APP_DEBUG ativo em produção.', null, [], 'Defina APP_DEBUG=false para não expor erros internos.');
        } else {
            $items[] = $this->warning('APP_ENV não está como production.', 'Esperado em desenvolvimento/homologação; revise antes de publicar.', ['env' => app()->environment()]);
        }

        return $items;
    }
}
