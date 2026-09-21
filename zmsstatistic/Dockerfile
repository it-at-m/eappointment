ARG PHP_VERSION=8.3
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:5743860e7e9bf5755d75e9f2cd6b0f8986a779a0ef02e8cffee2bfd21fdd85fc
ARG ZMSBASE_BASE_DIGEST=sha256:d6f26d2d94bfad1497dac61d6bdbcdb55b03cc432cc0251ad030ebf8767b45bb
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /build
WORKDIR /build
USER 1000:1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /build /var/www/html
