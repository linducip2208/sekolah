@extends('layouts.school-admin')
@section('title', 'Sertifikasi Guru')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Certificatio Magistri</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Sertifikasi Guru</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">Kelola sertifikasi profesi guru — sertifikat pendidik, lisensi mengajar, dan lainnya.</p>
        </div>
        <a href="{{ route('admin.training.index') }}" class="btn-elite-ghost">← Diklat</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-5 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Tambah Sertifikasi</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.training.store-certification') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Guru / Staff</label>
                    <select name="staff_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih —</option>
                        @foreach($staffList as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nama Sertifikasi</label>
                    <input name="certification_name" required maxlength="255" placeholder="Sertifikat Pendidik / Sertifikasi Kompetensi" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Lembaga Penerbit</label>
                    <input name="issuing_body" required maxlength="255" placeholder="Kemendikbud / BNSP / LSP" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Nomor Sertifikat</label>
                    <input name="certificate_number" required maxlength="100" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Terbit</label>
                        <input type="date" name="issue_date" required class="w-full border-2 border-rule px-2 py-2 font-serif text-sm">
                    </div>
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Kadaluarsa</label>
                        <input type="date" name="expiry_date" class="w-full border-2 border-rule px-2 py-2 font-serif text-sm">
                    </div>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_primary" value="0">
                    <input type="checkbox" name="is_primary" value="1" class="w-4 h-4">
                    <span class="font-serif text-xs text-gray-700">Sertifikasi Utama</span>
                </label>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Guru</th>
                        <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Sertifikasi</th>
                        <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">No. Sertifikat</th>
                        <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Masa Berlaku</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($certifications as $cert)
                        <tr class="border-t border-rule hover:bg-gray-50 {{ $cert->expiry_date && $cert->expiry_date->isPast() ? 'bg-red-50' : '' }}">
                            <td class="px-3 py-3">
                                <div class="font-serif font-semibold text-sm">{{ $cert->staff->name ?? '—' }}</div>
                                @if($cert->is_primary)<span class="text-[.55rem] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Utama</span>@endif
                            </td>
                            <td class="px-3 py-3">
                                <div class="text-sm">{{ $cert->certification_name }}</div>
                                <div class="text-xs text-gray-500">{{ $cert->issuing_body }}</div>
                            </td>
                            <td class="px-3 py-3 text-xs font-mono">{{ $cert->certificate_number }}</td>
                            <td class="px-3 py-3 text-center text-xs">
                                @if($cert->expiry_date)
                                    @php
                                        $daysLeft = now()->diffInDays($cert->expiry_date, false);
                                    @endphp
                                    @if($daysLeft < 0)
                                        <span class="text-red-600 font-semibold">Kadaluarsa {{ abs($daysLeft) }} hari lalu</span>
                                    @elseif($daysLeft <= 30)
                                        <span class="text-yellow-600 font-semibold">{{ $daysLeft }} hari lagi</span>
                                    @else
                                        <span class="text-green-700">{{ $cert->expiry_date->format('d M Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">Seumur hidup</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right">
                                <button onclick="editCert({{ $cert->id }})" class="text-xs text-gray-600 hover:underline mr-2">Edit</button>
                                <form method="POST" action="{{ route('admin.training.delete-certification', $cert) }}" class="inline" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-10 text-center text-gray-500 italic font-serif">Belum ada sertifikasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $certifications->links() }}</div>
    </div>
</div>

@endsection
