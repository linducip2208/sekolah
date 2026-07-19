@extends('layouts.school-admin')
@section('title', 'Pengumuman')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="flex justify-between items-end mb-7">
    <div>
        <div class="elite-kicker mb-2">Edicta</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Papan Pengumuman</h1>
        <div class="elite-rule"></div>
    </div>
    <a href="{{ route('admin.notices.create') }}" class="btn-elite-gold">+ Buat Pengumuman</a>
</div>

<div class="space-y-3">
    @forelse($notices as $n)
        <div class="bg-white border border-rule p-5">
            <div class="flex justify-between items-start gap-4">
                <div class="flex-1">
                    <div class="flex items-baseline gap-3 mb-1">
                        <h3 class="elite-h3 text-base ink-primary">{{ $n->title }}</h3>
                        @if($n->is_published)
                            <span class="text-xs text-green-700">● Published</span>
                        @else
                            <span class="text-xs text-gray-500">Draft</span>
                        @endif
                    </div>
                    <p class="font-serif text-sm text-gray-700 mb-2">{{ Str::limit(strip_tags($n->content), 200) }}</p>
                    <div class="text-xs text-gray-500">
                        Oleh {{ $n->creator?->name ?? 'Sistem' }} · {{ $n->publish_at?->diffForHumans() ?? 'Belum dipublikasi' }}
                        @if(!empty($n->target_roles))
                            · Target: <span class="elite-kicker text-[.55rem]">{{ implode(', ', $n->target_roles) }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col gap-2 items-end">
                    <a href="{{ route('admin.notices.edit', $n) }}" class="text-xs underline ink-secondary hover:ink-accent">Edit</a>
                    <form method="POST" action="{{ route('admin.notices.destroy', $n) }}" onsubmit="return confirm('Hapus?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-700 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">
            Belum ada pengumuman.
        </div>
    @endforelse
</div>

<div class="mt-5">{{ $notices->links() }}</div>

@endsection
