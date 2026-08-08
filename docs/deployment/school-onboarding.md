# School Onboarding Wizard

Step-by-step setup untuk admin sekolah baru.

## Hari 1 — Setup Dasar

### 1. Login ke `/admin/login`
Credentials dari super admin saat school registration.

### 2. School Profile (Module 03)
- Update nama lengkap sekolah, alamat, kontak
- NPSN (untuk Dapodik sync)
- Tahun ajaran aktif

### 3. Branding (Module 03b)
**Wajib upload:**
- Logo primary (header) — PNG transparent
- Favicon — 32x32 ICO/PNG

**Opsional:**
- Logo secondary, monochrome, login bg, splash screen
- Color palette (primary, secondary)
- Receipt layout pilih: simple/formal/modern

### 4. Academic Structure (Module 04)
- Buat tingkat kelas (X, XI, XII atau 1, 2, 3, ...)
- Buat jurusan (IPA, IPS, Bahasa)
- Mata pelajaran
- Wali kelas

## Hari 2 — Keuangan

### 5. Payment Gateway (Module 11b) — KRITIS
**Tambah minimal 1 provider per metode:**

#### Cash (default, pre-seeded)
Sudah ada via seeder. Edit nama jika perlu.

#### Bank Transfer Manual
1. **Settings → Payment Providers → + Add**
2. Format: `bank_transfer_manual`
3. Edit `extra_config.bank_accounts` dengan rekening sekolah:
   ```json
   [
     {
       "bank_name": "BCA",
       "account_number": "1234567890",
       "account_holder": "Yayasan ABC"
     }
   ]
   ```
4. Tambah method "Transfer Manual" yang reference provider ini.

#### VA / QRIS / E-Wallet (via gateway)
1. Daftar di gateway (Midtrans/Xendit/Doku/Faspay/dll.) — dapatkan API key
2. **Settings → Payment Providers → + Add**
3. Klik **Load Preset** → pilih format yang sesuai (autofill base URL)
4. Input API Key + Secret + Webhook Secret dari dashboard gateway
5. Klik **Test Connection** → pastikan OK
6. Tambah methods: VA BCA, VA Mandiri, GoPay, OVO, QRIS, dll.

### 6. Fee Structures (Module 11)
- Setup biaya SPP per tingkat kelas
- Setup biaya kegiatan, seragam, ujian

### 7. Initial Subscription Activation
Pastikan plan_expires_at di database masih aktif (super admin yang set).

## Hari 3 — Operasional

### 8. PPDB (Module 22) — Jika sudah dekat tahun ajaran baru
1. Buat PPDB Period
2. Set jalur quotas (zonasi 50%, prestasi 30%, dst)
3. Set form fee dan persyaratan dokumen
4. Publish

### 9. Roles & Users
- Buat user untuk: admin tambahan, accountant, librarian, receptionist, teachers, students
- Assign role via Spatie Permission

### 10. Communication (Module 17, 19)
- Buat first announcement: "Selamat datang di portal sekolah baru"
- Test email & FCM push

## Hari 4-7 — Fitur Lanjutan

### 11. Bus Tracking + ID Gate (Module 23)
**Hanya jika ada bus & gerbang QR:**
- Setup vehicles + GPS device tokens
- Setup ID gate devices
- Issue student ID cards (admin batch)

### 12. UKS / Klinik (Module 24)
- Buat user role `nurse` untuk perawat sekolah
- Aktivasi medical_records auto-create saat student register

### 13. BP/BK + Discipline (Module 25)
- Setup discipline categories (pre-seeded ada default, customize)
- Buat user role `counselor`
- Buat anti-bullying policy + brief siswa

### 14. AI Assistant (Module 31) — Opsional
- Daftar akun OpenAI/Anthropic/Gemini
- **Settings → AI Providers → + Add** → format-based (NO vendor name di code)
- Add models (model_name = `gpt-4o-mini`/`claude-haiku-4-5`/etc — input sendiri)
- Assign model ke fitur (`study_assistant`, `lesson_plan_gen`, dll.)

### 15. Pesantren Mode (Module 28) — Khusus pesantren/madrasah
- **Settings → Religious Mode** → toggle aktif
- Pilih religion + institution_type
- Setup hafalan targets per kelas
- Ustadz/musyrif user untuk verify setoran

## Hari 8+ — Going Live

### 16. SEO Submission
```bash
# Submit sitemap to Google
curl "https://www.google.com/ping?sitemap=https://your-school.sikadpro.app/sitemap.xml"

# Verify in Google Search Console
# Add property: https://your-school.sikadpro.app
```

### 17. Mobile App Distribution
- Build Flutter APK / iOS IPA
- Brand splash screen sudah otomatis dari branding settings
- Distribute via Play Store (production) atau Firebase App Distribution (testing)

### 18. Parent Onboarding
Send WhatsApp/email blast dengan:
- Link unduh app
- Tutorial video (rekam sendiri 2-3 menit)
- Login credentials anak

## Health Check Daily

Pasang di Slack/email reminder admin sekolah:
- ✅ Pembayaran masuk = Pembayaran tercatat (rekonsiliasi)
- ✅ FCM push reach rate > 80%
- ✅ Bus GPS update masih jalan (cek `/admin/transport`)
- ✅ Daily report ter-generate (cek `/admin/daily-reports`)
- ✅ Risk score updated (cek `/admin/analytics`)

## Common Issues

### Pembayaran online tidak masuk
1. Cek `payment_webhook_logs` table — ada incoming?
2. Signature valid?
3. Cek storage di gateway dashboard — apakah webhook URL benar?

### Push notification tidak muncul
1. FCM token registered? Check `users.fcm_token`
2. Firebase server key valid di `config/services.php`
3. Device internet OK?

### Daily report kosong
1. Scheduler jalan? `docker compose logs scheduler | grep daily-reports`
2. Cron command valid? `php artisan daily-reports:generate --school_id={id}`
