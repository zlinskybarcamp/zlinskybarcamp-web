FROM php:5.6-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/www

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && apt-get update \
	&& apt-get install -y git unzip \
	&& docker-php-ext-install mysql pdo_mysql \
	&& curl -sS https://getcomposer.org/installer | php \
  	&& mv ./composer.phar /usr/local/bin/composer \
	&& a2enmod rewrite \
	&& service apache2 restart
