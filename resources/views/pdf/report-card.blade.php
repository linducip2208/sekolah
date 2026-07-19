<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
    .header { text-align: center; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; margin-bottom: 16px; }
    .header h1 { font-size: 16px; font-weight: bold; }
    .header h2 { font-size: 14px; margin-top: 4px; }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; }
    .info-row { display: flex; gap: 8px; }
    .info-label { min-width: 120px; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    th { background: #f0f0f0; font-weight: bold; }
    .grade-cell { text-align: center; font-weight: bold; }
    .summary { margin-top: 24px; }
    .signature-row { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-top: 48px; }
    .signature { text-align: center; }
    .signature-line { border-top: 1px solid #1a1a1a; margin-top: 48px; padding-top: 4px; }
    .rank-badge { background: #4f46e5; color: white; padding: 2px 8px; border-radius: 99px; font-size: 10px; }
</style>
</head>
<body>

<div class="header">
    <h1>{{ $school?->name ?? 'eSchool' }}</h1>
    <h2>LAPORAN HASIL BELAJAR SISWA</h2>
    <p>Semester {{ $reportCard->semester?->name ?? '-' }} — Tahun Pelajaran {{ $reportCard->semester?->academicYear?->name ?? '-' }}</p>
</div>

<div class="info-grid">
    <div>
        <div class="info-row"><span class="info-label">Nama Siswa</span><span>: {{ $student->user->name }}</span></div>
        <div class="info-row"><span class="info-label">No. Induk</span><span>: {{ $student->admission_no ?? '-' }}</span></div>
        <div class="info-row"><span class="info-label">Kelas</span><span>: {{ $student->classSection?->classRoom?->name }} - {{ $student->classSection?->section?->name }}</span></div>
    </div>
    <div>
        <div class="info-row"><span class="info-label">Peringkat</span><span>: <span class="rank-badge">{{ $reportCard->rank ?? '-' }}</span></span></div>
        <div class="info-row"><span class="info-label">Rata-rata</span><span>: {{ number_format($reportCard->average_percentage ?? 0, 1) }}</span></div>
        <div class="info-row"><span class="info-label">Total Hadir</span><span>: {{ $reportCard->attendance_present ?? '-' }} hari</span></div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Mata Pelajaran</th>
            <th>Nilai</th>
            <th>Nilai Maks</th>
            <th>%</th>
            <th>Grade</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($reportCard->marks ?? [] as $mark)
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ $mark['subject'] ?? '-' }}</td>
            <td class="grade-cell">{{ $mark['obtained_marks'] ?? '-' }}</td>
            <td class="grade-cell">{{ $mark['total_marks'] ?? '-' }}</td>
            <td class="grade-cell">{{ number_format($mark['percentage'] ?? 0, 1) }}</td>
            <td class="grade-cell" style="font-weight:bold;color:{{ ($mark['percentage'] ?? 0) >= 75 ? '#15803d' : '#dc2626' }}">
                {{ $mark['grade'] ?? '-' }}
            </td>
            <td>{{ ($mark['percentage'] ?? 0) >= 75 ? 'Tuntas' : 'Belum Tuntas' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@if($reportCard->notes)
<div class="summary">
    <strong>Catatan Wali Kelas:</strong>
    <p style="margin-top:4px">{{ $reportCard->notes }}</p>
</div>
@endif

<div class="signature-row">
    <div class="signature">
        <p>Orang Tua / Wali,</p>
        <div class="signature-line">(...................................)</div>
    </div>
    <div class="signature">
        <p>Wali Kelas,</p>
        <div class="signature-line">(...................................)</div>
    </div>
</div>

</body>
</html>
