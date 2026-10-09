#!/bin/bash
# Kids Avon – run on the server after each update (cPanel runs it automatically
# through .cpanel.yml when you deploy). Safe to run again at any time:
#   bash deploy.sh
set -u
cd "$(dirname "$0")"

# cPanel's plain "php" command can be an old version; prefer PHP 8.3 / 8.2.
PHP_BIN="${PHP_BIN:-}"
if [ -z "$PHP_BIN" ]; then
  for p in /opt/cpanel/ea-php83/root/usr/bin/php /opt/cpanel/ea-php82/root/usr/bin/php /usr/local/bin/php83 /usr/local/bin/php82 php; do
    if command -v "$p" >/dev/null 2>&1 && "$p" -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);' 2>/dev/null; then PHP_BIN="$p"; break; fi
  done
fi
if [ -z "$PHP_BIN" ]; then echo "✗ PHP 8.2 or newer not found. Set PHP_BIN=/path/to/php"; exit 1; fi
echo "Using $($PHP_BIN -r 'echo "PHP ".PHP_VERSION;') ($PHP_BIN)"

# 1. Libraries (vendor folder)
COMPOSER_BIN="${COMPOSER_BIN:-}"
if [ -z "$COMPOSER_BIN" ]; then
  for c in /opt/cpanel/composer/bin/composer /usr/local/bin/composer composer; do
    if command -v "$c" >/dev/null 2>&1; then COMPOSER_BIN="$c"; break; fi
  done
fi
if [ "${SKIP_COMPOSER:-0}" != "1" ] && [ -n "$COMPOSER_BIN" ]; then
  COMPOSER_ALLOW_SUPERUSER=1 "$PHP_BIN" "$(command -v "$COMPOSER_BIN")" install --no-dev --optimize-autoloader --no-interaction --no-progress \
    || { echo "✗ composer install failed"; exit 1; }
elif [ ! -f vendor/autoload.php ]; then
  echo "✗ Composer not found and there is no vendor folder. Install Composer or upload the vendor folder."; exit 1
fi

# 2. Settings file
if [ ! -f .env ]; then
  cp .env.production.example .env
  echo "! Created .env from .env.production.example – fill in the database, email and Turnstile details, then deploy again."
fi

# 3. Forget cached settings so changes to .env are picked up
rm -f bootstrap/cache/config.php bootstrap/cache/routes-v7.php bootstrap/cache/events.php

# 4. Folders, key, database tables, first admin, caches, settings check
"$PHP_BIN" artisan kidsavon:setup
