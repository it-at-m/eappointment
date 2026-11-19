ARG PHP_VERSION=8.4
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:d04c1f040417c409cd4f075d289fd1c3b59994b1326566b7e04e73be8b47c06f
ARG ZMSBASE_BASE_DIGEST=sha256:6698c59f9d88ecce77d46cefa799a5b345f43bba2a354079aad9be3fbbd36dc6
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /build
WORKDIR /build
USER 1000:1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /build /var/www/html
