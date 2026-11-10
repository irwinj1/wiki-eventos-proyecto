#!/bin/sh
set -e

set_laravel_permissions() {
    mkdir -p storage/logs bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache
    find storage bootstrap/cache -type d -exec chmod 775 {} +
    find storage bootstrap/cache -type f -exec chmod 664 {} +
}

echo "==> Configurando permisos de Laravel..."
set_laravel_permissions

echo "==> Verificando dependencias de Composer..."
if [ ! -f "vendor/autoload.php" ]; then
    echo "==> Instalando dependencias de Composer..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
    composer dump-autoload --optimize --no-interaction
else
    echo "==> Dependencias ya instaladas, saltando composer install..."
fi

echo "==> Verificando archivo .env..."
if [ ! -f ".env" ] && [ -f ".env.example" ]; then
    cp .env.example .env
fi

echo "==> Verificando APP_KEY..."
php artisan key:generate --no-interaction --force 2>/dev/null || true
php artisan jwt:secret --no-interaction --force 2>/dev/null || true

echo "==> Limpiando cache de configuracion y rutas..."
php artisan config:clear
php artisan route:clear

echo "==> Corriendo migraciones..."
php artisan migrate --force
php artisan storage:link 2>/dev/null || true

# echo "==> Corriendo seeders (deshabilitado por defecto para acelerar el inicio)..."
# php artisan db:seed --force || true

echo "==> Reaplicando permisos de Laravel..."
set_laravel_permissions

echo "==> Iniciando PHP-FPM..."
exec php-fpm