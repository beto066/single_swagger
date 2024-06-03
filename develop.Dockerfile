FROM sumina46/base-arm:8.2

COPY ./php.ini /usr/local/etc/php/conf.d/overrides.ini

RUN pecl install excimer

COPY . /var/task
