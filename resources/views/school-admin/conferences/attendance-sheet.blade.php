<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Absensi Konferensi — {{ $session->title }}</title>
    <style>
        body { font-family: 'Inter', 'Segoe UI', sans-serif; font-size: 13px; color: #1a1a2e; max-width: 800px; margin: 30px auto; padding: 0 20px; }
        h1 { font-family: 'Playfair Display', 'Cormorant Garamond', serif; font-size: 24px; margin-bottom: 4px; }
        .meta { font-size: 12px; color: #64748b; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { text-align: left; padding: 8px 10px; border-bottom: 2px solid #1a1a2e; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; }
        td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        .slot-header { background: #f8fafc; font-weight: 600; padding: 10px; border-bottom: 2px solid #e2e8f0; }
        .signature-area { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-box { text-align: center; width: 180px; }
        .signature-line { border-bottom: 1px solid #1a1a2e; margin: 60px 0 10px; }
        .empty-slot { color: #94a3b8; font-style: italic; padding: 6px 0; font-size: 12px; }
        @media print {
            body { margin: 0; padding: 0 15px; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

<h1>Absensi Konferensi</h1>
<div class="meta">
    <strong>{{ $session->title }}</strong><br>
    {{ \Carbon\Carbon::parse($session->date)->translatedFormat('l, d F Y') }} ·
    {{ $session->start_time }} - {{ $session->end_time }} ·
    {{ $session->location === 'online' ? 'Online' : $session->location_detail ?: 'Fisik' }}
</div>

@php $slots = $session->timeSlots(); @endphp

<table>
    <thead>
        <tr>
            <th style="width:60px;">No</th>
            <th style="width:70px;">NIS</th>
            <th>Nama Siswa</th>
            <th>Orang Tua</th>
            <th style="width:60px;">Hadir</th>
            <th style="width:80px;">Tanda Tangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($slots as $slot)
            @php $slotBookings = $bookings->where('booking_time', $slot['time']); @endphp
            <tr><td colspan="6" class="slot-header">{{ $slot['time'] }}</td></tr>
            @forelse($slotBookings as $i => $b)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $b->student->admission_no }}</td>
                    <td>{{ $b->student->user->name }}</td>
                    <td>{{ $b->parent->name }}</td>
                    <td style="text-align:center;">☐</td>
                    <td></td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-slot">Tidak ada booking</td></tr>
            @endforelse
        @endforeach
    </tbody>
</table>

<div class="signature-area">
    <div class="signature-box">
        <div class="signature-line"></div>
        <div>Guru / Wali Kelas</div>
    </div>
    <div class="signature-box">
        <div class="signature-line"></div>
        <div>Kepala Sekolah</div>
    </div>
</div>

<script>window.onload = function() { window.print(); }</script>
</body>
</html>
