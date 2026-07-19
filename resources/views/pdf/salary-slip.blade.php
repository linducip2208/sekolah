<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Slip Gaji {{ $slip->month }}</title>
<style>
body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #1f2937; margin: 0; padding: 25px; }
.header { border-bottom: 3px double #b8860b; padding-bottom: 12px; margin-bottom: 20px; }
.header table { width: 100%; }
.school { font-size: 13pt; font-weight: bold; color: #0b1d3a; }
.title { font-size: 18pt; text-align: right; color: #0b1d3a; margin: 0; }
.info-grid { display: table; width: 100%; margin: 15px 0; }
.info-cell { display: table-cell; width: 50%; padding: 5px 10px; }
.info-label { color: #6b7280; font-size: 9pt; }
.info-value { font-weight: bold; }
table.items { width: 100%; border-collapse: collapse; margin: 15px 0; }
table.items th { background: #0b1d3a; color: white; padding: 8px 10px; text-align: left; font-size: 9pt; }
table.items td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
table.items .right { text-align: right; }
.section-header { background: #f9fafb; font-weight: bold; }
.totals-box { border: 2px solid #b8860b; background: #fff7ed; padding: 15px; margin-top: 20px; }
.takehome { font-size: 18pt; font-weight: bold; color: #0b1d3a; }
.signature-box { margin-top: 40px; display: table; width: 100%; }
.signature-cell { display: table-cell; width: 50%; text-align: center; }
.signature-line { border-top: 1px solid #000; padding-top: 3px; margin-top: 60px; font-weight: bold; }
</style></head><body>

<div class="header"><table><tr>
<td><div class="school">{{ $school->name }}</div>
<div style="font-size:8pt;color:#6b7280;">{{ $school->address }}</div></td>
<td style="text-align:right;"><h1 class="title">SLIP GAJI</h1>
<div style="font-size:11pt;font-weight:bold;color:#b8860b;">Periode: {{ $slip->month }}</div></td>
</tr></table></div>

<div class="info-grid">
<div class="info-cell">
<div class="info-label">Nama Karyawan</div>
<div class="info-value">{{ $slip->staff?->user?->name }}</div>
</div>
<div class="info-cell">
<div class="info-label">NIP</div>
<div class="info-value">{{ $slip->staff?->employee_id ?? '—' }}</div>
</div>
<div class="info-cell">
<div class="info-label">Departemen</div>
<div class="info-value">{{ $slip->staff?->department ?? '—' }}</div>
</div>
<div class="info-cell">
<div class="info-label">Jabatan</div>
<div class="info-value">{{ $slip->staff?->designation ?? '—' }}</div>
</div>
</div>

<table class="items">
<thead><tr><th colspan="2">PENDAPATAN</th></tr></thead>
<tbody>
<tr><td>Gaji Pokok</td><td class="right">Rp {{ number_format($slip->basic_salary/100, 0, ',', '.') }}</td></tr>
@foreach(($slip->allowances_detail ?? []) as $a)
<tr><td>{{ $a['name'] }}</td><td class="right">+ Rp {{ number_format($a['amount']/100, 0, ',', '.') }}</td></tr>
@endforeach
<tr class="section-header"><td>Total Pendapatan</td><td class="right">Rp {{ number_format(($slip->basic_salary + $slip->total_allowances)/100, 0, ',', '.') }}</td></tr>
</tbody>
<thead><tr><th colspan="2">POTONGAN</th></tr></thead>
<tbody>
@forelse(($slip->deductions_detail ?? []) as $d)
<tr><td>{{ $d['name'] }}</td><td class="right" style="color:#dc2626;">- Rp {{ number_format($d['amount']/100, 0, ',', '.') }}</td></tr>
@empty
<tr><td colspan="2" style="text-align:center;font-style:italic;color:#6b7280;">Tidak ada potongan</td></tr>
@endforelse
<tr class="section-header"><td>Total Potongan</td><td class="right" style="color:#dc2626;">- Rp {{ number_format($slip->total_deductions/100, 0, ',', '.') }}</td></tr>
</tbody>
</table>

<div class="totals-box">
<table style="width:100%;"><tr>
<td><div style="font-size:9pt;color:#92400e;text-transform:uppercase;letter-spacing:2px;">Take Home Pay</div></td>
<td style="text-align:right;"><div class="takehome">Rp {{ number_format($slip->net_salary/100, 0, ',', '.') }}</div></td>
</tr></table>
</div>

<div style="margin-top:20px;">
<strong>Status:</strong>
@if($slip->status === 'paid')
<span style="color:#16a34a;">✓ Sudah Dibayar pada {{ $slip->paid_on?->format('d M Y') }}</span>
@else
<span style="color:#a16207;">⏳ Belum Dibayar (Draft)</span>
@endif
</div>

<div class="signature-box">
<div class="signature-cell">
<div style="font-size:9pt;">Mengetahui,</div>
<div class="signature-line">Kepala Sekolah</div>
</div>
<div class="signature-cell">
<div style="font-size:9pt;">Yang Menerima,</div>
<div class="signature-line">{{ $slip->staff?->user?->name }}</div>
</div>
</div>

</body></html>
