ARG PHP_VERSION=8.3
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:a536a926be1975d848f7ea6346dd9bcfa4b751af9f2a3fb29c654017b544c95a
ARG ZMSBASE_BASE_DIGEST=sha256:a159a1613fac8f3f11ac408873f2aeefefd201b850ffef729e780777fa766f1b
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /var/www/html
WORKDIR /var/www/html
USER 1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /var/www/html /var/www/html
RUN chmod -R 1777 /var/www/html/data
