@props(['title' => 'Terjadi kendala', 'description' => 'Kami tidak dapat memuat data ini.', 'retry' => false])
<div class="empty-state">
    <div class="empty-icon" aria-hidden="true" style="color: var(--color-danger); background: var(--color-danger-soft);">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>
    <div class="empty-title">{{ $title }}</div>
    <p class="empty-desc">{{ $description }}</p>
    @if($retry)
        <button type="button" class="btn btn-sm mt-4" onclick="window.location.reload()"><x-ui.icon name="refresh" class="w-4 h-4" /> Coba lagi</button>
    @endif
</div>
