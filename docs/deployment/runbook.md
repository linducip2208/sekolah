# Deployment Runbook — eSchool SaaS

Production deployment & operations playbook.

## Initial Deployment

### Prerequisites
- Docker 24+ & Docker Compose v2
- Domain dengan wildcard DNS pointing ke server (`*.eschool.app`)
- SSL cert (Let's Encrypt wildcard atau Cloudflare)
- SMTP credentials (untuk email)
- (Opsional) Firebase project untuk FCM push

### Steps

```bash
# 1. Clone & configure
git clone <repo> /opt/eschool
cd /opt/eschool
cp .env.example .env
# Edit .env: APP_KEY, DB_*, REDIS_*, SMTP_*, FCM_*, etc.

# 2. Build & start
docker compose build
docker compose up -d

# 3. Run initial migrations + seed
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --class=PlanSeeder
docker compose exec app php artisan db:seed --class=RolePermissionSeeder
docker compose exec app php artisan db:seed --class=DemoSchoolSeeder
docker compose exec app php artisan db:seed --class=Phase8To11DemoSeeder

# 4. Optimize
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan event:cache

# 5. Storage links
docker compose exec app php artisan storage:link

# 6. Submit sitemap
curl -X GET "https://www.google.com/ping?sitemap=https://eschool.app/sitemap.xml"
```

## Post-Deployment Checklist

### Phase 1-7 (Foundation)
- [ ] Super admin user created (`/super/login`)
- [ ] Plans seeded (Free, Basic, Pro)
- [ ] License config valid (`config/license.php`)
- [ ] At least one school registered
- [ ] Branding configured (logo, colors)

### Phase 8-11 (Best-in-class features)
- [ ] **Payment gateway:** at least 1 provider added per school via admin UI
- [ ] **AI assistant:** at least 1 AI provider added if AI features enabled
- [ ] **Live class:** video provider configured if remote classes used
- [ ] **PPDB:** active period created for current academic year
- [ ] **Dapodik:** NPSN configured
- [ ] **Branding:** all 8 logo slots populated

## Daily Operations

### Backup
```bash
# Auto-runs daily 02:00 via scheduler
# Manual:
docker compose exec app php artisan eschool:backup
```

### Queue Worker
Monitor with `docker compose logs -f worker`. Restart if memory grows:
```bash
docker compose restart worker
```

### Scheduler
Single-instance via `eschool-scheduler` container. Monitor:
```bash
docker compose logs -f scheduler
```

## Scaling

### Vertical (single server)
- Increase `worker` replicas: `docker compose up -d --scale worker=4`
- Increase MySQL `max-connections` in `docker-compose.yml`

### Horizontal (multi-server)
1. Move MySQL to managed RDS / Cloud SQL
2. Move Redis to managed (ElastiCache / Memorystore)
3. Move file storage to S3 (set `FILESYSTEM_DISK=s3` in .env)
4. Run multiple `app` instances behind load balancer
5. Soketi (WebSocket) → managed Pusher OR clustered Soketi

## Health Checks

```bash
# Quick
curl https://eschool.app/up

# Deep
curl https://eschool.app/api/v1/health/deep
```

Returns JSON with status of: DB, Redis, S3, queue.

## Logs

```bash
# Application
docker compose logs -f app
tail -f storage/logs/laravel.log

# Queue worker
docker compose logs -f worker

# Slow queries
docker compose exec mysql mysql -e "SHOW FULL PROCESSLIST;"
```

## Common Operations

### Add new school
Use super admin panel `/super/schools/create` OR:
```bash
docker compose exec app php artisan tinker
> School::create([
    'name' => 'SMK Test',
    'subdomain' => 'smk-test',
    'plan_id' => Plan::first()->id,
    'plan_expires_at' => now()->addYear(),
    'is_active' => true,
  ]);
```

### Reset cache after config change
```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
```

### Deploy update
```bash
cd /opt/eschool
git pull
docker compose build app worker scheduler
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache route:cache view:cache
docker compose exec app php artisan queue:restart
```

## Disaster Recovery

### Database restore
```bash
# Stop app + worker
docker compose stop app worker

# Restore from backup
docker compose exec -T mysql mysql -u root -p < backup-2026-01-15.sql

# Restart
docker compose up -d
```

### Storage restore
```bash
aws s3 sync s3://eschool-backups/storage/ ./storage/
```

## Monitoring Recommendations

- **Uptime:** UptimeRobot / Pingdom hitting `/up` every 1 min
- **Error tracking:** Sentry (`SENTRY_LARAVEL_DSN` in .env)
- **APM:** New Relic / Datadog APM
- **Log aggregation:** Loki / CloudWatch Logs
- **Metrics:** Grafana dashboard pulling from MySQL slow query log + Redis stats

## Security Hardening

- [ ] Firewall: only 80, 443, 22 open
- [ ] SSH key-only (no password auth)
- [ ] Rate limit: nginx 60r/m per IP for `/api/`
- [ ] WAF: Cloudflare in front
- [ ] Database: only accessible from app subnet
- [ ] Encrypted credentials: confirm `APP_KEY` set, payment + AI keys encrypted at rest
- [ ] Backup encryption: `BACKUP_ENCRYPTION_PASSWORD` set
- [ ] License protection: `LICENSE_VERIFY_URL` reachable from production
