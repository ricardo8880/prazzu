<?php

namespace App\Http\Middleware;

use App\Filament\Pages\Home;
use App\Models\User;
use App\Support\AccountingProfileNavigation;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountingProfileCanAccessFilament
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        if (! $this->isFilamentAdminPageRequest($request)) {
            return $next($request);
        }

        $label = $this->resolveNavigationLabel($request);

        if (! $label) {
            return $next($request);
        }

        if (AccountingProfileNavigation::canAccessLabel($user, $label)) {
            return $next($request);
        }

        Notification::make()
            ->title('Acesso não permitido')
            ->body('Seu perfil contábil não possui permissão para abrir esta tela.')
            ->warning()
            ->send();

        return redirect()->to(Home::getUrl());
    }

    private function isFilamentAdminPageRequest(Request $request): bool
    {
        $routeName = (string) $request->route()?->getName();
        $path = trim($request->path(), '/');

        if (Str::contains($routeName, ['livewire', 'filament.exports', 'filament.imports'])) {
            return false;
        }

        return Str::startsWith($routeName, 'filament.admin.') || $path === 'admin' || Str::startsWith($path, 'admin/');
    }

    private function resolveNavigationLabel(Request $request): ?string
    {
        $routeName = Str::lower((string) $request->route()?->getName());
        $path = Str::lower(trim($request->path(), '/'));
        $target = $routeName.' '.$path;

        if ($path === 'admin' || $path === 'admin/' || Str::endsWith($routeName, '.pages.dashboard')) {
            return 'Home';
        }

        foreach ($this->routeLabelMap() as $needle => $label) {
            if (Str::contains($target, $needle)) {
                return $label;
            }
        }

        return null;
    }

    private function routeLabelMap(): array
    {
        return [
            'central-aprovacoes' => 'Central de Aprovações',
            'aprovacoes' => 'Central de Aprovações',
            'indicadores-conta' => 'Indicadores da Conta',
            'dashboard-executivo' => 'Dashboard Executivo',
            'relatorios-exportaveis' => 'Relatórios Exportáveis',
            'relatorios-internos' => 'Relatórios',
            'relatorios-personalizados' => 'Relatórios',
            'relatorios' => 'Relatórios',
            'controle-cobrancas' => 'Cobranças',
            'cobrancas' => 'Cobranças',
            'sla-prazos' => 'SLA e Prazos',
            'timeline-operacional' => 'Timeline Operacional',
            'centro-operacional' => 'Centro Operacional',
            'fluxos-operacionais' => 'Fluxos Operacionais',
            'portal-cliente' => 'Portal do Cliente',
            'item-controles.checklist' => 'Checklist',
            'categoria-checklist' => 'Checklist',
            'checklist' => 'Checklist',
            'central-contratos' => 'Contratos',
            'contratos' => 'Contratos',
            'responsaveis' => 'Clientes',
            'clientes' => 'Clientes',
            'item-controles.timeline' => 'Timeline Operacional',
            'item-controles.anexos' => 'Documentos',
            'item-controles.assinaturas' => 'Documentos',
            'item-controles' => 'Tarefas',
            'tarefas' => 'Tarefas',
            'pendencias' => 'Pendências',
            'atendimentos' => 'Atendimentos',
            'documentos' => 'Documentos',
            'armazenamento' => 'Armazenamento',
            'validades' => 'Validades',
            'financeiro' => 'Financeiro',
            'riscos' => 'Riscos',
            'calendario' => 'Calendário',
            'auditoria' => 'Auditoria',
            'usuarios' => 'Usuários',
            'users' => 'Usuários',
            'configuracoes' => 'Configurações',
            'permissoes' => 'Perfis e Permissões',
            'empresas' => 'Empresas',
            'dashboard-configuravel' => 'Dashboard Configurável',
            'inteligencia-produto' => 'Inteligência do Produto',
            'templates-enterprise' => 'Templates Enterprise',
            'system-health' => 'Saúde do Sistema',
            'white-label' => 'White Label',
            'onboarding' => 'Onboarding',
            'projetos' => 'Projetos',
            'timeline-global' => 'Timeline Global',
        ];
    }
}
