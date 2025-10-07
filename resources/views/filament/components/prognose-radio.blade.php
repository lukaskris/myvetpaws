@php
    $radioClass = match($state) {
        'Fausta' => 'bg-success-500',
        'Dubius' => 'bg-warning-500',
        'Infausta' => 'bg-danger-500',
        default => 'bg-gray-500'
    };
@endphp

<div class="flex items-center space-x-2">
    <div class="flex items-center space-x-4">
        <label class="flex items-center space-x-2">
            <div class="w-4 h-4 rounded-full {{ $state === 'Fausta' ? $radioClass : 'bg-gray-200' }}"></div>
            <span class="{{ $state === 'Fausta' ? 'font-medium' : 'text-gray-500' }}">Fausta</span>
        </label>
        
        <label class="flex items-center space-x-2">
            <div class="w-4 h-4 rounded-full {{ $state === 'Dubius' ? $radioClass : 'bg-gray-200' }}"></div>
            <span class="{{ $state === 'Dubius' ? 'font-medium' : 'text-gray-500' }}">Dubius</span>
        </label>
        
        <label class="flex items-center space-x-2">
            <div class="w-4 h-4 rounded-full {{ $state === 'Infausta' ? $radioClass : 'bg-gray-200' }}"></div>
            <span class="{{ $state === 'Infausta' ? 'font-medium' : 'text-gray-500' }}">Infausta</span>
        </label>
    </div>
</div>