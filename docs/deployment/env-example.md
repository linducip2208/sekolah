# .env Reference — Sikad Pro

## .env.example (commit ke repo)

```env
APP_NAME="Sikad Pro"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

# License (whitelabel.co.id)
LICENSE_KEY=
LICENSE_SECRET=
LICENSE_CHECK=false
LICENSE_PRODUCT=sikadpro
LICENSE_API_URL=https://whitelabel.co.id/api/license

# Database (MySQL 8)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikadpro_saas
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_DOMAIN=.sikadpro.app

# Queue
QUEUE_CONNECTION=redis
QUEUE_FAILED_DRIVER=database-uuids

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis

# Broadcasting (Real-time Chat)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=ap1
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Mail
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@sikadpro.app
MAIL_FROM_NAME="${APP_NAME}"

# Storage (S3-compatible)
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=sikadpro-storage
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Firebase FCM
FIREBASE_SERVER_KEY=

# SMS Gateway (opsional)
SMS_GATEWAY_URL=
SMS_GATEWAY_KEY=
SMS_GATEWAY_SENDER=Sikad Pro

# Multi-tenancy
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,*.sikadpro.app
```

---

## Minimal Local Dev Setup

```bash
# 1. Copy .env
cp .env.example .env

# 2. Generate key
php artisan key:generate

# 3. Setup MySQL lokal
mysql -u root -e "CREATE DATABASE sikadpro_saas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Update .env
DB_DATABASE=sikadpro_saas
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
LICENSE_CHECK=false   # skip license check di local

# 5. Migrate + seed
php artisan migrate --seed

# 6. Start
php artisan serve
php artisan queue:work   # terminal terpisah
```
