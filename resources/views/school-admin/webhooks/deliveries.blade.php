@extends('layouts.school-admin')

@section('title', 'Deliveries — '.$webhook->name)

@section('content')
<a href="{{ route('admin.webhooks.index') }}" class="text-sm ink-secondary underline mb-3 inline-block">← Kembali ke Webhooks</a>

<div class="mb-4">
    <h1 class="elite-h1 text-2xl ink-primary">{{ $webhook->name }}</h1>
    <div class="font-mono text-xs text-gray-600">{{ $webhook->url }}</div>
</div>

@if(session('success'))<div class="mb-4 p-3 bg-green-50 border-l-4 border-green-700 text-green-800 text-sm">{{ session('success') }}</div>@endif

<div class="bg-white border border-rule">
    <table class="w-full text-xs">
        <thead class="bg-gray-50 border-b border-rule">
            <tr>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Waktu</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Event</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Status</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">HTTP</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Attempts</th>
                <th class="text-left px-3 py-2 elite-kicker text-[.6rem]">Response</th>
                <th class="text-right px-3 py-2 elite-kicker text-[.6rem]">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deliveries as $d)
                <tr class="border-b border-rule align-top">
                    <td class="px-3 py-2 font-mono">{{ $d->created_at?->format('d/m H:i:s') }}</td>
                    <td class="px-3 py-2 font-mono">{{ $d->event }}</td>
                    <td class="px-3 py-2">
                        @php $color = match($d->status) {
                            'success' => 'text-green-700', 'failed' => 'text-red-700',
                            'retrying' => 'text-amber-700', default => 'text-gray-600',
                        }; @endphp
                        <span class="{{ $color }} font-semibold">{{ strtoupper($d->status) }}</span>
                    </td>
                    <td class="px-3 py-2 font-mono">{{ $d->http_status ?? '—' }}</td>
                    <td class="px-3 py-2 font-mono">{{ $d->attempts }}</td>
                    <td class="px-3 py-2 max-w-[320px] truncate font-mono">{{ \Illuminate\Support\Str::limit($d->response_body, 80) }}</td>
                    <td class="px-3 py-2 text-right">
                        @if($d->status !== 'success')
                        <form method="POST" action="{{ route('admin.webhooks.retry', $d) }}" class="inline">@csrf
                            <button class="text-blue-700 underline">Retry</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-6 text-center text-gray-500 font-serif">Belum ada delivery.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $deliveries->links() }}</div>
@endsection
