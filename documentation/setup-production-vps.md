# Setup Production — VPS Manual (LEMP Stack) — SIOPK Badung

Deployment ke VPS Ubuntu tanpa Docker. Menggunakan Nginx, PHP-FPM, MySQL, Redis.

---

## 1. Setup Server (Ubuntu 24.04)

```bash
apt update && apt upgrade -y

# Add PHP PPA (Ubuntu default hanya menyediakan PHP 8.3)
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y nginx mysql-server redis-server \
  php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring \
  php8.4-xml php8.4-gd php8.4-zip php8.4-bcmath \
  php8.4-curl php8.4-redis php8.4-opcache \
  composer git unzip

systemctl enable --now nginx mysql redis-server php8.4-fpm
```

---

## 2. Setup MySQL

```bash
mysql -u root

CREATE DATABASE siopk_badung CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'siopk_user'@'localhost' IDENTIFIED BY 'password_kuat';
GRANT ALL PRIVILEGES ON siopk_badung.* TO 'siopk_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 3. Clone & Install Project

```bash
git clone https://github.com/infokbrc-alt/siopk-badung.git /var/www/siopk
cd /var/www/siopk

cp .env.example .env
nano .env   # isi DB credentials, APP_URL, dll

composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link

# Build frontend
npm ci && npm run build

# Optimize cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permission
chown -R www-data:www-data /var/www/siopk
chmod -R 755 /var/www/siopk/storage
chmod -R 755 /var/www/siopk/bootstrap/cache
```

---

## 4. Konfigurasi Nginx

Buat file `/etc/nginx/sites-available/siopk`:

```nginx
server {
    listen 80;
    server_name siopk.badungkab.go.id;
    root /var/www/siopk/public;
    index index.php;

    charset utf-8;
    client_max_body_size 20M;

    gzip on;
    gzip_types text/css application/javascript image/svg+xml;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 60s;
    }

    location ~ /\. {
        deny all;
    }

    location ~* \.(env|log|md|yml|yaml)$ {
        deny all;
    }
}
```

```bash
ln -s /etc/nginx/sites-available/siopk /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## 5. SSL dengan Certbot

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d siopk.badungkab.go.id
```

---

## 6. Setup Queue Worker (systemd)

Buat file `/etc/systemd/system/siopk-worker.service`:

```ini
[Unit]
Description=SIOPK Queue Worker
After=network.target mysql.service redis.service

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/siopk
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

```bash
systemctl daemon-reload
systemctl enable --now siopk-worker
```

---

## 7. Setup Laravel Scheduler (cron)

```bash
crontab -e
```

Tambah:

```
* * * * * cd /var/www/siopk && php artisan schedule:run >> /dev/null 2>&1
```

---

## 8. Setup Firewall (UFW)

```bash
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```
