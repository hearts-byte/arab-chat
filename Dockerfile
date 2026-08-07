FROM php:8.1-apache

# تثبيت مكتبات النظام اللي يحتاجها GD و ZIP و باقي الإضافات
RUN apt-get update && apt-get install -y \
    libgd-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip curl mbstring opcache pdo_mysql mysqli \
    && rm -rf /var/lib/apt/lists/*

# تفعيل mod_rewrite حتى يشتغل ملف htaccess
RUN a2enmod rewrite

# السماح بـ .htaccess (AllowOverride All)
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# نسخ ملفات المشروع
COPY . /var/www/html/

# إعداد الصلاحيات للمجلدات المطلوبة في صفحة التثبيت
RUN mkdir -p /var/www/html/avatar /var/www/html/cover /var/www/html/upload \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/avatar /var/www/html/cover /var/www/html/upload

# ملف تشغيل يهيّئ منفذ Railway الديناميكي
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
