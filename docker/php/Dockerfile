FROM danilovl/final-work-system:php AS final-work-system-php

RUN --mount=type=cache,target=/tmp/blobs \
    curl -v -L "https://github.com/elastic/apm-agent-php/releases/download/v1.13.0/apm-agent-php_1.13.0_amd64.deb" -o "/tmp/blobs/apm-agent-1.13.0.deb" \
    && dpkg -i "/tmp/blobs/apm-agent-1.13.0.deb" \
    && apt update && apt list --upgradable

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./docker/php/conf/php.ini /etc/php/conf.d/
COPY ./docker/php/conf/php.ini /etc/php/cli/conf.d/
COPY ./docker/php/conf/php.ini /usr/local/etc/php
COPY ./ /var/www/html/app

FROM final-work-system-php AS final-work-system-php-www-data

ARG GITHUB_ACTION_UUID
ARG UUID
ENV UUID=${GITHUB_ACTION_UUID:-${UUID}}

WORKDIR /var/www/html/app

RUN \
 usermod -u ${UUID} www-data && \
 groupmod -g ${UUID} www-data && \
 usermod -d /home/www-data -s /bin/bash www-data

RUN chown -R ${UUID}:${UUID} ./
RUN chmod -R 774 ./

RUN mkdir var && chmod 777 var
RUN mkdir -p public/upload && chmod 777 public/upload

USER www-data

FROM final-work-system-php AS final-work-system-php-supervisor

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        cron && \
    rm -rf \
        /tmp/* \
        /var/lib/apt/lists/* \
        /var/tmp/* && \
    touch /var/log/cron.log

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        supervisor && \
    sed -i 's/^\(\[supervisord\]\)$/\1\nnodaemon=true/' /etc/supervisor/supervisord.conf && \
    sed -i 's/^\(\[supervisord\]\)$/\1\nuser=root/' /etc/supervisor/supervisord.conf  && \
    sed -i 's/^\(\[unix_http_server\]\)$/\1\nusername=dummy/' /etc/supervisor/supervisord.conf && \
    sed -i 's/^\(\[unix_http_server\]\)$/\1\npassword=dummy/' /etc/supervisor/supervisord.conf && \
    sed -i 's/^\(\[supervisorctl\]\)$/\1\nusername=dummy/' /etc/supervisor/supervisord.conf && \
    sed -i 's/^\(\[supervisorctl\]\)$/\1\npassword=dummy/' /etc/supervisor/supervisord.conf && \
    rm -rf \
        /tmp/* \
        /var/lib/apt/lists/* \
        /var/tmp/*

COPY ./docker/supervisor/etc/supervisor/conf.d/messenger-worker.conf /etc/supervisor/conf.d/messenger-worker.conf

WORKDIR /var/www/html/app

CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
