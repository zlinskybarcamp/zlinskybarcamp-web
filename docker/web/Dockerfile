FROM php:5.6-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/www

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && apt-get update \
    && apt-get install -y git unzip libpng-dev libjpeg62-turbo-dev \
    && docker-php-ext-install mysqli pdo_mysql \
    && docker-php-ext-configure gd --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd \
    && curl -sS https://getcomposer.org/installer | php \
    && mv ./composer.phar /usr/local/bin/composer \
    && a2enmod rewrite \
    && service apache2 restart

#RUN pecl install xdebug \
#  && docker-php-ext-enable xdebug \
#  && echo "xdebug.remote_enable=on" > /usr/local/etc/php/conf.d/xdebug.ini \
#  && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini