# Build Stage
ARG PHP_VERSION
FROM registry.gitlab.com/eappointment/php-base:${PHP_VERSION}-dev as build
COPY --chown=1000:1000 . /build
WORKDIR /build
USER 1000:1000

# Install Composer and PHPUnit
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer require --dev phpunit/phpunit ^9.5

# Continue with your build process
RUN make live

# Final Stage
FROM registry.gitlab.com/eappointment/php-base:${PHP_VERSION}-base
COPY --from=build --chown=0:0 /build /var/www/html

# Copy PHPUnit to the final image
COPY --from=build /build/vendor/bin/phpunit /usr/local/bin/phpunit
