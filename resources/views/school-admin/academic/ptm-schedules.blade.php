@extends('layouts.school-admin')
@section('title', 'Jadwal PTM')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Parent-Teacher Meeting</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Jadwal PTM</h1>
    <div class="elite-rule"></div>
</div>

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-sm text-green-800 rounded">{{ session('success') }}</div>
@endif

<div class="grid lg:grid-cols-3 gap-6">

    {{-- Form Panel --}}
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Buat Jadwal PTM</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.ptm-schedules.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Siswa</label>
                    <select name="student_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— Pilih Siswa —</option>
                        @foreach(\App\Models\Academic\Student::where('school_id', auth()->user()->school_id)->where('status', 'active')->with('user:id,name')->orderBy('admission_no')->get() as $stu)
                            <option value="{{ $stu->id }}">{{ $stu->user?->name ?? '—' }} ({{ $stu->admission_no }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Orang Tua</label>
                    <select name="parent_user_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— Pilih Orang Tua —</option>
                        @foreach(\App\Models\User::where('school_id', auth()->user()->school_id)->whereHas('roles', fn($q) => $q->whereIn('name', ['parent']))->orderBy('name')->get() as $par)
                            <option value="{{ $par->id }}">{{ $par->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Guru</label>
                    <select name="teacher_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— Pilih Guru —</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Kelas</label>
                    <select name="class_room_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— Opsional —</option>
                        @foreach(\App\Models\Academic\ClassRoom::where('school_id', auth()->user()->school_id)->orderBy('name')->get() as $cr)
                            <option value="{{ $cr->id }}">{{ $cr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Tanggal</label>
                        <input type="date" name="meeting_date" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    </div>
                    <div>
                        <label class="elite-kicker text-[.6rem] block mb-1">Jam Mulai</label>
                        <input type="time" name="start_time" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    </div>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Jam Selesai</label>
                    <input type="time" name="end_time" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Opsional"></textarea>
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
            </form>
        </div>
    </div>

    {{-- Table Panel --}}
    <div class="lg:col-span-2">
        {{-- Filter Bar --}}
        <div class="bg-white border border-rule p-4 mb-4">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Status</label>
                    <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">Semua</option>
                        <option value="scheduled" {{ request('status')==='scheduled'?'selected':'' }}>Terjadwal</option>
                        <option value="completed" {{ request('status')==='completed'?'selected':'' }}>Selesai</option>
                        <option value="cancelled" {{ request('status')==='cancelled'?'selected':'' }}>Dibatalkan</option>
                        <option value="no_show" {{ request('status')==='no_show'?'selected':'' }}>Tidak Hadir</option>
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Sampai</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <button class="btn-elite" style="padding:.5rem .8rem;font-size:.6rem;">Filter</button>
                <a href="{{ route('admin.ptm-schedules.index') }}" class="text-xs text-gray-500 hover:underline py-2">Reset</a>
            </form>
        </div>

        {{-- Action Buttons --}}
        <div class="flex justify-between items-center mb-3">
            <span class="text-xs text-gray-500">{{ $schedules->total() }} jadwal</span>
            <form method="POST" action="{{ route('admin.ptm-schedules.send-reminders') }}" class="inline"
                  onsubmit="return confirm('Kirim pengingat PTM H-7 untuk semua jadwal yang belum diingatkan?')">
                @csrf
                <button class="btn-elite" style="padding:.4rem .7rem;font-size:.58rem;">Kirim Pengingat (H-7)</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-rule overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Tanggal</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Jam</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Siswa</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Orang Tua</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Guru</th>
                        <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                        <th class="px-4 py-3 elite-kicker text-[.6rem]">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $s)
                        <tr class="border-t border-rule" x-data="{ edit: false }">
                            <td class="px-4 py-3 font-serif text-sm ink-primary whitespace-nowrap">{{ $s->meeting_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">{{ substr($s->start_time, 0, 5) }}{{ $s->end_time ? ' – '.substr($s->end_time, 0, 5) : '' }}</td>
                            <td class="px-4 py-3 font-serif text-sm">{{ $s->student?->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $s->parent?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $s->teacher?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($s->status === 'scheduled')
                                    <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">Terjadwal</span>
                                @elseif($s->status === 'completed')
                                    <span class="text-xs font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded">Selesai</span>
                                @elseif($s->status === 'cancelled')
                                    <span class="text-xs font-semibold text-red-700 bg-red-50 px-2 py-0.5 rounded">Dibatalkan</span>
                                @else
                                    <span class="text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded">Tidak Hadir</span>
                                @endif
                                @if($s->reminder_sent)
                                    <span class="text-[10px] text-gray-400 ml-1" title="Pengingat sudah dikirim">&#10003;</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button @click="edit = !edit" class="text-xs underline ink-secondary hover:ink-accent">Edit</button>
                                <form method="POST" action="{{ route('admin.ptm-schedules.update', $s) }}" class="inline ml-1">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="meeting_date" value="{{ $s->meeting_date->format('Y-m-d') }}">
                                    <input type="hidden" name="start_time" value="{{ $s->start_time }}">
                                    <input type="hidden" name="end_time" value="{{ $s->end_time }}">
                                    <input type="hidden" name="notes" value="{{ $s->notes }}">
                                    <input type="hidden" name="follow_up" value="{{ $s->follow_up }}">
                                    <select name="status" onchange="this.form.submit()" class="border border-rule rounded px-1 py-0.5 text-xs">
                                        <option value="scheduled" {{ $s->status==='scheduled'?'selected':'' }}>Terjadwal</option>
                                        <option value="completed" {{ $s->status==='completed'?'selected':'' }}>Selesai</option>
                                        <option value="cancelled" {{ $s->status==='cancelled'?'selected':'' }}>Batal</option>
                                        <option value="no_show" {{ $s->status==='no_show'?'selected':'' }}>No Show</option>
                                    </select>
                                </form>
                                <form method="POST" action="{{ route('admin.ptm-schedules.destroy', $s) }}" class="inline ml-1"
                                      onsubmit="return confirm('Hapus jadwal ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">&#10005;</button>
                                </form>
                            </td>
                        </tr>
                        <tr x-show="edit" x-cloak class="bg-gray-50">
                            <td colspan="7" class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.ptm-schedules.update', $s) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="elite-kicker text-[.6rem] block mb-1">Catatan</label>
                                        <textarea name="notes" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">{{ $s->notes }}</textarea>
                                    </div>
                                    <div>
                                        <label class="elite-kicker text-[.6rem] block mb-1">Follow Up</label>
                                        <textarea name="follow_up" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">{{ $s->follow_up }}</textarea>
                                    </div>
                                    <div class="flex items-end">
                                        <input type="hidden" name="meeting_date" value="{{ $s->meeting_date->format('Y-m-d') }}">
                                        <input type="hidden" name="start_time" value="{{ $s->start_time }}">
                                        <input type="hidden" name="end_time" value="{{ $s->end_time }}">
                                        <input type="hidden" name="status" value="{{ $s->status }}">
                                        <button class="btn-elite" style="padding:.4rem .8rem;font-size:.6rem;">Update</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-10 text-center text-gray-500 italic font-serif">Belum ada jadwal PTM.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $schedules->links() }}</div>
    </div>
</div>

@endsection
