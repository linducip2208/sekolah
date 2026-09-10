@props([
    'label' => 'Filter',
    'labels' => [],   // param => label manusiawi, e.g. ['status' => 'Status', 'class_section_id' => 'Kelas']
    'valueLabels' => [], // "param.value" => label, e.g. ['status.overdue' => 'Terlambat']
])
@php
    $query = request()->query();
    unset($query['page']); // pagination bukan filter
    $chips = [];
    foreach ($query as $key => $val) {
        if ($val === null || $val === '') continue;
        foreach ((array) $val as $v) {
            if ($v === '' || $v === null) continue;
            $chips[] = [
                'key' => $key,
                'value' => (string) $v,
                'label' => ($labels[$key] ?? ucwords(str_replace('_', ' ', $key))) . ': '
                    . ($valueLabels["{$key}.{$v}"] ?? str_replace('_', ' ', $v)),
            ];
        }
    }
@endphp
<div x-data="savedViews()" {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2 mb-3']) }}>
    @if(count($chips))
        <span class="text-xs font-semibold text-[var(--color-text-muted)] uppercase tracking-wide mr-1">{{ $label }}</span>
        @foreach($chips as $chip)
            @php
                $without = $query; unset($without[$chip['key']]);
                $qs = http_build_query(array_map(fn ($v) => is_array($v) ? implode(',', $v) : $v, $without));
            @endphp
            <a href="{{ request()->url() . ($qs ? '?' . $qs : '') }}"
               class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-full text-xs font-medium border transition group"
               style="background: var(--color-primary-soft); color: var(--color-primary); border-color: transparent;">
                {{ $chip['label'] }}
                <span class="h-4 w-4 rounded-full flex items-center justify-center opacity-60 group-hover:opacity-100" style="background: var(--color-primary); color: #fff;" aria-hidden="true">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </span>
                <span class="sr-only">Hapus filter {{ $chip['label'] }}</span>
            </a>
        @endforeach
        <a href="{{ request()->url() }}" class="text-xs font-medium text-[var(--color-text-muted)] hover:text-[var(--color-danger)] underline underline-offset-2 ml-1">Bersihkan semua</a>
    @endif

    {{-- Saved views — private per browser, tersimpan di localStorage --}}
    <div class="ml-auto relative" x-data="{ open: false }">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-haspopup="true"
                class="btn btn-ghost btn-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2l.117.007A1.998 1.998 0 018.41 4.283L8.5 5.5H19a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/></svg>
            Tampilan Tersimpan
        </button>
        <div x-show="open" x-cloak @click.outside="open = false"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             class="dropdown-panel right-0 z-40" style="min-width: 16rem;">
            <div class="dropdown-label">Tampilan halaman ini</div>
            <template x-for="(v, idx) in views" :key="idx">
                <div class="dropdown-item justify-between gap-2">
                    <a :href="v.url" class="truncate flex-1 min-w-0" x-text="v.name"></a>
                    <button type="button" class="text-[var(--color-danger)] hover:opacity-70 p-1" @click="remove(idx)" aria-label="Hapus tampilan">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <template x-if="views.length === 0">
                <div class="px-3 py-2 text-xs text-[var(--color-text-muted)]">Belum ada tampilan tersimpan.</div>
            </template>
            <div class="border-t border-[var(--color-border)] p-2">
                <input type="text" x-model="name" placeholder="Nama tampilan…" class="input text-xs mb-2" aria-label="Nama tampilan">
                <button type="button" class="btn btn-sm w-full" @click="save(); open = false;">Simpan filter saat ini</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function savedViews() {
            return {
                key: 'sikadpro:savedview:' + location.pathname,
                views: [],
                name: '',
                init() {
                    try { this.views = JSON.parse(localStorage.getItem(this.key) || '[]'); } catch (e) { this.views = []; }
                },
                save() {
                    const qs = location.search;
                    if (!qs && this.views.length === 0) return;
                    this.views.push({ name: this.name || ('Filter #' + (this.views.length + 1)), url: location.pathname + qs });
                    localStorage.setItem(this.key, JSON.stringify(this.views));
                    this.name = '';
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Tampilan tersimpan.', type: 'success' } }));
                },
                remove(i) {
                    this.views.splice(i, 1);
                    localStorage.setItem(this.key, JSON.stringify(this.views));
                },
            };
        }
    </script>
    @endpush
</div>
