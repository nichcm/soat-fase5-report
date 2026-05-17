#!/bin/sh

set -e

echo "Ajustando permissoes"
chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Instalando dependencias"
if [ ! -f vendor/autoload.php ]; then
    mkdir -p vendor
    composer install --no-dev --optimize-autoloader || {
        echo "Falha na instalacao das dependencias"
        exit 1
    }
else
    echo "Dependencias ja instaladas"
fi

if [ ! -f .env ]; then
    cp .env.example .env
    echo "Gerando chave da aplicacao"
    composer run gen-app-key
fi

echo "Iniciando o container"
exec php-fpm
