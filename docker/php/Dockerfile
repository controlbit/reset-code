FROM php:8.3-cli-bullseye AS base

# SYS: Install required packages
RUN apt-get update
RUN apt-get install --no-install-recommends -y \
      bash \
      git \
      sudo \
      autoconf \
      gcc \
      g++ \
      make \
      gettext \
      nano \
      zip \
      unzip \
      libzip-dev \
      libxml2-dev

COPY files/bin                    /usr/local/bin/

# PHP Extensions, via docker-php-ext-install and PECL :)
RUN docker-php-ext-install ctype
RUN docker-php-ext-install xml
RUN docker-php-ext-install zip
RUN docker-php-ext-install pdo_mysql
RUN php-ext-enable pdo_mysql

RUN git config --global --add safe.directory /app

# Install composer
RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

# Setting Working Directory
WORKDIR /app

FROM base AS development

COPY files/ini/20-xdebug.ini      /usr/local/etc/php/available-ini/

# Excensions
RUN pecl install pcov
RUN pecl install xdebug
RUN php-ext-enable pcov
RUN php-ext-enable xdebug
