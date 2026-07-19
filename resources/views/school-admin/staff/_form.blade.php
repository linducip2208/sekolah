@php
    $staff = $staff ?? null;
    $isEdit = $staff !== null;
    $u = $isEdit ? $staff->user : null;
    $currentRole = $isEdit ? $u?->roles->first()?->name : null;
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
        <h3 class="elite-h3 text-lg ink-primary mb-3">Akun Staff</h3>
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
                       class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="min. 6 karakter">
            </div>
            @endif
            <div class="md:col-span-2">
                <label class="elite-kicker text-[.6rem] block mb-1">Role / Peran</label>
                <select name="role" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    @foreach($roles as $r)
                        <option value="{{ $r }}" @selected(old('role', $currentRole) === $r)>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="pt-5 border-t border-rule">
        <h3 class="elite-h3 text-lg ink-primary mb-3">Data Kepegawaian</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">NIP / Employee ID</label>
                <input name="employee_id" maxlength="50" value="{{ old('employee_id', $staff?->employee_id) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Departemen</label>
                <input name="department" maxlength="100" value="{{ old('department', $staff?->department) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Akademik / TU / Keuangan">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Jabatan</label>
                <input name="designation" maxlength="100" value="{{ old('designation', $staff?->designation) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Guru Kelas / Wakasek / dll">
            </div>
            <div>
                <label class="elite-kicker text-[.6rem] block mb-1">Tanggal Bergabung</label>
                <input type="date" name="joining_date" value="{{ old('joining_date', $staff?->joining_date?->toDateString()) }}"
                       class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="elite-kicker text-[.6rem] block mb-1">Gaji Pokok (Rp, dalam Rupiah)</label>
                <input type="number" step="1000" min="0" name="basic_salary"
                       value="{{ old('basic_salary', $staff?->basic_salary ? $staff->basic_salary / 100 : '') }}"
                       class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="3500000">
                <p class="text-xs text-gray-500 mt-1">Disimpan sebagai cents untuk akurasi (3.500.000 → 350.000.000 cents).</p>
            </div>
        </div>
    </div>

    <div class="pt-5 border-t border-rule flex gap-3">
        <button type="submit" class="btn-elite">{{ $isEdit ? 'Simpan Perubahan' : 'Tambah Staff' }}</button>
        <a href="{{ route('admin.staff.index') }}" class="btn-elite-ghost">Batal</a>
    </div>
</div>
