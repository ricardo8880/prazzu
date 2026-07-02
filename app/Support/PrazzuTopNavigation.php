<?php

namespace App\Support;

use App\Filament\Pages\Auditoria;
use App\Filament\Pages\ControleCobrancas;
use App\Filament\Pages\PlanosBilling;
use App\Filament\Pages\PortalCliente;
use App\Filament\Pages\Relatorios;
use App\Filament\Pages\SlaPrazos;
use App\Filament\Resources\Empresas\EmpresaResource;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Filament\Resources\Responsaveis\ResponsavelResource;
use Throwable;

class PrazzuTopNavigation
{
    /**
     * Navegação principal do produto, posicionada no topo para reduzir a coluna lateral.
     *
     * @return array<int, array{label: string, items: array<int, array{label: string, url: string, active: bool}>}>
     */
    public static function sections(): array
    {
        return collect([
            [
                'label' => 'Clientes',
                'items' => [
                    self::resourceItem('Empresas', EmpresaResource::class),
                    self::resourceItem('Responsáveis', ResponsavelResource::class),
                    self::pageItem('Portal do Cliente', PortalCliente::class),
                ],
            ],
            [
                'label' => 'Operação',
                'items' => [
                    self::resourceItem('ItemControle', ItemControleResource::class),
                    self::resourcePageItem('Checklist', ItemControleResource::class, 'checklists'),
                    self::resourcePageItem('Anexos', ItemControleResource::class, 'anexos'),
                    self::resourcePageItem('Timeline', ItemControleResource::class, 'timelines'),
                    self::pageItem('SLA', SlaPrazos::class),
                ],
            ],
            [
                'label' => 'Cobrança',
                'items' => [
                    self::pageItem('Cobranças', ControleCobrancas::class),
                    self::pageItem('Plano', PlanosBilling::class),
                ],
            ],
            [
                'label' => 'Gestão',
                'items' => [
                    self::pageItem('Relatórios', Relatorios::class),
                    self::pageItem('Auditoria', Auditoria::class),
                ],
            ],
        ])
            ->map(fn (array $section): array => [
                'label' => $section['label'],
                'items' => collect($section['items'])->filter()->values()->all(),
            ])
            ->filter(fn (array $section): bool => ! empty($section['items']))
            ->values()
            ->all();
    }

    private static function resourceItem(string $label, string $resource): ?array
    {
        return self::makeItem($label, $resource, fn (): string => $resource::getUrl());
    }

    private static function resourcePageItem(string $label, string $resource, string $page): ?array
    {
        return self::makeItem($label, $resource, fn (): string => $resource::getUrl($page));
    }

    private static function pageItem(string $label, string $page): ?array
    {
        return self::makeItem($label, $page, fn (): string => $page::getUrl());
    }

    private static function makeItem(string $label, string $class, callable $urlResolver): ?array
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
            'url' => $url,
            'active' => request()->fullUrlIs(rtrim($url, '/') . '*'),
        ];
    }
}
