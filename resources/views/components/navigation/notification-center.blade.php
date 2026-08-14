@php
    $notifCount = 0;
    $recentNotifs = collect();
    try {
        if (auth()->check() && auth()->user()->school_id) {
            $base = \App\Models\Communication\NotificationLog::query()
                ->where('school_id', auth()->user()->school_id)
                ->where('user_id', auth()->id());
            $notifCount = (clone $base)->where('is_read', false)->count();
            $recentNotifs = (clone $base)->orderByDesc('created_at')->limit(8)->get();
        }
    } catch (\Throwable) {}
@endphp
<div x-data="dropdown()" class="relative">
    <button type="button" class="btn-icon relative" @click="toggle()" :aria-expanded="open.toString()" aria-label="Notifikasi">
        <x-ui.icon name="bell" />
        @if($notifCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-[1.1rem] h-[1.1rem] px-1 flex items-center justify-center text-[10px] font-bold text-white rounded-full" style="background: var(--color-danger);">{{ $notifCount > 99 ? '99+' : $notifCount }}</span>
        @endif
    </button>
    <div x-show="open" x-cloak @click.outside="close()" x-transition.opacity.duration.150ms class="dropdown-panel right-0" style="width: 22rem; max-width: calc(100vw - 2rem);">
        <div class="flex items-center justify-between px-3 py-2.5 border-b border-[var(--color-border)]">
            <span class="font-semibold text-sm">Notifikasi</span>
            @if($notifCount > 0)<span class="badge badge-danger">{{ $notifCount }} baru</span>@endif
        </div>
        <div class="max-h-80 overflow-y-auto">
            @forelse($recentNotifs as $n)
                <div class="notif-item {{ $n->is_read ? 'read' : 'unread' }}">
                    <span class="notif-dot" aria-hidden="true"></span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium leading-snug">{{ $n->title }}</div>
                        <div class="text-xs text-[var(--color-text-secondary)] line-clamp-2">{{ $n->body }}</div>
                        <div class="text-[11px] text-[var(--color-text-muted)] mt-1">{{ $n->created_at?->diffForHumans() }}</div>
                    </div>
                    @if(!$n->is_read)
                        <form method="POST" action="{{ route('admin.notifications.read', $n) }}">@csrf
                            <button type="submit" class="text-[11px] underline hover:opacity-70 whitespace-nowrap">Tandai dibaca</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-[var(--color-text-muted)]">Belum ada notifikasi.</div>
            @endforelse
        </div>
        <a href="{{ route('admin.notifications.index') }}" class="dropdown-item justify-center border-t border-[var(--color-border)]">Lihat semua notifikasi</a>
    </div>
</div>
