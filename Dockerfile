FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    nodejs \
    npm

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-interaction

RUN npm install
RUN npm run build

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD php artisan config:cache && php artisan route:cache && php artisan migrate --force && php -S 0.0.0.0:8080 -t public