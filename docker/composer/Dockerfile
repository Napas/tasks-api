FROM        php:7.3-cli-alpine

RUN         apk --no-cache --update add \
                zlib-dev \
                libzip-dev \
                curl-dev

RUN         docker-php-ext-install \
                zip \
                curl

RUN         wget https://raw.githubusercontent.com/composer/getcomposer.org/1b137f8bf6db3e79a38a5bc45324414a6b1f9df2/web/installer -O - -q | php -- --quiet && \
            mv composer.phar /usr/local/bin/composer && \
            chmod +x /usr/local/bin/composer

RUN         mkdir -p /var/www/ny

WORKDIR     /var/www/ny

ENTRYPOINT  ["composer"]
