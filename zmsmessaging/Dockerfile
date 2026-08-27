ARG PHP_VERSION=8.3
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:fcb177bdc9281c6576b4bb1c64d828a7dac0df5cce0040564b59260f8b8f5097
ARG ZMSBASE_BASE_DIGEST=sha256:defb6f994929e9953149a9034f2641ffde03862021451bac63d84360d88e8098
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /var/www/html
WORKDIR /var/www/html
USER 1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /var/www/html /var/www/html
