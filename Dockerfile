FROM php:8.1-apache

# تثبيت امتدادات قاعدة البيانات وتفعيل mod_rewrite
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

# تعطيل mpm_event ومنع تضارب موديلات Apache
RUN a2dismod mpm_event || true \
    && a2enmod mpm_prefork || true

# تعديل المنفذ لـ 8080 لتطابق إعدادات Railway
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:8080>/' /etc/apache2/sites-available/000-default.conf

# نسخ الملفات وتحديد الصلاحيات
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 8080
