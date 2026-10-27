ARG PHP_VERSION=8.4
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:a1b8d46aab3e1ddf703a79c192cfcf9d23817cbddb9db0c69abdb23beeece59c
ARG ZMSBASE_BASE_DIGEST=sha256:d4e1f38f4cce6b7963a8eaa8cdee4a6099ed2688270b53d286c6f092c81ed84c
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /var/www/html
WORKDIR /var/www/html
USER 1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /var/www/html /var/www/html
