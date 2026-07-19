@extends('layouts.school-admin')
@section('title', 'Riwayat Percakapan WA Bot')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="max-w-7xl mx-auto">
<div class="mb-7 flex justify-between items-end">
    <div>
        <div class="elite-kicker mb-2">Komunikasi</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Riwayat Percakapan WA Bot</h1>
        <div class="elite-rule"></div>
    </div>
    <a href="{{ route('admin.wa-bot.commands.index') }}" class="btn-elite-ghost text-xs">← Perintah Bot</a>
</div>

<div class="bg-white border border-rule p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-stone-600 mb-1">No HP</label>
            <input name="phone" value="{{ request('phone') }}" placeholder="Filter no HP..." class="border-2 border-rule px-3 py-2 text-sm font-mono">
        </div>
        <div>
            <label class="block text-xs font-semibold text-stone-600 mb-1">Arah</label>
            <select name="direction" class="border-2 border-rule px-3 py-2 text-sm">
                <option value="">Semua</option>
                <option value="incoming" {{ request('direction') === 'incoming' ? 'selected' : '' }}>Masuk</option>
                <option value="outgoing" {{ request('direction') === 'outgoing' ? 'selected' : '' }}>Keluar</option>
            </select>
        </div>
        <button class="btn-elite text-xs">Filter</button>
        <a href="{{ route('admin.wa-bot.conversations.index') }}" class="text-xs underline ink-secondary hover:ink-accent">Reset</a>
    </form>
</div>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Waktu</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">No HP</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Arah</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Pesan</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Perintah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($conversations as $c)
            <tr class="border-t border-rule {{ $c->message_direction === 'incoming' ? 'bg-stone-50' : '' }}">
                <td class="px-4 py-3 text-xs font-mono whitespace-nowrap">{{ $c->created_at->format('d M H:i') }}</td>
                <td class="px-4 py-3 text-xs font-mono">{{ $c->phone }}</td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded {{ $c->message_direction === 'incoming' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ $c->message_direction === 'incoming' ? 'Masuk' : 'Keluar' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs text-stone-700 max-w-md">{{ \Illuminate\Support\Str::limit($c->message_text, 100) }}</td>
                <td class="px-4 py-3 text-xs font-mono">{{ $c->matched_command ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada percakapan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $conversations->links() }}</div>
</div>
@endsection
