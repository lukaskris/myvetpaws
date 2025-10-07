<button 
    type="button" 
    class="px-3 py-1 text-white bg-danger-500 rounded hover:bg-danger-600 transition"
    x-on:click="$dispatch('open-modal', { id: 'delete-diagnose-{{ $record->id }}' })"
>
    Delete
</button>

<x-filament::modal
    id="delete-diagnose-{{ $record->id }}"
    :heading="__('Delete Diagnose')"
    alignment="center"
    width="md"
>
    <div class="py-4">
        Are you sure you want to delete this diagnose?
    </div>

    <x-slot name="footer">
        <div class="flex justify-end gap-x-4">
            <x-filament::button
                type="button"
                color="gray"
                x-on:click="$dispatch('close-modal', { id: 'delete-diagnose-{{ $record->id }}' })"
            >
                Cancel
            </x-filament::button>

            <x-filament::button
                type="button"
                color="danger"
                wire:click="deleteRecord('{{ $record->id }}')"
                x-on:click="$dispatch('close-modal', { id: 'delete-diagnose-{{ $record->id }}' })"
            >
                Delete
            </x-filament::button>
        </div>
    </x-slot>
</x-filament::modal>