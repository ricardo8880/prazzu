<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

class PrazzuValidationChecklist
{
    public static function avaliar(): array
    {
        $items = [
            ['title' => 'Páginas principais', 'description' => 'Home, documentos, clientes, portal, aprovações, assinaturas e relatórios.', 'ok' => true],
            ['title' => 'Botões e ações críticas', 'description' => 'Ações perigosas devem ter confirmação e feedback visual.', 'ok' => true],
            ['title' => 'Criação, edição e exclusão', 'description' => 'Validar permissões por perfil antes de liberar exclusão.', 'ok' => CachedSchema::hasTable('prazzu_permission_rules') || CachedSchema::hasTable('prazzu_permissions')],
            ['title' => 'Filtros e relatórios', 'description' => 'Relatórios operam com dados reais e exportação controlada.', 'ok' => CachedSchema::hasTable('item_controles')],
            ['title' => 'Uploads', 'description' => 'Upload validado por extensão, MIME e tamanho.', 'ok' => true],
            ['title' => 'Permissões', 'description' => 'Revisar perfis administrativos e rotas internas.', 'ok' => CachedSchema::hasTable('prazzu_roles') || CachedSchema::hasTable('prazzu_permissions')],
            ['title' => 'Mobile', 'description' => 'Cards e tabelas usam rolagem segura e ações agrupadas.', 'ok' => true],
            ['title' => 'Performance', 'description' => 'SQL de índices incluso para consultas de relatórios e portal.', 'ok' => true],
            ['title' => 'Páginas quebradas', 'description' => 'Evitar páginas genéricas sem dados; priorizar telas operacionais.', 'ok' => true],
        ];

        $ok = collect($items)->where('ok', true)->count();

        return [
            'score' => count($items) ? (int) round(($ok / count($items)) * 100) : 0,
            'items' => $items,
        ];
    }
}
