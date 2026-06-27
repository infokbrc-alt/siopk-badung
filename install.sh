#!/bin/bash
# ============================================================
# SIOPK Badung — Script Instalasi Otomatis
# Jalankan di root folder project: bash install.sh
# ============================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo ""
echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}   SIOPK Badung — Instalasi Otomatis           ${NC}"
echo -e "${BLUE}   Sistem Informasi OPK Kabupaten Badung       ${NC}"
echo -e "${BLUE}================================================${NC}"
echo ""

# 1. Copy .env
echo -e "${YELLOW}[1/7] Menyiapkan file .env...${NC}"
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo -e "${GREEN}      ✓ .env berhasil dibuat${NC}"
else
    echo -e "${GREEN}      ✓ .env sudah ada, dilewati${NC}"
fi

# 2. Install dependencies
echo -e "${YELLOW}[2/7] Menginstall dependencies (composer)...${NC}"
composer install --no-dev --optimize-autoloader
if [ $? -ne 0 ]; then
    echo -e "${RED}      ✗ Composer gagal. Pastikan Composer sudah terinstall.${NC}"
    exit 1
fi
echo -e "${GREEN}      ✓ Dependencies terinstall${NC}"

# 3. Set folder permissions
echo -e "${YELLOW}[3/8] Mengatur permission folder...${NC}"
chmod -R 775 storage bootstrap/cache 2>/dev/null
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}      ✓ Permission diatur (www-data)${NC}"
else
    echo -e "${YELLOW}      ⚠ chown gagal — Anda mungkin bukan root. Lanjut...${NC}"
    chmod -R 777 storage bootstrap/cache 2>/dev/null
    echo -e "${GREEN}      ✓ Permission diatur (fallback 777)${NC}"
fi

# 4. Generate APP_KEY
echo -e "${YELLOW}[4/8] Generate application key...${NC}"
php artisan key:generate
echo -e "${GREEN}      ✓ App key berhasil digenerate${NC}"

# 5. Buat database
echo -e "${YELLOW}[5/8] Membuat database MySQL...${NC}"
echo ""
echo -e "      ${BLUE}Pastikan MySQL XAMPP sudah berjalan!${NC}"
echo -e "      Database yang akan dibuat: ${YELLOW}siopk_badung${NC}"
echo ""
mysql -u root -e "CREATE DATABASE IF NOT EXISTS siopk_badung CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}      ✓ Database siopk_badung berhasil dibuat${NC}"
else
    echo -e "${YELLOW}      ⚠ Gagal membuat database otomatis.${NC}"
    echo -e "      Buat manual via phpMyAdmin: CREATE DATABASE siopk_badung;"
fi

# 6. Migrate & Seed
echo -e "${YELLOW}[6/8] Menjalankan migrasi & seeder...${NC}"
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo -e "${RED}      ✗ Migrasi gagal. Periksa koneksi database di .env${NC}"
    exit 1
fi
php artisan db:seed --force
echo -e "${GREEN}      ✓ Migrasi & data awal berhasil${NC}"

# 7. Storage link
echo -e "${YELLOW}[7/8] Membuat storage symlink...${NC}"
php artisan storage:link
echo -e "${GREEN}      ✓ Storage symlink berhasil${NC}"

# 7. Optimasi cache
echo -e "${YELLOW}[7/7] Optimasi cache...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}      ✓ Cache dioptimasi${NC}"

echo ""
echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}   INSTALASI SELESAI!                          ${NC}"
echo -e "${GREEN}================================================${NC}"
echo ""
echo -e "  URL Sistem : ${BLUE}http://localhost/siopk-badung/public${NC}"
echo -e "  Portal Publik : ${BLUE}http://localhost/siopk-badung/public/lapor${NC}"
echo ""
echo -e "  ${YELLOW}Akun Default:${NC}"
echo -e "  ┌─────────────────────────────────────────────────┐"
echo -e "  │ Superadmin : superadmin@siopk-badung.id         │"
echo -e "  │ Password   : SiOPK@2025!                        │"
echo -e "  ├─────────────────────────────────────────────────┤"
echo -e "  │ Admin      : admin@siopk-badung.id              │"
echo -e "  │ Password   : Admin@2025                         │"
echo -e "  ├─────────────────────────────────────────────────┤"
echo -e "  │ Verifikator: verifikator@siopk-badung.id        │"
echo -e "  │ Password   : Verif@2025                         │"
echo -e "  └─────────────────────────────────────────────────┘"
echo ""
echo -e "  ${RED}⚠ Ganti semua password sebelum digunakan di produksi!${NC}"
echo ""
