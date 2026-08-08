# Nginx Configuration Reference

## Wildcard Subdomain Setup

Sikad Pro menggunakan wildcard subdomain `*.sikadpro.app`. Setiap sekolah mendapat
subdomain sendiri (`smkn1.sikadpro.app`), admin platform di `admin.sikadpro.app`.

### DNS Setup (Cloudflare atau DNS provider)

```
Type    Name      Value           TTL
A       @         YOUR_SERVER_IP  Auto
A       *         YOUR_SERVER_IP  Auto
A       admin     YOUR_SERVER_IP  Auto
```

---

## SSL Wildcard dengan Certbot + Cloudflare DNS

```bash
# Install certbot dan plugin Cloudflare
apt install certbot python3-certbot-dns-cloudflare

# Buat file credential Cloudflare
mkdir /root/.secrets
cat > /root/.secrets/cloudflare.ini << 'EOF'
dns_cloudflare_email = your-email@example.com
dns_cloudflare_api_key = your-cloudflare-global-api-key
EOF
chmod 600 /root/.secrets/cloudflare.ini

# Request wildcard cert
certbot certonly \
  --dns-cloudflare \
  --dns-cloudflare-credentials /root/.secrets/cloudflare.ini \
  -d "sikadpro.app" \
  -d "*.sikadpro.app" \
  --email admin@sikadpro.app \
  --agree-tos \
  --non-interactive

# Auto-renew (sudah otomatis via systemd timer)
certbot renew --dry-run
```

---

## Multi-Domain Routing Logic

```
Request: smkn1.sikadpro.app/dashboard
  → Nginx forward ke Laravel (PHP-FPM)
  → ResolveSchool middleware menangkap subdomain "smkn1"
  → School::where('subdomain', 'smkn1')->firstOrFail()
  → Set app('current_school') dan config('app.school_id')
  → EnsureSchoolAccess middleware verify user.school_id

Request: admin.sikadpro.app/schools
  → Nginx forward ke Laravel (PHP-FPM)
  → Route prefix: /super/* dengan middleware super_admin
  → SchoolScope TIDAK berlaku (super_admin bypass)
```

---

## Nginx Config — Production (Standalone, tanpa Docker)

```nginx
# /etc/nginx/sites-available/sikadpro
server {
    listen 80;
    server_name sikadpro.app *.sikadpro.app;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name sikadpro.app *.sikadpro.app;

    ssl_certificate     /etc/letsencrypt/live/sikadpro.app/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sikadpro.app/privkey.pem;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         HIGH:!aNULL:!MD5;
    ssl_session_cache   shared:SSL:10m;
    ssl_session_timeout 10m;

    root /opt/sikadpro/public;
    index index.php;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_read_timeout 300;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    # Static files cache
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # WebSocket untuk Soketi / Pusher
    location /app/ {
        proxy_pass http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_cache_bypass $http_upgrade;
    }

    client_max_body_size 50M;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
}

# Aktifkan
ln -s /etc/nginx/sites-available/sikadpro /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```
