<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="{{ $refreshInterval }}">
    <title>Digital Signage — {{ $school->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700,800,900|inter:300,400,500,600,700|cormorant:500,600,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #080808;
            color: #e5e5e5;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .container {
            flex: 1;
            display: grid;
            grid-template-columns: 280px 1fr 280px;
            gap: 0;
            height: calc(100% - 48px);
        }

        .panel {
            padding: 28px 22px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .panel-left {
            border-right: 1px solid rgba(255,255,255,.08);
            background: linear-gradient(180deg, #0d0d0d 0%, #0a0a0a 100%);
        }
        .panel-right {
            border-left: 1px solid rgba(255,255,255,.08);
            background: linear-gradient(180deg, #0a0a0a 0%, #0d0d0d 100%);
        }
        .panel-center {
            background: #080808;
        }

        .clock-time {
            font-family: 'Inter', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            letter-spacing: -.02em;
            color: #fff;
            text-align: center;
        }
        .clock-date {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: #b8860b;
            text-align: center;
            margin-top: 4px;
            font-weight: 500;
        }
        .clock-hijri {
            font-family: 'Inter', sans-serif;
            font-size: .7rem;
            color: #888;
            text-align: center;
            margin-top: 4px;
        }

        .section-title {
            font-family: 'Inter', sans-serif;
            font-size: .6rem;
            letter-spacing: .2em;
            text-transform: uppercase;
            font-weight: 600;
            color: #b8860b;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .prayer-item {
            display: flex; justify-content: space-between;
            padding: 5px 0;
            font-size: .8rem;
            border-bottom: 1px solid rgba(255,255,255,.04);
        }
        .prayer-name { color: #ccc; }
        .prayer-time { font-family: 'Inter', sans-serif; font-weight: 600; color: #fff; }
        .prayer-active { color: #b8860b !important; }

        .schedule-item {
            padding: 6px 0;
            border-bottom: 1px solid rgba(255,255,255,.04);
            font-size: .75rem;
        }
        .schedule-time { color: #b8860b; font-family: 'Inter', sans-serif; font-weight: 600; font-size: .7rem; }
        .schedule-subject { color: #fff; font-weight: 600; }
        .schedule-class { color: #888; font-size: .65rem; }

        .event-item {
            padding: 8px 10px;
            margin-bottom: 6px;
            background: rgba(255,255,255,.03);
            border-left: 2px solid #b8860b;
            font-size: .75rem;
        }
        .event-date { font-family: 'Inter', sans-serif; font-weight: 600; color: #b8860b; font-size: .6rem; letter-spacing: .1em; text-transform: uppercase; }
        .event-name { color: #fff; font-weight: 600; margin-top: 2px; }

        .slideshow {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            text-align: center;
            padding: 40px;
        }
        .slide {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            opacity: 0;
            transition: opacity 1.2s ease;
        }
        .slide.active { opacity: 1; }

        .slide-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
            max-width: 700px;
        }
        .slide-subtitle {
            font-family: 'Cormorant', serif;
            font-size: 1.1rem;
            color: #b8860b;
            margin-top: 12px;
            font-weight: 500;
        }
        .slide-dot {
            display: inline-block;
            width: 6px; height: 6px;
            border-radius: 50%;
            background: rgba(255,255,255,.2);
            margin: 0 4px;
            cursor: pointer;
            transition: background .3s;
        }
        .slide-dot.active { background: #b8860b; }

        .ticker-bar {
            height: 48px;
            background: #111;
            border-top: 1px solid #b8860b;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        .ticker-text {
            white-space: nowrap;
            font-size: .8rem;
            color: #b8860b;
            font-weight: 500;
            animation: ticker-scroll 25s linear infinite;
        }
        @keyframes ticker-scroll {
            0% { transform: translateX(100vw); }
            100% { transform: translateX(-100%); }
        }

        .school-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #fff;
            font-weight: 700;
            text-align: center;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) {
            .container { grid-template-columns: 1fr; }
            .panel-left, .panel-right { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    {{-- LEFT PANEL: Clock + Prayer Times --}}
    <div class="panel panel-left">
        @if(!empty($signage['show_clock']))
        <div class="school-logo">{{ $schoolMotto }}</div>
        <div class="clock-time" id="clockTime">00:00</div>
        <div class="clock-date" id="clockDate"></div>
        <div class="clock-hijri" id="clockHijri"></div>
        <div style="height:24px;"></div>
        @endif

        @if(!empty($signage['show_prayer_times']))
        <div class="section-title">Jadwal Sholat</div>
        @php
            $prayerNames = ['Subuh' => '04:30', 'Dzuhur' => '12:00', 'Ashar' => '15:30', 'Maghrib' => '18:00', 'Isya' => '19:30'];
            $currentHour = (int) date('H');
            $currentMin  = (int) date('i');
            $currentTotal = $currentHour * 60 + $currentMin;
            $activeFound = false;
        @endphp
        @foreach($prayerNames as $name => $time)
            @php
                $parts = explode(':', $time);
                $prayerTotal = (int)$parts[0] * 60 + (int)$parts[1];
                $isActive = !$activeFound && $currentTotal >= $prayerTotal && $currentTotal < ($prayerTotal + 90);
                if ($isActive) $activeFound = true;
            @endphp
            <div class="prayer-item">
                <span class="prayer-name">{{ $name }}</span>
                <span class="prayer-time {{ $isActive ? 'prayer-active' : '' }}">{{ $time }}</span>
            </div>
        @endforeach
        @endif
    </div>

    {{-- CENTER: Slideshow --}}
    <div class="panel panel-center slideshow" id="slideshow">
        @php $slides = collect(); @endphp

        @if(!empty($signage['show_announcements']) && $announcements->isNotEmpty())
            @foreach($announcements as $n)
                @php $slides->push(['type' => 'announcement', 'title' => $n->title, 'sub' => 'Pengumuman']); @endphp
            @endforeach
        @endif

        @if(!empty($signage['show_achievements']) && $achievements->isNotEmpty())
            @foreach($achievements as $a)
                @php $slides->push(['type' => 'achievement', 'title' => $a->title ?? 'Prestasi ' . ($a->student?->user?->name ?? 'Siswa'), 'sub' => '🏆 ' . ($a->category?->name ?? 'Prestasi') . ' — ' . ($a->student?->user?->name ?? '')]); @endphp
            @endforeach
        @endif

        @if($slides->isEmpty())
            @php $slides->push(['type' => 'welcome', 'title' => $school->name, 'sub' => $schoolMotto]); @endphp
        @endif

        @foreach($slides as $i => $slide)
        <div class="slide {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}">
            <div class="slide-title">{{ $slide['title'] }}</div>
            <div class="slide-subtitle">{{ $slide['sub'] }}</div>
        </div>
        @endforeach

        <div style="position:absolute;bottom:20px;display:flex;gap:4px;">
            @foreach($slides as $i => $s)
                <span class="slide-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}" onclick="goToSlide({{ $i }})"></span>
            @endforeach
        </div>
    </div>

    {{-- RIGHT PANEL: Schedule + Events --}}
    <div class="panel panel-right">
        @if(!empty($signage['show_schedule']) && $todaysSchedule->isNotEmpty())
        <div class="section-title">Jadwal Hari Ini</div>
        <div style="flex:1;overflow-y:auto;">
            @foreach($todaysSchedule as $slot)
            <div class="schedule-item">
                <div class="schedule-time">{{ substr($slot->start_time, 0, 5) }} — {{ substr($slot->end_time, 0, 5) }}</div>
                <div class="schedule-subject">{{ $slot->subject?->name ?? '-' }}</div>
                <div class="schedule-class">{{ $slot->classSection?->classRoom?->name ?? '' }} {{ $slot->classSection?->section?->name ?? '' }} • {{ $slot->teacher?->name ?? '' }}</div>
            </div>
            @endforeach
        </div>
        @endif

        @if(!empty($signage['show_events']) && $upcomingEvents->isNotEmpty())
        <div class="section-title" style="margin-top:16px;">Event Mendatang</div>
        <div style="flex:1;overflow-y:auto;">
            @foreach($upcomingEvents as $ev)
            <div class="event-item">
                <div class="event-date">{{ \Carbon\Carbon::parse($ev->starts_at)->translatedFormat('d M Y') }}</div>
                <div class="event-name">{{ $ev->title }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- TICKER BAR --}}
@if(!empty($signage['show_ticker']))
<div class="ticker-bar">
    <div class="ticker-text">
        @if($tickerText)
            {{ $tickerText }} &nbsp;&nbsp;✦&nbsp;&nbsp;
        @endif
        {{ $schoolMotto }} &nbsp;&nbsp;✦&nbsp;&nbsp;
        @if(!empty($signage['show_events']) && $upcomingEvents->isNotEmpty())
        @foreach($upcomingEvents->take(3) as $ev)
            {{ $ev->title }} — {{ \Carbon\Carbon::parse($ev->starts_at)->translatedFormat('d M') }} &nbsp;&nbsp;✦&nbsp;&nbsp;
        @endforeach
        @endif
        Kunjungi website kami untuk info lebih lanjut &nbsp;&nbsp;✦&nbsp;&nbsp; {{ $school->name }}
    </div>
</div>
@endif

<script>
(function() {
    var slides = document.querySelectorAll('.slide');
    var dots = document.querySelectorAll('.slide-dot');
    var current = 0;
    var total = slides.length;

    function showSlide(idx) {
        slides.forEach(function(s, i) {
            s.classList.toggle('active', i === idx);
            if (dots[i]) dots[i].classList.toggle('active', i === idx);
        });
        current = idx;
    }

    window.goToSlide = showSlide;

    if (total > 1) {
        setInterval(function() {
            var next = (current + 1) % total;
            showSlide(next);
        }, 6000);
    }

    function updateClock() {
        var now = new Date();
        var h = String(now.getHours()).padStart(2, '0');
        var m = String(now.getMinutes()).padStart(2, '0');
        var s = String(now.getSeconds()).padStart(2, '0');
        var el = document.getElementById('clockTime');
        if (el) el.textContent = h + ':' + m + ':' + s;

        var months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        var days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        var dateEl = document.getElementById('clockDate');
        if (dateEl) dateEl.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
    }

    updateClock();
    setInterval(updateClock, 1000);
})();
</script>

</body>
</html>
