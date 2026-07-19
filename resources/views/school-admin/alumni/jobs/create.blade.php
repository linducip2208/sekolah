@extends('layouts.school-admin')
@section('title', isset($listing) ? 'Edit Lowongan' : 'Posting Lowongan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<a href="{{ route('admin.jobs.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali ke Job Board</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Jaringan Profesional</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ isset($listing) ? 'Edit Lowongan' : 'Posting Lowongan Baru' }}</h1>
    <div class="elite-rule"></div>
</div>

<div class="elite-card p-8 max-w-3xl">
    <form method="POST" action="{{ isset($listing) ? route('admin.jobs.update', $listing) : route('admin.jobs.store') }}">
        @csrf
        @if(isset($listing)) @method('PUT') @endif

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">Posting Oleh (Alumni) *</label>
                <select name="alumni_profile_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                    <option value="">— Pilih Alumni —</option>
                    @foreach($alumni as $a)
                    <option value="{{ $a->id }}" {{ (isset($listing) && $listing->alumni_profile_id === $a->id) ? 'selected' : '' }}>
                        {{ $a->user?->name }} — Lulus {{ $a->graduation_year }} ({{ $a->current_position ?? '—' }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">Nama Perusahaan *</label>
                    <input type="text" name="company_name" required maxlength="200"
                           value="{{ old('company_name', $listing->company_name ?? '') }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Contoh: PT Teknologi Nusantara">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">Posisi *</label>
                    <input type="text" name="position_title" required maxlength="200"
                           value="{{ old('position_title', $listing->position_title ?? '') }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Contoh: Software Engineer">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">Tipe Pekerjaan *</label>
                    <select name="job_type" required class="w-full border-2 border-rule px-3 py-2 text-sm">
                        @foreach($jobTypes as $key => $label)
                        <option value="{{ $key }}" {{ old('job_type', $listing->job_type ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">Lokasi</label>
                    <input type="text" name="location" maxlength="200"
                           value="{{ old('location', $listing->location ?? '') }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Contoh: Jakarta Selatan">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">Gaji</label>
                    <input type="text" name="salary_range" maxlength="100"
                           value="{{ old('salary_range', $listing->salary_range ?? '') }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="Contoh: Rp 5-10jt/bulan">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">Deskripsi Pekerjaan</label>
                <textarea name="description" rows="6"
                          class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"
                          placeholder="Deskripsikan tanggung jawab dan detail pekerjaan...">{{ old('description', $listing->description ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">Persyaratan</label>
                <textarea name="requirements" rows="5"
                          class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"
                          placeholder="Sebutkan kualifikasi, skill, dan pengalaman yang dibutuhkan...">{{ old('requirements', $listing->requirements ?? '') }}</textarea>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">URL Aplikasi</label>
                    <input type="url" name="application_url" maxlength="500"
                           value="{{ old('application_url', $listing->application_url ?? '') }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="https://...">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">Email Lamaran</label>
                    <input type="email" name="application_email" maxlength="200"
                           value="{{ old('application_email', $listing->application_email ?? '') }}"
                           class="w-full border-2 border-rule px-3 py-2 font-serif text-sm" placeholder="hr@perusahaan.com">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider mb-1 ink-primary">Tanggal Kadaluarsa</label>
                <input type="date" name="expires_at"
                       value="{{ old('expires_at', isset($listing) && $listing->expires_at ? $listing->expires_at->format('Y-m-d') : '') }}"
                       class="border-2 border-rule px-3 py-2 text-sm">
            </div>

            <div class="pt-4 flex items-center gap-4">
                <button type="submit" class="btn-elite-gold">
                    {{ isset($listing) ? 'Simpan Perubahan' : 'Posting Lowongan' }}
                </button>
                <a href="{{ route('admin.jobs.index') }}" class="btn-elite-ghost text-xs">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
