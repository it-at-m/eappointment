ARG PHP_VERSION=8.4
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:e6c894ea6e2f4ff264493669fa7b5dfc255f90d543a052bcdfb7035182d82c24
ARG ZMSBASE_BASE_DIGEST=sha256:c5f0a1a8a555d50ed266bb9174c7b7cdb94cdc4cc2d7a8f9c04ceb86c3b4162e
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /var/www/html
WORKDIR /var/www/html
USER 1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /var/www/html /var/www/html
