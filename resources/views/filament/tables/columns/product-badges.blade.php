@php
    /** @var array<int, string> $badges */
@endphp

<span class="flex flex-wrap gap-1 pt-1">
    @foreach ($badges as $badge)
        <x-filament::badge size="sm">
            {{ $badge }}
        </x-filament::badge>
    @endforeach
</span>
