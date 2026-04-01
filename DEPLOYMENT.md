# ITcloud Cyber-Glass Deployment Guide 🚀

Ushbu loyiha Laravel (Backend), Vite (Frontend) va Python FastAPI (FaceID Service) dan tashkil topgan. Serverga joylash uchun quyidagi ko'rsatmalarga amal qiling.

## 1. Talablar
- PHP 8.3+
- Node.js 18+
- Python 3.10+
- SQLite (yoki MySQL/PostgreSQL)
- Supervisor (Background jarayonlar uchun)
- Nginx

## 2. Loyihani o'rnatish

```bash
# Repo'ni klonlash
git clone https://github.com/itcloud-uz/ITcloud-Cyber-Glass.git
cd ITcloud-Cyber-Glass

# PHP Dependency'larni o'rnatish
composer install --no-dev --optimize-autoloader

# Node.js Dependency'larni o'rnatish va build qilish
npm install
npm run build

# .env sozlash
cp .env.example .env
php artisan key:generate
# .env ichida bazani va API keylarni sozlang

# Migratsiyalarni ishga tushirish
php artisan migrate --force
```

## 3. Python FaceID Serviceni sozlash
FaceID xizmati alohida portda (8001) ishlashi kerak.

```bash
cd faceid-service
pip install -r requirements.txt
# Serviceni fon rejimida ishga tushirish (Supervisor tavsiya etiladi)
python main.py
```

## 4. Supervisor Configuration
Background job'lar (AI loglar, emaillar) va Python service uchun Supervisor ishlating.

### Laravel Queue (/etc/supervisor/conf.d/itcloud-worker.conf)
```ini
[program:itcloud-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/itcloud-cyber-glass/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/itcloud-cyber-glass/storage/logs/worker.log
```

### FaceID Service (/etc/supervisor/conf.d/itcloud-faceid.conf)
```ini
[program:itcloud-faceid]
command=python3 /var/www/itcloud-cyber-glass/faceid-service/main.py
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/itcloud-cyber-glass/storage/logs/faceid.log
```

## 5. Nginx Configuration
Asosiy ilova va FaceID API uchun proxy o'rnating.

```nginx
server {
    listen 80;
    server_name portal.itcloud.uz;
    root /var/www/itcloud-cyber-glass/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # API Proxy for FaceID
    location /api/v1/verify-face {
        proxy_pass http://127.0.0.1:8001;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
    }
}
```

## 6. Supervisor AI
Tizimga yangi qo'shilgan **Supervisor AI** har kuni/soatda tizim holatini tahlil qiladi. Uni Dashboard'ning "Analytics" bo'limida ko'rishingiz mumkin.
