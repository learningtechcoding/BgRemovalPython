# Single container: Laravel (web + queue + scheduler) and the Python rembg service.
# Built for Hugging Face Spaces (Docker SDK, port 7860) but works on any Docker host.

# ---- 1. Frontend assets ----
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources resources
COPY public public
RUN npm run build

# ---- 2. PHP dependencies ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts --ignore-platform-reqs

# ---- 3. Runtime ----
FROM php:8.4-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg-dev libwebp-dev libzip-dev libsqlite3-dev \
        python3 python3-venv python3-pip libgl1 libglib2.0-0 curl \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd zip pcntl \
    && rm -rf /var/lib/apt/lists/*

# Python inference environment + pre-downloaded model (so the first request is not a 224 MB download)
ENV U2NET_HOME=/opt/models
RUN python3 -m venv /opt/venv \
    && /opt/venv/bin/pip install --no-cache-dir "rembg[cpu]" fastapi uvicorn python-multipart pillow \
    && mkdir -p /opt/models \
    && /opt/venv/bin/python -c "from rembg import new_session; new_session('birefnet-general-lite')" \
    && chmod -R a+rX /opt/models

RUN useradd -m -u 1000 user
WORKDIR /home/user/app
COPY --from=vendor --chown=user:user /app ./
COPY --from=assets --chown=user:user /app/public/build ./public/build
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
RUN chmod +x docker/start.sh && chown -R user:user /home/user

USER user
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/home/user/app/database/database.sqlite \
    SESSION_DRIVER=database \
    SESSION_SECURE_COOKIE=true \
    QUEUE_CONNECTION=database \
    CACHE_STORE=database \
    BG_PROVIDER=python \
    BG_MODEL=birefnet-general-lite \
    BG_PYTHON_URL=http://127.0.0.1:8001 \
    PHP_CLI_SERVER_WORKERS=8

EXPOSE 7860
CMD ["bash", "docker/start.sh"]
