ARG PHP_VERSION=8.3
# Digests pinned for supply-chain integrity; override via build-args when retargeting.
ARG ZMSBASE_DEV_DIGEST=sha256:cdf964fa2e02d11b1309f1006d0d71d0afef6a550d5722c7634860e5c594d4cb
ARG ZMSBASE_BASE_DIGEST=sha256:c743242ccf89669618a2f132904bd87fabd9258ede769522fe376859391cb598
FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-dev@${ZMSBASE_DEV_DIGEST} AS build
COPY --chown=1000:1000 . /build
WORKDIR /build
USER 1000:1000
RUN make live

FROM ghcr.io/it-at-m/eappointment/zmsbase:${PHP_VERSION}-base@${ZMSBASE_BASE_DIGEST}
COPY --from=build --chown=0:0 /build /var/www/html