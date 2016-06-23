FROM php:5.6
MAINTAINER Mathias Fischer <mathias.fischer@berlinonline.de>

RUN echo "precedence ::ffff:0:0/96 100" >> /etc/gai.conf

# Install software
RUN ( apt-get update -yqq && apt-get install -yqq openssh-client git zip unzip rsync libpng-dev gettext) && apt-get clean && rm -rf /var/lib/apt/lists/*

# For Docker builds disable host key checking. Be aware that by adding that
# you are suspectible to man-in-the-middle attacks.
# WARNING: Use this only with the Docker executor, if you use it with shell
# you will overwrite your user's SSH config.
RUN mkdir -p ~/.ssh
RUN test -f /.dockerinit && echo -e "Host *\n\tStrictHostKeyChecking no\n\n" > ~/.ssh/config

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring gd gettext

# Install composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/

# Install xdebug after composer for performance
RUN pecl install xdebug > /dev/null
#RUN docker-php-ext-disable xdebug

