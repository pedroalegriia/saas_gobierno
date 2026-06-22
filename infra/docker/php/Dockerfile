FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    bash \
    $PHPIZE_DEPS \
    curl-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    supervisor \
    zip \
  && docker-php-ext-install \
    bcmath \
    curl \
    intl \
    mbstring \
    opcache \
    pdo_mysql \
    zip \
  && pecl install redis \
  && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

CMD ["php-fpm"]
