@props([
    'name',           // Alpine state property name
    'title' => null,
    'side' => 'right',
])
<div x-show="{{ $name }}" x-cloak
     @keydown.escape.window="{{ $name }} = false"
     class="fixed inset-0 z-[var(--z-drawer)]" role="dialog" aria-modal="true" aria-label="{{ $title ?? 'Panel' }}">
    <div class="absolute inset-0" style="background: rgba(11,29,58,.55);" @click="{{ $name }} = false" aria-hidden="true"></div>
    <div x-show="{{ $name }}"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="{{ $side === 'right' ? 'translate-x-full' : '-translate-x-full' }}"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="{{ $side === 'right' ? 'translate-x-full' : '-translate-x-full' }}"
         class="absolute inset-y-0 {{ $side === 'right' ? 'right-0' : 'left-0' }} w-full max-w-md bg-[var(--color-surface)] border-l border-[var(--color-border)] shadow-2xl flex flex-col">
        <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--color-border)]">
            <h3 class="font-bold">{{ $title }}</h3>
            <button type="button" class="btn-icon" @click="{{ $name }} = false" aria-label="Tutup panel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
            {{ $slot }}
        </div>
    </div>
</div>
