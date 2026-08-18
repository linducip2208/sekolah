@extends('school-admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Selamat Datang, {{ auth()->user()->name }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Dashboard untuk role <span class="font-semibold capitalize">{{ $role }}</span></p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
            ← Dashboard Utama
        </a>
    </div>

    {{-- Widget Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($widgets as $widget)
        <a href="{{ $widget['url'] ?? '#' }}" class="group block bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-{{ $widget['color'] }}-200 transition-all duration-200">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ $widget['title'] }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 group-hover:text-{{ $widget['color'] }}-600 transition">{{ $widget['value'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-{{ $widget['color'] }}-50 text-{{ $widget['color'] }}-600">
                    @if($widget['icon'] === 'students')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    @elseif($widget['icon'] === 'people')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    @elseif($widget['icon'] === 'calendar')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    @elseif($widget['icon'] === 'finance')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif($widget['icon'] === 'tasks')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    @elseif($widget['icon'] === 'bell')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @elseif($widget['icon'] === 'academic')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                    @elseif($widget['icon'] === 'library')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    @elseif($widget['icon'] === 'reports')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Role-specific extra sections --}}
    @if($role === 'principal')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Link Cepat</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.analytics.executive') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">Executive Dashboard</div>
                        <div class="text-xs text-gray-500">Ringkasan performa sekolah</div>
                    </div>
                </a>
                <a href="{{ route('admin.reports.spp-aging') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">SPP Aging Report</div>
                        <div class="text-xs text-gray-500">Status pembayaran SPP</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    @endif

    @if($role === 'student')
    @if($upcomingExams && $upcomingExams->count())
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Ujian Mendatang</h3>
        <div class="space-y-2">
            @foreach($upcomingExams as $exam)
            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $exam->name ?? 'Ujian' }}</div>
                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($exam->date)->translatedFormat('d M Y') }}</div>
                </div>
                <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">Mendatang</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    @if($role === 'parent' && isset($children) && $children->count())
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Anak Anda</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($children as $child)
            <a href="{{ route('portal.child', $child) }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/30 transition">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                    {{ strtoupper(substr($child->first_name ?? $child->name, 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $child->first_name ?? $child->name }}</div>
                    <div class="text-xs text-gray-500">{{ $child->classSection?->name ?? '-' }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($role === 'librarian')
    <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Link Cepat Pustakawan</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <a href="{{ route('admin.library.books.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-indigo-200 transition">
                <span class="text-lg">📚</span>
                <span class="text-sm font-medium">Kelola Buku</span>
            </a>
            <a href="{{ route('admin.library.issues.index') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-indigo-200 transition">
                <span class="text-lg">📋</span>
                <span class="text-sm font-medium">Peminjaman</span>
            </a>
            <a href="{{ route('admin.library.digital.upload') }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-indigo-200 transition">
                <span class="text-lg">💻</span>
                <span class="text-sm font-medium">e-Library</span>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
