<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Concerns\BuildsHealthItems;
use App\Services\SystemHealth\HealthCheckContract;
use App\Support\CachedSchema;
use Illuminate\Support\Facades\DB;
use Throwable;

class DatabaseHealthCheck implements HealthCheckContract
{
    use BuildsHealthItems;

    public function key(): string { return 'database'; }
    public function name(): string { return 'Banco e dados'; }
    public function description(): string { return 'Schema real, multiempresa, duplicidades e inconsistências de registros operacionais.'; }

    public function run(int $limit = 500): array
    {
        $items = [];

        try {
            DB::connection()->getPdo();
            $items[] = $this->ok('Conexão com banco funcionando.', config('database.default'));
        } catch (Throwable $exception) {
            return [$this->error('Conexão com banco falhou.', $exception->getMessage(), [], 'Corrija as credenciais e disponibilidade do banco antes de validar o restante.')];
        }

        foreach (['users', 'empresas', 'assinaturas', 'pagamentos', 'responsaveis', 'item_controles', 'item_controle_assinaturas', 'item_controle_anexos', 'portal_solicitacoes', 'prazzu_client_portal_messages', 'prazzu_permissions'] as $table) {
            $items[] = CachedSchema::hasTable($table)
                ? $this->ok("Tabela {$table} existe.")
                : $this->error("Tabela crítica ausente: {$table}.", null, [], 'Importe o SQL oficial do projeto ou ajuste o schema real.');
        }

        if (CachedSchema::hasTable('empresas')) {
            $items[] = $this->metricWarning('Empresas com e-mail duplicado.', $this->duplicatedCount('empresas', 'email'), 'Revise cadastros duplicados para evitar cobrança e suporte inconsistentes.');
            $items[] = $this->metricWarning('Empresas com CNPJ duplicado.', $this->duplicatedCount('empresas', 'cnpj'), 'Unifique ou corrija CNPJs duplicados antes de criar restrições únicas.');
        }

        if (CachedSchema::hasTable('users')) {
            $usersWithoutCompany = $this->countWhereNull('users', 'empresa_id');
            $items[] = $usersWithoutCompany > 0
                ? $this->warning('Existem usuários sem empresa vinculada.', "Total encontrado: {$usersWithoutCompany}.", ['count' => $usersWithoutCompany], 'Vincule usuários a uma empresa ou trate explicitamente usuários globais/admin.')
                : $this->ok('Usuários possuem empresa vinculada ou não há inconsistência detectada.');
        }

        if (CachedSchema::hasTable('item_controles')) {
            $expiredOpen = $this->countExpiredOpenItems();
            $items[] = $expiredOpen > 0
                ? $this->warning('Existem itens vencidos ainda abertos.', "Total encontrado: {$expiredOpen}.", ['count' => $expiredOpen], 'Execute o comando oficial de vencidos e revise status customizados.')
                : $this->ok('Itens vencidos não permanecem em status aberto.');
        }

        return $items;
    }

    private function duplicatedCount(string $table, string $column): int
    {
        if (! CachedSchema::hasColumn($table, $column)) {
            return 0;
        }

        return (int) DB::table($table)
            ->select($column)
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->count();
    }

    private function metricWarning(string $title, int $count, string $action): array
    {
        return $count > 0
            ? $this->warning($title, "Grupos duplicados encontrados: {$count}.", ['count' => $count], $action)
            : $this->ok(str_replace('duplicado', 'duplicidade', $title).' Nenhuma ocorrência encontrada.');
    }

    private function countWhereNull(string $table, string $column): int
    {
        if (! CachedSchema::hasColumn($table, $column)) {
            return 0;
        }

        return (int) DB::table($table)->whereNull($column)->count();
    }

    private function countExpiredOpenItems(): int
    {
        if (! CachedSchema::hasColumn('item_controles', 'data_vencimento') || ! CachedSchema::hasColumn('item_controles', 'status')) {
            return 0;
        }

        return (int) DB::table('item_controles')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->whereIn('status', ['aberto', 'pendente', 'em_andamento', 'aguardando', 'ativo'])
            ->count();
    }
}
