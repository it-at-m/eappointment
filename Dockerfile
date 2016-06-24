FROM php:5.6
MAINTAINER Mathias Fischer <mathias.fischer@berlinonline.de>

RUN echo "precedence ::ffff:0:0/96 100" >> /etc/gai.conf

# Install software
RUN apt-get update -yqq
RUN apt-get install -yqq openssh-client git zip unzip rsync libpng-dev gettext

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring gd gettext

# Install xdebug after composer for performance
RUN pecl install xdebug > /dev/null
#RUN docker-php-ext-disable xdebug

# Install composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/

# For Docker builds disable host key checking. Be aware that by adding that
# you are suspectible to man-in-the-middle attacks.
# WARNING: Use this only with the Docker executor, if you use it with shell
# you will overwrite your user's SSH config.
RUN mkdir -p ~/.ssh
RUN test -f /.dockerinit && echo "Host *\n\tStrictHostKeyChecking no\n\n" > ~/.ssh/config

RUN apt-get install -yqq locales
RUN echo "de_DE.UTF-8 UTF-8" >> /etc/locale.gen && locale-gen

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/* && rm -rf /usr/src


