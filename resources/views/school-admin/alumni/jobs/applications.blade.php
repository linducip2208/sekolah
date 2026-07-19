@extends('layouts.school-admin')
@section('title', 'Aplikasi Lowongan — ' . $listing->company_name)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection

@push('head')
<style>
.kanban-board { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
.kanban-column { background: #fff; border: 1px solid var(--c-rule); }
.kanban-column-header { padding: .75rem 1rem; border-bottom: 1px solid var(--c-rule); font-family: 'Inter', sans-serif; font-size: .65rem; letter-spacing: .2em; text-transform: uppercase; font-weight: 600; display: flex; align-items: center; justify-content: space-between; }
.kanban-column-header .count { font-family: 'JetBrains Mono', monospace; font-size: .8rem; font-weight: 700; }
.kanban-card { padding: .85rem 1rem; border-bottom: 1px solid var(--c-rule); transition: background .2s; }
.kanban-card:hover { background: rgba(184,134,11,.04); }
.kanban-column-body { max-height: 500px; overflow-y: auto; }
</style>
@endpush

@section('content')
<a href="{{ route('admin.jobs.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali ke Job Board</a>

<div class="mb-7">
    <div class="elite-kicker mb-2">Aplikasi Lamaran</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">{{ $listing->position_title }}</h1>
    <div class="elite-rule"></div>
    <p class="font-serif text-sm text-gray-600 mt-2">
        <strong>{{ $listing->company_name }}</strong> &middot; {{ $listing->location ?? 'Remote' }} &middot;
        Diposting {{ $listing->posted_at?->format('d M Y') }} &middot;
        <span class="{{ $listing->is_verified ? 'text-green-700' : 'text-yellow-600' }}">
            {{ $listing->is_verified ? 'Terverifikasi' : 'Belum Verifikasi' }}
        </span>
    </p>
</div>

{{-- Status Filter --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('admin.jobs.applications', $listing) }}" class="px-3 py-1 text-xs font-semibold border {{ $selectedStatus === null ? 'bg-[var(--c-primary)] text-white border-[var(--c-primary)]' : 'border-rule' }}">
        Semua ({{ array_sum(array_column($columns, 'count')) }})
    </a>
    @foreach($statuses as $key => $label)
    <button type="submit" name="status" value="{{ $key }}" class="px-3 py-1 text-xs font-semibold border {{ $selectedStatus === $key ? 'bg-[var(--c-primary)] text-white border-[var(--c-primary)]' : 'border-rule' }}">
        {{ $label }} ({{ $columns[$key]['count'] ?? 0 }})
    </button>
    @endforeach
    @if($selectedStatus)
    <a href="{{ route('admin.jobs.applications', $listing) }}" class="text-xs text-gray-400 hover:ink-accent self-center ml-1">× Reset</a>
    @endif
</form>

{{-- Kanban Board --}}
<div class="kanban-board">
    @foreach($columns as $key => $col)
    <div class="kanban-column">
        @php
            $headerColors = [
                'applied'  => 'background:#E5E7EB;color:#374151;',
                'reviewed' => 'background:#BFDBFE;color:#1E40AF;',
                'interview' => 'background:#DDD6FE;color:#5B21B6;',
                'offered'  => 'background:#FEF3C7;color:#92400E;',
                'rejected' => 'background:#FECACA;color:#991B1B;',
                'accepted' => 'background:#BBF7D0;color:#166534;',
            ];
        @endphp
        <div class="kanban-column-header" style="{{ $headerColors[$key] ?? '' }}">
            <span>{{ $col['label'] }}</span>
            <span class="count">{{ $col['count'] }}</span>
        </div>
        <div class="kanban-column-body">
            @forelse($col['items'] as $app)
            <div class="kanban-card">
                <div class="font-serif font-semibold text-sm mb-1">{{ $app->full_name }}</div>
                <div class="text-xs text-gray-500 mb-1">{{ $app->email }}</div>
                @if($app->phone)
                <div class="text-xs text-gray-400 mb-1">{{ $app->phone }}</div>
                @endif
                @if($app->cover_letter)
                <div class="text-xs text-gray-600 mt-1 mb-2 line-clamp-2 italic">{{ \Illuminate\Support\Str::limit($app->cover_letter, 120) }}</div>
                @endif
                @if($app->resume_path)
                <a href="{{ Storage::url($app->resume_path) }}" target="_blank" class="text-xs underline ink-accent">📄 Resume</a>
                @endif
                <div class="text-xs text-gray-400 mt-1">{{ $app->created_at->format('d M H:i') }}</div>
                <div class="mt-2 pt-2 border-t border-dashed border-rule">
                    <form method="POST" action="{{ route('admin.jobs.application-status', $app) }}" class="flex gap-1 flex-wrap">
                        @csrf
                        <select name="status" onchange="this.form.submit()" class="text-xs border-2 border-rule px-1 py-0.5 w-full mb-1">
                            @foreach($statuses as $sk => $sl)
                            <option value="{{ $sk }}" {{ $app->status === $sk ? 'selected' : '' }}>{{ $sl }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="notes" placeholder="Catatan..." value="{{ $app->notes }}" class="text-xs border-2 border-rule px-1 py-0.5 w-full">
                        <button type="submit" class="text-xs ink-accent underline w-full text-left">Update</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-4 text-center text-xs text-gray-400 italic font-serif">Kosong</div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

{{-- Table View --}}
<div class="elite-card overflow-hidden mt-8">
    <div class="px-5 py-4 border-b border-rule">
        <h3 class="elite-h3 text-lg ink-primary">Daftar Lengkap Pelamar ({{ $applications->total() }})</h3>
    </div>
    <div class="table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-[var(--c-primary)] text-white">
                <tr>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Nama</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Email</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Telepon</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tipe</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Dilamar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                <tr class="border-t border-rule">
                    <td class="px-3 py-3 font-serif font-semibold">{{ $app->full_name }}</td>
                    <td class="px-3 py-3 text-xs">{{ $app->email }}</td>
                    <td class="px-3 py-3 text-xs">{{ $app->phone ?? '—' }}</td>
                    <td class="px-3 py-3 text-xs">{{ $app->applicant_type }}</td>
                    <td class="px-3 py-3">
                        <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded
                            {{ $app->status === 'applied' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $app->status === 'reviewed' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $app->status === 'interview' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $app->status === 'offered' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $app->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $app->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ $statuses[$app->status] ?? $app->status }}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-xs">{{ $app->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada pelamar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $applications->links() }}</div>
@endsection
