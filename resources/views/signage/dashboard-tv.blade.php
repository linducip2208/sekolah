<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="{{ $refreshInterval }}">
    <title>Dashboard TV — {{ $school->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700,800,900|inter:300,400,500,600,700|cormorant:500,600,700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        *, *::before, *::after { margin:0;padding:0;box-sizing:border-box; }
        body { font-family:'Inter',sans-serif;background:#06080d;color:#e2e8f0;width:100vw;height:100vh;overflow:hidden;display:flex;flex-direction:column; }
        .main { flex:1;display:grid;grid-template-columns:1fr;grid-template-rows:auto 1fr auto;gap:0;padding:24px 32px; }
        .header { display:flex;justify-content:space-between;align-items:center;padding-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06); }
        .header-left .school { font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#f8fafc; }
        .clock-block { text-align:right; }
        .clock-time { font-family:'Inter',sans-serif;font-size:42px;font-weight:700;color:#3b82f6;line-height:1; }
        .clock-date { font-size:14px;color:#64748b;margin-top:4px; }
        .body-grid { display:grid;grid-template-columns:1fr 1fr;grid-template-rows:auto auto auto;gap:20px;padding:20px 0;overflow:hidden; }
        .stat-card { background:rgba(15,23,42,.8);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:20px;display:flex;flex-direction:column;justify-content:center; }
        .stat-value { font-family:'Inter',sans-serif;font-size:56px;font-weight:800;letter-spacing:-1px; }
        .stat-label { font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:2px;margin-top:4px; }
        .stat-sub { font-size:16px;color:#94a3b8;margin-top:8px; }
        .stat-green { color:#22c55e; }
        .stat-blue { color:#3b82f6; }
        .stat-amber { color:#f59e0b; }
        .chart-box { background:rgba(15,23,42,.8);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:20px;grid-column:span 2; }
        .chart-box canvas { max-height:220px; }
        .feed-box { background:rgba(15,23,42,.8);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:20px;overflow:hidden;display:flex;flex-direction:column; }
        .feed-title { font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:2px;margin-bottom:10px; }
        .feed-item { display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.03);font-size:12px; }
        .feed-time { color:#475569;font-family:monospace;white-space:nowrap; }
        .feed-label { color:#e2e8f0;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
        .event-box { background:rgba(15,23,42,.8);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:20px;overflow:hidden;display:flex;flex-direction:column; }
        .event-item { padding:6px 0;border-bottom:1px solid rgba(255,255,255,.03); }
        .event-title { font-size:13px;color:#e2e8f0;font-weight:500; }
        .event-date { font-size:11px;color:#3b82f6;margin-top:2px; }
        .ticker { background:rgba(15,23,42,.9);border-top:1px solid rgba(255,255,255,.06);padding:12px 32px;text-align:center; }
        .ticker-text { font-family:monospace;font-size:13px;color:#64748b;letter-spacing:1px;animation:scroll 20s linear infinite;white-space:nowrap; }
        @keyframes scroll { 0%{transform:translateX(100%)} 100%{transform:translateX(-100%)} }
        .stat-row { display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px; }
        .progress-ring { width:80px;height:80px;border-radius:50%;background:conic-gradient(#22c55e {{ ($todayAttendance['attendancePercent'] ?? 0) * 3.6 }}deg, rgba(255,255,255,.05) 0deg);display:flex;align-items:center;justify-content:center; }
        .progress-inner { width:60px;height:60px;border-radius:50%;background:#06080d;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700; }
    </style>
</head>
<body>
    <div class="main">
        <div class="header">
            <div class="header-left">
                <div class="school">{{ $school->name }}</div>
                <div style="font-size:12px;color:#64748b;font-family:'Playfair Display',serif;font-style:italic;margin-top:4px;">Dashboard Real-Time</div>
            </div>
            <div class="clock-block">
                <div class="clock-time" id="clock">{{ now()->format('H:i') }}</div>
                <div class="clock-date" id="date">{{ now()->translatedFormat('l, d F Y') }}</div>
            </div>
        </div>

        <div class="body-grid">
            @if(($tvConfig['show_attendance'] ?? true) && !empty($todayAttendance))
            <div class="stat-card">
                <div class="stat-label">Kehadiran Hari Ini</div>
                <div style="display:flex;align-items:center;gap:24px;margin-top:8px;">
                    <div class="progress-ring">
                        <div class="progress-inner stat-green">{{ $todayAttendance['attendancePercent'] }}%</div>
                    </div>
                    <div>
                        <div class="stat-value stat-green">{{ $todayAttendance['presentCount'] }}</div>
                        <div class="stat-sub">dari {{ $todayAttendance['totalStudents'] }} siswa hadir</div>
                    </div>
                </div>
            </div>
            @else
            <div class="stat-card">
                <div class="stat-label">Kehadiran Hari Ini</div>
                <div class="stat-value stat-blue" style="font-size:36px;">—</div>
                <div class="stat-sub">Widget dinonaktifkan</div>
            </div>
            @endif

            @if($tvConfig['show_revenue'] ?? true)
            <div class="stat-card">
                <div class="stat-label">Pemasukan Hari Ini</div>
                <div class="stat-value stat-amber" style="font-size:42px;">Rp {{ number_format($todayRevenue / 100, 0, ',', '.') }}</div>
                <div class="stat-sub">Total pembayaran SPP hari ini</div>
            </div>
            @else
            <div class="stat-card">
                <div class="stat-label">Pemasukan Hari Ini</div>
                <div class="stat-value stat-blue" style="font-size:36px;">—</div>
                <div class="stat-sub">Widget dinonaktifkan</div>
            </div>
            @endif

            @if(($tvConfig['show_attendance_chart'] ?? true) && !empty($attendanceChartData))
            <div class="chart-box">
                <div class="feed-title">Kehadiran per Rombel</div>
                <canvas id="attendanceChart"></canvas>
            </div>
            @endif

            @if($tvConfig['show_activities'] ?? true)
            <div class="feed-box">
                <div class="feed-title">Aktivitas Terkini</div>
                <div style="flex:1;overflow:hidden;">
                    @forelse($recentActivities as $act)
                        <div class="feed-item">
                            <span class="feed-time">{{ $act['time'] }}</span>
                            <span class="feed-label">[{{ $act['type'] }}] {{ $act['label'] }} — {{ $act['detail'] }}</span>
                        </div>
                    @empty
                        <div class="feed-item"><span class="feed-label">Belum ada aktivitas hari ini.</span></div>
                    @endforelse
                </div>
            </div>
            @endif

            @if(($tvConfig['show_events'] ?? true) && $upcomingEvents->isNotEmpty())
            <div class="event-box">
                <div class="feed-title">Event Mendatang</div>
                <div style="flex:1;overflow:hidden;">
                    @foreach($upcomingEvents as $ev)
                        <div class="event-item">
                            <div class="event-title">{{ $ev->title }}</div>
                            <div class="event-date">📅 {{ $ev->starts_at->format('d M Y H:i') }} @if($ev->venue)· {{ $ev->venue }}@endif</div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="ticker">
        <div class="ticker-text">
            Total Siswa: {{ number_format($totalStudents) }} &nbsp;|&nbsp;
            Total Guru: {{ number_format($totalTeachers) }} &nbsp;|&nbsp;
            Total Rombel: {{ number_format($totalRombel) }} &nbsp;|&nbsp;
            Tahun Ajaran: {{ $academicYearLabel }} &nbsp;|&nbsp;
            {{ $school->name }} &nbsp;—&nbsp; Sikad Pro
        </div>
    </div>

    <script>
        const ctx = document.getElementById('attendanceChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($attendanceChartData, 'label')) !!},
                    datasets: [{
                        label: 'Hadir',
                        data: {!! json_encode(array_column($attendanceChartData, 'present')) !!},
                        backgroundColor: '#22c55e',
                        borderRadius: 4,
                        barPercentage: 0.6,
                    }, {
                        label: 'Tidak Hadir',
                        data: {!! json_encode(array_map(fn($d) => $d['total'] - $d['present'], $attendanceChartData)) !!},
                        backgroundColor: '#ef4444',
                        borderRadius: 4,
                        barPercentage: 0.6,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true, labels: { color: '#94a3b8', font: { size: 11 } } } },
                    scales: {
                        x: { stacked: true, grid: { color: 'rgba(255,255,255,.04)' }, ticks: { color: '#64748b', font: { size: 10 } } },
                        y: { stacked: true, grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                    },
                },
            });
        }

        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID', { hour:'2-digit',minute:'2-digit' });
            document.getElementById('date').textContent = now.toLocaleDateString('id-ID', { weekday:'long',day:'numeric',month:'long',year:'numeric' });
        }
        setInterval(updateClock, 10000);
        updateClock();
    </script>
</body>
</html>
