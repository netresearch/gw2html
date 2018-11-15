FROM php:7

RUN set -ex \
 && apt-get update -y \
 && apt-get install -y \
    nginx freetds-dev freetds-bin icu-devtools libicu-dev unzip \
    --no-install-recommends \
 && docker-php-ext-configure pdo_dblib --with-libdir=/lib/x86_64-linux-gnu \
 && docker-php-ext-install pdo_dblib \
 && docker-php-ext-enable pdo_dblib \
 && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false \
    freetds-dev libicu-dev \
 && apt-get clean \
 && rm -rf /usr/src/* \
 && rm -rf /var/lib/apt/lists/* \
 && rm -rf /tmp/* \
 && rm -rf /var/tmp/* \
 && for logs in `find /var/log -type f`; do > ${logs}; done \
 && rm -rf /usr/share/locale/* \
 && rm -rf /usr/share/man/* \
 && rm -rf /usr/share/doc/*


#
# composer
#
# https://getcomposer.org/download/
# https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
#
RUN set -ex \
 && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php \
 && mv composer.phar /usr/bin/composer \
 && rm composer-setup.php

COPY . /var/www

RUN cd /var/www \
 && composer install \
 && rm -rf html \
 && ln -s www html

WORKDIR /var/www

VOLUME /var/www/www

# forward request and error logs to docker log collector
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log

EXPOSE 80

STOPSIGNAL SIGTERM

CMD ["nginx", "-g", "daemon off;"]