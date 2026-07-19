# CI/CD Pipeline — GitHub Actions

## Pipeline Overview

```
Push ke main branch
  └── GitHub Actions
        ├── 1. Lint (PHP CS Fixer)
        ├── 2. Test (PHPUnit — semua Feature tests)
        ├── 3. Build Docker image
        ├── 4. Push ke registry
        └── 5. Deploy ke server (SSH)
```

---

## `.github/workflows/deploy.yml`

```yaml
name: Deploy eSchool SaaS

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

env:
  PHP_VERSION: '8.3'
  NODE_VERSION: '20'

jobs:

  # ─── JOB 1: Tests ──────────────────────────────────────────
  test:
    name: PHPUnit Tests
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: eschool_test
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
        ports:
          - 3306:3306

      redis:
        image: redis:7-alpine
        options: >-
          --health-cmd="redis-cli ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
        ports:
          - 6379:6379

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          extensions: pdo_mysql, redis, gd, bcmath, intl
          coverage: none

      - name: Cache Composer
        uses: actions/cache@v3
        with:
          path: vendor
          key: composer-${{ hashFiles('composer.lock') }}

      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist --optimize-autoloader

      - name: Copy .env
        run: |
          cp .env.example .env.testing
          php artisan key:generate --env=testing

      - name: Configure test env
        run: |
          echo "DB_HOST=127.0.0.1" >> .env.testing
          echo "DB_DATABASE=eschool_test" >> .env.testing
          echo "DB_USERNAME=root" >> .env.testing
          echo "DB_PASSWORD=root" >> .env.testing
          echo "REDIS_HOST=127.0.0.1" >> .env.testing
          echo "LICENSE_CHECK=false" >> .env.testing

      - name: Run migrations
        run: php artisan migrate --env=testing --force

      - name: Run tests
        run: php artisan test --env=testing --parallel

  # ─── JOB 2: Deploy (hanya di push ke main) ─────────────────
  deploy:
    name: Deploy to Production
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main' && github.event_name == 'push'

    steps:
      - uses: actions/checkout@v4

      - name: Deploy via SSH
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SERVER_SSH_KEY }}
          script: |
            cd /opt/eschool
            git pull origin main
            docker compose exec -T app composer install --no-dev --optimize-autoloader
            docker compose exec -T app php artisan migrate --force
            docker compose exec -T app php artisan config:cache
            docker compose exec -T app php artisan route:cache
            docker compose exec -T app php artisan view:cache
            docker compose restart worker scheduler
            echo "✅ Deploy complete — $(date)"

      - name: Notify Slack (optional)
        if: always()
        uses: 8398a7/action-slack@v3
        with:
          status: ${{ job.status }}
          text: "eSchool deploy: ${{ job.status }}"
        env:
          SLACK_WEBHOOK_URL: ${{ secrets.SLACK_WEBHOOK }}
```

---

## GitHub Secrets yang Diperlukan

| Secret | Keterangan |
|--------|------------|
| `SERVER_HOST` | IP atau hostname server production |
| `SERVER_USER` | SSH username (misal: `ubuntu`) |
| `SERVER_SSH_KEY` | Private SSH key untuk akses server |
| `SLACK_WEBHOOK` | (opsional) URL webhook Slack |

---

## Pull Request Workflow

```yaml
# .github/workflows/pr-check.yml
name: PR Check

on:
  pull_request:
    branches: [ main, develop ]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
      - run: composer install --no-interaction
      - name: PHP CS Fixer
        run: vendor/bin/php-cs-fixer fix --dry-run --diff

  test:
    # sama dengan job test di atas
    uses: ./.github/workflows/deploy.yml
```
