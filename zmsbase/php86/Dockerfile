ARG COMPOSER_VERSION="2"
ARG NODE_MAJOR="22"
ARG PHP_VERSION="8.6-fpm-trixie"

# composer version to make available in built image
FROM composer:2@sha256:30621647c7caf81d0a173f42b5e46ab7fed94d5a0e19e7259429517bb9f9d342 AS composer-image

# PIE replaces pecl, which the official PHP 8.6 images no longer ship.
FROM ghcr.io/php/pie:bin@sha256:f49c15dbb88a7aec373fdb15ab9004bf08958772db3b476c42364e10a8ea416f AS pie

# PHP 8.6.0 (19 November 2026). 8.6-fpm-trixie tracks the latest 8.6 patch.
FROM php:8.6-fpm-trixie@sha256:df9ead21fc3a43c80587a5461db3b859b0f6e3effe5da33a22fc762d5d8a4364 AS base

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
        libjpeg62-turbo-dev \
        #libgif-dev \
        #libgmp-dev \
        libicu-dev \
        libonig-dev \
        libjpeg-dev \
        libpng-dev \
        #libpq-dev \
        #libpspell-dev \
        #libtidy-dev \
        libwebp-dev \
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
# Official 8.6 images omit mbstring. libonig-dev lets the installer build it
# with its default oniguruma backend. Install one extension at a time: these
# images are ZTS, and one parallel docker-php-ext-install races at
# install-modules (cp: cannot stat 'modules/*').
# Opcache is already compiled into the official 8.6 binary. Installing it again
# builds no modules/*.so, so make install fails.
RUN docker-php-ext-configure calendar \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-configure intl \
        --enable-intl \
    && set -e \
    && for ext in bcmath bz2 calendar gd gettext intl mbstring pdo_mysql soap zip; do \
        docker-php-ext-install -j"$(nproc)" "$ext" || exit 1; \
    done \
    && rm -rf /usr/src/

FROM base AS dev

COPY scripts/ /usr/local/sbin/

# Xdebug 3.5 does not support PHP 8.6. 3.6.0alpha1 is the release that does.
# Install it, then drop the ini PIE writes so xdebug stays disabled by default.
# Enable it with: zend_extension=xdebug
ARG XDEBUG_VERSION="3.6.0alpha1"
COPY --from=pie /pie /usr/bin/pie
RUN docker-apt-clean-install libtool \
    && pie install "xdebug/xdebug:${XDEBUG_VERSION}" \
    && find /usr/local/etc/php/conf.d -name '*xdebug*' -delete

# put composer into image
COPY --from=composer-image /usr/bin/composer /usr/bin/composer
ENV COMPOSER_NO_INTERACTION=1
ENV COMPOSER_HOME=/tmp/composer
# show ext-* and lib-* entries one may use in composer.json on this platform
#RUN COMPOSER_ALLOW_SUPERUSER=1 composer show --platform --no-plugins --no-interaction

# Add locale de_DE.UTF-8
RUN echo "de_DE.UTF-8 UTF-8" >> /etc/locale.gen && locale-gen

# Add current node version
ARG NODE_MAJOR
RUN docker-node-install node_${NODE_MAJOR}.x

# Add current Python libraries
RUN apt update && apt install -y python3 python3-click python3-colorama python3-git && \
    apt clean && rm -rf /var/lib/apt/lists/*
