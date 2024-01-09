# Build Stage
ARG PHP_VERSION
FROM registry.gitlab.com/eappointment/php-base:${PHP_VERSION}-dev as build
COPY --chown=1000:1000 . /build
WORKDIR /build
USER 1000:1000

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Continue with your build process
RUN make live

# Final Stage
FROM registry.gitlab.com/eappointment/php-base:${PHP_VERSION}-base
COPY --from=build --chown=0:0 /build /var/www/html

# Copy PHPUnit to the final image
COPY --from=build /build/vendor/bin/phpunit /usr/local/bin/phpunit
