@php
    $safe = fn (string $name) => rescue(fn () => route($name), '#', false);
    $user = auth()->user();

    // Aksi cepat berbasis role (quick create yang benar-benar ada routenya).
    $actions = collect(app(\App\Services\Navigation\NavigationService::class)->quickCreateFor($user))
        ->take(6)
        ->map(fn ($q) => [
            'title' => $q['label'],
            'group' => 'Aksi',
            'icon'  => in_array($q['icon'], ['students','people']) ? ($q['icon'] === 'students' ? 'user' : 'users') : $q['icon'],
            'url'   => $q['url'],
        ])->values()->all();

    // Seluruh navigasi domain yang visible untuk role ini — semua menu
    // kini bisa dijangkau lewat ⌘K tanpa menelusuri sidebar.
    $nav = collect(app(\App\Services\Navigation\NavigationService::class)->flatForPalette($user))
        ->filter(fn ($n) => str_starts_with($n['url'], url('/')))
        ->values()->all();
@endphp

<div x-data="commandPalette({{ Js::from(['searchUrl' => $safe('admin.search'), 'actions' => $actions, 'nav' => $nav]) }})"
     x-init="init()"
     x-show="open" x-cloak
     x-trap.inert.noscroll="open"
     @keydown.escape.window="hide()"
     class="command-overlay">
    <div class="command-panel" @click.outside="hide()">
        <div class="flex items-center gap-3 px-4 py-3 border-b border-[var(--color-border)]">
            <x-ui.icon name="search" class="w-5 h-5 text-[var(--color-text-muted)]" />
            <input x-ref="input" x-model="query" @input="onInput" @keydown="onKeydown"
                   placeholder="Cari siswa, guru, invoice… atau ketik nama menu"
                   aria-label="Cari global"
                   class="flex-1 outline-none bg-transparent text-base" />
            <span class="command-kbd" aria-hidden="true">ESC</span>
        </div>

        <div class="max-h-96 overflow-y-auto py-1.5" role="listbox">
            {{-- Recent searches --}}
            <template x-if="mode === 'idle' && recent.length">
                <div>
                    <div class="dropdown-label px-4 pt-1">Terakhir dicari</div>
                    <template x-for="(r, idx) in recent" :key="r">
                        <button type="button" class="command-item" :class="{ active: false }" @click="runRecent(r)">
                            <x-ui.icon name="refresh" class="w-4 h-4 text-[var(--color-text-muted)]" />
                            <span class="truncate" x-text="r"></span>
                        </button>
                    </template>
                </div>
            </template>

            {{-- Loading --}}
            <template x-if="loading">
                <div class="px-4 py-6 flex items-center justify-center gap-2 text-sm text-[var(--color-text-muted)]">
                    <span class="spinner"></span> Mencari…
                </div>
            </template>

            {{-- Error --}}
            <template x-if="mode === 'error'">
                <div class="px-4 py-6 text-center">
                    <p class="text-sm text-[var(--color-danger)]">Pencarian gagal. Periksa koneksi Anda.</p>
                    <button type="button" class="btn btn-sm mt-3" @click="onInput()">Coba lagi</button>
                </div>
            </template>

            {{-- Empty --}}
            <template x-if="(mode === 'results' || mode === 'idle') && !loading && results.length === 0 && filteredActions.length === 0 && filteredNav.length === 0">
                <div class="px-4 py-6 text-center text-sm text-[var(--color-text-muted)]">
                    Tidak ada hasil untuk "<span x-text="query"></span>".
                </div>
            </template>

            {{-- Search results --}}
            <template x-for="(r, idx) in results" :key="'r' + idx">
                <a :href="r.url" class="command-item" :class="{ active: active === idx }" @mouseenter="active = idx" @click.prevent="choose(r)">
                    <svg class="w-5 h-5 text-[var(--color-text-muted)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path :d="iconPaths[r.icon] || iconPaths.school"></path></svg>
                    <span class="flex-1 min-w-0">
                        <span class="block truncate font-medium" x-text="r.title"></span>
                        <span class="block text-xs text-[var(--color-text-muted)] truncate" x-text="r.sub"></span>
                    </span>
                </a>
            </template>

            {{-- Quick actions --}}
            <template x-if="filteredActions.length">
                <div>
                    <div class="dropdown-label px-4 pt-1">Aksi</div>
                    <template x-for="(a, idx) in filteredActions" :key="'a' + idx">
                        <a :href="a.url" class="command-item" :class="{ active: active === (mode === 'results' ? results.length + idx : idx) }" @mouseenter="active = (mode === 'results' ? results.length + idx : idx)" @click.prevent="choose(a)">
                            <svg class="w-5 h-5 text-[var(--color-text-muted)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path :d="iconPaths[a.icon] || iconPaths.school"></path></svg>
                            <span class="flex-1 min-w-0 truncate" x-text="a.title"></span>
                            <span class="text-[11px] text-[var(--color-text-muted)]" x-text="a.group"></span>
                        </a>
                    </template>
                </div>
            </template>

            {{-- Navigation (seluruh menu sesuai role) --}}
            <template x-if="filteredNav.length">
                <div :class="filteredActions.length || results.length ? 'border-t border-[var(--color-border)] mt-1.5 pt-1' : ''">
                    <div class="dropdown-label px-4 pt-1">Navigasi</div>
                    <template x-for="(n, idx) in filteredNav" :key="'n' + n.title + n.group">
                        <a :href="n.url" class="command-item" :class="{ active: active === (mode === 'results' ? results.length + filteredActions.length + idx : filteredActions.length + idx) }" @mouseenter="active = (mode === 'results' ? results.length + filteredActions.length + idx : filteredActions.length + idx)" @click.prevent="choose(n)">
                            <svg class="w-5 h-5 text-[var(--color-text-muted)] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path :d="iconPaths[n.icon] || iconPaths.school"></path></svg>
                            <span class="flex-1 min-w-0 truncate" x-text="n.title"></span>
                            <span class="text-[11px] text-[var(--color-text-muted)]" x-text="n.group"></span>
                        </a>
                    </template>
                </div>
            </template>

            {{-- Idle hint --}}
            <template x-if="mode === 'idle' && !recent.length">
                <div class="px-4 py-6 text-center text-sm text-[var(--color-text-muted)]">
                    Ketik minimal 2 karakter untuk mencari siswa/invoice, atau pilih aksi & navigasi di bawah.
                </div>
            </template>
        </div>

        <div class="flex items-center gap-3 px-4 py-2 border-t border-[var(--color-border)] text-[11px] text-[var(--color-text-muted)]">
            <span><kbd class="command-kbd">↑↓</kbd> Navigasi</span>
            <span><kbd class="command-kbd">↵</kbd> Buka</span>
            <span><kbd class="command-kbd">ESC</kbd> Tutup</span>
            <span class="ml-auto"><kbd class="command-kbd">⌘K</kbd> / <kbd class="command-kbd">Ctrl K</kbd></span>
        </div>
    </div>
</div>
