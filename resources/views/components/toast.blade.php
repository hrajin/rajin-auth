@php
    $toasts = [];

    if (session('toast_success')) {
        $toasts[] = ['message' => session('toast_success'), 'type' => 'success'];
    }
    if (session('toast_error')) {
        $toasts[] = ['message' => session('toast_error'), 'type' => 'error'];
    }
    if (session('toast_warning')) {
        $toasts[] = ['message' => session('toast_warning'), 'type' => 'warning'];
    }
@endphp

@if (count($toasts) > 0)
<style>
    .toast-item {
        width: 100%;
    }
    @media (min-width: 1024px) {
        .toast-item {
            width: 70%;
        }
    }
</style>
<div class="fixed bottom-5 left-0 right-0 z-50 flex flex-col items-center gap-2 pointer-events-none">
    @foreach ($toasts as $toast)
    @php
        $typeClasses = match($toast['type']) {
            'success' => 'border-green-200 dark:border-green-800 text-green-800 dark:text-green-300',
            'error'   => 'border-red-200 dark:border-red-800 text-red-800 dark:text-red-300',
            'warning' => 'border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-300',
            default   => 'border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-300',
        };
    @endphp
    <div
        x-data="{ show: false }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        x-init="$nextTick(() => { show = true; setTimeout(() => show = false, 4000); })"
        class="toast-item pointer-events-auto flex items-center gap-3 pl-4 pr-3 py-3 rounded-xl shadow-lg border text-sm font-medium bg-white dark:bg-gray-900 {{ $typeClasses }}"
    >
        @if ($toast['type'] === 'success')
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        @elseif ($toast['type'] === 'error')
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
        @elseif ($toast['type'] === 'warning')
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-yellow-100 dark:bg-yellow-900/50 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        @endif

        <span class="flex-1">{{ $toast['message'] }}</span>

        <button @click="show = false" class="flex-shrink-0 ml-1 opacity-40 hover:opacity-70 transition-opacity" aria-label="Dismiss">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endforeach
</div>
@endif
