@extends('layouts.school-admin')
@section('title', 'Import Data')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Importatio</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Bulk Import CSV</h1><div class="elite-rule"></div>
<p class="font-serif text-base text-gray-600 mt-3">Migrasi data dari sekolah lama via upload file CSV.</p></div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 text-sm text-green-800 border-l-4 border-green-700">{{ session('success') }}</div>@endif
@if($errors->any())<div class="mb-4 p-3 bg-red-50 text-sm text-red-800 border-l-4 border-red-700">{{ $errors->first() }}</div>@endif

<div class="grid lg:grid-cols-2 gap-6">

<div class="bg-white border border-rule p-7">
<div class="elite-kicker mb-2" style="color: var(--c-accent);">Import Siswa</div>
<h3 class="elite-h3 text-xl ink-primary mb-4">Upload CSV Siswa</h3>
<p class="font-serif text-sm text-gray-600 mb-4">Upload daftar siswa massal. Format CSV harus punya header: <code class="font-mono text-xs bg-gray-100 px-1">admission_no, name, email, gender, password</code> + opsional <code class="font-mono text-xs bg-gray-100 px-1">phone, date_of_birth, address, guardian_name, guardian_phone</code>.</p>

<a href="{{ route('admin.import.template.students') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent inline-block mb-4">⤓ Download Template CSV →</a>

<form method="POST" action="{{ route('admin.import.students') }}" enctype="multipart/form-data" class="space-y-3">@csrf
<div>
<label class="elite-kicker text-[.6rem] block mb-1">Tempatkan ke Rombel (opsional)</label>
<select name="class_section_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="">— belum ditempatkan —</option>
@foreach($classSections as $cs)<option value="{{ $cs->id }}">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</option>@endforeach
</select>
</div>
<div>
<label class="elite-kicker text-[.6rem] block mb-1">File CSV</label>
<input type="file" name="file" required accept=".csv,text/csv" class="w-full border-2 border-rule px-3 py-2 text-sm">
</div>
<button class="btn-elite">Upload & Import</button>
</form>
</div>

<div class="bg-white border border-rule p-7">
<div class="elite-kicker mb-2" style="color: var(--c-accent);">Import Staff</div>
<h3 class="elite-h3 text-xl ink-primary mb-4">Upload CSV Staff/Guru</h3>
<p class="font-serif text-sm text-gray-600 mb-4">Upload daftar staff/guru. Header wajib: <code class="font-mono text-xs bg-gray-100 px-1">name, email, role, password</code>. Opsional: <code class="font-mono text-xs bg-gray-100 px-1">employee_id, phone, department, designation, joining_date, basic_salary_rupiah</code>.</p>
<p class="text-xs text-gray-500 mb-4">Role yang valid: <code>teacher</code>, <code>admin</code>, <code>accountant</code>, <code>librarian</code>, <code>counselor</code>, <code>nurse</code>, <code>receptionist</code>.</p>

<a href="{{ route('admin.import.template.staff') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent inline-block mb-4">⤓ Download Template CSV →</a>

<form method="POST" action="{{ route('admin.import.staff') }}" enctype="multipart/form-data" class="space-y-3">@csrf
<div>
<label class="elite-kicker text-[.6rem] block mb-1">File CSV</label>
<input type="file" name="file" required accept=".csv,text/csv" class="w-full border-2 border-rule px-3 py-2 text-sm">
</div>
<button class="btn-elite">Upload & Import</button>
</form>
</div>

</div>

<div class="mt-6 deco-frame">
<div class="bg-white p-7">
<div class="elite-kicker mb-2" style="color: var(--c-accent);">Tips</div>
<ul class="font-serif text-sm text-gray-700 space-y-2 list-disc list-inside">
<li>Pastikan email tidak duplikat dengan user existing — duplikat akan di-skip.</li>
<li>Kolom <code class="font-mono">password</code> akan di-hash. Beritahu siswa/staff untuk ganti password setelah login pertama.</li>
<li>Ukuran file maksimal 5 MB.</li>
<li>Format tanggal: <code>YYYY-MM-DD</code> (mis. 2010-05-15).</li>
<li>Untuk migrasi besar (1000+), import per batch 200-500 baris untuk hindari timeout.</li>
</ul>
</div>
</div>

@endsection
