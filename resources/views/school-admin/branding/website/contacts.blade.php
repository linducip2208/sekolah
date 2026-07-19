@extends('layouts.school-admin')
@section('title', 'Website — Kotak Masuk Kontak')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Kotak Masuk Kontak</h2>
            <p class="text-sm text-gray-600">Pesan dari form kontak website sekolah. {{ $unreadCount > 0 ? "{$unreadCount} belum dibaca." : 'Semua sudah dibaca.' }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                <tr>
                    <th class="text-left px-4 py-3" style="width:30px"></th>
                    <th class="text-left px-4 py-3">Pengirim</th>
                    <th class="text-left px-4 py-3">Pesan</th>
                    <th class="text-left px-4 py-3">Tanggal</th>
                    <th class="text-center px-4 py-3" style="width:100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                <tr class="border-t hover:bg-gray-50 {{ $msg->is_read ? '' : 'bg-blue-50/30 font-semibold' }}">
                    <td class="px-4 py-3">
                        @if(!$msg->is_read)
                            <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div>{{ $msg->name }}</div>
                        <div class="text-xs text-gray-400">{{ $msg->email }}</div>
                        @if($msg->phone)
                            <div class="text-xs text-gray-400">{{ $msg->phone }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 max-w-sm">
                        <div class="line-clamp-2">{{ $msg->message }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $msg->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            @if(!$msg->is_read)
                            <form method="POST" action="{{ route('admin.branding.website.contacts.read', $msg) }}">
                                @csrf
                                <button class="text-xs text-blue-600 hover:underline" title="Tandai sudah dibaca">Baca</button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('admin.branding.website.contacts.destroy', $msg) }}" onsubmit="return confirm('Hapus pesan ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:underline">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-400">Belum ada pesan masuk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
