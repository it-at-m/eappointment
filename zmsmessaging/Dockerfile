ARG PHP_VERSION=8.4
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:6d9046ff3c9baba80fcc5862bca3c27fe31dabdc03b8ccf950607f4e73798b05
ARG ZMSBASE_BASE_DIGEST=sha256:89e39c938c6b42e6209ad3c3075041031467dd7646fefa3375d6fd91298e07c6
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /var/www/html
WORKDIR /var/www/html
USER 1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /var/www/html /var/www/html
