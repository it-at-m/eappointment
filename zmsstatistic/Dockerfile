ARG PHP_VERSION=8.4
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:3ba315e310cf72a0b0d5ad14390d82212c6dd8c78e954cbd0061a834d8bbcb31
ARG ZMSBASE_BASE_DIGEST=sha256:7fd1d05494c9101f5409cb39e4c0b4ac07c9a72d0fe6a67d58c73d25e5d6c2b2
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /build
WORKDIR /build
USER 1000:1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /build /var/www/html
