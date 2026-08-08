@extends('layouts.parent')
@section('title', 'Bursa Kerja — Sikad Pro')
@section('content')
@include('student-portal._nav')

<div class="mb-7">
    <div class="elite-kicker mb-2">Bursa Kerja Khusus SMK</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Lowongan Kerja</h1>
    <div class="elite-rule"></div>
</div>

@if($applicationHistory->count() > 0)
<div class="elite-card p-5 mb-6">
    <h3 class="elite-h3 text-base ink-primary mb-3">Status Lamaran Anda</h3>
    <div class="space-y-2">
        @foreach($applicationHistory as $app)
        <div class="flex justify-between items-center p-3 border border-rule">
            <div>
                <div class="font-serif font-semibold text-sm">{{ $app->jobListing?->position_title }}</div>
                <div class="text-xs text-gray-500">{{ $app->jobListing?->company_name }} · {{ $app->application_date->format('d/m/Y') }}</div>
            </div>
            <span class="text-[.6rem] uppercase px-2 py-1 rounded
                {{ $app->status === 'accepted' ? 'bg-green-100 text-green-800' : ($app->status === 'rejected' ? 'bg-red-100 text-red-800' : ($app->status === 'interview' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')) }}">
                {{ $app->status === 'applied' ? 'Melamar' : ($app->status === 'interview' ? 'Interview' : ($app->status === 'accepted' ? 'Diterima' : 'Ditolak')) }}
            </span>
        </div>
        @endforeach
    </div>
</div>
@endif

<form method="GET" class="flex flex-wrap gap-3 mb-4 bg-white border border-rule p-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari perusahaan atau posisi..." class="border-2 border-rule px-3 py-2 text-sm flex-1 min-w-[200px]">
    <select name="type" class="border-2 border-rule px-3 py-2 text-sm">
        <option value="">— Semua Tipe —</option>
        <option value="fulltime" {{ request('type') === 'fulltime' ? 'selected' : '' }}>Full-time</option>
        <option value="internship" {{ request('type') === 'internship' ? 'selected' : '' }}>Magang</option>
        <option value="contract" {{ request('type') === 'contract' ? 'selected' : '' }}>Kontrak</option>
    </select>
    <button type="submit" class="btn-elite-ghost text-xs">Cari</button>
</form>

@if($listings->count() === 0)
<div class="elite-card p-10 text-center">
    <p class="font-serif text-lg text-gray-500 italic">Belum ada lowongan tersedia saat ini.</p>
    <p class="text-xs text-gray-400 mt-2">Silakan cek kembali nanti.</p>
</div>
@else
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    @foreach($listings as $listing)
    <div class="elite-card p-5 group">
        <div class="flex justify-between items-start mb-3">
            <div>
                <div class="font-serif font-semibold ink-primary">{{ $listing->company_name }}</div>
                <div class="elite-kicker text-[.55rem]">{{ $listing->position_title }}</div>
            </div>
            <span class="text-[.55rem] uppercase px-2 py-0.5 rounded
                {{ $listing->job_type === 'fulltime' ? 'bg-green-100 text-green-800' : ($listing->job_type === 'internship' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600') }}">
                {{ $listing->job_type === 'fulltime' ? 'Full-time' : ($listing->job_type === 'internship' ? 'Magang' : $listing->job_type) }}
            </span>
        </div>
        <p class="font-serif text-xs text-gray-600 mb-3 line-clamp-2">{{ $listing->description }}</p>
        <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
            <span>{{ $listing->location ?? 'Lokasi tidak disebutkan' }}</span>
            <span>{{ $listing->salary_range ?? '—' }}</span>
        </div>
        @if(isset($myApplications[$listing->id]))
        <div class="text-xs text-center py-2 bg-gray-50 border border-rule">
            Sudah dilamar — {{ $myApplications[$listing->id] }}
        </div>
        @else
        <form method="POST" action="{{ route('student.bkk.apply') }}" onsubmit="return confirm('Lamar lowongan ini?')">
            @csrf
            <input type="hidden" name="job_listing_id" value="{{ $listing->id }}">
            <textarea name="notes" placeholder="Catatan tambahan (opsional)" rows="2" class="w-full border-2 border-rule px-3 py-1 text-xs mb-2"></textarea>
            <button type="submit" class="btn-elite-gold text-xs w-full">Kirim Lamaran</button>
        </form>
        @endif
    </div>
    @endforeach
</div>
<div>{{ $listings->links() }}</div>
@endif
@endsection
