FROM php:8.0.0-fpm

RUN apt-get update \
  && apt-get install -y \
     apt-utils \
     vim \
     git \
     man \
     curl \
     wget \
     unzip \
     pkg-config \
     icu-devtools \
     libicu-dev \
     libcurl4 \
     libcurl4-gnutls-dev \
     libfreetype6-dev \
     libjpeg62-turbo-dev \
     libpng-dev \
     libbz2-dev \
     libssl-dev \
     libgmp-dev \
     libtidy-dev \
     libxml2-dev \
     libxslt1-dev \
     libzip-dev \
     libonig-dev \
  &&  ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h \
  &&  pecl install redis

RUN docker-php-ext-install mysqli \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install intl \
    && docker-php-ext-install zip \
    && docker-php-ext-install bz2 \
    && docker-php-ext-install calendar \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install exif \
    && docker-php-ext-install gettext \
    && docker-php-ext-install gmp \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install shmop \
    && docker-php-ext-install sockets \
    && docker-php-ext-install sysvmsg \
    && docker-php-ext-install sysvsem \
    && docker-php-ext-install sysvshm \
    && docker-php-ext-install tidy \
    && docker-php-ext-install xsl \
    && docker-php-ext-install opcache \
    && docker-php-ext-install gd \
    && docker-php-source delete \
    && docker-php-ext-enable redis

RUN rm /etc/localtime \
    && ln -s /usr/share/zoneinfo/Europe/Berlin /etc/localtime \
    && "date"

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./docker/php/conf/php.ini /etc/php/conf.d/
COPY ./docker/php/conf/php.ini /etc/php/cli/conf.d/
COPY ./docker/php/conf/php.ini /usr/local/etc/php
COPY ./ /var/www/html/app

WORKDIR /var/www/html/app

RUN mkdir -m 777 /var/www/html/app/var
RUN chmod 777 /var/www/html/app/docker/docker-install-composer.sh
