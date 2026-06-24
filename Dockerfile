FROM dunglas/frankenphp:php8.4-bookworm

RUN install-php-extensions \
    pdo_pgsql \
    redis \
    opcache \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    exif

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist

COPY . .
RUN rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && composer dump-autoload --optimize --no-scripts \
    && mkdir -p storage/framework/{cache,sessions,testing,views} \
    && mkdir -p storage/logs \
    && chmod -R 775 storage bootstrap/cache

COPY frankenphp/Caddyfile /etc/caddy/Caddyfile

EXPOSE 80 443

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
