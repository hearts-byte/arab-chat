FROM php:8.1-cli

# تثبيت الحزم والمكتبات المطلوبة لنظام النظام (GD, ZIP, MySQLi)
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip mysqli pdo pdo_mysql

# تحديد مجلد العمل
WORKDIR /var/www/html

# نسخ جميع ملفات السكريبت
COPY . .

# ضبط صلاحيات مجلدات الصور والرفع لتكون قابلة للكتابة
RUN chmod -R 777 avatar cover upload system/database.php || true

# فتح المنفذ 8080
EXPOSE 8080

# تشغيل خادم PHP المدمج
CMD ["php", "-S", "0.0.0.0:8080"]
