FROM laravelphp/vapor:php82-arm

RUN pecl install mongodb
RUN apk add curl-dev
RUN pecl install raphf
RUN docker-php-ext-enable raphf
RUN pecl install pecl_http

COPY ./php.ini /usr/local/etc/php/conf.d/overrides.ini
