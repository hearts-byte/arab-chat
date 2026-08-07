FROM php:8.1-apache

# تثبيت المكتبات
RUN apt-get update && apt-get install -y \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev libzip-dev zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip mysqli pdo pdo_mysql

# تفعيل mod_rewrite لعمل ملفات .htaccess
RUN a2enmod rewrite

# ضبط المنفذ 8080 في Apache
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# نسخ الملفات
COPY . /var/www/html/

# صلاحيات كاملة للمجلدات
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 777 /var/www/html

EXPOSE 8080
