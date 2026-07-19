@extends('layouts.school-admin')

@section('title', 'Detail Audit Log')

@section('content')
<a href="{{ route('admin.audit.index') }}" class="text-sm ink-secondary underline mb-3 inline-block">← Kembali</a>

<div class="bg-white border border-rule p-6">
    <h1 class="elite-h2 text-2xl ink-primary mb-4">Audit #{{ $log->id }}</h1>

    <dl class="grid md:grid-cols-2 gap-3 text-sm font-serif mb-4">
        <div><dt class="elite-kicker text-[.6rem]">Waktu</dt><dd>{{ $log->created_at?->format('d/m/Y H:i:s') }}</dd></div>
        <div><dt class="elite-kicker text-[.6rem]">User</dt><dd>{{ $log->causer?->name }} ({{ $log->causer?->email }})</dd></div>
        <div><dt class="elite-kicker text-[.6rem]">Event</dt><dd>{{ $log->event }}</dd></div>
        <div><dt class="elite-kicker text-[.6rem]">Model</dt><dd class="font-mono text-xs">{{ $log->log_name }} #{{ $log->subject_id }}</dd></div>
        <div class="md:col-span-2"><dt class="elite-kicker text-[.6rem]">Deskripsi</dt><dd>{{ $log->description }}</dd></div>
    </dl>

    @php
        $attrs = data_get($log->properties, 'attributes', []);
        $old   = data_get($log->properties, 'old', []);
        $keys  = array_unique(array_merge(array_keys((array) $old), array_keys((array) $attrs)));
    @endphp

    <h3 class="elite-h3 text-lg mb-2">Per-field changes</h3>
    @if(count($keys))
    <table class="w-full text-sm border border-rule">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Field</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Sebelum</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Sesudah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($keys as $k)
                <tr class="border-t border-rule align-top">
                    <td class="px-3 py-2 font-mono text-xs">{{ $k }}</td>
                    <td class="px-3 py-2 text-red-700 font-mono text-xs break-all">{{ is_scalar(data_get($old, $k)) ? data_get($old, $k) : json_encode(data_get($old, $k)) }}</td>
                    <td class="px-3 py-2 text-green-700 font-mono text-xs break-all">{{ is_scalar(data_get($attrs, $k)) ? data_get($attrs, $k) : json_encode(data_get($attrs, $k)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <p class="text-gray-500 font-serif">Tidak ada perubahan per-field tersimpan.</p>
    @endif

    <h3 class="elite-h3 text-lg mb-2 mt-4">Raw properties</h3>
    <pre class="bg-gray-50 border border-rule p-3 text-[.7rem] overflow-auto">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
</div>
@endsection
