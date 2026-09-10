@php
    $navService = app(\App\Services\Navigation\NavigationService::class);
    $navIcons = config('navigation.icons');
    $user = auth()->user();

    // Role-aware domain groups + top links (dead routes & menu asing otomatis difilter).
    $topLinks = $navService->topLinksFor($user);
    $groups = $navService->groupsFor($user);

    // Badge counts (cached 60s, tenant-scoped).
    $badges = $user?->school_id ? $navService->badges((int) $user->school_id) : ['ppdb' => 0, 'invoices' => 0, 'mywork' => 0];

    $iconSvg = fn ($key, $class = 'w-4 h-4 flex-shrink-0') => "<svg class='{$class}' fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='1.8'><path stroke-linecap='round' stroke-linejoin='round' d='" . e($navIcons[$key] ?? $navIcons['home']) . "'/></svg>";
    $chevron = "<svg class='w-3 h-3 transition-transform duration-200' :class=\"open ? 'rotate-90' : ''\" fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='2.5'><path stroke-linecap='round' stroke-linejoin='round' d='M9 5l7 7-7 7'/></svg>";

    $currentRoute = request()->route()?->getName() ?? '';

    // Pattern "a.*|b.*" → apakah route sekarang match salah satu?
    $matchesPattern = function (?string $patterns) use ($currentRoute): bool {
        if (!$patterns) return false;
        foreach (explode('|', $patterns) as $p) {
            $p = trim($p);
            if ($p === '') continue;
            if (str_ends_with($p, '*')) {
                if (str_starts_with($currentRoute, rtrim($p, '*'))) return true;
            } elseif ($currentRoute === $p) {
                return true;
            }
        }
        return false;
    };

    // Group terbuka otomatis jika ada item aktif di dalamnya.
    $groupHasActive = fn (array $items): bool => collect($items)->contains(fn ($i) => $matchesPattern($i['active']));

    $badgeFor = fn (?string $key) => $key && ($badges[$key] ?? 0) > 0
        ? "<span class='sidebar-badge'>" . number_format($badges[$key], 0, ',', '.') . '</span>'
        : '';
@endphp

<div class="px-3 pt-3 pb-1.5 flex items-center gap-1.5 sidebar-search">
    <div class="relative flex-1" x-data="{ q: '' }">
        <svg class="w-4 h-4 absolute left-2.5 top-1/2 -translate-y-1/2 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="search" x-model="q" placeholder="Cari menu..." aria-label="Cari menu"
               class="w-full bg-white/10 border border-white/10 rounded-md pl-8 pr-2 py-1.5 text-[13px] text-white/80 placeholder-white/40 outline-none focus:border-white/30 focus:bg-white/15 transition"
               @input="document.querySelectorAll('.sidebar-section').forEach(s => { s.style.display = q.length<2 || s.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none' })">
    </div>
    <button onclick="document.querySelectorAll('.sidebar-section').forEach(s=>s.__x.$data.open=true)" class="text-white/40 hover:text-white/80 p-1.5 text-xs" title="Buka semua" aria-label="Buka semua bagian">&#x25BC;</button>
    <button onclick="document.querySelectorAll('.sidebar-section').forEach(s=>s.__x.$data.open=false)" class="text-white/40 hover:text-white/80 p-1.5 text-xs" title="Tutup semua" aria-label="Tutup semua bagian">&#x25B2;</button>
</div>

{{-- ===== FAVORITES ===== --}}
<div x-data="{ items: window.favorites ? window.favorites.all() : [] }"
     x-init="window.addEventListener('sikadpro:favorites-changed', () => items = window.favorites.all())"
     x-show="items.length" x-cloak class="sidebar-section">
    <div class="sidebar-section-header" style="cursor: default;">Favorites</div>
    <div class="sidebar-section-body">
        <template x-for="f in items" :key="f.href">
            <a :href="f.href" class="sidebar-sub-link" x-text="f.label"></a>
        </template>
    </div>
</div>

{{-- ===== TOP LINKS ===== --}}
@foreach($topLinks as $link)
    <a href="{{ $link['url'] }}" class="sidebar-link {{ $matchesPattern($link['active']) ? 'active' : '' }}">
        {!! $iconSvg($link['icon']) !!}<span>{{ $link['label'] }}</span>{!! $badgeFor($link['badge'] ?? null) !!}
    </a>
@endforeach

{{-- ===== DOMAIN GROUPS ===== --}}
@foreach($groups as $group)
    @php $open = $groupHasActive($group['items']) ? 'true' : 'false'; @endphp
    <div class="sidebar-section" x-data="{ open: {{ $open }} }">
        <button @click="open=!open" type="button" class="sidebar-section-header" :aria-expanded="open ? 'true' : 'false'">
            <span class="flex items-center gap-2.5">{!! $iconSvg($group['icon']) !!}{{ $group['label'] }}</span>{!! $chevron !!}
        </button>
        <div x-show="open" x-collapse class="sidebar-section-body">
            @foreach($group['items'] as $item)
                <a href="{{ $item['url'] }}"
                   class="sidebar-sub-link {{ $matchesPattern($item['active'] ?? null) ? 'active' : '' }}"
                   @if(!empty($item['title'])) title="{{ $item['title'] }}" @endif>
                    {{ $item['label'] }}{!! $badgeFor($item['badge'] ?? null) !!}
                </a>
            @endforeach
        </div>
    </div>
@endforeach
