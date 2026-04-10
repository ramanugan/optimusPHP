FROM php:7.4-fpm

WORKDIR /var/www/html
COPY ./site /var/www/html/
RUN apt-get update && \
    apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev libxml2-dev && \
    apt-get install -y default-mysql-client && \
    docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && \
    docker-php-ext-install gd && \
    docker-php-ext-install mysqli && \
    docker-php-ext-install soap sockets

COPY docker/php/php.ini $PHP_INI_DIR/php.ini

RUN chown -R www-data:www-data /var/www/html
RUN chmod 777 /var/www/html

RUN ln -sf /dev/stdout /var/log/stdout.log
RUN ln -sf /dev/stderr /var/log/stderr.log

EXPOSE 9000
