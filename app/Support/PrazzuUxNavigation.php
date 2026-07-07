<?php

namespace App\Support;

use App\Filament\Pages\Auditoria;
use App\Filament\Pages\Calendario;
use App\Filament\Pages\CentralAprovacoes;
use App\Filament\Pages\CentroOperacional;
use App\Filament\Pages\Contratos;
use App\Filament\Pages\ControleCobrancas;
use App\Filament\Pages\Documentos;
use App\Filament\Pages\Financeiro;
use App\Filament\Pages\Home;
use App\Filament\Pages\Pendencias;
use App\Filament\Pages\PortalCliente;
use App\Filament\Pages\Relatorios;
use App\Filament\Pages\SlaPrazos;
use App\Filament\Pages\SystemHealthDashboard;
use App\Filament\Resources\Empresas\EmpresaResource;
use App\Filament\Resources\ItemControles\ItemControleResource;
use Throwable;

class PrazzuUxNavigation
{
    /**
     * Mapa único de navegação para reduzir duplicidade entre Home, topo e atalhos.
     * Cada destino tem dono funcional definido para evitar telas com a mesma finalidade.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function clusters(): array
    {
        return [
            [
                'key' => 'visao-geral',
                'label' => 'Visão geral',
                'hint' => 'Comece pelo resumo executivo e siga para a tela dona da ação.',
                'icon' => 'bi-speedometer2',
                'items' => [
                    self::page('Home', Home::class, 'Resumo diário da operação'),
                    self::page('Pendências', Pendencias::class, 'Fila de execução e priorização'),
                    self::page('Prazos/SLA', SlaPrazos::class, 'Risco de vencimento e criticidade'),
                ],
            ],
            [
                'key' => 'operacao',
                'label' => 'Operação',
                'hint' => 'Tudo que move o ItemControle e o trabalho interno.',
                'icon' => 'bi-diagram-3',
                'items' => [
                    self::resource('ItemControle', ItemControleResource::class, 'Fonte da verdade operacional'),
                    self::page('Centro Operacional', CentroOperacional::class, 'Fila central e execução'),
                    self::page('Aprovações', CentralAprovacoes::class, 'Decisões pendentes'),
                    self::page('Calendário', Calendario::class, 'Agenda de obrigações'),
                ],
            ],
            [
                'key' => 'clientes',
                'label' => 'Clientes',
                'hint' => 'Carteira de clientes e portal público.',
                'icon' => 'bi-buildings',
                'items' => [
                    self::resource('Empresas', EmpresaResource::class, 'Cadastro e vínculo multiempresa'),
                    self::page('Portal do Cliente', PortalCliente::class, 'Acesso externo controlado'),
                ],
            ],
            [
                'key' => 'documentos',
                'label' => 'Documentos',
                'hint' => 'Upload, download, validade e retenção documental.',
                'icon' => 'bi-folder2-open',
                'items' => [
                    self::page('Documentos', Documentos::class, 'Central documental validada'),
                    self::resourcePage('Anexos', ItemControleResource::class, 'anexos', 'Anexos do ItemControle'),
                    self::resourcePage('Checklist', ItemControleResource::class, 'checklists', 'Conferência por item'),
                ],
            ],
            [
                'key' => 'financeiro',
                'label' => 'Financeiro',
                'hint' => 'Contratos, cobrança e conciliação Asaas.',
                'icon' => 'bi-cash-coin',
                'items' => [
                    self::page('Financeiro', Financeiro::class, 'Visão financeira'),
                    self::page('Cobranças', ControleCobrancas::class, 'Gestão de cobranças'),
                    self::page('Contratos', Contratos::class, 'Contratos e assinatura'),
                ],
            ],
            [
                'key' => 'governanca',
                'label' => 'Governança',
                'hint' => 'Relatórios, auditoria e saúde do sistema.',
                'icon' => 'bi-shield-check',
                'items' => [
                    self::page('Relatórios', Relatorios::class, 'Indicadores e exportações'),
                    self::page('Auditoria', Auditoria::class, 'Rastro das ações críticas'),
                    self::page('Saúde do Sistema', SystemHealthDashboard::class, 'Diagnóstico técnico'),
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    public static function topSections(): array
    {
        return collect(self::clusters())
            ->map(fn (array $cluster): array => [
                'label' => $cluster['label'],
                'items' => collect($cluster['items'])->filter()->values()->all(),
            ])
            ->filter(fn (array $section): bool => ! empty($section['items']))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function homeJourney(): array
    {
        return collect(self::clusters())
            ->map(fn (array $cluster): array => [
                'label' => $cluster['label'],
                'hint' => $cluster['hint'],
                'icon' => $cluster['icon'],
                'items' => collect($cluster['items'])->filter()->take(3)->values()->all(),
            ])
            ->filter(fn (array $cluster): bool => ! empty($cluster['items']))
            ->values()
            ->all();
    }

    private static function page(string $label, string $page, string $hint): ?array
    {
        return self::makeItem($label, $page, fn (): string => $page::getUrl(), $hint);
    }

    private static function resource(string $label, string $resource, string $hint): ?array
    {
        return self::makeItem($label, $resource, fn (): string => $resource::getUrl(), $hint);
    }

    private static function resourcePage(string $label, string $resource, string $page, string $hint): ?array
    {
        return self::makeItem($label, $resource, fn (): string => $resource::getUrl($page), $hint);
    }

    private static function makeItem(string $label, string $class, callable $urlResolver, string $hint): ?array
    {
        try {
            if (method_exists($class, 'shouldRegisterNavigation') && ! $class::shouldRegisterNavigation()) {
                return null;
            }

            if (method_exists($class, 'canAccess') && ! $class::canAccess()) {
                return null;
            }

            if (method_exists($class, 'canViewAny') && ! $class::canViewAny()) {
                return null;
            }

            $url = $urlResolver();
        } catch (Throwable) {
            return null;
        }

        return [
            'label' => $label,
            'hint' => $hint,
            'url' => $url,
            'active' => request()->fullUrlIs(rtrim($url, '/') . '*'),
        ];
    }
}
