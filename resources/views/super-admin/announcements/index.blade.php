@extends('super-admin.layout')
@section('title', 'Pengumuman')
@section('content')

<div class="mb-8">
    <div class="elite-kicker mb-2">Edicta Platforma</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Pengumuman Platform</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-base text-gray-600">Broadcast pengumuman ke semua admin sekolah. Tampil sebagai banner di dashboard mereka.</p>
</div>

<form method="POST" action="{{ route('super.announcements.store') }}" class="bg-white border border-rule p-7 mb-7 space-y-4">
    @csrf
    <div>
        <label class="elite-kicker block mb-2">Judul Pengumuman</label>
        <input type="text" name="title" required maxlength="255"
               class="w-full border-2 border-rule px-4 py-3 font-serif">
    </div>
    <div>
        <label class="elite-kicker block mb-2">Isi Pengumuman</label>
        <textarea name="body" rows="4" required maxlength="5000"
                  class="w-full border-2 border-rule px-4 py-3 font-serif"></textarea>
    </div>
    <div>
        <label class="elite-kicker block mb-2">Tingkat Urgensi</label>
        <select name="level" required class="border-2 border-rule px-4 py-3 font-serif">
            <option value="info">Info — informasi umum</option>
            <option value="warning">Warning — perlu perhatian</option>
            <option value="critical">Critical — harus segera ditindak</option>
        </select>
    </div>
    <button type="submit" class="btn-elite">Kirim ke Semua Sekolah</button>
</form>

<h2 class="elite-h2 text-xl ink-primary mb-4">Riwayat Pengumuman ({{ count($announcements) }})</h2>

<div class="space-y-3">
    @forelse($announcements as $a)
        <div class="bg-white border-l-4 p-5
            {{ ($a['level'] ?? 'info') === 'critical' ? 'border-red-700' : '' }}
            {{ ($a['level'] ?? 'info') === 'warning' ? 'border-yellow-600' : '' }}
            {{ ($a['level'] ?? 'info') === 'info' ? 'border-blue-600' : '' }}">
            <div class="flex justify-between items-start gap-4">
                <div class="flex-1">
                    <div class="flex items-baseline gap-3 mb-1">
                        <h3 class="elite-h3 text-base ink-primary">{{ $a['title'] }}</h3>
                        <span class="text-xs uppercase font-semibold tracking-wider
                            {{ ($a['level'] ?? 'info') === 'critical' ? 'text-red-700' : '' }}
                            {{ ($a['level'] ?? 'info') === 'warning' ? 'text-yellow-700' : '' }}
                            {{ ($a['level'] ?? 'info') === 'info' ? 'text-blue-700' : '' }}">
                            {{ $a['level'] ?? 'info' }}
                        </span>
                    </div>
                    <p class="font-serif text-sm text-gray-700 mb-2">{{ $a['body'] }}</p>
                    <div class="text-xs text-gray-400">{{ $a['created_by'] ?? '—' }} · {{ \Carbon\Carbon::parse($a['created_at'])->diffForHumans() }}</div>
                </div>
                <form method="POST" action="{{ route('super.announcements.destroy', $a['id']) }}">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-700 underline hover:no-underline">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-white border border-rule p-10 text-center text-gray-500 font-serif italic">
            Belum ada pengumuman terkirim.
        </div>
    @endforelse
</div>

@endsection
