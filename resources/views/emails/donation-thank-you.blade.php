<x-mail::message>
# 🙏 Terima Kasih, {{ $donation->donor_name ?? 'Donatur' }}!

Donasi Anda sebesar **Rp {{ number_format($donation->amount / 100, 0, ',', '.') }}** untuk kampanye "**{{ $campaign?->title ?? '—' }}**" telah kami terima dengan baik.

<x-mail::panel>
**Detail Donasi**

- **No. Kuitansi:** {{ $donation->receipt_no ?? '—' }}
- **Tanggal:** {{ optional($donation->donated_at)->format('d M Y H:i') ?? '—' }}
- **Kampanye:** {{ $campaign?->title ?? '—' }}
@if($donation->npwp)
- **NPWP:** {{ $donation->npwp }} (untuk laporan pajak)
@endif
</x-mail::panel>

Donasi Anda akan digunakan langsung untuk kebaikan murid-murid kami. Semoga Allah membalas kebaikan Anda.

@if(!$donation->is_anonymous)
<x-mail::button :url="config('app.url')">
Lihat Update Kampanye
</x-mail::button>
@endif

Salam hangat,<br>
{{ config('app.name') }}
</x-mail::message>
