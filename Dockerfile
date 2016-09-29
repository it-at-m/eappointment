FROM php:5.6
MAINTAINER Mathias Fischer <mathias.fischer@berlinonline.de>

RUN echo "precedence ::ffff:0:0/96 100" >> /etc/gai.conf

# Install software
RUN apt-get update -yqq
RUN apt-get install -yqq openssh-client git zip unzip rsync libpng-dev gettext libfreetype6-dev libmcrypt-dev libpng12-dev libjpeg-dev libpng-dev
#
# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring gettext iconv
RUN docker-php-ext-configure gd \
        --enable-gd-native-ttf \
        --with-freetype-dir=/usr/include/freetype2 \
        --with-png-dir=/usr/include \
        --with-jpeg-dir=/usr/include \
 	&& docker-php-ext-install -j$(nproc) iconv mcrypt \
    	&& docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    	&& docker-php-ext-install -j$(nproc) gd
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

RUN apt-get install -yqq libbz2-dev

# Install PHP extensions
RUN docker-php-ext-install bz2

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*



