FROM php:8.4-fpm

# Configure git safe directory
RUN git config --global --add safe.directory /var/www/html

# Install system dependencies & Nginx for Google Cloud Run web serving
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    zip \
    unzip \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip opcache

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application source
COPY . /var/www/html

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Copy Cloud Run Nginx configuration & entrypoint
COPY docker/cloudrun/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/cloudrun/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose HTTP port for Cloud Run (default 8080) and 9000 for PHP-FPM
EXPOSE 8080 9000

CMD ["/usr/local/bin/entrypoint.sh"]
