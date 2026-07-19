# eSchool SaaS — Deployment Guide

Panduan deploy eSchool SaaS ke production server (VPS / dedicated / Docker).

---

## Prerequisites

| Item | Minimum | Recommended |
|------|---------|-------------|
| PHP | 8.3+ | 8.3+ |
| MySQL | 8.0+ | 8.0+ / MariaDB 11+ |
| Redis | 7.x | 7.x |
| Node.js | 22+ | 22+ |
| Composer | 2+ | 2+ |
| OS | Ubuntu 22.04 | Ubuntu 24.04 |
| RAM | 4 GB | 8 GB |
| Storage | 40 GB SSD | 80 GB SSD NVMe |
| SSL | Let's Encrypt (wildcard) | Let's Encrypt (wildcard) |

---

## Quick Start (Local Development)

```bash
# 1. Clone repo
git clone <repo-url> eschool
cd eschool

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env
# DB_CONNECTION=mysql
# DB_DATABASE=eschool_saas
# Set LICENSE_DEV_BYPASS=true for local dev

# 5. Migrate and seed
php artisan migrate --seed

# 6. Link storage
php artisan storage:link

# 7. Build frontend
npm run build

# 8. Start dev server
php artisan serve

# 9. Start queue worker (separate terminal)
php artisan queue:work

# 10. Open browser
# Super admin: http://localhost:8000/admin/login
# School portal: http://localhost:8000
```

---

## Production Setup (Standalone VPS)

### 1. Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3 + extensions
sudo add-apt-repository ppa:ondrej/php -y
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-redis \
    php8.3-mbstring php8.3-xml php8.3-gd php8.3-curl \
    php8.3-intl php8.3-bcmath php8.3-zip php8.3-opcache

# Install Node.js 22
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt install -y nodejs

# Install MySQL 8
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Redis
sudo apt install -y redis-server

# Install Nginx
sudo apt install -y nginx

# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --version=2.7.0
sudo mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"
```

### 2. Clone & Install

```bash
cd /var/www
git clone <repo-url> eschool
sudo chown -R www-data:www-data eschool
cd eschool

sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm install
sudo -u www-data npm run build
```

### 3. Configure .env

```bash
sudo -u www-data cp .env.example .env
sudo -u www-data nano .env
```

Key production settings:

```
APP_NAME="eSchool SaaS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admin.eschool.app
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

# Multi-tenant base domain
TENANT_BASE_DOMAIN=eschool.app
SUPER_ADMIN_DOMAIN=admin.eschool.app

# License (dev bypass off in production)
LICENSE_DEV_BYPASS=false
LICENSE_SERVER_URL=https://whitelabel.co.id
LICENSE_HEARTBEAT_INTERVAL=86400
LICENSE_HEARTBEAT_GRACE=604800

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eschool_saas
DB_USERNAME=eschool
DB_PASSWORD=<strong-password>

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=<redis-password>
REDIS_PORT=6379

# Cache & Session & Queue
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Broadcasting (Reverb / Soketi)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=eschool
REVERB_APP_KEY=eschool-key
REVERB_APP_SECRET=eschool-secret
REVERB_HOST=eschool.app
REVERB_PORT=443
REVERB_SCHEME=https

# Mail
MAIL_MAILER=smtp
MAIL_HOST=<smtp-host>
MAIL_PORT=587
MAIL_USERNAME=<username>
MAIL_PASSWORD=<password>
MAIL_FROM_ADDRESS="noreply@eschool.app"
MAIL_FROM_NAME="eSchool SaaS"

# Storage
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=<key>
AWS_SECRET_ACCESS_KEY=<secret>
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=eschool-storage

# Multi-tenancy
SANCTUM_STATEFUL_DOMAINS=*.eschool.app,admin.eschool.app
SESSION_DOMAIN=.eschool.app
```

### 4. Create Database

```sql
mysql -u root -p

CREATE DATABASE eschool_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'eschool'@'localhost' IDENTIFIED BY '<strong-password>';
GRANT ALL PRIVILEGES ON eschool_saas.* TO 'eschool'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Run Migrations + Seed

```bash
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan migrate --force --seed
sudo -u www-data php artisan storage:link
```

### 6. Cache Everything

```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

### 7. Set Permissions

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 8. Configure Nginx

```bash
sudo cp deploy/nginx.conf /etc/nginx/sites-available/eschool
# Edit server_name and root path
sudo nano /etc/nginx/sites-available/eschool
sudo ln -s /etc/nginx/sites-available/eschool /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 9. SSL Wildcard (Cloudflare DNS)

```bash
# Install certbot + Cloudflare plugin
sudo apt install -y certbot python3-certbot-dns-cloudflare

# Create Cloudflare credentials
sudo mkdir -p /root/.secrets
sudo bash -c 'cat > /root/.secrets/cloudflare.ini << EOF
dns_cloudflare_email = your-email@example.com
dns_cloudflare_api_key = your-cloudflare-global-api-key
EOF'
sudo chmod 600 /root/.secrets/cloudflare.ini

# Request wildcard certificate
sudo certbot certonly --dns-cloudflare \
    --dns-cloudflare-credentials /root/.secrets/cloudflare.ini \
    -d "eschool.app" -d "*.eschool.app" \
    --email admin@eschool.app --agree-tos --non-interactive

# Reload Nginx
sudo systemctl reload nginx
```

### 10. Configure Queue Worker + Scheduler (Supervisor)

```bash
sudo apt install -y supervisor
sudo cp deploy/supervisor.conf /etc/supervisor/conf.d/eschool.conf
# Edit paths in the config file
sudo nano /etc/supervisor/conf.d/eschool.conf
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start eschool-queue:*
```

### 11. License Activation

1. Buka `https://admin.eschool.app/__pair` di browser
2. Masukkan license key dari `whitelabel.co.id`
3. Masukkan email buyer (yang dipakai saat beli)
4. Klik "Aktifkan License"
5. Sistem akan verifikasi dengan license server
6. Jika berhasil → lock file tersimpan → sekolah bisa diakses

---

## Docker Deployment

eSchool SaaS includes a complete `docker-compose.yml` for containerized deployment.

```bash
# 1. Clone and setup
cd /opt/eschool
cp .env.example .env
nano .env

# 2. SSL Wildcard (run once)
certbot certonly --dns-cloudflare \
    --dns-cloudflare-credentials /root/.secrets/cloudflare.ini \
    -d "eschool.app" -d "*.eschool.app" \
    --email admin@eschool.app --agree-tos

# 3. Build and start all services
docker compose up -d --build

# 4. Setup Laravel inside container
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan storage:link
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# 5. Check status
docker compose ps
docker compose exec app php artisan license:status
```

### Docker Services

| Service | Container | Port |
|---------|-----------|------|
| app | eschool-app | PHP-FPM 9000 (internal) |
| nginx | eschool-nginx | 80, 443 |
| mysql | eschool-mysql | 3306 |
| redis | eschool-redis | 6379 (internal) |
| worker | eschool-worker | - |
| scheduler | eschool-scheduler | - |
| soketi | eschool-soketi | 6001 |

### Updating (Docker)

```bash
cd /opt/eschool
git pull origin main
docker compose up -d --build app
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose restart worker scheduler
```

---

## Nginx Config Reference

See `deploy/nginx.conf` for the complete production Nginx configuration with:
- Wildcard subdomain support (`*.eschool.app`)
- PHP-FPM upstream (standalone) or Docker container forwarding
- Static asset caching with `Cache-Control: public, immutable`
- Security headers (HSTS, X-Frame-Options, X-Content-Type-Options)
- Gzip compression
- Rate limiting for login routes
- robots.txt location block
- WebSocket proxy for Reverb/Soketi (`/app/` location)

---

## Supervisor Config

See `deploy/supervisor.conf` for the production supervisor configuration with 3 programs:

| Program | Command | Purpose |
|---------|---------|---------|
| eschool-queue | `php artisan queue:work --sleep=3 --tries=3 --max-time=3600` | Process background jobs |
| eschool-scheduler | `php artisan schedule:work` | Run scheduled tasks |
| eschool-reverb | `php artisan reverb:start` | WebSocket server for real-time |

---

## Backup Strategy

### Database Backup (via Scheduler)

The application includes a `BackupDatabase` command that runs daily at 02:00.
Configure in `.env`:

```
BACKUP_DISK=s3
BACKUP_S3_BUCKET=eschool-backups
BACKUP_RETENTION_DAYS=30
BACKUP_ENCRYPTION_PASSWORD=<strong-encryption-password>
```

### Manual Backup

```bash
# MySQL dump
mysqldump -u eschool -p eschool_saas > backup_$(date +%Y%m%d_%H%M%S).sql

# Files backup (uploads, assets)
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public/

# Full backup script recommendation — run via cron:
# 0 2 * * * /opt/eschool/scripts/backup.sh >> /var/log/eschool-backup.log 2>&1
```

---

## Cron Jobs

For non-Docker deployments, add to crontab (`sudo crontab -u www-data -e`):

```
* * * * * cd /var/www/eschool && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Commands

| Command | Schedule | Purpose |
|---------|----------|---------|
| `BackupDatabase` | Daily 02:00 | Database backup + upload to S3 |
| `SendPendingNotifications` | Every 5 minutes | Dispatch queued notifications (email/FCM/SMS) |
| `SendReminderNotifications` | Daily 08:00 | Reminder for upcoming events, fee due dates |
| `IndexNowSubmit` | Daily 02:45 | Submit new/changed URLs to search engines |
| `EscalateOverdueFees` | Every hour | Auto-escalate overdue fee invoices |

Verify with: `php artisan schedule:list`

---

## First Login

After seeding, use demo accounts:

| Email | Role | Password |
|-------|------|----------|
| `admin@sman1demo.sch.id` | School Admin | `password` |
| `super@eschool.app` | Super Admin | `password` |

Admin portal: `https://admin.eschool.app/admin/login`

**PENTING:** Change all demo passwords before go-live.

---

## Monitoring Tips

### Logs

```bash
# Laravel logs
tail -f /var/www/eschool/storage/logs/laravel.log

# Nginx access/error
tail -f /var/log/nginx/eschool-access.log
tail -f /var/log/nginx/eschool-error.log

# Supervisor worker logs
tail -f /var/www/eschool/storage/logs/worker.log

# Docker logs (if using Docker)
docker compose logs -f app
docker compose logs -f worker
```

### Queue Monitor

```bash
# Check queue stats
php artisan queue:monitor redis:default

# List failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Health Check Endpoints

```
GET /api/v1/health          → { "status": "ok", "timestamp": "..." }
GET /__pair/health          → License pairing check
```

### System Resources

```bash
# Check disk usage
df -h

# Check memory
free -h

# Check running services
systemctl status nginx php8.3-fpm mysql redis-server supervisor

# Docker containers
docker compose ps
docker stats
```

---

## Domain Setup (Post-Deploy)

After deployment, update:
- `APP_URL` in `.env`
- Canonical link in marketing landing page
- Sitemap domain (auto-generated from `APP_URL`)
- OG meta tags in public pages
- Submit `https://admin.eschool.app/sitemap.xml` to Google Search Console
- Configure IndexNow API key for auto-indexing

---

## Common Issues

| Issue | Solution |
|-------|----------|
| 403 Forbidden | Check storage permissions: `chmod -R 775 storage` |
| 500 after deploy | Clear cache: `php artisan optimize:clear` |
| Queue not processing | Check supervisor: `supervisorctl status eschool-queue:*` |
| License expired | Re-pair via `/__pair` |
| Missing assets | Rebuild: `npm run build` |
| DB connection failed | Verify `.env` credentials |
| School subdomain 404 | Check wildcard DNS `*.eschool.app` points to server |
| Reverb not connecting | Check firewall allows port 8080, verify SSL config |

---

## Quick Commands Reference

```bash
# Maintenance mode
php artisan down --message="Maintenance 5 menit" --retry=60
php artisan up

# Clear all cache
php artisan optimize:clear

# Rebuild cache (production)
php artisan optimize

# List scheduled commands
php artisan schedule:list

# Run scheduler manually
php artisan schedule:run

# Queue status
php artisan queue:monitor

# Restart queue workers
sudo supervisorctl restart eschool-queue:*

# View Laravel logs
tail -f storage/logs/laravel.log

# Test SMTP
php artisan tinker
>>> Mail::raw('test', fn($m) => $m->to('you@example.com'));
```

---

**Version:** eSchool SaaS v1.0  
**Detailed docs:** `docs/deployment/` directory  
**Docker compose:** `docker-compose.yml` at project root
