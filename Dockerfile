FROM node:22.12-bookworm AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build


FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && docker-php-ext-install \
    zip \
    mbstring \
    xml \
    intl \
    bcmath \
    pdo \
    pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

COPY --from=frontend /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader

RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

CMD php artisan serve --host=0.0.0.0 --port=${PORT}
