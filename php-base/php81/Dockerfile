ARG COMPOSER_VERSION="2"
ARG PHP_VERSION="8.1-fpm-bullseye"

# composer version to make available in built image
FROM composer:${COMPOSER_VERSION} AS composer-image

# php version to actually build
FROM php:${PHP_VERSION} AS base

# bash as default shell should exit on non-zero exit-codes in piped commands
SHELL ["/bin/bash", "-o", "pipefail", "-c"]

COPY scripts/docker-apt-clean-install /usr/local/sbin/
ENV PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

ENV DEBIAN_FRONTEND=noninteractive

# install software available by default in this image
# hadolint ignore=DL3008,DL3009
RUN docker-apt-clean-install \
        calendar \
        #coreutils \
        #debconf-utils \
        #gettext \
        git \
        wget \
        gnupg2 \
        #gosu \
        libbz2-dev \
        #libexif-dev \
        libfreetype6-dev \
        #libgif-dev \
        #libgmp-dev \
        libicu-dev \
        #libjpeg-dev \
        libpng-dev \
        #libpq-dev \
        #libpspell-dev \
        #libtidy-dev \
        #libwebp-dev \
        libxml2-dev \
        #libxpm-dev \
        #libxslt1-dev \
        libzip-dev \
        locales \
        msmtp \
        tzdata \
        unzip \
        zlib1g-dev \
        zip \
    && echo "tzdata tzdata/Areas select Europe" | debconf-set-selections \
    && echo "tzdata tzdata/Zones/Europe select Berlin" | debconf-set-selections \
    && rm -f /etc/localtime /etc/timezone \
    && dpkg-reconfigure -f noninteractive tzdata

ENV TZ=Europe/Berlin

# configure and install PHP extensions
RUN docker-php-ext-configure calendar \
    && docker-php-ext-configure gd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        bz2 \
        calendar \
        #exif \
        gd \
        #gmp \
        gettext \
        intl \
        #mysqli \
        opcache \
        #pcntl \
        pdo_mysql \
        #pdo_pgsql \
        #pspell \
        soap \
        #sockets \
        #tidy \
        #xsl \
        zip \
    && rm -rf /usr/src/

FROM base as dev

COPY scripts/ /usr/local/sbin/

# install xdebug – it is not enabled by default though, you need to add the following line to your php.ini file:
# zend_extension="/usr/local/php/modules/xdebug.so"
ARG XDEBUG_VERSION="xdebug"
RUN pecl install ${XDEBUG_VERSION}

# put composer into image
COPY --from=composer-image /usr/bin/composer /usr/bin/composer
ENV COMPOSER_NO_INTERACTION=1
ENV COMPOSER_HOME=/tmp/composer
# show ext-* and lib-* entries one may use in composer.json on this platform
#RUN COMPOSER_ALLOW_SUPERUSER=1 composer show --platform --no-plugins --no-interaction

# Add locale de_DE.UTF-8
RUN echo "de_DE.UTF-8 UTF-8" >> /etc/locale.gen && locale-gen

# Add current node version
RUN docker-node-install node_16.x

