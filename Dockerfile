FROM php:8.1-fpm-alpine

# تثبيت Nginx ومكتبات الصور والضغط
RUN apk add --no- Together \
    nginx \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip mysqli pdo pdo_mysql

# إعداد Nginx لتشغيل السكريبت ودعم الـ Rewrite
RUN mkdir -p /run/nginx
RUN echo 'server { \
    listen 8080; \
    root /var/www/html; \
    index index.php index.html; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        include fastcgi_params; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
    } \
}' > /etc/nginx/http.d/default.conf

WORKDIR /var/www/html
COPY . .

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html

EXPOSE 8080

CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
