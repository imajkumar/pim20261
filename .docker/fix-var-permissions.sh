#!/bin/sh
set -e

mkdir -p /var/www/html/public/var/assets \
         /var/www/html/public/var/tmp/thumbnails \
         /var/www/html/public/var/tmp/pages \
         /var/www/html/public/var/versions \
         /var/www/html/public/var/thumbnails

chown -R www-data:www-data /var/www/html/public/var
chmod -R 775 /var/www/html/public/var

exec docker-php-entrypoint php-fpm
