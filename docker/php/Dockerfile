FROM danilovl/final-work-system:php

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./docker/php/conf/php.ini /etc/php/conf.d/
COPY ./docker/php/conf/php.ini /etc/php/cli/conf.d/
COPY ./docker/php/conf/php.ini /usr/local/etc/php
COPY ./ /var/www/html/app

WORKDIR /var/www/html/app

RUN mkdir -m 777 /var/www/html/app/var
RUN chmod 777 /var/www/html/app/bin/composer-first-install.sh
