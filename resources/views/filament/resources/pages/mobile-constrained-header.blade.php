@props([
    'actions' => [],
    'backUrl',
    'backClickHandler',
    'heading' => null,
])

<header class="fi-mobile-constrained-header">
    <div class="fi-mobile-constrained-header-start">
        <x-filament::button
            :href="$backUrl"
            color="gray"
            icon="heroicon-o-arrow-left"
            size="sm"
            tag="a"
            :x-on:click.prevent="$backClickHandler"
        >
            Back
        </x-filament::button>
    </div>

    @if (filled($heading))
        <div class="fi-mobile-constrained-header-heading">
            {{ $heading }}
        </div>
    @endif

    <div class="fi-mobile-constrained-header-end">
        @if ($actions)
            <x-filament::actions
                :actions="$actions"
                alignment="end"
            />
        @endif
    </div>
</header>
