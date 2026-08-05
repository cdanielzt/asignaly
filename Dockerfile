# --- build Vite assets ---
FROM node:20-alpine AS assets
WORKDIR /app
COPY . .
RUN npm ci && npm run build

# --- app ---
FROM dunglas/frankenphp:1-php8.3
RUN install-php-extensions pdo_pgsql intl zip gd bcmath opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
COPY --from=assets /app/public/build public/build
RUN composer install --no-dev --optimize-autoloader --no-interaction \
 && chmod -R ug+w storage bootstrap/cache
# migrate + cache config at boot (env is present at runtime), then serve on Render's $PORT
CMD php artisan migrate --force \
 && php artisan config:cache && php artisan route:cache && php artisan view:cache \
 && frankenphp php-server --listen :${PORT:-8080} --root public/
