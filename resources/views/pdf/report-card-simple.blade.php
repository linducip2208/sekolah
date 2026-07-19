<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Raport {{ $student->admission_no }}</title>
<style>
body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #1f2937; margin: 0; padding: 25px; }
.header { text-align: center; border-bottom: 3px double #b8860b; padding-bottom: 15px; margin-bottom: 20px; }
.school { font-size: 14pt; font-weight: bold; color: #0b1d3a; }
.title { font-size: 22pt; margin: 10px 0 5px 0; letter-spacing: 6px; color: #b8860b; }
.subtitle { font-size: 11pt; color: #6b7280; }
.info-grid { display: table; width: 100%; margin-bottom: 20px; background: #f9fafb; padding: 12px; }
.info-cell { display: table-cell; width: 50%; padding: 5px 10px; font-size: 10pt; }
.info-label { color: #6b7280; font-size: 9pt; }
.info-value { font-weight: bold; }
table.marks { width: 100%; border-collapse: collapse; }
table.marks th { background: #0b1d3a; color: white; padding: 10px; text-align: left; font-size: 10pt; }
table.marks td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
table.marks .right { text-align: right; }
table.marks .center { text-align: center; }
.grade { font-weight: bold; font-size: 12pt; color: #0b1d3a; }
.signature { margin-top: 40px; display: table; width: 100%; }
.sig-cell { display: table-cell; width: 33%; text-align: center; }
.sig-line { border-top: 1px solid #000; padding-top: 3px; margin-top: 60px; }
</style></head><body>

<div class="header">
<div class="school">{{ $school->name }}</div>
<div style="font-size:9pt;color:#6b7280;">{{ $school->address }}</div>
<h1 class="title">RAPOR SISWA</h1>
<div class="subtitle">Laporan Hasil Belajar</div>
</div>

<div class="info-grid">
<div class="info-cell">
<div class="info-label">Nama Siswa</div>
<div class="info-value">{{ $student->user?->name }}</div>
</div>
<div class="info-cell">
<div class="info-label">NIS</div>
<div class="info-value">{{ $student->admission_no }}</div>
</div>
<div class="info-cell">
<div class="info-label">Kelas</div>
<div class="info-value">{{ $student->classSection?->classRoom?->name }} {{ $student->classSection?->section?->name }}</div>
</div>
<div class="info-cell">
<div class="info-label">Tanggal Cetak</div>
<div class="info-value">{{ now()->format('d M Y') }}</div>
</div>
</div>

<table class="marks"><thead><tr>
<th>No</th><th>Mata Pelajaran</th><th class="center">Nilai Tertinggi</th>
<th class="center">Nilai Terendah</th><th class="center">Rata-rata</th><th class="center">Grade</th>
</tr></thead><tbody>
@php $no = 1; @endphp
@forelse($marks as $subjectName => $subjectMarks)
@php
    $highest = $subjectMarks->max('obtained_marks');
    $lowest  = $subjectMarks->min('obtained_marks');
    $avg     = round($subjectMarks->avg('obtained_marks'), 1);
    $totalMax = $subjectMarks->first()->total_marks ?? 100;
    $pct = $totalMax > 0 ? ($avg / $totalMax) * 100 : 0;
    $grade = $pct >= 90 ? 'A' : ($pct >= 80 ? 'B' : ($pct >= 70 ? 'C' : ($pct >= 60 ? 'D' : 'E')));
@endphp
<tr>
<td>{{ $no++ }}</td>
<td><strong>{{ $subjectName ?? 'N/A' }}</strong></td>
<td class="center font-mono">{{ $highest }}</td>
<td class="center font-mono">{{ $lowest }}</td>
<td class="center font-mono">{{ $avg }}/{{ $totalMax }}</td>
<td class="center"><span class="grade">{{ $grade }}</span></td>
</tr>
@empty
<tr><td colspan="6" style="text-align:center;padding:30px;color:#6b7280;font-style:italic;">Belum ada nilai tercatat.</td></tr>
@endforelse
</tbody></table>

<div style="margin-top:30px;padding:15px;background:#f9fafb;border-left:4px solid #b8860b;">
<strong>Catatan:</strong>
<p style="font-size:9pt;color:#374151;margin:5px 0 0 0;">Raport ini bersifat ringkasan dari semua nilai yang tercatat. Untuk raport semester resmi yang lebih lengkap (KKM, deskripsi sikap, kehadiran, ekstrakurikuler), silakan menggunakan generator raport semester di menu Akademik.</p>
</div>

<div class="signature">
<div class="sig-cell"><div style="font-size:9pt;">Mengetahui,</div>
<div class="sig-line">Orang Tua/Wali</div></div>
<div class="sig-cell"><div style="font-size:9pt;">Wali Kelas,</div>
<div class="sig-line">{{ $student->classSection?->classTeacher?->name ?? '_____________' }}</div></div>
<div class="sig-cell"><div style="font-size:9pt;">Kepala Sekolah,</div>
<div class="sig-line">_____________</div></div>
</div>

</body></html>
