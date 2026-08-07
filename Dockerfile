FROM php:8.1-apache

# تثبيت مكتبات الصور والضغط المفقودة
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip mysqli pdo pdo_mysql

# تفعيل mod_rewrite لدعم ملفات .htaccess
RUN a2enmod rewrite

# إصلاح مشكلة MPM نهائياً وتعطيل event لتشغيل prefork فقط
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork || true

# تغيير المنفذ إلى 8080 في إعدادات Apache
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf

# نسخ الملفات وضبط الصلاحيات
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html/system /var/www/html/avatar /var/www/html/cover /var/www/html/upload || true

EXPOSE 8080
