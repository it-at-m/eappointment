FROM php:5.6

ADD . /opt/zmsdb

WORKDIR /opt/zmsdb

RUN docker-php-ext-install pdo_mysql
