ARG PHP_VERSION=8.3
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:5743860e7e9bf5755d75e9f2cd6b0f8986a779a0ef02e8cffee2bfd21fdd85fc
ARG ZMSBASE_BASE_DIGEST=sha256:33e5a1369636cf52ea52ace59a8dab72edb63ce9f2c768ba1ebc4a922535636d
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /var/www/html
WORKDIR /var/www/html
USER 1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /var/www/html /var/www/html
