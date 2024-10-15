FROM danilovl/final-work-system:php

RUN --mount=type=cache,target=/tmp/blobs \
    curl -v -L "https://github.com/elastic/apm-agent-php/releases/download/v1.13.0/apm-agent-php_1.13.0_amd64.deb" -o "/tmp/blobs/apm-agent-1.13.0.deb" \
    && dpkg -i "/tmp/blobs/apm-agent-1.13.0.deb" \
    && apt update && apt list --upgradable

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./docker/php/conf/php.ini /etc/php/conf.d/
COPY ./docker/php/conf/php.ini /etc/php/cli/conf.d/
COPY ./docker/php/conf/php.ini /usr/local/etc/php
COPY ./ /var/www/html/app

WORKDIR /var/www/html/app

RUN mkdir -m 777 /var/www/html/app/var
RUN chmod 777 /var/www/html/app/bin/composer-first-install.sh
