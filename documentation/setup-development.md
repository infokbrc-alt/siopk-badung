# Setup Development Lokal — SIOPK Badung

Development tanpa Docker. Pilih sesuai OS.

---

## Windows

### Opsi A: Laragon (Rekomendasi)

1. Download & install [Laragon](https://laragon.org/download/)
2. Buka Laragon → **Menu** → **Tools** → **Quick Add** → pilih PHP 8.4+
3. Aktifkan Redis: **Menu** → **Tools** → **Quick Add** → `redis`
4. Clone project ke `C:\laragon\www\siopk`

```bash
cd C:\laragon\www\siopk
copy .env.example .env
# Edit .env — sesuaikan DB_DATABASE, DB_USERNAME (root), DB_PASSWORD (kosong)

composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm ci
npm run dev
```

5. Buka `http://siopk.test` di browser

Konfigurasi `.env` Laragon:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siopk_badung
DB_USERNAME=root
DB_PASSWORD=

# Redis (jika diaktifkan di Laragon)
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Tanpa Redis (fallback):

```ini
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### Opsi B: XAMPP

1. Download & install [XAMPP](https://www.apachefriends.org/) (PHP 8.4+)
2. Aktifkan Redis: download [Redis for Windows](https://github.com/tporadowski/redis/releases), jalankan `redis-server.exe`
3. Clone project ke `C:\xampp\htdocs\siopk`

```bash
cd C:\xampp\htdocs\siopk
copy .env.example .env
# Edit .env:
#   DB_HOST=127.0.0.1
#   DB_DATABASE=siopk_badung
#   DB_USERNAME=root
#   DB_PASSWORD=
#   REDIS_HOST=127.0.0.1  (atau ganti SESSION_DRIVER=file jika tanpa Redis)

composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm ci
npm run dev
```

4. Buka `http://localhost/siopk/public`

> **Catatan**: XAMPP tidak menyertakan Redis. Install manual atau pakai fallback `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`.

---

## macOS

### Opsi A: Laravel Herd (Rekomendasi)

1. Download & install [Herd](https://herd.laravel.com/)
2. Herd sudah include PHP 8.4, Composer, Node.js, Redis
3. Clone project ke `~/Herd/siopk`

```bash
cd ~/Herd/siopk
cp .env.example .env

composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm ci && npm run dev
```

4. Buka `https://siopk.test` di browser

Konfigurasi `.env`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siopk_badung
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Herd menyediakan MySQL di port 3306 (default). Jika belum aktif: **Herd menu** → **Services** → aktifkan MySQL.

### Opsi B: Homebrew Manual

```bash
# Install semua service
brew install php@8.4 composer mysql redis node

# Start service
brew services start php@8.4
brew services start mysql
brew services start redis

# Clone dan setup
git clone https://github.com/infokbrc-alt/siopk-badung.git ~/siopk
cd ~/siopk
cp .env.example .env

composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm ci && npm run dev

# Jalankan built-in server
php artisan serve
```

Buka `http://localhost:8000`.

---

## Linux (Ubuntu/Debian)

```bash
# Add PHP PPA (Ubuntu default hanya menyediakan PHP 8.3)
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install dependencies
sudo apt install -y php8.4-cli php8.4-fpm php8.4-mysql \
  php8.4-mbstring php8.4-xml php8.4-gd php8.4-zip \
  php8.4-bcmath php8.4-curl php8.4-redis php8.4-opcache \
  mysql-server redis-server composer nodejs npm git

# Start service
sudo systemctl enable --now mysql redis-server

# Clone project
git clone https://github.com/infokbrc-alt/siopk-badung.git ~/siopk
cd ~/siopk
cp .env.example .env
# Edit .env — sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Setup database
sudo mysql -e "CREATE DATABASE siopk_badung CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
sudo mysql -e "CREATE USER 'siopk_user'@'localhost' IDENTIFIED BY 'password'"
sudo mysql -e "GRANT ALL PRIVILEGES ON siopk_badung.* TO 'siopk_user'@'localhost'"
sudo mysql -e "FLUSH PRIVILEGES"

# Install project
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm ci && npm run dev

# Jalankan server
php artisan serve
```

Buka `http://localhost:8000`.
