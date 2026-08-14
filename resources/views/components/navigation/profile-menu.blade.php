@props(['name' => null, 'role' => null, 'logoutRoute' => 'admin.logout', 'profileRoute' => null, 'notificationsRoute' => null])
<div x-data="dropdown()" class="relative">
    <button type="button" class="flex items-center gap-2" @click="toggle()" :aria-expanded="open.toString()" aria-haspopup="true">
        <x-ui.avatar :name="$name ?? auth()->user()?->name ?? 'A'" />
        <span class="hidden lg:block text-left">
            <span class="block text-sm font-semibold leading-tight max-w-[150px] truncate">{{ $name ?? auth()->user()?->name }}</span>
            <span class="block text-xs text-[var(--color-text-muted)]">{{ $role ?? '' }}</span>
        </span>
        <svg class="hidden lg:block w-4 h-4 text-[var(--color-text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div x-show="open" x-cloak @click.outside="close()" x-transition.opacity.duration.150ms class="dropdown-panel right-0">
        @if($profileRoute)
            <a href="{{ $profileRoute }}" class="dropdown-item"><x-ui.icon name="user" class="w-4 h-4" /> Profil</a>
        @endif
        @if($notificationsRoute)
            <a href="{{ $notificationsRoute }}" class="dropdown-item"><x-ui.icon name="bell" class="w-4 h-4" /> Notifikasi</a>
        @endif
        <a href="/docs" class="dropdown-item"><x-ui.icon name="question" class="w-4 h-4" /> Bantuan</a>
        <div class="dropdown-divider"></div>
        <form method="POST" action="{{ route($logoutRoute) }}">
            @csrf
            <button type="submit" class="dropdown-item danger"><x-ui.icon name="logout" class="w-4 h-4" /> Keluar</button>
        </form>
    </div>
</div>
