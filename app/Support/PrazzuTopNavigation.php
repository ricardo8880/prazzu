<?php

namespace App\Support;

class PrazzuTopNavigation
{
    /**
     * Navegação principal do produto. A origem agora é o mapa de UX para
     * manter topo, Home e atalhos com os mesmos destinos oficiais.
     *
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    public static function sections(): array
    {
        return PrazzuUxNavigation::topSections();
    }
}
