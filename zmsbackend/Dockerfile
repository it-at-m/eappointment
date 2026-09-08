ARG PHP_VERSION=8.3
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:d9cd4aa052563e62ee33f611950016daadaf05055f39a09000aabf8e492e56c3
ARG ZMSBASE_BASE_DIGEST=sha256:fbcd6db84657fdf80d713ddf7545fa77536fa872ad26c2094ee7f61f9ba10d56
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /var/www/html
WORKDIR /var/www/html
USER 1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /var/www/html /var/www/html
RUN chmod -R 1777 /var/www/html/data
