ARG PHP_VERSION=8.3
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:1988e5a9ebb0fb8a3fe1d8551c71b79321d9cf2dd055cc25d66ebb91ca528791
ARG ZMSBASE_BASE_DIGEST=sha256:fbb700b98b68e04ebcc221fc9bb6113ac373a64ca3b58c99e89e3b2b8b26f7b9
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /build
WORKDIR /build
USER 1000:1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /build /var/www/html
