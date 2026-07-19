@extends('layouts.school-admin')
@section('title', 'Manajemen Ruangan — Booking')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="max-w-5xl space-y-6" x-data="{ showAdd: false, showEdit: false, editRoom: null }">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Manajemen Ruangan</h2>
            <p class="text-sm text-gray-600">Kelola ruangan yang tersedia untuk dipesan oleh guru dan staff.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.facilities.rooms.approvals') }}" class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 hover:bg-gray-50">
                Persetujuan
                @php $pendingCount = \App\Models\RoomBooking\RoomBooking::where('status','pending')->count(); @endphp
                @if($pendingCount > 0)
                    <span class="ml-1 px-1.5 py-0.5 bg-yellow-400 text-white rounded-full text-xs">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.facilities.rooms.calendar') }}" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">
                Kalender
            </a>
            <button @click="showAdd=true" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Ruangan
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($rooms as $room)
        <div class="bg-white rounded-lg shadow p-5 {{ $room->is_active ? '' : 'opacity-60' }}">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-lg">{{ $room->name }}</h3>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        @if($room->room_type === 'classroom') bg-blue-100 text-blue-700
                        @elseif($room->room_type === 'lab') bg-green-100 text-green-700
                        @elseif($room->room_type === 'library') bg-purple-100 text-purple-700
                        @elseif($room->room_type === 'hall') bg-orange-100 text-orange-700
                        @elseif($room->room_type === 'meeting') bg-teal-100 text-teal-700
                        @elseif($room->room_type === 'sports') bg-red-100 text-red-700
                        @else bg-gray-100 text-gray-700
                        @endif
                    ">
                        @switch($room->room_type)
                            @case('classroom')Kelas
                            @break
                            @case('lab')Lab
                            @break
                            @case('library')Perpustakaan
                            @break
                            @case('hall')Aula
                            @break
                            @case('meeting')Meeting
                            @break
                            @case('sports')Olahraga
                            @break
                            @defaultLainnya
                        @endswitch
                    </span>
                </div>
                <span class="text-xs {{ $room->is_active ? 'text-green-600' : 'text-red-600' }}">
                    {{ $room->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <div class="space-y-1 text-sm text-gray-600 mb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    Kapasitas: {{ $room->capacity }} orang
                </div>
                @if($room->floor || $room->building)
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    {{ collect([$room->floor, $room->building])->filter()->join(', ') }}
                </div>
                @endif
            </div>
            @if(!empty($room->facilities))
            <div class="flex flex-wrap gap-1 mb-3">
                @foreach($room->facilities as $facility)
                <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">{{ $facility }}</span>
                @endforeach
            </div>
            @endif
            <div class="flex items-center gap-1 pt-2 border-t">
                <a href="{{ route('admin.facilities.rooms.rules', $room) }}" class="text-xs text-gray-600 hover:underline">Aturan</a>
                <button @click="showEdit=true; editRoom={{ $room->id }}" class="text-xs text-blue-600 hover:underline ml-2">Edit</button>
                <form method="POST" action="{{ route('admin.facilities.rooms.destroy', $room) }}" onsubmit="return confirm('Hapus ruangan ini?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-600 hover:underline ml-2">Hapus</button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-lg shadow p-12 text-center text-gray-400">
            <p class="text-lg mb-2">🏫 Belum ada ruangan</p>
            <p>Klik "Tambah Ruangan" untuk mulai mengelola booking ruangan.</p>
        </div>
        @endforelse
    </div>

    {{-- Add Room Modal --}}
    <div x-show="showAdd" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showAdd=false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md space-y-4 max-h-[90vh] overflow-y-auto">
            <h3 class="font-semibold text-lg">Tambah Ruangan</h3>
            <form method="POST" action="{{ route('admin.facilities.rooms.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Ruangan</label>
                        <input type="text" name="name" required maxlength="200" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Ruang 101">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipe</label>
                        <select name="room_type" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="classroom">Kelas</option>
                            <option value="lab">Laboratorium</option>
                            <option value="library">Perpustakaan</option>
                            <option value="hall">Aula</option>
                            <option value="meeting">Meeting</option>
                            <option value="sports">Olahraga</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Kapasitas</label>
                            <input type="number" name="capacity" min="1" value="30" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Lantai</label>
                            <input type="text" name="floor" maxlength="50" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Lt. 1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Gedung</label>
                            <input type="text" name="building" maxlength="100" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="A">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Fasilitas (pisahkan dengan koma)</label>
                        <input type="text" name="facilities_string" id="facilities-input" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Proyektor, AC, Papan Tulis">
                        <p class="text-xs text-gray-400 mt-0.5">Tambahkan satu per satu lalu tekan Enter.</p>
                        <div id="facilities-tags" class="flex flex-wrap gap-1 mt-2"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Deskripsi</label>
                        <textarea name="description" rows="2" maxlength="1000" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Deskripsi singkat ruangan..."></textarea>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded">
                            <span class="text-sm">Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="showAdd=false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Room Modal --}}
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" x-transition>
        <div class="fixed inset-0 bg-black/40" @click="showEdit=false"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md space-y-4">
            <h3 class="font-semibold text-lg">Edit Ruangan</h3>
            <form method="POST" :action="'/admin/facilities/rooms/' + editRoom + '/update'">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Ruangan</label>
                        <input type="text" name="name" required maxlength="200" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipe</label>
                        <select name="room_type" class="w-full border rounded-lg px-3 py-2 text-sm">
                            <option value="classroom">Kelas</option>
                            <option value="lab">Laboratorium</option>
                            <option value="library">Perpustakaan</option>
                            <option value="hall">Aula</option>
                            <option value="meeting">Meeting</option>
                            <option value="sports">Olahraga</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Kapasitas</label>
                            <input type="number" name="capacity" min="1" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Lantai</label>
                            <input type="text" name="floor" maxlength="50" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Gedung</label>
                            <input type="text" name="building" maxlength="100" class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Fasilitas (pisahkan dengan koma)</label>
                        <input type="text" name="facilities_string" class="w-full border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Deskripsi</label>
                        <textarea name="description" rows="2" maxlength="1000" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="rounded">
                            <span class="text-sm">Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6 pt-4 border-t">
                    <button type="button" @click="showEdit=false" class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">Simpan</button>
                </div>
            </form>
            <div class="border-t pt-4 mt-4">
                <form method="POST" :action="'/admin/facilities/rooms/' + editRoom + '/upload-photo'" enctype="multipart/form-data">
                    @csrf
                    <label class="block text-sm font-medium mb-1">Upload Foto Ruangan</label>
                    <div class="flex items-center gap-2">
                        <input type="file" name="photo" accept="image/*" class="text-sm" required>
                        <button type="submit" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('facilities-input')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const val = this.value.trim();
        if (val) {
            const tags = document.getElementById('facilities-tags');
            const span = document.createElement('span');
            span.className = 'text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full cursor-pointer';
            span.textContent = val;
            span.onclick = function() { this.remove(); };
            tags.appendChild(span);
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'facilities[]';
            hidden.value = val;
            tags.appendChild(hidden);
            this.value = '';
        }
    }
});
</script>
@endpush
@endsection
