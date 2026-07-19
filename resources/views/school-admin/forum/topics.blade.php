@extends('layouts.school-admin')
@section('title', 'Moderasi Topik Forum')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Moderatio</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Moderasi Topik</h1>
    <div class="elite-rule"></div>
</div>

<div class="bg-white border border-rule">
    <div class="table-elite overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-rule text-left">
                    <th class="py-3 px-4">Judul</th>
                    <th class="py-3 px-4">Kategori</th>
                    <th class="py-3 px-4">Oleh</th>
                    <th class="py-3 px-4 text-center">Lihat</th>
                    <th class="py-3 px-4 text-center">Balasan</th>
                    <th class="py-3 px-4">Terakhir</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topics as $t)
                    <tr class="border-b border-rule/40">
                        <td class="py-3 px-4 font-semibold max-w-[250px] truncate">{{ $t->title }}</td>
                        <td class="py-3 px-4 text-xs">{{ $t->category?->name }}</td>
                        <td class="py-3 px-4 text-xs">{{ $t->user?->name }}</td>
                        <td class="py-3 px-4 text-center text-xs">{{ $t->view_count }}</td>
                        <td class="py-3 px-4 text-center text-xs">{{ $t->replyCount() }}</td>
                        <td class="py-3 px-4 text-xs text-gray-500">{{ $t->last_reply_at?->diffForHumans() ?? '—' }}</td>
                        <td class="py-3 px-4">
                            @if($t->is_locked)
                                <span class="text-xs text-red-700">🔒 Dikunci</span>
                            @elseif($t->is_pinned)
                                <span class="text-xs text-blue-700">📌 Dipin</span>
                            @else
                                <span class="text-xs text-gray-400">Normal</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex gap-2 text-xs flex-wrap">
                                <form method="POST" action="{{ route('admin.forum.topics.pin', $t) }}">
                                    @csrf
                                    <button class="underline {{ $t->is_pinned ? 'text-blue-700' : 'text-gray-500' }} hover:text-blue-900">{{ $t->is_pinned ? 'Unpin' : 'Pin' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.forum.topics.lock', $t) }}">
                                    @csrf
                                    <button class="underline {{ $t->is_locked ? 'text-green-700' : 'text-red-500' }} hover:text-red-700">{{ $t->is_locked ? 'Buka' : 'Kunci' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.forum.topics.destroy', $t) }}" onsubmit="return confirm('Hapus topik ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-700 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-gray-500 italic font-serif">Belum ada topik forum.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-5">{{ $topics->links() }}</div>

@endsection
