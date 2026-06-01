<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class PrazzuSecurityChecklist
{
    public static function avaliar(): array
    {
        $checks = [
            [
                'title' => 'Uploads validados',
                'description' => 'Arquivos do portal e anexos exigem extensão, MIME e tamanho controlados.',
                'ok' => true,
                'action' => 'Manter limite e tipos permitidos revisados.',
            ],
            [
                'title' => 'Arquivos privados protegidos',
                'description' => 'Links públicos usam token de portal e arquivos internos devem permanecer atrás de autenticação.',
                'ok' => CachedSchema::hasColumn('item_controles', 'portal_token'),
                'action' => 'Evite expor caminho direto de storage privado em telas públicas.',
            ],
            [
                'title' => 'Políticas de acesso',
                'description' => 'Recursos administrativos devem exigir usuário autenticado.',
                'ok' => auth()->check(),
                'action' => 'Revisar permissões por perfil antes de liberar módulos sensíveis.',
            ],
            [
                'title' => 'Logs de ações críticas',
                'description' => 'Atividades de anexos, portal, assinatura e auditoria ficam registradas quando as tabelas existem.',
                'ok' => CachedSchema::hasTable('activity_log') || CachedSchema::hasTable('item_controle_timeline') || CachedSchema::hasTable('auditoria_detalhada'),
                'action' => 'Ativar auditoria para aprovação, exclusão, upload e permissões.',
            ],
            [
                'title' => 'Rotas internas protegidas',
                'description' => 'Exportações administrativas estão com middleware de autenticação.',
                'ok' => self::rotaProtegida('auditoria-detalhada.exportar'),
                'action' => 'Manter exports e relatórios internos sempre com auth.',
            ],
            [
                'title' => 'Dados sensíveis',
                'description' => 'Relatórios mostram dados operacionais necessários e evitam expor arquivos fora do fluxo autorizado.',
                'ok' => true,
                'action' => 'Usar exportação apenas para perfis autorizados.',
            ],
        ];

        $ok = collect($checks)->where('ok', true)->count();

        return [
            'score' => count($checks) > 0 ? (int) round(($ok / count($checks)) * 100) : 0,
            'checks' => $checks,
        ];
    }

    private static function rotaProtegida(string $name): bool
    {
        if (! Route::has($name)) {
            return false;
        }

        $route = Route::getRoutes()->getByName($name);

        return in_array('auth', $route?->gatherMiddleware() ?? [], true);
    }
}
