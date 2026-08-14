#!/usr/bin/env bash
# =============================================================================
# Sikad Pro — Production Deploy Script
# Jalankan DI SERVER PRODUKSI (sudah ter-clone & ter-setup).
#   ./deploy.sh
# =============================================================================
set -euo pipefail

cd "$(dirname "$0")"

echo "==> [1/8] Pull kode terbaru"
git pull origin main

echo "==> [2/8] Install dependency PHP"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> [3/8] Build frontend (Vite)"
npm install --no-audit --no-fund
npm run build

echo "==> [4/8] Migrasi database"
php artisan migrate --force

echo "==> [5/8] Bersihkan cache lama"
php artisan optimize:clear

echo "==> [6/8] Cache konfigurasi & route & view"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> [7/8] Link storage (kalau belum)"
php artisan storage:link || true

echo "==> [8/8] Restart queue worker"
php artisan queue:restart || true

echo ""
echo "✅ Deploy selesai. Restart PHP-FPM / supervisor jika perlu:"
echo "   sudo supervisorctl reload   (atau)  sudo systemctl reload php8.3-fpm"
