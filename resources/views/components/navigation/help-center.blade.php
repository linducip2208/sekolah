@props(['name' => 'A'])
<div x-data="dropdown()" class="relative">
    <button type="button" class="btn-icon" @click="toggle()" :aria-expanded="open.toString()" aria-label="Bantuan">
        <x-ui.icon name="question" />
    </button>
    <div x-show="open" x-cloak @click.outside="close()" x-transition.opacity.duration.150ms class="dropdown-panel right-0">
        <a href="/docs" class="dropdown-item"><x-ui.icon name="external" class="w-4 h-4" /> Buku Panduan</a>
        <a href="/docs/admin" class="dropdown-item"><x-ui.icon name="external" class="w-4 h-4" /> Panduan Admin</a>
        <a href="/api-docs" class="dropdown-item"><x-ui.icon name="external" class="w-4 h-4" /> Dokumentasi API</a>
    </div>
</div>
