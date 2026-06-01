<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Concerns\BuildsHealthItems;
use App\Services\SystemHealth\HealthCheckContract;
use App\Support\CachedSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PortalHealthCheck implements HealthCheckContract
{
    use BuildsHealthItems;

    public function key(): string { return 'portal'; }
    public function name(): string { return 'Portal público'; }
    public function description(): string { return 'Tokens, links públicos, solicitações, mensagens, assinaturas e rotas críticas do portal.'; }

    public function run(int $limit = 500): array
    {
        $items = [];

        foreach (['portal.cliente.show', 'portal.cliente.solicitacoes.store', 'portal.item-controles.show', 'portal.item-controles.assinar', 'portal.item-controles.documentos'] as $routeName) {
            $items[] = Route::has($routeName)
                ? $this->ok("Rota {$routeName} registrada.")
                : $this->error("Rota {$routeName} ausente.", null, [], 'Registre a rota para manter o portal público operacional.');
        }

        if (CachedSchema::hasTable('item_controles')) {
            $withoutToken = $this->countPortalActiveWithoutToken();
            $items[] = $withoutToken > 0
                ? $this->error('Itens com portal ativo sem token.', "Total encontrado: {$withoutToken}.", ['count' => $withoutToken], 'Gere tokens para itens ativos ou desative o portal nesses registros.')
                : $this->ok('Itens com portal ativo possuem token.');

            $duplicatedTokens = $this->countDuplicatedPortalTokens();
            $items[] = $duplicatedTokens > 0
                ? $this->error('Tokens públicos duplicados encontrados.', "Grupos duplicados: {$duplicatedTokens}.", ['count' => $duplicatedTokens], 'Regere tokens duplicados para impedir exposição cruzada de dados.')
                : $this->ok('Tokens públicos sem duplicidade.');

            $expiredActive = $this->countExpiredActivePortals();
            $items[] = $expiredActive > 0
                ? $this->warning('Portais expirados ainda ativos.', "Total encontrado: {$expiredActive}.", ['count' => $expiredActive], 'Desative portais expirados ou renove a data de expiração conscientemente.')
                : $this->ok('Não há portais expirados ainda ativos.');
        }

        if (CachedSchema::hasTable('item_controle_assinaturas') && CachedSchema::hasTable('item_controles')) {
            $orphanSignatures = (int) DB::table('item_controle_assinaturas as s')
                ->leftJoin('item_controles as i', 'i.id', '=', 's.item_controle_id')
                ->whereNull('i.id')
                ->limit($limit)
                ->count();
            $items[] = $orphanSignatures > 0
                ? $this->error('Assinaturas do portal sem item vinculado.', "Total amostrado: {$orphanSignatures}.", ['count' => $orphanSignatures], 'Corrija ou remova assinaturas órfãs para manter rastreabilidade jurídica.')
                : $this->ok('Assinaturas do portal apontam para itens existentes.');
        }

        if (CachedSchema::hasTable('portal_solicitacoes') && CachedSchema::hasTable('empresas')) {
            $orphanRequests = (int) DB::table('portal_solicitacoes as p')
                ->leftJoin('empresas as e', 'e.id', '=', 'p.empresa_id')
                ->whereNull('e.id')
                ->limit($limit)
                ->count();
            $items[] = $orphanRequests > 0
                ? $this->error('Solicitações do portal sem empresa válida.', "Total amostrado: {$orphanRequests}.", ['count' => $orphanRequests], 'Vincule as solicitações a empresas existentes ou arquive registros inválidos.')
                : $this->ok('Solicitações do portal apontam para empresas existentes.');
        }

        if (CachedSchema::hasTable('prazzu_client_portal_messages')) {
            $items[] = CachedSchema::hasColumn('prazzu_client_portal_messages', 'sender_type')
                ? $this->ok('Mensagens do portal possuem sender_type.')
                : $this->warning('Mensagens do portal sem coluna sender_type.', null, [], 'Adicione sender_type para diferenciar cliente, sistema e usuário interno.');
        }

        return $items;
    }

    private function countPortalActiveWithoutToken(): int
    {
        if (! CachedSchema::hasColumn('item_controles', 'portal_ativo') || ! CachedSchema::hasColumn('item_controles', 'portal_token')) {
            return 0;
        }

        return (int) DB::table('item_controles')
            ->where('portal_ativo', 1)
            ->where(fn ($query) => $query->whereNull('portal_token')->orWhere('portal_token', ''))
            ->count();
    }

    private function countDuplicatedPortalTokens(): int
    {
        if (! CachedSchema::hasColumn('item_controles', 'portal_token')) {
            return 0;
        }

        return (int) DB::table('item_controles')
            ->select('portal_token')
            ->whereNotNull('portal_token')
            ->where('portal_token', '<>', '')
            ->groupBy('portal_token')
            ->havingRaw('COUNT(*) > 1')
            ->count();
    }

    private function countExpiredActivePortals(): int
    {
        if (! CachedSchema::hasColumn('item_controles', 'portal_ativo') || ! CachedSchema::hasColumn('item_controles', 'portal_expira_em')) {
            return 0;
        }

        return (int) DB::table('item_controles')
            ->where('portal_ativo', 1)
            ->whereNotNull('portal_expira_em')
            ->where('portal_expira_em', '<', now())
            ->count();
    }
}
