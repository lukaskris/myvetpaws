{{-- resources/views/filament/pages/setup-clinic.blade.php --}}
<x-filament-panels::page>


    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-900 dark:to-gray-800 py-12 px-4">
        <div class="mx-auto w-full max-w-4xl space-y-10">

            {{-- Header --}}
            <div class="text-center header-heading">
                <div class="mx-auto h-16 w-16 bg-primary-600 rounded-full flex items-center justify-center mb-4">
                    <x-filament::icon icon="heroicon-o-building-office" class="h-8 w-8 text-white" />
                </div>
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Set Up Your Veterinary Clinic
                </h2>
                <p class="mt-2 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto mb-6">
                    Almost there! Just a few more details to complete your clinic profile.
                </p>
            </div>

            {{-- Form Card --}}
            <x-filament::card class="overflow-hidden pt-10">
                <div class="p-6 sm:p-8">
                    <form wire:submit.prevent="create" class="space-y-6">
                        {{ $this->form }}

                        <hr class="border-t border-gray-200 dark:border-gray-700 my-6" />

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Step 2 of 2 • Clinic Information
                            </span>

                            <x-filament::button type="submit" size="xl" icon="heroicon-o-check-circle"
                                icon-position="after">
                                Complete Clinic Setup
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </x-filament::card>
        </div>
    </div>

    {{-- Optional Custom Styles --}}
    @push('styles')
        <style>
            .fi-fieldset {
                border: 1px solid rgb(var(--gray-200));
                border-radius: 0.75rem;
                padding: 1.75rem;
                margin-bottom: 2rem;
                background: rgb(var(--white));
                transition: all 0.2s ease;
            }

            .dark .fi-fieldset {
                border-color: rgb(var(--gray-700));
                background: rgb(var(--gray-800));
            }

            .fi-fieldset:hover {
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }

            .fi-section-header-heading {
                font-size: 1.25rem;
                font-weight: 600;
                margin-bottom: 1.25rem;
                color: rgb(var(--primary-600));
            }

            .dark .fi-section-header-heading {
                color: rgb(var(--primary-400));
            }

            .fi-input:focus,
            .fi-select:focus,
            .fi-textarea:focus {
                border-color: rgb(var(--primary-500));
                box-shadow: 0 0 0 1px rgb(var(--primary-500));
            }

            .fi-time-picker input {
                padding: 0.5rem 0.75rem;
            }
        </style>
    @endpush
</x-filament-panels::page>
