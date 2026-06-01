<?php

namespace App\Filament\Resources\ItemControles\Pages\Concerns;

use App\Filament\Resources\ItemControles\ItemControleResource;
use Filament\Navigation\NavigationItem;

trait HasItemControleSubNavigation
{
    public function getSubNavigation(): array
    {
        return [
            NavigationItem::make('Tarefas')
                ->icon('heroicon-o-check-circle')
                ->url(ItemControleResource::getUrl('index'))
                ->isActiveWhen(fn (): bool => url()->current() === ItemControleResource::getUrl('index'))
                ->sort(1),

            NavigationItem::make('Checklist')
                ->icon('heroicon-o-clipboard-document-check')
                ->url(ItemControleResource::getUrl('checklists'))
                ->isActiveWhen(fn (): bool => url()->current() === ItemControleResource::getUrl('checklists'))
                ->sort(2),

            NavigationItem::make('Timeline')
                ->icon('heroicon-o-clock')
                ->url(ItemControleResource::getUrl('timelines'))
                ->isActiveWhen(fn (): bool => url()->current() === ItemControleResource::getUrl('timelines'))
                ->sort(3),

            NavigationItem::make('Portal e Assinaturas')
                ->icon('heroicon-o-document-check')
                ->url(ItemControleResource::getUrl('assinaturas'))
                ->isActiveWhen(fn (): bool => url()->current() === ItemControleResource::getUrl('assinaturas'))
                ->sort(4),

            NavigationItem::make('Aprovações e Alertas')
                ->icon('heroicon-o-bell-alert')
                ->url(ItemControleResource::getUrl('aprovacoes'))
                ->isActiveWhen(fn (): bool => url()->current() === ItemControleResource::getUrl('aprovacoes'))
                ->sort(5),

            NavigationItem::make('Anexos e Comentários')
                ->icon('heroicon-o-paper-clip')
                ->url(ItemControleResource::getUrl('anexos'))
                ->isActiveWhen(fn (): bool => url()->current() === ItemControleResource::getUrl('anexos'))
                ->sort(6),
        ];
    }
}
