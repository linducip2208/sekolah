@extends('layouts.school-admin')
@section('title', 'Log Pengingat')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="max-w-7xl mx-auto">
<div class="mb-7 flex justify-between items-end">
    <div>
        <div class="elite-kicker mb-2">Komunikasi</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Log Pengingat</h1>
        <div class="elite-rule"></div>
    </div>
    <a href="{{ route('admin.reminders.index') }}" class="btn-elite-ghost text-xs">← Jadwal Pengingat</a>
</div>

<div class="bg-white border border-rule p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-stone-600 mb-1">Jadwal</label>
            <select name="schedule_id" class="border-2 border-rule px-3 py-2 text-sm">
                <option value="">Semua Jadwal</option>
                @foreach($schedules as $s)
                    <option value="{{ $s->id }}" {{ request('schedule_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-stone-600 mb-1">Status</label>
            <select name="status" class="border-2 border-rule px-3 py-2 text-sm">
                <option value="">Semua</option>
                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Sukses</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
            </select>
        </div>
        <button class="btn-elite text-xs">Filter</button>
        <a href="{{ route('admin.reminders.logs.index') }}" class="text-xs underline ink-secondary hover:ink-accent">Reset</a>
    </form>
</div>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Waktu</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jadwal</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Target</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Channel</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Error</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr class="border-t border-rule hover:bg-stone-50">
                <td class="px-4 py-3 text-xs font-mono whitespace-nowrap">{{ $log->sent_at?->format('d M Y H:i:s') ?? $log->created_at->format('d M Y H:i:s') }}</td>
                <td class="px-4 py-3 text-xs font-semibold">{{ $log->schedule?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-xs">
                    <div class="font-mono">{{ $log->target_phone ?? $log->target_email ?? '—' }}</div>
                    <div class="text-stone-500 mt-0.5">{{ \Illuminate\Support\Str::limit($log->message_sent, 50) }}</div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700">{{ strtoupper($log->channel) }}</span>
                </td>
                <td class="px-4 py-3">
                    <span class="text-xs px-2 py-1 rounded {{ $log->status === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $log->status === 'success' ? 'Sukses' : 'Gagal' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs text-red-500 max-w-[200px] truncate">{{ $log->error_message ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada log pengingat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
</div>
@endsection
