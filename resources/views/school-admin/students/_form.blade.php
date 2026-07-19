@php
    $student = $student ?? null;
    $isEdit = $student !== null;
    $u = $isEdit ? $student->user : null;
@endphp

@if($errors->any())
    <div class="mb-5 px-5 py-3 bg-red-50 border-l-4 border-red-700">
        <ul class="list-disc list-inside font-serif text-sm text-red-800">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="bg-white border border-rule p-7 space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <h3 class="elite-h3 text-lg ink-primary mb-3">Akun Siswa</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Nama Lengkap</label>
                <input name="name" required maxlength="200" value="{{ old('name', $u?->name) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Email</label>
                <input type="email" name="email" required maxlength="200" value="{{ old('email', $u?->email) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">No. HP</label>
                <input name="phone" maxlength="30" value="{{ old('phone', $u?->phone) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
            @if(!$isEdit)
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Password</label>
                <input type="text" name="password" required minlength="6"
                       class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"
                       placeholder="min. 6 karakter">
            </div>
            @endif
        </div>
    </div>

    <div class="pt-5 border-t border-rule">
        <h3 class="elite-h3 text-lg ink-primary mb-3">Data Akademik</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">NIS / Admission No</label>
                <input name="admission_no" required maxlength="50" value="{{ old('admission_no', $student?->admission_no) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Masuk</label>
                <input type="date" name="admission_date" value="{{ old('admission_date', $student?->admission_date?->toDateString()) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Rombel</label>
                <select name="class_section_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">— belum ditempatkan —</option>
                    @foreach($classSections as $cs)
                        <option value="{{ $cs->id }}" @selected(old('class_section_id', $student?->class_section_id) == $cs->id)>
                            {{ $cs->classRoom?->name }} {{ $cs->section?->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Gender</label>
                <select name="gender" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="male" @selected(old('gender', $student?->gender) === 'male')>Laki-laki</option>
                    <option value="female" @selected(old('gender', $student?->gender) === 'female')>Perempuan</option>
                    <option value="other" @selected(old('gender', $student?->gender) === 'other')>Lainnya</option>
                </select>
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Lahir</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student?->date_of_birth?->toDateString()) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Golongan Darah</label>
                <input name="blood_group" maxlength="10" value="{{ old('blood_group', $student?->blood_group) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="A / B / AB / O">
            </div>
            <div class="md:col-span-2">
                <label class="elite-kicker text-[.6rem] block mb-1">Alamat</label>
                <textarea name="address" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">{{ old('address', $student?->address) }}</textarea>
            </div>
        </div>
    </div>

    <div class="pt-5 border-t border-rule">
        <h3 class="elite-h3 text-lg ink-primary mb-3">Wali Murid</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Nama Wali</label>
                <input name="guardian_name" maxlength="200" value="{{ old('guardian_name', $student?->guardian_name) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">No. HP Wali</label>
                <input name="guardian_phone" maxlength="30" value="{{ old('guardian_phone', $student?->guardian_phone) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="elite-kicker text-[.6rem] block mb-1">No. WhatsApp (notifikasi ke orang tua)</label>
                <input name="whatsapp_phone" maxlength="30" value="{{ old('whatsapp_phone', $student?->whatsapp_phone) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="62812xxxxxxxx">
            </div>
        </div>
    </div>

    <div class="pt-5 border-t border-rule flex gap-3">
        <button type="submit" class="btn-elite">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Siswa' }}</button>
        <a href="{{ route('admin.students.index') }}" class="btn-elite-ghost">Batal</a>
    </div>
</div>
