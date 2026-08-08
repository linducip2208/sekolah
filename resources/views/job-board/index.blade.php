<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Job Board Alumni — Sikad Pro</title>
    @include('elite.partials.head')
    <style>
        .job-card { background: #fff; border: 1px solid var(--c-rule); padding: 1.5rem; transition: all .3s ease; }
        .job-card:hover { box-shadow: 0 16px 36px -18px rgba(11,29,58,.18); transform: translateY(-2px); }
        .job-card:hover { border-color: var(--c-accent); }
        .type-badge { display: inline-block; padding: .2rem .7rem; font-size: .65rem; letter-spacing: .1em; text-transform: uppercase; font-weight: 600; border-radius: 2px; }
        .type-fulltime { background: #DCFCE7; color: #166534; }
        .type-parttime { background: #DBEAFE; color: #1E40AF; }
        .type-internship { background: #FEF3C7; color: #92400E; }
        .type-contract { background: #F3E8FF; color: #6B21A8; }
        .type-freelance { background: #FCE7F3; color: #9D1B4C; }
    </style>
</head>
<body class="paper antialiased">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        {{-- Header --}}
        <div class="text-center mb-10">
            <div class="elite-kicker mb-2">Jaringan Profesional</div>
            <h1 class="elite-h1 text-3xl sm:text-5xl ink-primary mb-3">Job Board Alumni</h1>
            <div class="font-serif text-lg text-gray-600 mb-6">Lowongan kerja eksklusif dari jaringan alumni Sikad Pro</div>
            <div class="elite-rule justify-content-center"></div>
        </div>

        {{-- Search & Filter --}}
        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-8">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari perusahaan, posisi, atau lokasi..."
                       class="w-full pl-11 pr-4 py-3 border-2 border-rule text-sm">
            </div>
            <select name="type" class="border-2 border-rule px-4 py-3 text-sm min-w-[160px]">
                <option value="">Semua Tipe</option>
                @foreach($jobTypes as $key => $label)
                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-elite">Cari</button>
        </form>

        {{-- Job Listings --}}
        <div class="space-y-4">
            @forelse($listings as $listing)
            <a href="{{ route('job-board.show', $listing->slug) }}" class="job-card block">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                    <div>
                        <h2 class="font-display text-xl sm:text-2xl ink-primary leading-tight">{{ $listing->position_title }}</h2>
                        <div class="font-serif text-lg text-gray-700">{{ $listing->company_name }}</div>
                    </div>
                    <span class="type-badge type-{{ $listing->job_type }}">
                        {{ $jobTypes[$listing->job_type] ?? $listing->job_type }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-gray-500 font-medium">
                    @if($listing->location)
                    <span>📍 {{ $listing->location }}</span>
                    @endif
                    @if($listing->salary_range)
                    <span>💰 {{ $listing->salary_range }}</span>
                    @endif
                    <span>📅 Diposting {{ $listing->posted_at?->diffForHumans() }}</span>
                    @if($listing->expires_at)
                    <span>⏰ Sampai {{ $listing->expires_at->format('d M Y') }}</span>
                    @endif
                    <span>👁 {{ $listing->view_count }} dilihat</span>
                    <span>📨 {{ $listing->applications_count }} pelamar</span>
                </div>
                @if($listing->description)
                <p class="text-sm text-gray-600 mt-3 leading-relaxed line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($listing->description), 250) }}</p>
                @endif
                <div class="mt-3 text-xs text-gray-400">
                    Diposting oleh {{ $listing->alumniProfile?->user?->name ?? 'Alumni' }} (Lulus {{ $listing->alumniProfile?->graduation_year ?? '—' }})
                </div>
            </a>
            @empty
            <div class="text-center py-16">
                <div class="ornament-center mb-3"></div>
                <p class="font-serif text-xl text-gray-500 italic">Belum ada lowongan tersedia saat ini.</p>
                <p class="text-sm text-gray-400 mt-2">Silakan kembali lagi nanti.</p>
            </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $listings->links() }}
        </div>

        {{-- Footer --}}
        <div class="mt-12 pt-8 border-t border-rule text-center">
            <p class="font-script italic text-sm text-gray-500">Didukung oleh Sikad Pro — Jaringan Profesional Alumni</p>
        </div>
    </div>
</body>
</html>
