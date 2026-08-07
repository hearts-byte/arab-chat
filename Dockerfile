FROM php:8.1-apache

# تثبيت امتدادات قواعد البيانات وتفعيل mod_rewrite
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

# نسخ الملفات وإعطاء الصلاحيات
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# تغيير منفذ الاستماع إلى 8080 بشكل آمن
ENV PORT 8080
EXPOSE 8080
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:8080>/g' /etc/apache2/sites-available/000-default.conf
