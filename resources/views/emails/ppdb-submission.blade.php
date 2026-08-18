<x-mail::message>
# ✅ Pendaftaran PPDB Berhasil Dikirim

Yth. Bapak/Ibu **{{ $application->parent_name }}**,

Kami telah menerima pendaftaran PPDB untuk anak Anda. Berikut detail pendaftarannya:

<x-mail::panel>
**Detail Pendaftaran**

- **No. Pendaftaran:** {{ $application->registration_no }}
- **Nama Siswa:** {{ $application->student_name }}
- **Jalur:** {{ ucfirst($application->jalur) }}
- **Tanggal Lahir:** {{ $application->date_of_birth?->format('d M Y') }}
- **Asal Sekolah:** {{ $application->previous_school ?? '—' }}
</x-mail::panel>

## Status Pendaftaran

Pendaftaran Anda saat ini berstatus **{{ ucfirst($application->status) }}**. Tim kami akan melakukan verifikasi data dan menginformasikan hasilnya melalui email.

## Yang Perlu Dilakukan

1. **Lengkapi Dokumen** — pastikan semua dokumen yang diperlukan telah diupload
2. **Pantau Email** — kami akan mengirimkan notifikasi setiap ada perubahan status
3. **Periksa Portal** — login ke portal untuk melihat status terkini

<x-mail::button :url="config('app.url')">
Lihat Status Pendaftaran
</x-mail::button>

Terima kasih telah memilih sekolah kami.<br>
{{ config('app.name') }}
</x-mail::message>
