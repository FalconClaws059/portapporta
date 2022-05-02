#!/bin/bash
set -e

echo "Deployment started ..."

# Enter maintenance mode or return true
# if already is in maintenance mode
(php artisan down) || true

# backup database

echo "create gz backup"
pg_dump pap > ~/backup/$(date +%Y-%m-%d).backup
gzip  ~/backup/$(date +%Y-%m-%d).backup --force
echo "Clearing old backups. takes only the last 10"
cd ~/backup
rm `ls -t | awk 'NR>10'` -f
cd ~/portapporta



# Pull the latest version of the app
git pull origin main

# Install composer dependencies
composer install  --no-interaction --prefer-dist --optimize-autoloader
php artisan nova:install

# Clear caches
php artisan cache:clear

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache config
php artisan config:clear
php artisan config:cache

# Clear the old cache
php artisan clear-compiled

composer dump-autoload
php artisan optimize

# Compile npm assets
# npm run prod

# Exit maintenance mode
php artisan up

echo "Deployment finished!"
