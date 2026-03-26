#!/bin/bash

# 1. Konfiguratsiya
PROJECT_DIR="/var/www/itcloud-cyber-glass"
REPO_URL="https://github.com/itcloud-uz/ITcloud-Cyber-Glass.git"

echo "🚀 ITcloud Cyber-Glass Deployment boshlanmoqda..."

# 2. Papka yaratish (agar yo'q bo'lsa)
if [ ! -d "$PROJECT_DIR" ]; then
    echo "📂 Yangi papka yaratilmoqda: $PROJECT_DIR"
    sudo mkdir -p "$PROJECT_DIR"
    sudo chown $USER:$USER "$PROJECT_DIR"
fi

# 3. GitHub'dan loyihani olish
cd "$PROJECT_DIR"
if [ ! -d ".git" ]; then
    echo "📥 Loyiha klonlanmoqda..."
    git clone "$REPO_URL" .
else
    echo "🔄 Yangi yangilanishlar (pull) olinmoqda..."
    git pull origin main
fi

# 4. PHP Dependency-larni o'rnatish
echo "📦 Composer paketlari o'rnatilmoqda..."
composer install --no-dev --optimize-autoloader

# 5. .env sozlash
if [ ! -f ".env" ]; then
    echo "⚙️ .env fayli yaratilmoqda. Iltimos, keyinchalik bazani ubu yerda sozlang!"
    cp .env.example .env
    php artisan key:generate
fi

# 6. Ruxsatlarni to'g'irlash
echo "🔒 Papka ruxsatlari sozlanmoqda..."
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache

# 7. Database Migration
echo "🗄️ Ma'lumotlar bazasi yangilanmoqda..."
php artisan migrate --force

# 8. Keshni tozalash
echo "🧹 Kesh tozalanmoqda..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✨ Muvaffaqiyatli! Loyiha $PROJECT_DIR manzili o'rnatildi."
