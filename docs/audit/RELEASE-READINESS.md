# RELEASE-READINESS

Status: **READY FOR STAGING VALIDATION** (bukan production langsung — produksi behind, butuh deploy + migrasi-nol + smoke).

## Quality gate (17/17)

1. Suite lama + baru lulus (413 Pest + 21 Playwright). 2. Regresi baru lulus. 3. P0 lokal selesai.
4. Tidak ada P0 disembunyikan (daftar di REMAINING-GAPS). 5. Navigasi per-role sesuai.
6. Workflow P1 lulus. 7. Persistensi terbukti (reload + DB assertion). 8. Tenant isolation lulus.
9. Payment/accounting lulus. 10. Console bersih pada workflow PASS. 11. Tanpa 500 tak terduga.
12. Build OK. 13. `composer validate` + `git diff --check` OK. 14. Migrasi testing aman (allowlist E2E).
15. Keamanan tidak dilemahkan. 16. Tanpa secret di commit. 17. Dokumen = HEAD aktual.

## Deploy (aaPanel, tanpa Docker)

```bash
SITE=/www/wwwroot/sikadpro.whitelabel.co.id; PHP=/www/server/php/83/bin/php
cd $SITE; $PHP artisan down --retry=60
git pull origin main   # pastikan memuat 8b10a18 + commit sesi ini setelah push
$PHP artisan migrate --force   # tanpa migrasi baru sesi ini; tetap jalankan (idempoten)
$PHP artisan optimize:clear; $PHP artisan config:cache; $PHP artisan route:cache; $PHP artisan view:cache
$PHP artisan queue:restart; $PHP artisan up
curl -s https://sikadpro.whitelabel.co.id/api/v1/health/deep
```

Rollback: `git reset --hard <sebelum>` + `$PHP artisan up` (tanpa migrasi destruktif sesi ini → aman).
