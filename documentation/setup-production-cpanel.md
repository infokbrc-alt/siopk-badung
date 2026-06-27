# Setup Production — cPanel / Shared Hosting — SIOPK Badung

---

## Prasyarat Hosting

| Fitur | Wajib |
|-------|-------|
| PHP 8.4+ | ✅ |
| MySQL 8.4+ / MariaDB 10.5+ | ✅ |
| Redis | ❌ (fallback: file/database) |
| SSH / Terminal | Direkomendasikan |
| Node.js | ❌ (build frontend di local) |

---

## 1. Persiapan Lokal

Build frontend di local sebelum upload — hasil build ada di `/public/build/`:

```bash
npm ci
npm run build
```

---

## 2. Upload ke cPanel

```
├── app/
├── bootstrap/
├── config/
├── database/
├── public/            ← Document Root
│   ├── index.php
│   ├── .htaccess
│   └── build/         ← hasil build Vite
├── resources/
├── routes/
├── storage/
├── vendor/
├── composer.json
├── .env
└── artisan
```

**Document Root** harus mengarah ke `public/`, bukan root project.

Jika tidak bisa mengubah Document Root, letakkan seluruh project di atas `public_html/` dan symlink folder public:

```
/home/user/
├── siopk/             ← project root
│   └── public/
└── public_html/       ← Apache Document Root
    └── symlink → ../siopk/public/
```

---

## 3. Konfigurasi .env untuk cPanel

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_db
DB_USERNAME=nama_user
DB_PASSWORD=password_db

# Tanpa Redis — fallback ke file
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=database

FILESYSTEM_DISK=public
```

---

## 4. Setup via SSH / Terminal

```bash
cd /home/user/siopk
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 5. Setup Queue Worker

cPanel biasanya tidak mengizinkan proses background. Pilihan:

### A. Cron Job (tiap menit)

```bash
# Tambah di cPanel → Cron Jobs
* * * * * /usr/local/bin/php /home/user/siopk/artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

### B. Queue via sync (darurat)

Gunakan `QUEUE_CONNECTION=sync` — tapi ini blocking dan **tidak disarankan** untuk production.

---

## 6. .htaccess (Apache)

`public/.htaccess` sudah disediakan Laravel. Pastikan `mod_rewrite` aktif.

---

## 7. Batasan Shared Hosting

| Fitur | Status |
|-------|--------|
| Redis | ❌ Tidak tersedia di shared hosting |
| Queue worker long-running | ❌ Gunakan cron |
| AI analysis async | ✅ Via cron queue worker |
| WhatsApp notification | ✅ Jika cron queue jalan |
| File upload | ✅ Maks upload sesuai limit hosting |
