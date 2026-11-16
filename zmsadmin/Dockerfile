ARG PHP_VERSION=8.4
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:d04c1f040417c409cd4f075d289fd1c3b59994b1326566b7e04e73be8b47c06f
ARG ZMSBASE_BASE_DIGEST=sha256:b8eb2adac91cb49282ec155f5389798739b70392e9d245ad43c75a14a2a59770
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /build
WORKDIR /build
USER 1000:1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /build /var/www/html