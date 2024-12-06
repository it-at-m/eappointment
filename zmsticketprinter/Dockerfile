ARG PHP_VERSION
FROM ghcr.io/it-at-m/eappointment-php-base:${PHP_VERSION}-dev as build
COPY --chown=1000:1000 . /var/www/html
WORKDIR /var/www/html
USER 1000
RUN make live

FROM ghcr.io/it-at-m/eappointment-php-base:${PHP_VERSION}-base
COPY --from=build --chown=0:0 /var/www/html /var/www/html
