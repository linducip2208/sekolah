FROM php:8.3-fpm

ARG WWWUSER=1000
ARG WWWGROUP=1000

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev \
    zip unzip nodejs npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl

RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && npm ci --only=production \
    && npm run build

RUN groupadd -g ${WWWGROUP} sikadpro && useradd -u ${WWWUSER} -g sikadpro -m sikadpro \
    && chown -R sikadpro:sikadpro /var/www/html/storage /var/www/html/bootstrap/cache

USER sikadpro

EXPOSE 9000

CMD ["php-fpm"]
