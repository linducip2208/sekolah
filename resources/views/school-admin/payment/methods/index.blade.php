@extends('layouts.school-admin')
@section('title', 'Metode Pembayaran')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h2 class="text-xl font-bold">Metode Pembayaran</h2>
        <p class="text-sm text-gray-600">Yang ditampilkan ke orang tua / siswa saat bayar SPP.</p>
    </div>
    <a href="{{ route('admin.payment.methods.create') }}" class="btn-brand">+ Tambah Metode</a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-600">
            <tr>
                <th class="px-4 py-3">Logo</th>
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">Kode</th>
                <th class="px-4 py-3">Provider</th>
                <th class="px-4 py-3">Biaya Admin</th>
                <th class="px-4 py-3">Ditanggung</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($methods as $m)
                <tr>
                    <td class="px-4 py-2">
                        @if($m->logo_url)
                            <img src="{{ $m->logo_url }}" alt="" class="h-6">
                        @else
                            <div class="w-6 h-6 bg-gray-200 rounded"></div>
                        @endif
                    </td>
                    <td class="px-4 py-2 font-medium">{{ $m->display_name }}</td>
                    <td class="px-4 py-2"><code class="text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $m->code }}</code></td>
                    <td class="px-4 py-2 text-gray-600">{{ $m->provider->name }} <span class="text-xs text-gray-400">({{ $m->provider->api_format }})</span></td>
                    <td class="px-4 py-2">
                        Rp {{ number_format($m->fee_flat / 100, 0, ',', '.') }}
                        @if($m->fee_percent_bp)
                            + {{ $m->fee_percent_bp / 100 }}%
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ $m->feeBorneByParent() ? 'Orang tua' : 'Sekolah' }}</td>
                    <td class="px-4 py-2">
                        @if($m->is_active)
                            <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span> Aktif
                        @else
                            <span class="inline-block w-2 h-2 rounded-full bg-gray-400"></span> Nonaktif
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <a href="{{ route('admin.payment.methods.edit', $m->id) }}" class="text-brand-primary hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.payment.methods.destroy', $m->id) }}" class="inline" onsubmit="return confirm('Hapus metode?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-500">
                    Belum ada metode. Tambahkan metode setelah Anda punya minimal 1 provider aktif.
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
