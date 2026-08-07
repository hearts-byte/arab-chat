FROM php:8.1-cli

# تثبيت الحزم والمكتبات المطلوبة (GD, ZIP, MySQLi)
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip mysqli pdo pdo_mysql

WORKDIR /var/www/html

COPY . .

# منح صلاحيات القراءة والكتابة الشاملة للمجلدات وملف القاعدة
RUN chmod -R 777 avatar cover upload system system/database.php || true

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080"]
