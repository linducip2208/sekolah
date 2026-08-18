<x-mail::message>
# Informasi Status Pendaftaran

Yth. Bapak/Ibu **{{ $application->parent_name }}**,

Kami memberitahukan bahwa pendaftaran PPDB untuk anak Anda tidak dapat kami terima pada periode ini.

<x-mail::panel>
**Detail Pendaftaran**

- **No. Pendaftaran:** {{ $application->registration_no }}
- **Nama Siswa:** {{ $application->student_name }}
- **Jalur:** {{ ucfirst($application->jalur) }}
- **Status:** Ditolak
</x-mail::panel>

@if($application->reviewer_note)
## Catatan

{{ $application->reviewer_note }}
@endif

## Langkah Selanjutnya

- Anda dapat mendaftar kembali pada periode PPDB berikutnya
- Silakan hubungi bagian administrasi sekolah untuk informasi lebih lanjut

<x-mail::button :url="config('app.url')">
Hubungi Sekolah
</x-mail::button>

Terima kasih atas perhatian Anda.<br>
{{ config('app.name') }}
</x-mail::message>
