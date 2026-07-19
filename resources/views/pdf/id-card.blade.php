<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>ID {{ $student->admission_no }}</title>
<style>
@page { margin: 0; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 8pt; color: #1f2937; margin: 0; padding: 0; }
.card { width: 245px; height: 153px; border: 2px solid #b8860b; padding: 10px; box-sizing: border-box; }
.header { background: #0b1d3a; color: white; padding: 4px 8px; margin: -10px -10px 8px -10px; }
.school { font-size: 9pt; font-weight: bold; }
.school-sub { font-size: 6pt; opacity: 0.8; }
.body { display: table; width: 100%; }
.photo-cell { display: table-cell; width: 60px; vertical-align: top; }
.photo { width: 55px; height: 65px; border: 1px solid #d1d5db; background: #f9fafb; text-align: center; padding-top: 25px; color: #9ca3af; font-size: 7pt; }
.info-cell { display: table-cell; vertical-align: top; padding-left: 8px; font-size: 7pt; }
.info-cell .name { font-size: 10pt; font-weight: bold; color: #0b1d3a; line-height: 1.1; margin-bottom: 4px; }
.info-cell .nis { font-family: monospace; color: #b8860b; }
.info-cell .field { padding: 1px 0; }
.info-cell .field .label { color: #6b7280; }
.qr { float: right; width: 35px; height: 35px; background: repeating-linear-gradient(45deg, #000 0 2px, #fff 2px 4px); border: 1px solid #000; }
.footer { position: absolute; bottom: 5px; left: 10px; right: 10px; font-size: 6pt; color: #6b7280; text-align: center; }
</style></head><body>

<div class="card">
<div class="header">
<div class="school">{{ $school->name }}</div>
<div class="school-sub">KARTU PELAJAR · STUDENT ID</div>
</div>

<div class="body">
<div class="photo-cell">
<div class="photo">FOTO<br>3×4</div>
</div>
<div class="info-cell">
<div class="name">{{ $student->user?->name }}</div>
<div class="nis">{{ $student->admission_no }}</div>
<div class="field"><span class="label">Kelas:</span> {{ $student->classSection?->classRoom?->name }} {{ $student->classSection?->section?->name }}</div>
<div class="field"><span class="label">L/P:</span> {{ $student->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</div>
@if($student->date_of_birth)
<div class="field"><span class="label">TTL:</span> {{ $student->date_of_birth->format('d M Y') }}</div>
@endif
@if($student->blood_group)
<div class="field"><span class="label">Gol. Darah:</span> {{ $student->blood_group }}</div>
@endif
</div>
</div>

<div class="footer">
{{ $school->phone }} · Berlaku sampai akhir tahun ajaran
</div>
</div>

</body></html>
