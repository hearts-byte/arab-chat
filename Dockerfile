FROM php:8.1-apache

# تثبيت امتدادات قواعد البيانات
RUN docker-php-ext-install mysqli pdo pdo_mysql

# تفعيل mod_rewrite
RUN a2enmod rewrite

# ضبط سيرفر Apache ليستمع على المنفذ 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# نسخ الملفات
COPY . /var/www/html/

# ضبط الصلاحيات
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080
