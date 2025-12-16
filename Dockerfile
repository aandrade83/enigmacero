FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Permisos Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

ENV PORT=8080
EXPOSE 8080

COPY start.sh /app/start.sh
RUN chmod +x /app/start.sh

CMD ["sh", "/app/start.sh"]
