# Build Stage
ARG PHP_VERSION
FROM registry.gitlab.com/eappointment/php-base:${PHP_VERSION}-dev as build
COPY --chown=1000:1000 . /build
WORKDIR /build
USER 1000:1000

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install PHPUnit
RUN composer require --dev phpunit/phpunit ^8.0

# Continue with your build process
RUN make live

# Final Stage
FROM registry.gitlab.com/eappointment/php-base:${PHP_VERSION}-base
COPY --from=build --chown=0:0 /build /var/www/html

# Ensure the PHPUnit command is available
COPY --from=build /root/.composer/vendor/bin/phpunit /usr/local/bin/phpunit
