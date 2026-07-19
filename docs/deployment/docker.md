# Deployment Guide — Docker + Nginx + Let's Encrypt

## Stack Production

```
┌──────────────────────────────────────────────────────────────┐
│  Server (Ubuntu 22.04 / VPS / Cloud)                         │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Docker Compose                                       │   │
│  │                                                       │   │
│  │  nginx ──── app (PHP-FPM 8.3)                        │   │
│  │             ├── mysql:8.0                             │   │
│  │             ├── redis:7-alpine                        │   │
│  │             ├── worker (queue:work)                   │   │
│  │             └── scheduler (artisan schedule:work)     │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                              │
│  SSL: Certbot (Let's Encrypt) — wildcard *.eschool.app       │
└──────────────────────────────────────────────────────────────┘
```

---

## docker-compose.yml

```yaml
version: '3.9'

services:

  app:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: eschool_app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - .:/var/www
      - ./storage:/var/www/storage
    environment:
      - PHP_MEMORY_LIMIT=256M
    networks:
      - eschool_net
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:1.25-alpine
    container_name: eschool_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - /etc/letsencrypt:/etc/letsencrypt:ro
    networks:
      - eschool_net
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    container_name: eschool_mysql
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf
    networks:
      - eschool_net
    ports:
      - "3306:3306"   # hanya untuk dev, tutup di production

  redis:
    image: redis:7-alpine
    container_name: eschool_redis
    restart: unless-stopped
    command: redis-server --appendonly yes --requirepass ${REDIS_PASSWORD}
    volumes:
      - redis_data:/data
    networks:
      - eschool_net

  worker:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: eschool_worker
    restart: unless-stopped
    working_dir: /var/www
    command: php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
    volumes:
      - .:/var/www
    networks:
      - eschool_net
    depends_on:
      - redis
      - mysql

  scheduler:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: eschool_scheduler
    restart: unless-stopped
    working_dir: /var/www
    command: php artisan schedule:work
    volumes:
      - .:/var/www
    networks:
      - eschool_net
    depends_on:
      - mysql
      - redis

volumes:
  mysql_data:
  redis_data:

networks:
  eschool_net:
    driver: bridge
```

---

## docker/php/Dockerfile

```dockerfile
FROM php:8.3-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    git curl libpng-dev libxml2-dev zip unzip \
    icu-dev oniguruma-dev libjpeg-turbo-dev \
    freetype-dev

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
        pdo_mysql mbstring exif pcntl bcmath gd \
        intl xml opcache

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
```

---

## docker/nginx/conf.d/eschool.conf

```nginx
# Redirect HTTP → HTTPS
server {
    listen 80;
    server_name *.eschool.app admin.eschool.app;
    return 301 https://$host$request_uri;
}

# School subdomain + Admin panel
server {
    listen 443 ssl http2;
    server_name *.eschool.app;

    ssl_certificate     /etc/letsencrypt/live/eschool.app/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/eschool.app/privkey.pem;

    root /var/www/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass   eschool_app:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include        fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Static assets cache
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    client_max_body_size 50M;
}
```

---

## docker/mysql/my.cnf

```ini
[mysqld]
character-set-server  = utf8mb4
collation-server      = utf8mb4_unicode_ci
default-storage-engine = InnoDB
innodb_buffer_pool_size = 256M
max_connections       = 200
slow_query_log        = 1
slow_query_log_file   = /var/lib/mysql/slow.log
long_query_time       = 2
```

---

## .env Production Template

```env
APP_NAME="eSchool SaaS"
APP_ENV=production
APP_KEY=                              # php artisan key:generate
APP_DEBUG=false
APP_URL=https://admin.eschool.app

# License
LICENSE_KEY=XXXXX-XXXXX-XXXXX-XXXXX
LICENSE_SECRET=secret-dari-whitelabel
LICENSE_CHECK=true

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=eschool_saas
DB_USERNAME=eschool
DB_PASSWORD=strong-password-here

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=strong-redis-password
REDIS_PORT=6379

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@eschool.app
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@eschool.app
MAIL_FROM_NAME="eSchool SaaS"

# Firebase
FIREBASE_SERVER_KEY=

# Pusher / Soketi
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=https

# AWS S3 (atau S3-compatible)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=eschool-storage
AWS_URL=

# Multi-tenancy
SANCTUM_STATEFUL_DOMAINS=*.eschool.app,admin.eschool.app
SESSION_DOMAIN=.eschool.app
```

---

## Deploy Commands

```bash
# 1. Clone repo
git clone https://github.com/yourorg/eschool-saas.git /opt/eschool
cd /opt/eschool

# 2. Setup .env
cp .env.example .env
nano .env  # isi semua value

# 3. SSL Wildcard (sekali saja)
certbot certonly --dns-cloudflare \
  --dns-cloudflare-credentials /root/.secrets/cloudflare.ini \
  -d "eschool.app" -d "*.eschool.app" \
  --email admin@eschool.app --agree-tos

# 4. Build dan start
docker compose up -d --build

# 5. Setup Laravel
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
docker compose exec app php artisan storage:link
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# 6. Aktivasi license
docker compose exec app php artisan license:activate XXXXX-XXXXX yourdomain.com

# 7. Cek status
docker compose exec app php artisan license:status
docker compose ps
```

---

## Update / Deploy Baru

```bash
cd /opt/eschool

# Pull kode baru
git pull origin main

# Rebuild hanya jika Dockerfile berubah
docker compose up -d --build app

# Jalankan migration + cache refresh
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Restart worker agar kode baru dipakai
docker compose restart worker scheduler
```

---

## Health Check Endpoints

```
GET /api/v1/health          → { "status": "ok", "timestamp": "..." }
GET /api/v1/license/status  → { "valid": true }
```

---

## Monitoring

```bash
# Lihat log real-time
docker compose logs -f app
docker compose logs -f worker

# Queue monitor
docker compose exec app php artisan queue:monitor redis:default

# Artisan tinker
docker compose exec app php artisan tinker
```
