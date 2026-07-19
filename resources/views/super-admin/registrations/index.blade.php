@extends('super-admin.layout')
@section('title', 'Pendaftaran Sekolah')
@section('content')

<div class="mb-8">
    <div class="elite-kicker mb-2">Inscriptiones</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Pendaftaran Sekolah Baru</h1>
    <div class="elite-rule mb-3"></div>
    <p class="font-serif text-base text-gray-600">Verifikasi pembayaran manual dan aktivasi sekolah ke plan yang dipilih.</p>
</div>

{{-- Status counters --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-7">
    @foreach([
        ['pending', 'Pending', 'gray'],
        ['verifying', 'Verifying', 'yellow'],
        ['paid', 'Paid', 'blue'],
        ['activated', 'Activated', 'green'],
        ['rejected', 'Rejected', 'red'],
    ] as [$key, $label, $color])
        <a href="?status={{ $key }}" class="bg-white border-l-4 border-{{ $color }}-600 p-4 hover:bg-gray-50">
            <div class="elite-kicker text-[.6rem]">{{ $label }}</div>
            <div class="font-display text-2xl ink-primary mt-1">{{ $counts[$key] ?? 0 }}</div>
        </a>
    @endforeach
</div>

<form method="GET" class="bg-white border border-rule p-5 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email / subdomain"
           class="md:col-span-2 border-2 border-rule px-3 py-2 font-serif text-sm">
    <select name="status" class="border-2 border-rule px-3 py-2 font-serif text-sm">
        <option value="">— Semua Status —</option>
        @foreach(['pending','verifying','paid','activated','rejected'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="btn-elite" style="padding:.6rem 1rem;font-size:.65rem;">Filter</button>
</form>

<div class="bg-white border border-rule overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-[var(--c-primary)] text-white">
            <tr>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">ID</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Sekolah</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Plan</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Total</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Status</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]">Daftar</th>
                <th class="text-left px-4 py-3 elite-kicker text-[.6rem]"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $r)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs">REG-{{ str_pad($r->id, 6, '0', STR_PAD_LEFT) }}</td>
                    <td class="px-4 py-3">
                        <div class="font-serif font-semibold ink-primary">{{ $r->school_name }}</div>
                        <div class="text-xs text-gray-500">{{ $r->subdomain }} · {{ $r->admin_email }}</div>
                    </td>
                    <td class="px-4 py-3 font-serif">{{ $r->plan->name }} <span class="text-xs text-gray-500">× {{ $r->billing_months }}bln</span></td>
                    <td class="px-4 py-3 font-mono">Rp {{ number_format($r->plan_price/100, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-1 rounded
                            {{ $r->status === 'pending' ? 'bg-gray-100 text-gray-700' : '' }}
                            {{ $r->status === 'verifying' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $r->status === 'paid' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $r->status === 'activated' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $r->status === 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ $r->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $r->created_at->diffForHumans() }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('super.registrations.show', $r) }}" class="elite-kicker text-xs ink-secondary hover:ink-accent">Detail →</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500 italic font-serif">Belum ada pendaftaran.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $registrations->links() }}</div>

@endsection
