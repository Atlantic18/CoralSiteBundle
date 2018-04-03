FROM php:cli

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN apt-get update && apt-get install -y \
    git \
    zlib1g-dev

RUN docker-php-ext-install zip

WORKDIR /app
