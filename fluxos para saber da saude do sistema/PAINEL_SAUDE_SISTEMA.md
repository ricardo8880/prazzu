# Painel Administrativo de Saúde do Sistema

## Acesso

A página fica no Filament Admin, no grupo **Governança**, com o nome **Saúde do Sistema**.

Caminho esperado:

```bash
/admin/system-health-dashboard
```

O slug pode variar se o Filament aplicar outra convenção interna, mas a página é descoberta automaticamente em `app/Filament/Pages`.

## O que o painel valida

- Ambiente PHP/Laravel
- Banco e dados principais
- Portal público
- Financeiro / Asaas
- Arquivos, uploads e permissões
- Comandos e scheduler
- Logs recentes
- Arquivos críticos da aplicação

## Comando CLI equivalente

```bash
php artisan sistemrh:saude --limite=1000
```

Para exportar JSON:

```bash
php artisan sistemrh:saude --limite=1000 --json=storage/app/relatorios/saude-sistema.json
```

## Como fazer novas funcionalidades aparecerem no painel

O painel é modular. Para um novo módulo aparecer de forma correta, crie uma classe em:

```text
app/Services/SystemHealth/Checks
```

A classe deve implementar:

```php
App\Services\SystemHealth\HealthCheckContract
```

Depois, registre a classe em `SystemHealthService::defaultChecks()`.

Exemplo de uso: se for criado um módulo de contratos, crie `ContratosHealthCheck` validando tabela, colunas, rotas, arquivos, vínculos e estados do novo módulo.

## Segurança

O painel é apenas leitura. Ele não altera banco, arquivos ou configurações.
