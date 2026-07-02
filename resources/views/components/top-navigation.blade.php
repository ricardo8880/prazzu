@php
    $sections = \App\Support\PrazzuTopNavigation::sections();
@endphp

@if (! empty($sections))
    <div class="prazzu-top-navigation" role="navigation" aria-label="Navegação principal do Prazzu">
        @foreach ($sections as $section)
            <div class="prazzu-top-navigation__section">
                <span class="prazzu-top-navigation__section-label">{{ $section['label'] }}</span>

                <div class="prazzu-top-navigation__items">
                    @foreach ($section['items'] as $item)
                        <a
                            href="{{ $item['url'] }}"
                            @class([
                                'prazzu-top-navigation__item',
                                'prazzu-top-navigation__item--active' => $item['active'],
                            ])
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif
