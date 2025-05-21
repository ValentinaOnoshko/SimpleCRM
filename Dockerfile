FROM php:8.1-fpm as backend

# Install system dependencies
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        gnupg2 \
        apt-transport-https \
        lsb-release && \
    rm -rf /var/lib/apt/lists/*

# Install basic tools
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        zip \
        unzip \
        git \
        curl && \
    rm -rf /var/lib/apt/lists/*

# Install PHP dependencies
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libwebp-dev \
        libxpm-dev \
        libvpx-dev \
        libssl-dev \
        libicu-dev \
        g++ \
        libpq-dev && \
    rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first to leverage Docker cache
COPY backend/composer.json backend/composer.lock ./

# Install composer dependencies
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts

# Copy application files
COPY backend/ ./

# Set permissions
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

# Frontend build stage
FROM node:18-alpine as frontend

WORKDIR /app

# Copy package files
COPY frontend/package*.json ./

# Install dependencies with cache
RUN npm ci

# Copy frontend files
COPY frontend/ ./

# Build frontend
RUN npm run build

# Production stage
FROM backend as production

# Copy frontend build
COPY --from=frontend /app/dist /var/www/frontend/dist

WORKDIR /var/www

# Run composer scripts in production
RUN composer dump-autoload --optimize

CMD ["php-fpm"]
