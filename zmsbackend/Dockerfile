ARG PHP_VERSION=8.4
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:fa1884cfc1da3b686c9fc7a45ca4c486901f56b1fce1f7000e64573e3a7921e7
ARG ZMSBASE_BASE_DIGEST=sha256:08e63832af295bbad9fff530a3d39b44d738078fef6b884bc16a7735e3ce1849
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /var/www/html
WORKDIR /var/www/html
USER 1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /var/www/html /var/www/html
RUN chmod -R 1777 /var/www/html/data
