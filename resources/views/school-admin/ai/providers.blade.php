@extends('layouts.school-admin')
@section('title', 'AI Providers')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Auxilium Intelligens</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">AI Provider (Sekolah Anda)</h1>
<div class="elite-rule"></div>
<p class="font-serif text-sm text-gray-600 mt-3">Konfigurasi AI provider untuk fitur AI di sekolah ini. Format-agnostic — input nama, URL, dan API key sendiri.</p></div>

<div class="grid lg:grid-cols-3 gap-6">
<div class="lg:col-span-1"><div class="bg-white border border-rule p-6 sticky top-6">
<h3 class="elite-h3 text-base ink-primary mb-3">Tambah Provider</h3>
@if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.ai.providers.store') }}" class="space-y-3">@csrf
<input name="name" required maxlength="200" placeholder="OpenAI / DeepSeek / Groq" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<input name="slug" required pattern="[a-z0-9\-_]+" placeholder="openai-prod" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<select name="api_format" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
@foreach($formats as $f)<option value="{{ $f }}">{{ $f }}</option>@endforeach
</select>
<input type="url" name="base_url" placeholder="https://api.openai.com/v1" class="w-full border-2 border-rule px-3 py-2 font-mono text-xs">
<input type="text" name="api_key" placeholder="sk-..." class="w-full border-2 border-rule px-3 py-2 font-mono text-xs" autocomplete="off">
<input type="number" name="priority" min="0" max="100" value="0" placeholder="Priority" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
<button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Simpan</button>
</form></div></div>

<div class="lg:col-span-2"><div class="space-y-3">
@forelse($providers as $p)
<div class="bg-white border border-rule p-5 {{ !$p->is_active ? 'opacity-50' : '' }}">
<div class="flex justify-between items-start"><div>
<div class="elite-kicker text-[.6rem]" style="color: var(--c-muted);">{{ $p->slug }}</div>
<h4 class="elite-h3 text-base ink-primary mb-1">{{ $p->name }}</h4>
<div class="text-xs space-y-1">
<div><strong>Format:</strong> {{ $p->api_format }}</div>
@if($p->base_url)<div class="font-mono text-gray-600">{{ $p->base_url }}</div>@endif
<div class="text-gray-500">API Key: {{ $p->api_key_encrypted ? '✓ Tersimpan' : '× Belum diset' }}</div>
</div></div>
<form method="POST" action="{{ route('admin.ai.providers.destroy', $p) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
</div></div>
@empty<div class="bg-white border border-rule p-10 text-center text-gray-500 italic font-serif">Belum ada AI provider.</div>
@endforelse
</div></div></div>
@endsection
