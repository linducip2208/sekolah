<x-mail::message>
# 🎉 Selamat! Anak Anda Diterima

Kami dengan senang hati menginformasikan bahwa **{{ $application->student_name }}** telah diterima sebagai siswa baru di sekolah kami.

<x-mail::panel>
**Detail Penerimaan**

- **No. Pendaftaran:** {{ $application->registration_no }}
- **Jalur:** {{ ucfirst($application->jalur) }}
- **Peringkat:** {{ $application->rank_position ? '#' . $application->rank_position : '—' }}
- **Tanggal Diterima:** {{ optional($application->accepted_at)->format('d M Y') }}
</x-mail::panel>

## Langkah Selanjutnya

1. **Daftar Ulang** — selesaikan pembayaran daftar ulang sebelum deadline
2. **Lengkapi Dokumen** — upload dokumen yang masih kurang
3. **Orientasi Siswa Baru** — informasi jadwal akan menyusul

<x-mail::button :url="config('app.url')">
Login ke Portal
</x-mail::button>

Selamat bergabung di keluarga besar kami!<br>
{{ config('app.name') }}
</x-mail::message>
