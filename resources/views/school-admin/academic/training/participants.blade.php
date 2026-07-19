@extends('layouts.school-admin')
@section('title', 'Peserta — ' . $training->title)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Participantes</div>
            <h1 class="elite-h1 text-2xl ink-primary mb-2">{{ $training->title }}</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-2">{{ $training->provider ?? 'Mandiri' }} · {{ $training->start_date->format('d M Y') }} · {{ $training->duration_hours }} jam</p>
        </div>
        <a href="{{ route('admin.training.index') }}" class="btn-elite-ghost">← Kembali</a>
    </div>
</div>

<div class="grid sm:grid-cols-3 gap-4 mb-6">
    <div class="elite-card p-4 text-center">
        <div class="font-display text-2xl ink-accent">{{ $completion['total'] }}</div>
        <div class="elite-kicker text-[.6rem]">Total Peserta</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="font-display text-2xl ink-accent">{{ $completion['completed'] }}</div>
        <div class="elite-kicker text-[.6rem]">Selesai</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="font-display text-2xl ink-accent">{{ $completion['rate'] }}%</div>
        <div class="elite-kicker text-[.6rem]">Tingkat Kelulusan</div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-5">
            <h3 class="elite-h3 text-base ink-primary mb-3">Daftarkan Peserta</h3>
            <form method="POST" action="{{ route('admin.training.register-participant', $training) }}">
                @csrf
                <div class="mb-3">
                    <label class="elite-kicker text-[.6rem] block mb-1">Pilih Guru / Staff</label>
                    <select name="staff_ids[]" multiple required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" style="min-height:200px;">
                        @foreach($staffList as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Ctrl+klik untuk multi-pilih</p>
                </div>
                <button class="btn-elite w-full text-xs" style="padding:.6rem;">Daftarkan</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Nama</th>
                        <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Status</th>
                        <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Nilai</th>
                        <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">No. Sertifikat</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $p)
                        <tr class="border-t border-rule hover:bg-gray-50">
                            <td class="px-3 py-3">
                                <div class="font-serif font-semibold text-sm">{{ $p->staff->name ?? '—' }}</div>
                                @if($p->feedback)<div class="text-xs text-gray-500 italic mt-0.5">"{{ Str::limit($p->feedback, 60) }}"</div>@endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                <form method="POST" action="{{ route('admin.training.update-participant', [$training, $p]) }}" class="inline-flex items-center gap-1">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" class="border border-rule px-2 py-1 text-xs rounded">
                                        @foreach(['registered'=>'Terdaftar','attended'=>'Hadir','completed'=>'Selesai','absent'=>'Tidak Hadir'] as $val => $label)
                                            <option value="{{ $val }}" {{ $p->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <form method="POST" action="{{ route('admin.training.update-participant', [$training, $p]) }}" class="inline-flex items-center gap-1">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $p->status }}">
                                    <input type="number" name="score" value="{{ $p->score }}" min="0" max="100" placeholder="0-100" onchange="this.form.submit()" style="width:55px;" class="border border-rule px-2 py-1 text-xs rounded text-center font-mono">
                                </form>
                            </td>
                            <td class="px-3 py-3 text-xs font-mono">
                                @if($p->certificate_number)
                                    <span class="text-green-700">{{ $p->certificate_number }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right">
                                @if($p->certificate_number)
                                    <a href="{{ route('admin.training.certificate-pdf', [$training, $p]) }}" class="text-xs ink-accent hover:underline mr-2">PDF</a>
                                @endif
                                @if(!$p->certificate_number && in_array($p->status, ['attended','completed']))
                                    <form method="POST" action="{{ route('admin.training.issue-certificate', [$training, $p]) }}" class="inline">
                                        @csrf
                                        <button class="text-xs text-green-700 hover:underline mr-2">Terbitkan Sertifikat</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.training.remove-participant', [$training, $p]) }}" class="inline" onsubmit="return confirm('Hapus peserta?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-gray-500 italic font-serif">Belum ada peserta. Daftarkan guru/staff di panel kiri.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
