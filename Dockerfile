FROM php:8.1-fpm as backend

RUN apt-get update && apt-get install -y \
    zip unzip curl git libpng-dev libonig-dev libxml2-dev libzip-dev \
    libjpeg-dev libfreetype6-dev libwebp-dev libxpm-dev libvpx-dev \
    libssl-dev libicu-dev g++ libpq-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY backend/ ./

RUN composer install --no-dev --prefer-dist --optimize-autoloader

RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

FROM node:18-alpine as frontend

WORKDIR /app
COPY frontend/package*.json ./
RUN npm install
COPY frontend/ ./
RUN npm run build

FROM php:8.1-fpm

RUN apt update && apt install -y unzip git curl \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY --from=backend /var/www /var/www
COPY --from=frontend /app/dist /var/www/frontend/dist

WORKDIR /var/www

RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

CMD ["php-fpm"]
