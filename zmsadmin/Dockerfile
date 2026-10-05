ARG PHP_VERSION=8.4
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:7b38413cc0747352ebff26b39e7cd740b928eef0d0d96a82bceb01ef3feb2517
ARG ZMSBASE_BASE_DIGEST=sha256:abf80094cb4ca834be2da1bbcfadd05ddfd6f62faaa04e2e8ef104476702b0ca
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /build
WORKDIR /build
USER 1000:1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /build /var/www/html