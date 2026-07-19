@extends('layouts.school-admin')
@section('title', 'Job Board Alumni')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@section('content')
<div class="mb-4 flex items-center justify-between flex-wrap gap-3">
    <div>
        <div class="elite-kicker mb-2">Jaringan Profesional</div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Job Board Alumni</h1>
        <div class="elite-rule"></div>
    </div>
    <a href="{{ route('admin.jobs.create') }}" class="btn-elite-gold">+ Posting Lowongan</a>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="elite-card p-4 text-center">
        <div class="font-display text-2xl ink-primary">{{ $totalActive }}</div>
        <div class="elite-kicker text-[.55rem]">Lowongan Aktif</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="font-display text-2xl ink-accent">{{ $totalToday }}</div>
        <div class="elite-kicker text-[.55rem]">Posting Hari Ini</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="font-display text-2xl ink-secondary">{{ $listings->total() }}</div>
        <div class="elite-kicker text-[.55rem]">Total Lowongan</div>
    </div>
    <div class="elite-card p-4 text-center">
        <div class="font-display text-2xl" style="color:var(--c-muted);">
            {{ \App\Models\Alumni\JobListing::where('school_id', auth()->user()->school_id)->where('is_verified', false)->count() }}
        </div>
        <div class="elite-kicker text-[.55rem]">Menunggu Verifikasi</div>
    </div>
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-4 bg-white border border-rule p-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari perusahaan atau posisi..." class="border-2 border-rule px-3 py-2 text-sm flex-1 min-w-[200px]">
    <select name="type" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Semua Tipe —</option>
        @foreach($jobTypes as $key => $label)
        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <select name="verified" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Status Verifikasi —</option>
        <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Terverifikasi</option>
        <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Belum Verifikasi</option>
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Filter</button>
    <a href="{{ route('admin.jobs.index') }}" class="text-xs text-gray-500 hover:ink-accent self-center">Reset</a>
</form>

<div class="elite-card overflow-hidden">
    <div class="table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Perusahaan</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Posisi</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Lokasi</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Pelamar</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Verifikasi</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Aktif</th>
                    <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Diposting</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($listings as $listing)
                <tr class="border-t border-rule">
                    <td class="px-3 py-3">
                        <div class="font-serif font-semibold">{{ $listing->company_name }}</div>
                        <div class="text-xs text-gray-500">oleh {{ $listing->alumniProfile?->user?->name ?? '—' }}</div>
                    </td>
                    <td class="px-3 py-3 font-serif">{{ $listing->position_title }}</td>
                    <td class="px-3 py-3">
                        <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded {{ $listing->job_type === 'fulltime' ? 'bg-green-100 text-green-800' : ($listing->job_type === 'internship' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ $jobTypes[$listing->job_type] ?? $listing->job_type }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-xs">{{ $listing->location ?? '—' }}</td>
                    <td class="px-3 py-3 text-center">
                        <a href="{{ route('admin.jobs.applications', $listing) }}" class="font-mono font-bold ink-accent hover:underline">
                            {{ $listing->applications_count }}
                        </a>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <form method="POST" action="{{ route('admin.jobs.toggle-verify', $listing) }}" class="inline">
                            @csrf
                            <button class="text-xs {{ $listing->is_verified ? 'text-green-700' : 'text-yellow-600' }} underline hover:no-underline" title="Toggle verifikasi">
                                {{ $listing->is_verified ? '✓ OK' : 'Pending' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <form method="POST" action="{{ route('admin.jobs.toggle-active', $listing) }}" class="inline">
                            @csrf
                            <button class="inline-block w-3 h-3 rounded-full {{ $listing->is_active ? 'bg-green-500' : 'bg-gray-300' }}" title="Toggle aktif"></button>
                        </form>
                    </td>
                    <td class="px-3 py-3 text-xs">{{ $listing->posted_at?->format('d/m/Y') }}</td>
                    <td class="px-3 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.jobs.edit', $listing) }}" class="text-xs underline ink-secondary hover:ink-accent mr-2">Edit</a>
                        <form method="POST" action="{{ route('admin.jobs.destroy', $listing) }}" class="inline" onsubmit="return confirm('Hapus lowongan ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs underline text-red-600 hover:text-red-800">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="p-10 text-center text-gray-500 italic font-serif">Belum ada lowongan kerja.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $listings->links() }}</div>
@endsection
