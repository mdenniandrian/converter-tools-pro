FROM php:8.2-fpm-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    icu-data-full \
    icu-dev

RUN docker-php-ext-install pdo pdo_pgsql zip bcmath intl

# Increase PHP File Upload Limits (Max 100MB)
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
 && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
 && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy Nginx Site Configuration
COPY nginx.conf /etc/nginx/http.d/default.conf

# Create nginx run directory
RUN mkdir -p /run/nginx

EXPOSE 80

CMD ["sh", "-c", "if [ -f artisan ]; then php artisan migrate --force; fi && php-fpm -D && nginx -g 'daemon off;'"]
