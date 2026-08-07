FROM php:8.1-apache

# تثبيت الحزم والمكتبات المطلوبة (GD, ZIP, MySQLi, PDO)
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip mysqli pdo pdo_mysql

# تفعيل mod_rewrite لعمل ملف .htaccess
RUN a2enmod rewrite

# تغيير منفذ Apache المباشر عبر ملف الموانئ
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf
RUN sed -i 's/:80>/:8080>/g' /etc/apache2/sites-available/000-default.conf

# نسخ جميع ملفات السكريبت
COPY . /var/www/html/

# إعطاء ملكية وصلاحيات شاملة لمجلدات السكريبت وملف القاعدة
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html

EXPOSE 8080

# تشغيل Apache باستخدام الأيقونة المباشرة لتفادي خطأ MPM
CMD ["apache2ctl", "-D", "FOREGROUND"]
