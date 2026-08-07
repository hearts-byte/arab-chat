FROM php:8.1-apache

# تثبيت أداة تثبيت الإضافات الجاهزة (تتعامل مع كل الاعتماديات تلقائيًا)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions gd zip curl mbstring opcache pdo_mysql mysqli

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
