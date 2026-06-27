FROM php:8.5-fpm

RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-enable pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get -y update
RUN apt-get install -y libzip-dev

RUN docker-php-ext-install zip


#set work dir
WORKDIR /usr/share/nginx/html