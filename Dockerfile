FROM php:8.1-cli

# تثبيت امتدادات قاعدة البيانات
RUN docker-php-ext-install mysqli pdo pdo_mysql

# تحديد مجلد العمل
WORKDIR /var/www/html

# نسخ جميع ملفات المشروع
COPY . .

# فتح المنفذ 8080
EXPOSE 8080

# تشغيل خادم PHP المدمج مباشرة على المنفذ 8080
CMD ["php", "-S", "0.0.0.0:8080"]
