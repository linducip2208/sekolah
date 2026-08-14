@php
    $safe = fn (string $name) => rescue(fn () => route($name), '#', false);
    $role    = auth()->check() ? (auth()->user()->getRoleNames()->first() ?? 'admin') : 'admin';
    $isAdmin = in_array($role, ['admin', 'super_admin'], true);

    $actions = collect($isAdmin
        ? [
            ['title' => 'Tambah Siswa',       'group' => 'Aksi', 'icon' => '👨‍🎓', 'url' => $safe('admin.students.create')],
            ['title' => 'Tambah Staff / Guru', 'group' => 'Aksi', 'icon' => '👨‍🏫', 'url' => $safe('admin.staff.create')],
            ['title' => 'Buat Pengumuman',    'group' => 'Aksi', 'icon' => '📢', 'url' => $safe('admin.notices.create')],
            ['title' => 'Absensi Harian',     'group' => 'Aksi', 'icon' => '📋', 'url' => $safe('admin.attendance.index')],
            ['title' => 'Kelola Invoice',     'group' => 'Aksi', 'icon' => '🧾', 'url' => $safe('admin.fee.invoices.index')],
            ['title' => 'Dashboard PPDB',     'group' => 'Aksi', 'icon' => '🧒', 'url' => $safe('admin.ppdb.dashboard')],
        ]
        : [
            ['title' => 'Kelola Invoice',     'group' => 'Aksi', 'icon' => '🧾', 'url' => $safe('admin.fee.invoices.index')],
            ['title' => 'Slip Gaji',          'group' => 'Aksi', 'icon' => '💳', 'url' => $safe('admin.payroll.slips.index')],
            ['title' => 'Ringkasan Keuangan', 'group' => 'Aksi', 'icon' => '📊', 'url' => $safe('admin.finance.reports.summary')],
            ['title' => 'Buat Laporan',       'group' => 'Aksi', 'icon' => '📈', 'url' => $safe('admin.reports.builder.index')],
        ])->filter(fn ($a) => $a['url'] !== '#')->values()->all();

    $nav = collect($isAdmin
        ? [
            ['title' => 'Dashboard',          'group' => 'Navigasi', 'icon' => '🏠', 'url' => $safe('admin.dashboard')],
            ['title' => 'Data Siswa',         'group' => 'Navigasi', 'icon' => '👨‍🎓', 'url' => $safe('admin.students.index')],
            ['title' => 'Staff & Guru',       'group' => 'Navigasi', 'icon' => '👨‍🏫', 'url' => $safe('admin.staff.index')],
            ['title' => 'Jadwal Pelajaran',   'group' => 'Navigasi', 'icon' => '📅', 'url' => $safe('admin.timetable.index')],
            ['title' => 'Ujian',              'group' => 'Navigasi', 'icon' => '📝', 'url' => $safe('admin.exams.index')],
            ['title' => 'Invoice / Tagihan',  'group' => 'Navigasi', 'icon' => '💰', 'url' => $safe('admin.fee.invoices.index')],
            ['title' => 'Report Builder',     'group' => 'Navigasi', 'icon' => '📊', 'url' => $safe('admin.reports.builder.index')],
            ['title' => 'Pengumuman',         'group' => 'Navigasi', 'icon' => '📢', 'url' => $safe('admin.notices.index')],
            ['title' => 'Perpustakaan',       'group' => 'Navigasi', 'icon' => '📚', 'url' => $safe('admin.library.books.index')],
        ]
        : [
            ['title' => 'Dashboard',          'group' => 'Navigasi', 'icon' => '🏠', 'url' => $safe('admin.dashboard')],
            ['title' => 'Invoice / Tagihan',  'group' => 'Navigasi', 'icon' => '💰', 'url' => $safe('admin.fee.invoices.index')],
            ['title' => 'Slip Gaji',          'group' => 'Navigasi', 'icon' => '💳', 'url' => $safe('admin.payroll.slips.index')],
            ['title' => 'Ringkasan Keuangan', 'group' => 'Navigasi', 'icon' => '📊', 'url' => $safe('admin.finance.reports.summary')],
            ['title' => 'Report Builder',     'group' => 'Navigasi', 'icon' => '📈', 'url' => $safe('admin.reports.builder.index')],
        ])->filter(fn ($a) => $a['url'] !== '#')->values()->all();
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
                   placeholder="Cari siswa, guru, invoice, pengumuman… atau ketik aksi"
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
            <template x-if="mode === 'results' && !loading && results.length === 0">
                <div class="px-4 py-6 text-center text-sm text-[var(--color-text-muted)]">
                    Tidak ada hasil untuk "<span x-text="query"></span>".
                </div>
            </template>

            {{-- Search results --}}
            <template x-for="(r, idx) in results" :key="'r' + idx">
                <a :href="r.url" class="command-item" :class="{ active: active === idx }" @mouseenter="active = idx" @click.prevent="choose(r)">
                    <span class="text-lg" aria-hidden="true" x-text="r.icon"></span>
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
                            <span class="text-lg" aria-hidden="true" x-text="a.icon"></span>
                            <span class="flex-1 min-w-0 truncate" x-text="a.title"></span>
                            <span class="text-[11px] text-[var(--color-text-muted)]" x-text="a.group"></span>
                        </a>
                    </template>
                </div>
            </template>

            {{-- Navigation --}}
            <template x-if="filteredNav.length">
                <div>
                    <div class="dropdown-label px-4 pt-1">Navigasi</div>
                    <template x-for="(n, idx) in filteredNav" :key="'n' + idx">
                        <a :href="n.url" class="command-item" :class="{ active: active === (mode === 'results' ? results.length + filteredActions.length + idx : filteredActions.length + idx) }" @mouseenter="active = (mode === 'results' ? results.length + filteredActions.length + idx : filteredActions.length + idx)" @click.prevent="choose(n)">
                            <span class="text-lg" aria-hidden="true" x-text="n.icon"></span>
                            <span class="flex-1 min-w-0 truncate" x-text="n.title"></span>
                            <span class="text-[11px] text-[var(--color-text-muted)]" x-text="n.group"></span>
                        </a>
                    </template>
                </div>
            </template>

            {{-- Idle hint --}}
            <template x-if="mode === 'idle'">
                <div class="px-4 py-6 text-center text-sm text-[var(--color-text-muted)]">
                    Ketik minimal 2 karakter untuk mencari, atau pilih aksi cepat di bawah.
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
