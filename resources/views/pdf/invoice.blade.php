<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Invoice {{ $invoice->invoice_no }}</title>
<style>
body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; color: #1f2937; margin: 0; padding: 30px; }
h1 { color: #0b1d3a; margin: 0 0 5px 0; }
.header { border-bottom: 3px double #b8860b; padding-bottom: 15px; margin-bottom: 25px; }
.header table { width: 100%; }
.header .school { font-size: 14pt; font-weight: bold; color: #0b1d3a; }
.header .right { text-align: right; }
.invoice-info { background: #f9fafb; padding: 15px; margin-bottom: 20px; }
.invoice-info table { width: 100%; }
.invoice-info td { padding: 4px 0; }
.invoice-info .label { color: #6b7280; font-size: 9pt; text-transform: uppercase; letter-spacing: 1px; }
table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
table.items th { background: #0b1d3a; color: white; padding: 10px; text-align: left; font-weight: 600; font-size: 10pt; }
table.items td { padding: 12px 10px; border-bottom: 1px solid #e5e7eb; }
table.items .right { text-align: right; }
.total-row { background: #fff7ed; font-weight: bold; font-size: 13pt; }
.status { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 9pt; font-weight: 600; }
.status-paid { background: #dcfce7; color: #166534; }
.status-unpaid { background: #fee2e2; color: #991b1b; }
.status-partial { background: #fef9c3; color: #854d0e; }
.footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 9pt; text-align: center; }
</style></head><body>

<div class="header"><table><tr>
<td><div class="school">{{ $school->name }}</div>
<div style="font-size:9pt;color:#6b7280;">{{ $school->address }}</div>
<div style="font-size:9pt;color:#6b7280;">{{ $school->phone }} · {{ $school->email }}</div></td>
<td class="right"><h1>INVOICE</h1>
<div style="font-family:monospace;font-size:11pt;">{{ $invoice->invoice_no }}</div>
<div style="font-size:9pt;margin-top:5px;">Diterbitkan: {{ $invoice->created_at->format('d M Y') }}</div></td>
</tr></table></div>

<div class="invoice-info"><table><tr>
<td><div class="label">Kepada</div>
<div style="font-size:13pt;font-weight:bold;">{{ $invoice->student?->user?->name }}</div>
<div style="font-size:9pt;color:#6b7280;">NIS: {{ $invoice->student?->admission_no }}</div>
<div style="font-size:9pt;color:#6b7280;">{{ $invoice->student?->classSection?->classRoom?->name }} {{ $invoice->student?->classSection?->section?->name }}</div></td>
<td class="right"><div class="label">Jatuh Tempo</div>
<div style="font-size:13pt;font-weight:bold;">{{ $invoice->due_date->format('d M Y') }}</div>
<div style="margin-top:8px;"><span class="status status-{{ $invoice->status === 'paid' ? 'paid' : ($invoice->status === 'partial' ? 'partial' : 'unpaid') }}">{{ strtoupper($invoice->status) }}</span></div></td>
</tr></table></div>

<table class="items"><thead><tr>
<th>Deskripsi</th><th>Periode</th><th class="right">Jumlah</th>
</tr></thead><tbody>
<tr><td>{{ $invoice->feeStructure?->name ?? 'Biaya Sekolah' }}</td>
<td>{{ $invoice->period }}</td>
<td class="right">Rp {{ number_format($invoice->amount/100, 0, ',', '.') }}</td></tr>
@if($invoice->discount > 0)
<tr><td>Diskon</td><td></td><td class="right" style="color:#dc2626;">- Rp {{ number_format($invoice->discount/100, 0, ',', '.') }}</td></tr>
@endif
<tr><td>Sudah Dibayar</td><td></td><td class="right" style="color:#16a34a;">Rp {{ number_format($invoice->paid_amount/100, 0, ',', '.') }}</td></tr>
<tr class="total-row"><td colspan="2">SISA TAGIHAN</td>
<td class="right">Rp {{ number_format(($invoice->amount - $invoice->paid_amount - $invoice->discount)/100, 0, ',', '.') }}</td></tr>
</tbody></table>

<div style="margin-top:30px;"><strong>Cara Pembayaran:</strong>
<ul style="font-size:10pt;color:#374151;">
<li>Tunai di kasir sekolah</li>
<li>Transfer bank atau virtual account (lihat portal pembayaran)</li>
<li>QRIS / e-wallet</li>
</ul></div>

<div class="footer">Terima kasih atas perhatian Anda. Invoice digenerate otomatis oleh sistem.<br>
{{ $school->name }} · {{ now()->format('d M Y H:i') }}</div>
</body></html>
