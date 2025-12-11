FROM php:8.3-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer desde imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiar el código de la aplicación
COPY . /app

# Instalar dependencias de PHP en modo producción
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Permisos para logs y cache de Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Cloud Run define la variable PORT (por defecto 8080)
ENV PORT=8080
EXPOSE 8080

# Iniciar el servidor embebido de Laravel
#CMD php artisan serve --host=0.0.0.0 --port=${PORT}
#CMD php -S 0.0.0.0:${PORT} -t public public/index.php
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "server.php"]

