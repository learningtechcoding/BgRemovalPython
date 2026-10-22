#!/usr/bin/env bash
set -e
cd /home/user/app

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
         storage/app/private/uploads storage/app/private/results bootstrap/cache database
touch database/database.sqlite

# Secrets: set APP_KEY and BG_PYTHON_TOKEN as Space secrets so they survive restarts.
export APP_KEY="${APP_KEY:-$(php artisan key:generate --show)}"
export BG_PYTHON_TOKEN="${BG_PYTHON_TOKEN:-$(php -r 'echo bin2hex(random_bytes(16));')}"

php artisan package:discover --ansi
php artisan migrate --force
php artisan config:cache
php artisan view:cache

# Inference service (localhost only)
( cd inference && exec /opt/venv/bin/python -m uvicorn main:app --host 127.0.0.1 --port 8001 ) &

# Wait until the model is loaded (max ~3 min) so the first upload does not fail
for i in $(seq 1 90); do
  curl -sf http://127.0.0.1:8001/health >/dev/null && break
  sleep 2
done

php artisan queue:work --tries=1 --sleep=1 --max-time=3600 &
php artisan schedule:work &

# Multiple PHP workers so status polling is not blocked while uploads are processed
exec php artisan serve --host=0.0.0.0 --port=7860
