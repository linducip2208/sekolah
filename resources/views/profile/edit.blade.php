@extends('layouts.parent')
@section('title', 'Profil Saya')
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Persona</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Profil Saya</h1><div class="elite-rule"></div></div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 text-sm text-green-800 border-l-4 border-green-700">{{ session('success') }}</div>@endif

<div class="grid lg:grid-cols-2 gap-6">
<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">Edit Profil</h3>
@if($errors->updateProfile->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->updateProfile->first() }}</div>@endif
<form method="POST" action="{{ route('profile.update') }}" class="space-y-3">@csrf @method('PUT')
<div><label class="elite-kicker text-[.6rem] block mb-1">Nama</label>
<input name="name" required maxlength="200" value="{{ old('name', $user->name) }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Email</label>
<input type="email" name="email" required maxlength="200" value="{{ old('email', $user->email) }}" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">No. HP</label>
<input name="phone" maxlength="30" value="{{ old('phone', $user->phone) }}" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Bahasa</label>
<select name="locale" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
<option value="id" @selected($user->locale === 'id')>🇮🇩 Indonesia</option>
<option value="en" @selected($user->locale === 'en')>🇬🇧 English</option>
</select></div>
<button class="btn-elite">Simpan Profil</button>
</form>
</div>

<div class="bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-4">Ganti Password</h3>
@if($errors->any())@foreach($errors->all() as $e)<div class="mb-2 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $e }}</div>@endforeach @endif
<form method="POST" action="{{ route('profile.password') }}" class="space-y-3">@csrf
<div><label class="elite-kicker text-[.6rem] block mb-1">Password Lama</label>
<input type="password" name="current_password" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Password Baru (min 8)</label>
<input type="password" name="new_password" required minlength="8" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
<div><label class="elite-kicker text-[.6rem] block mb-1">Konfirmasi Password Baru</label>
<input type="password" name="new_password_confirmation" required class="w-full border-2 border-rule px-3 py-2 font-mono text-sm"></div>
<button class="btn-elite">Ganti Password</button>
</form>
</div>
</div>

<div class="mt-6 bg-white border border-rule p-7">
<h3 class="elite-h3 text-lg ink-primary mb-2">Info Akun</h3>
<table class="w-full text-sm"><tbody>
<tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem] w-40">Role</td><td class="py-2">@foreach($user->roles as $r)<span class="elite-kicker text-[.55rem] mr-1" style="color:var(--c-accent);">{{ $r->name }}</span>@endforeach</td></tr>
<tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem]">Sekolah</td><td class="py-2">{{ $user->school?->name ?? '— Platform —' }}</td></tr>
<tr class="border-b border-rule"><td class="py-2 elite-kicker text-[.6rem]">Status</td><td class="py-2">@if($user->is_active)<span class="text-xs text-green-700">● Aktif</span>@else<span class="text-xs text-red-700">● Nonaktif</span>@endif</td></tr>
<tr><td class="py-2 elite-kicker text-[.6rem]">Bergabung Sejak</td><td class="py-2">{{ $user->created_at?->format('d M Y') }}</td></tr>
</tbody></table>
</div>

@endsection
