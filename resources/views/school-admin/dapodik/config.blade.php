@extends('layouts.school-admin')
@section('title', 'Konfigurasi Dapodik')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Configuratio Dapodik</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Konfigurasi Sinkronisasi Dapodik</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Kredensial Dapodik (Kemendikbud) untuk sinkronisasi data sekolah ke pemerintah.</p></div>

<div class="max-w-2xl">
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.dapodik.config.update') }}" class="bg-white border border-rule p-7 space-y-4">
@csrf @method('PUT')
<div>
<label class="elite-kicker text-[.6rem] block mb-1">NPSN</label>
<input name="npsn" required maxlength="15" value="{{ old('npsn', $config->npsn) }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
</div>
<div>
<label class="elite-kicker text-[.6rem] block mb-1">Endpoint URL</label>
<input type="url" name="endpoint_url" value="{{ old('endpoint_url', $config->endpoint_url) }}" maxlength="500" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm" placeholder="https://api.dapodik.kemdikbud.go.id">
</div>
<div class="pt-3 border-t border-rule">
<p class="font-serif text-xs text-gray-500 italic mb-3">Kosongkan kalau tidak ingin mengubah kredensial yang tersimpan.</p>
<div class="grid grid-cols-2 gap-3">
<div><label class="elite-kicker text-[.6rem] block mb-1">Username @if($config->username_encrypted)<span class="text-green-700">(tersimpan)</span>@endif</label>
<input type="text" name="username" maxlength="200" autocomplete="off" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Password @if($config->password_encrypted)<span class="text-green-700">(tersimpan)</span>@endif</label>
<input type="password" name="password" maxlength="200" autocomplete="off" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs"></div>
</div></div>
<div class="pt-3"><button class="btn-elite">Simpan Konfigurasi</button></div>
</form>
</div>
@endsection
