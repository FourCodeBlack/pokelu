# ==========================================
# STAGE 1: Frontend Build (Node.js)
# ==========================================
FROM node:22.12 AS frontend
WORKDIR /app

# Copy package files
COPY package.json package-lock.json* ./
RUN npm ci

# Copy all project files (except those in .dockerignore)
COPY . .

# Run Vite build
RUN npm run build

# ==========================================
# STAGE 2: Backend (PHP 8.2 CLI)
# ==========================================
FROM php:8.2-cli
WORKDIR /app

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install zip pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy project files from host
COPY . .

# Copy Vite build assets from frontend stage
COPY --from=frontend /app/public/build ./public/build

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for storage & bootstrap cache
RUN chmod -R 777 storage bootstrap/cache

# Clear & cache configurations
RUN php artisan config:clear && \
    php artisan cache:clear && \
    php artisan route:clear && \
    php artisan view:clear

# Expose port (Render sets the PORT environment variable)
EXPOSE ${PORT:-8000}

# Start Laravel built-in server
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
