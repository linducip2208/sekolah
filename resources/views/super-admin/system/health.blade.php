@extends('super-admin.layout')
@section('title', 'Health Check')
@section('content')

<div class="mb-8">
    <div class="elite-kicker mb-2">Diagnostica Systematica</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">System Health Check</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-base text-gray-600">Status koneksi database, cache, queue, storage, dan info server.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @foreach($checks as $name => $check)
        @php
            $status = $check['status'] ?? 'info';
            $color = $status === 'ok' ? 'green' : ($status === 'fail' ? 'red' : ($status === 'warn' ? 'yellow' : 'blue'));
        @endphp
        <div class="bg-white border-l-4 border-{{ $color }}-600 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="elite-kicker">{{ ucfirst($name) }}</span>
                <span class="px-2 py-0.5 text-xs font-semibold uppercase rounded
                    {{ $status === 'ok' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $status === 'fail' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $status === 'warn' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $status === 'info' ? 'bg-blue-100 text-blue-700' : '' }}">
                    {{ $status }}
                </span>
            </div>
            <p class="font-mono text-xs text-gray-700">{{ $check['message'] ?? '' }}</p>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white border border-rule p-7">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Server Info</h3>
        <table class="w-full text-sm">
            @foreach($serverInfo as $key => $value)
                <tr class="border-b border-rule last:border-0">
                    <td class="py-2 elite-kicker text-[.6rem]">{{ str_replace('_', ' ', $key) }}</td>
                    <td class="py-2 font-mono text-right text-gray-700">{{ $value }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="bg-white border border-rule p-7">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Database Stats</h3>
        <table class="w-full text-sm">
            @foreach($dbStats as $key => $value)
                <tr class="border-b border-rule last:border-0">
                    <td class="py-2 elite-kicker text-[.6rem]">{{ str_replace('_', ' ', $key) }}</td>
                    <td class="py-2 font-mono text-right ink-secondary font-semibold">{{ number_format($value) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>

@endsection
