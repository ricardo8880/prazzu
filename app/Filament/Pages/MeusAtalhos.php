<?php

namespace App\Filament\Pages;

use App\Models\UserSidebarFavorite;
use App\Support\PrazzuSidebarNavigation;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use UnitEnum;

class MeusAtalhos extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-star';
    protected static string | UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Meus Atalhos';
    protected static ?string $title = 'Meus Atalhos';
    protected static ?int $navigationSort = -50;

    protected string $view = 'filament.pages.meus-atalhos';

    public ?array $data = [];

    public function mount(): void
    {
        $user = Filament::auth()->user();

        $this->form->fill([
            'favorites' => $user
                ? UserSidebarFavorite::query()
                    ->where('user_id', $user->id)
                    ->orderBy('position')
                    ->orderBy('id')
                    ->get(['item_key', 'position'])
                    ->map(fn (UserSidebarFavorite $favorite): array => [
                        'item_key' => $favorite->item_key,
                        'position' => $favorite->position,
                    ])
                    ->values()
                    ->all()
                : [],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Organize seus favoritos')
                    ->description('Adicione as páginas mais usadas e arraste os cards para definir a ordem. O primeiro card aparece primeiro na coluna lateral.')
                    ->schema([
                        Forms\Components\Repeater::make('favorites')
                            ->label(false)
                            ->addActionLabel('Adicionar atalho')
                            ->reorderable()
                            ->reorderableWithButtons()
                            ->collapsible(false)
                            ->itemLabel(fn (array $state): ?string => $this->favoriteLabel($state['item_key'] ?? null))
                            ->schema([
                                Forms\Components\Select::make('item_key')
                                    ->label('Página')
                                    ->placeholder('Escolha uma página para fixar nos favoritos')
                                    ->options(fn (): array => PrazzuSidebarNavigation::favoriteOptions())
                                    ->searchable()
                                    ->native(false)
                                    ->required(),

                                Forms\Components\Hidden::make('position'),
                            ])
                            ->columns(1)
                            ->defaultItems(0),
                    ]),
            ])
            ->statePath('data');
    }

    public function salvar(): void
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return;
        }

        $state = $this->form->getState();
        $favorites = collect(Arr::get($state, 'favorites', []))
            ->filter(fn (array $favorite): bool => filled($favorite['item_key'] ?? null))
            ->unique('item_key')
            ->values();

        UserSidebarFavorite::query()
            ->where('user_id', $user->id)
            ->delete();

        foreach ($favorites as $index => $favorite) {
            UserSidebarFavorite::query()->create([
                'user_id' => $user->id,
                'item_key' => $favorite['item_key'],
                'position' => $index + 1,
            ]);
        }

        $this->mount();

        Notification::make()
            ->title('Atalhos salvos')
            ->body('Seus favoritos foram atualizados no topo da coluna lateral.')
            ->success()
            ->send();
    }

    /**
     * @return array<int, array{label: string, group: string}>
     */
    public function favoritePreviewItems(): array
    {
        return collect(Arr::get($this->data, 'favorites', []))
            ->filter(fn (array $favorite): bool => filled($favorite['item_key'] ?? null))
            ->unique('item_key')
            ->values()
            ->map(fn (array $favorite): array => $this->favoriteDetails($favorite['item_key']))
            ->all();
    }

    public function favoriteCount(): int
    {
        return count($this->favoritePreviewItems());
    }

    public function availableCount(): int
    {
        return count(PrazzuSidebarNavigation::favoriteOptions());
    }

    public function favoriteLabel(?string $itemKey): ?string
    {
        if (! $itemKey) {
            return 'Novo atalho';
        }

        $details = $this->favoriteDetails($itemKey);

        return $details['label'] ?: 'Novo atalho';
    }

    /**
     * @return array{label: string, group: string}
     */
    private function favoriteDetails(string $itemKey): array
    {
        $option = PrazzuSidebarNavigation::favoriteOptions()[$itemKey] ?? $itemKey;
        $parts = explode(' — ', $option, 2);

        if (count($parts) === 2) {
            return [
                'group' => $parts[0],
                'label' => $parts[1],
            ];
        }

        return [
            'group' => 'Geral',
            'label' => $option,
        ];
    }
}
