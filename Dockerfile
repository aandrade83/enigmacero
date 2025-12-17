# 1) Build de assets (Vite)
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# 2) App PHP
FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copiamos el build generado por Vite al contenedor final
COPY --from=assets /app/public/build /app/public/build

RUN chown -R www-data:www-data storage bootstrap/cache public/build

ENV PORT=8080
EXPOSE 8080

RUN chmod +x /app/start.sh
CMD ["/app/start.sh"]

