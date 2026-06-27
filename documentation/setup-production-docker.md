# Setup Production — Docker Compose — SIOPK Badung

---

## Struktur Docker

Proyek sudah menyertakan `docker-compose.yml` lengkap dengan 6 service:

| Service | Image | Port |
|---------|-------|------|
| `app` | PHP 8.4-FPM (custom build) | — |
| `nginx` | nginx:alpine | 80, 443 |
| `mysql` | mysql:8.4 | 3306 |
| `redis` | redis:8-alpine | 6379 |
| `worker` | PHP 8.4-FPM (queue worker) | — |
| `scheduler` | php:8.4-cli (cron) | — |

---

## Quick Start — Local

```bash
cp .env.example .env
# Edit .env — isi DB_PASSWORD dan API key

docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

Buka `http://localhost`.

---

## Deploy ke VPS dengan Docker

### 1. Clone dan Konfigurasi

```bash
git clone https://github.com/infokbrc-alt/siopk-badung.git /opt/siopk
cd /opt/siopk

cp .env.example .env
nano .env
```

Isi `.env` untuk production:

```ini
APP_NAME="SIOPK Badung"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://siopk.badungkab.go.id

DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=siopk_badung
DB_USERNAME=root
DB_PASSWORD=<password-kuat>

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis

AI_PROVIDER=deepseek
DEEPSEEK_API_KEY=sk-xxxxx
FONNTE_TOKEN=xxxxx
```

### 2. Build, Install, Migrate

```bash
docker compose up -d

docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link

# Build frontend (opsional — sudah dibundle di repo)
docker compose exec app npm ci
docker compose exec app npm run build

# Optimize
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## SSL dengan Let's Encrypt (Certbot)

```bash
apt install certbot -y

# Stop nginx sementara, generate cert
docker compose stop nginx
certbot certonly --standalone -d siopk.badungkab.go.id

# Mount cert ke container — update docker-compose.yml volume:
#   - ./certbot/conf:/etc/letsencrypt:ro

# Tambahkan server block SSL di docker/nginx/default.conf

# Restart nginx
docker compose up -d nginx
```

---

## Maintenance

```bash
# Update kode
git pull origin main
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose restart app worker scheduler

# Lihat log
docker compose logs -f --tail=100 app
docker compose logs -f worker

# Restart semua
docker compose restart

# Stop semua
docker compose down
```
