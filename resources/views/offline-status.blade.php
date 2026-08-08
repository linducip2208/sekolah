{{-- Sikad Pro — Offline Status Indicator --}}
<div x-data="offlineIndicator()"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="-translate-y-full opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-y-0 opacity-100"
     x-transition:leave-end="-translate-y-full opacity-0"
     class="fixed top-0 left-0 right-0 z-[9999]"
     style="background: linear-gradient(135deg, #EAB308, #CA8A04);">
    <div class="flex items-center justify-between px-4 py-2.5 text-sm">
        <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728M5.636 5.636a9 9 0 000 12.728M12 8v4m0 4h.01"/>
            </svg>
            <span class="font-sans font-semibold text-[#1e293b] text-xs sm:text-sm">
                Anda sedang offline &mdash; perubahan akan disimpan dan disinkronkan saat terhubung kembali.
                <span x-show="queueCount > 0" class="ml-1 text-[#78350f] font-mono">
                    (<span x-text="queueCount"></span> antrian)
                </span>
            </span>
        </div>
        <button @click="dismiss()" class="text-[#78350f] hover:text-[#451a03] ml-3 flex-shrink-0" aria-label="Tutup">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>
