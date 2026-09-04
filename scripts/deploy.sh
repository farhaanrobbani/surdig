#!/bin/bash
set -e

cd /ai/proyek

export PATH="/root/.local/bin:/tools/node/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:$PATH"
export HOME=/root

git pull origin main
apt-get install -y fonts-liberation >/dev/null 2>&1 || true
mkdir -p storage/fonts
cp /usr/share/fonts/truetype/liberation/LiberationSans-*.ttf storage/fonts/ 2>/dev/null || true
chmod 755 /usr/share/fonts/truetype/liberation/*.ttf 2>/dev/null || true
composer install --no-dev --no-interaction --prefer-dist --no-progress
php artisan migrate --force
php artisan db:seed --force
if [ -f package.json ]; then
  npm install --no-progress --ignore-scripts 2>/dev/null || true
  npm run build
fi
php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

PHP_FPM_CONF_DIR=$(ls -d /etc/php/*/fpm/conf.d 2>/dev/null | head -1)
if [ -n "$PHP_FPM_CONF_DIR" ]; then
  cat > "$PHP_FPM_CONF_DIR/99-kua-uploads.ini" <<'PHPEOF'
upload_max_filesize = 3M
post_max_size = 4M
PHPEOF
fi

chown -R www-data:www-data storage bootstrap/cache
setsid service php8.3-fpm restart </dev/null >/dev/null 2>&1 &
echo "DEPLOY OK: $(git rev-parse --short HEAD)"
