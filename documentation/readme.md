# Dokumentasi Setup — SIOPK Badung

## Daftar Isi

| File | Isi |
|------|-----|
| [setup-development.md](setup-development.md) | Development lokal tanpa Docker — Windows, macOS, Linux |
| [setup-production-docker.md](setup-production-docker.md) | Production dengan Docker Compose |
| [setup-production-vps.md](setup-production-vps.md) | Production VPS manual (LEMP stack) |
| [setup-production-cpanel.md](setup-production-cpanel.md) | Production cPanel / Shared Hosting |

---

## Prasyarat Umum

| Komponen | Minimal |
|----------|---------|
| PHP | 8.4+ |
| MySQL | 8.4+ |
| Redis | 8.0+ (session/cache/queue) |
| Composer | 2.x |
| Node.js | 24+ (untuk build frontend Vite) |
| Web Server | Nginx (rekomendasi) / Apache |

### Ekstensi PHP Wajib

```
bcmath, ctype, curl, dom, fileinfo, filter, gd,
mbstring, openssl, pdo, pdo_mysql, redis, session,
tokenizer, xml, zip
```

---

## Perintah Artisan Berguna

```bash
# Development
php artisan serve                        # Built-in dev server (port 8000)
php artisan serve --port=8080            # Custom port
php artisan optimize:clear               # Clear semua cache (dev)
npm run dev                              # Vite dev server (hot reload)
npm run build                            # Build production assets

# Database
php artisan migrate                      # Jalankan migration
php artisan migrate:fresh --seed         # Reset & seed ulang
php artisan db:seed                      # Jalankan seeder

# Queue
php artisan queue:work                   # Jalankan worker
php artisan queue:work --sleep=3 --tries=3  # Worker dengan opsi
php artisan queue:listen                 # Development — restart otomatis

# AI
php artisan siopk:test-ai                # Test koneksi AI
php artisan siopk:analisis-semua         # Batch analisis semua laporan
php artisan siopk:analisis-semua --force # Re-analisis semua

# Cache
php artisan config:cache                 # Cache config (production)
php artisan route:cache                  # Cache routes (production)
php artisan view:cache                   # Cache views (production)
php artisan optimize                     # Semua cache (production)

# Log
php artisan log:clear                    # Hapus log

# Health
php artisan about                        # Info environment
php artisan schedule:run                 # Jalankan scheduler manual
```

---

## Troubleshooting Umum

| Masalah | Solusi |
|---------|--------|
| `500 Internal Server Error` | Cek `storage/logs/laravel.log`, pastikan `.env` ada dan `APP_KEY` terisi |
| `could not find driver` (MySQL) | Pastikan `pdo_mysql` extension aktif (`php -m \| grep pdo`) |
| `No application encryption key` | Jalankan `php artisan key:generate` |
| `The stream or file ... could not be opened` | `chmod -R 755 storage bootstrap/cache` |
| `Redis::connect(): Connection refused` | Pastikan Redis service berjalan, atau ganti ke `SESSION_DRIVER=file` |
| `Vite manifest not found` | Jalankan `npm run build` atau `npm run dev` |
| `419 Page Expired` (CSRF) | Pastikan `SESSION_DOMAIN` di `.env` sesuai dengan URL akses |
| `Too few arguments to function` | Jalankan `composer install` — mungkin ada dependensi belum terinstall |
| `Specified key was too long` | Pastikan MySQL versi 5.7.7+ atau MariaDB 10.2.2+ |
| Nginx `502 Bad Gateway` | PHP-FPM tidak berjalan — cek `systemctl status php8.4-fpm` |
| Permission denied upload foto | `chown -R www-data:www-data storage` |
