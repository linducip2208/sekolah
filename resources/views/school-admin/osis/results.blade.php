@extends('layouts.school-admin')
@section('title', 'Hasil Pemilihan — ' . $election->title)
@section('sidebar')
    @include('school-admin.partials.sidebar')
@endsection

@push('head')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="mb-7 flex justify-between items-start flex-wrap gap-3">
    <div>
        <h1 class="elite-h1 text-3xl ink-primary mb-2">Hasil {{ $election->title }}</h1>
        <div class="elite-rule"></div>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.osis.index') }}" class="btn-elite-ghost">← Kembali</a>
        <a href="{{ route('admin.osis.candidates', $election) }}" class="btn-elite-ghost">Kandidat</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-7">
    <div class="bg-white border-l-4 border-purple-600 p-5">
        <div class="elite-kicker text-[.6rem]">Total Pemilih</div>
        <div class="font-display text-3xl ink-primary mt-2">{{ $totalVoters }}</div>
    </div>
    <div class="bg-white border-l-4 border-green-600 p-5">
        <div class="elite-kicker text-[.6rem]">Status</div>
        <div class="font-display text-lg ink-primary mt-2">{{ $election->status === 'completed' ? 'Selesai' : 'Sedang Berlangsung' }}</div>
    </div>
    <div class="bg-white border-l-4 border-yellow-500 p-5">
        <div class="elite-kicker text-[.6rem]">Kandidat</div>
        <div class="font-display text-3xl ink-primary mt-2">{{ $election->candidates->count() }}</div>
    </div>
</div>

{{-- Pemenang --}}
@if(count($winners) > 0)
<div class="bg-white border-l-4 border-yellow-500 p-7 mb-7" style="background:linear-gradient(135deg,#fff9e6,#fff);">
    <h3 class="elite-h3 text-lg ink-primary mb-4">🏆 PEMENANG</h3>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($winners as $w)
        <div class="border border-rule p-5 text-center bg-white">
            <div class="crest-mark mx-auto mb-3 lg">
                <span class="font-display text-lg">{{ strtoupper(substr($w['candidate']->student?->user?->name ?? '?', 0, 1)) }}</span>
            </div>
            <div class="font-serif font-semibold ink-primary">{{ $w['candidate']->student?->user?->name }}</div>
            <div class="elite-kicker text-[.6rem] mt-1">{{ $w['position'] }}</div>
            <div class="font-display text-3xl ink-accent mt-2">{{ $w['vote_count'] }} <span class="text-sm text-gray-500">suara</span></div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Live chart --}}
<div class="grid lg:grid-cols-2 gap-7">
    <div class="bg-white border border-rule p-7" x-data="liveVotes({{ $election->id }})" x-init="poll()">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Perolehan Suara Live</h3>
        <canvas id="voteChart" height="300"></canvas>
        @if($election->status === 'voting')
        <div class="text-center mt-3" x-show="countdown > 0">
            <span class="text-xs text-gray-500">Auto-refresh: <span x-text="countdown"></span> detik</span>
        </div>
        @endif
    </div>

    <div class="bg-white border border-rule p-7">
        <h3 class="elite-h3 text-lg ink-primary mb-4">Detail Perolehan Suara</h3>
        <div class="space-y-2">
            @foreach($election->candidates as $c)
            <div class="flex justify-between items-center p-3 border border-rule">
                <div>
                    <div class="font-serif font-semibold">{{ $c->student?->user?->name }}</div>
                    <div class="elite-kicker text-[.55rem]">{{ $c->position }} · {{ $c->status }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-4 bg-gray-200 flex-1 min-w-[100px]">
                        @php
                        $maxVotes = $election->candidates->max('vote_count') ?: 1;
                        $barWidth = $maxVotes > 0 ? ($c->vote_count / $maxVotes * 100) : 0;
                        @endphp
                        <div class="h-4 bg-blue-600" style="width: {{ $barWidth }}%"></div>
                    </div>
                    <span class="font-mono text-sm font-semibold min-w-[3ch] text-right">{{ $c->vote_count }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
function liveVotes(electionId) {
    return {
        countdown: 15,
        chart: null,
        async poll() {
            const res = await fetch(`/admin/osis/${electionId}/live-votes`);
            const data = await res.json();
            this.renderChart(data.candidates);
            this.countdown = 15;
            const timer = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) { clearInterval(timer); this.poll(); }
            }, 1000);
        },
        renderChart(candidates) {
            const ctx = document.getElementById('voteChart').getContext('2d');
            if (this.chart) this.chart.destroy();
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: candidates.map(c => c.name),
                    datasets: [{
                        label: 'Suara',
                        data: candidates.map(c => c.vote_count),
                        backgroundColor: 'rgba(11,29,58,.85)',
                        borderColor: '#0b1d3a',
                        borderWidth: 1,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        }
    };
}
</script>
@endsection
