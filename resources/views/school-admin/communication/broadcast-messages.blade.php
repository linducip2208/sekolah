@extends('layouts.school-admin')
@section('title', 'Broadcast Messages')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7 flex flex-wrap items-end justify-between gap-3">
    <div>
        <div class="elite-kicker mb-2">Communication</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Broadcast Messages</h1>
        <div class="elite-rule"></div>
        <p class="font-serif text-sm text-gray-600 mt-3">Kirim pesan massal ke siswa, orang tua, guru, atau staff.</p>
    </div>
    <a href="{{ route('admin.broadcast.create') }}" class="btn-elite" style="padding:.5rem 1.2rem;font-size:.65rem;">+ Pesan Baru</a>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-sm text-green-800">{{ session('success') }}</div>
@endif

<div class="bg-white border border-rule overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-3 text-left text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Judul</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Channel</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Segment</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Status</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Recipients</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Dibuat</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-rule">
                @forelse($messages as $msg)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-3 font-serif text-sm">{{ $msg->title }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-[.6rem] uppercase px-2 py-1 rounded font-semibold
                            {{ $msg->channel === 'all' ? 'bg-blue-100 text-blue-700' : ($msg->channel === 'whatsapp' ? 'bg-green-100 text-green-700' : ($msg->channel === 'email' ? 'bg-purple-100 text-purple-700' : ($msg->channel === 'sms' ? 'bg-amber-100 text-amber-700' : 'bg-cyan-100 text-cyan-700'))) }}">
                            {{ $msg->channel }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs">{{ ucfirst($msg->segment) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-[.6rem] uppercase px-2 py-1 rounded font-semibold
                            {{ $msg->status === 'sent' ? 'bg-green-100 text-green-700' : ($msg->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : ($msg->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) }}">
                            {{ $msg->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs">{{ $msg->recipient_count }}</td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $msg->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            @if($msg->status === 'draft' || $msg->status === 'scheduled')
                            <form method="POST" action="{{ route('admin.broadcast.send', $msg) }}" class="inline" onsubmit="return confirm('Kirim pesan ini sekarang?')">
                                @csrf
                                <button type="submit" class="text-green-600 text-xs hover:underline">Kirim</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.broadcast.destroy', $msg) }}" class="inline" onsubmit="return confirm('Hapus pesan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 text-xs hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400 font-serif text-sm">Belum ada pesan broadcast.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-rule">
        {{ $messages->links() }}
    </div>
</div>

@endsection
