@extends('super-admin.layout')
@section('title', 'Email Templates')
@section('content')
<div class="mb-7"><div class="elite-kicker mb-2">Schedulae Litterae</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Email Templates</h1><div class="elite-rule"></div></div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 text-sm text-green-800 border-l-4 border-green-700">{{ session('success') }}</div>@endif

<div class="space-y-6">
@foreach($templates as $key => $tpl)
<form method="POST" action="{{ route('super.email-templates.save') }}" class="bg-white border border-rule p-6">
@csrf
<input type="hidden" name="key" value="{{ $key }}">
<div class="elite-kicker text-[.6rem] mb-1" style="color:var(--c-accent);">{{ $key }}</div>
<input name="subject" required maxlength="200" value="{{ $tpl['subject'] }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm mb-2" placeholder="Subject">
<textarea name="body" rows="4" required maxlength="10000" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">{{ $tpl['body'] }}</textarea>
<div class="mt-2 flex justify-between items-center">
<span class="text-xs text-gray-500 italic">Variables: {admin_name}, {school_name}, {login_url}, dll.</span>
<button class="text-xs btn-elite" style="padding:.4rem .8rem;font-size:.6rem;">Simpan</button>
</div>
</form>
@endforeach
</div>
@endsection
