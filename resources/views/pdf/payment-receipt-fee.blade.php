<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Kuitansi #{{ $payment->id }}</title>
<style>
body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #1f2937; margin: 0; padding: 25px; }
.header { text-align: center; border-bottom: 2px solid #b8860b; padding-bottom: 10px; margin-bottom: 15px; }
.school { font-size: 13pt; font-weight: bold; color: #0b1d3a; }
.title { font-size: 18pt; font-weight: bold; margin: 10px 0 5px 0; letter-spacing: 4px; }
.row { display: table; width: 100%; padding: 4px 0; border-bottom: 1px dotted #d1d5db; }
.label { display: table-cell; color: #6b7280; width: 45%; font-size: 9pt; }
.value { display: table-cell; font-weight: bold; }
.amount-box { background: #fff7ed; border: 2px solid #b8860b; padding: 12px; margin: 15px 0; text-align: center; }
.amount { font-size: 20pt; font-weight: bold; color: #0b1d3a; }
.footer { margin-top: 30px; text-align: center; font-size: 8pt; color: #6b7280; }
.stamp { border: 2px solid #16a34a; color: #16a34a; padding: 6px 16px; display: inline-block; transform: rotate(-3deg); font-weight: bold; font-size: 14pt; margin: 12px 0; }
</style></head><body>

<div class="header">
<div class="school">{{ $school->name }}</div>
<div style="font-size:8pt;">{{ $school->address }}</div>
<div class="title">KUITANSI</div>
<div style="font-family:monospace;font-size:9pt;">No. {{ str_pad($payment->id, 8, '0', STR_PAD_LEFT) }} · {{ $payment->payment_date->format('d M Y') }}</div>
</div>

<div class="row"><span class="label">Dibayar oleh</span><span class="value">{{ $invoice->student?->user?->name }}</span></div>
<div class="row"><span class="label">NIS</span><span class="value">{{ $invoice->student?->admission_no }}</span></div>
<div class="row"><span class="label">No. Invoice</span><span class="value">{{ $invoice->invoice_no }}</span></div>
<div class="row"><span class="label">Untuk Pembayaran</span><span class="value">{{ $invoice->feeStructure?->name }}</span></div>
<div class="row"><span class="label">Periode</span><span class="value">{{ $invoice->period }}</span></div>
<div class="row"><span class="label">Metode Pembayaran</span><span class="value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span></div>
@if($payment->reference)
<div class="row"><span class="label">Referensi</span><span class="value">{{ $payment->reference }}</span></div>
@endif

<div class="amount-box">
<div style="font-size:8pt;text-transform:uppercase;letter-spacing:2px;color:#92400e;">Jumlah Diterima</div>
<div class="amount">Rp {{ number_format($payment->amount/100, 0, ',', '.') }}</div>
</div>

@if($invoice->paid_amount >= $invoice->amount)
<div style="text-align:center;"><div class="stamp">LUNAS</div></div>
@endif

<div style="margin-top:30px;display:table;width:100%;">
<div style="display:table-cell;width:50%;">
<div style="font-size:9pt;">Diterima oleh,</div>
<div style="height:60px;"></div>
<div style="border-top:1px solid #000;padding-top:3px;font-weight:bold;">{{ $payment->collector?->name ?? 'Petugas' }}</div>
</div>
<div style="display:table-cell;width:50%;text-align:right;">
<div style="font-size:9pt;">{{ $school->name }}</div>
<div style="height:60px;"></div>
<div style="font-size:8pt;color:#6b7280;">Stempel & Tanda Tangan</div>
</div>
</div>

<div class="footer">Kuitansi resmi · Digenerate {{ now()->format('d M Y H:i') }}</div>
</body></html>
