<x-mail::message>
# 🎓 Beasiswa Diterima!

Kami dengan senang hati menginformasikan bahwa pengajuan beasiswa anak Anda telah **DITERIMA**.

<x-mail::panel>
**Detail Beasiswa**

- **Berlaku dari:** {{ optional($application->granted_from)->format('d M Y') ?? '—' }}
- **Berlaku sampai:** {{ optional($application->granted_until)->format('d M Y') ?? '—' }}
@if($application->reviewer_note)
- **Catatan dari Reviewer:** {{ $application->reviewer_note }}
@endif
</x-mail::panel>

Diskon SPP akan otomatis diterapkan pada invoice anak Anda mulai periode berikutnya.

<x-mail::button :url="config('app.url')">
Lihat di Portal
</x-mail::button>

Selamat dan teruslah berprestasi!<br>
{{ config('app.name') }}
</x-mail::message>
