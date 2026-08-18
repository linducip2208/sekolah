@extends('layouts.school-admin')
@section('title', 'Preferensi Notifikasi')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="elite-kicker mb-2">Communication</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Preferensi Notifikasi</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-3">Atur channel notifikasi untuk setiap jenis event.</p>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-sm text-green-800">{{ session('success') }}</div>
@endif

<div class="bg-white border border-rule overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-4 py-3 text-left text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Jenis Event</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Email</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">Push</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">SMS</th>
                    <th class="px-4 py-3 text-center text-[.6rem] uppercase tracking-wider text-gray-500 font-semibold">WhatsApp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-rule">
                @foreach($eventTypes as $key => $label)
                @php $pref = $existingPrefs->get($key); @endphp
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-3 font-serif text-sm">{{ $label }}</td>
                    <form method="POST" action="{{ route('admin.notif-prefs.update') }}" class="contents">
                        @csrf
                        <input type="hidden" name="event_type" value="{{ $key }}">
                        <td class="px-4 py-3 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="email_enabled" value="1" {{ $pref && $pref->email_enabled ? 'checked' : '' }}
                                       class="sr-only peer" onchange="this.form.submit()">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-[var(--c-primary)]/30 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--c-primary)]"></div>
                            </label>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="push_enabled" value="1" {{ $pref && $pref->push_enabled ? 'checked' : '' }}
                                       class="sr-only peer" onchange="this.form.submit()">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-[var(--c-primary)]/30 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--c-primary)]"></div>
                            </label>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="sms_enabled" value="1" {{ $pref && $pref->sms_enabled ? 'checked' : '' }}
                                       class="sr-only peer" onchange="this.form.submit()">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-[var(--c-primary)]/30 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--c-primary)]"></div>
                            </label>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="whatsapp_enabled" value="1" {{ $pref && $pref->whatsapp_enabled ? 'checked' : '' }}
                                       class="sr-only peer" onchange="this.form.submit()">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-[var(--c-primary)]/30 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[var(--c-primary)]"></div>
                            </label>
                        </td>
                    </form>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
