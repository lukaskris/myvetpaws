@php
    use Filament\Support\Enums\MaxWidth;
    $navigation = filament()->getNavigation();
    $livewire ??= null;
@endphp

<x-filament-panels::layout.base :livewire="$livewire">
    {{-- Clean layout without sidebar --}}
    <div class="fi-layout flex min-h-screen w-full flex-col">
        {{-- Optional topbar --}}
        @if (filament()->hasTopbar())
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_BEFORE, scopes: $livewire?->getRenderHookScopes()) }}

            <x-filament-panels::topbar :navigation="$navigation" />

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_AFTER, scopes: $livewire?->getRenderHookScopes()) }}
        @endif

        {{-- Main content --}}
        <main @class([
            'fi-main mx-auto h-full w-full px-4 md:px-6 lg:px-8',
            match (
                ($maxContentWidth ??=
                    filament()->getMaxContentWidth() ?? MaxWidth::SevenExtraLarge)
            ) {
                MaxWidth::ExtraSmall, 'xs' => 'max-w-xs',
                MaxWidth::Small, 'sm' => 'max-w-sm',
                MaxWidth::Medium, 'md' => 'max-w-md',
                MaxWidth::Large, 'lg' => 'max-w-lg',
                MaxWidth::ExtraLarge, 'xl' => 'max-w-xl',
                MaxWidth::TwoExtraLarge, '2xl' => 'max-w-2xl',
                MaxWidth::ThreeExtraLarge, '3xl' => 'max-w-3xl',
                MaxWidth::FourExtraLarge, '4xl' => 'max-w-4xl',
                MaxWidth::FiveExtraLarge, '5xl' => 'max-w-5xl',
                MaxWidth::SixExtraLarge, '6xl' => 'max-w-6xl',
                MaxWidth::SevenExtraLarge, '7xl' => 'max-w-7xl',
                MaxWidth::Full, 'full' => 'max-w-full',
                MaxWidth::MinContent, 'min' => 'max-w-min',
                MaxWidth::MaxContent, 'max' => 'max-w-max',
                MaxWidth::FitContent, 'fit' => 'max-w-fit',
                MaxWidth::Prose, 'prose' => 'max-w-prose',
                MaxWidth::ScreenSmall, 'screen-sm' => 'max-w-screen-sm',
                MaxWidth::ScreenMedium, 'screen-md' => 'max-w-screen-md',
                MaxWidth::ScreenLarge, 'screen-lg' => 'max-w-screen-lg',
                MaxWidth::ScreenExtraLarge, 'screen-xl' => 'max-w-screen-xl',
                MaxWidth::ScreenTwoExtraLarge, 'screen-2xl' => 'max-w-screen-2xl',
                default => $maxContentWidth,
            },
        ])>
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::CONTENT_START, scopes: $livewire?->getRenderHookScopes()) }}

            {{ $slot }}

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::CONTENT_END, scopes: $livewire?->getRenderHookScopes()) }}
        </main>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::FOOTER, scopes: $livewire?->getRenderHookScopes()) }}
    </div>
</x-filament-panels::layout.base>
