<x-mail::message>
# ✅ Pembayaran Berhasil

Terima kasih, pembayaran Anda telah kami terima.

<x-mail::panel>
**Detail Transaksi**

- **No. Referensi:** {{ $tx->reference_no }}
- **Invoice:** {{ $invoice?->invoice_no ?? '—' }}
- **Periode:** {{ $invoice?->period ?? '—' }}
- **Jumlah:** Rp {{ number_format($tx->amount / 100, 0, ',', '.') }}
- **Metode:** {{ $tx->method?->display_name ?? '—' }}
- **Tanggal Bayar:** {{ optional($tx->paid_at)->format('d M Y H:i') ?? '—' }}
</x-mail::panel>

Receipt resmi dapat diunduh di portal orang tua.

<x-mail::button :url="config('app.url') . '/portal/payments/' . $tx->reference_no">
Lihat Receipt
</x-mail::button>

Terima kasih atas kepercayaan Anda.<br>
{{ config('app.name') }}
</x-mail::message>
