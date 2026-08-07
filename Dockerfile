# استخدام صورة PHP رسمية مع سيرفر Apache
FROM php:8.1-apache

# تثبيت الامتدادات المطلوبة للاتصال بقواعد البيانات (mysqli)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# تفعيل وحدة mod_rewrite في Apache (مهمة لملفات .htaccess)
RUN a2enmod rewrite

# نسخ ملفات السكريبت إلى مسار سيرفر الويب
COPY . /var/www/html/

# إعطاء الصلاحيات المناسبة للملفات
RUN chown -R www-data:www-data /var/www/html

# فتح المنفذ الخاص بـ Railway
EXPOSE 80
