# Global build args
ARG COMPOSER_VERSION=2
ARG NODE_MAJOR=18
ARG PHP_VERSION=8.3-apache

# Composer stage
FROM composer:${COMPOSER_VERSION} AS composer-image

# Node/npm/npx (official multi-arch image; NodeSource apt can miss npm on some Debian/arch combos)
FROM node:${NODE_MAJOR}-bookworm AS node-tools

# Main dev image
FROM php:${PHP_VERSION}

# Set by docker buildx --platform=... (linux/amd64 | linux/arm64)
ARG TARGETARCH

SHELL ["/bin/bash", "-o", "pipefail", "-c"]

# --------------------------------------------------
# Build args
# --------------------------------------------------
ARG TZ=Europe/Berlin

ARG XDEBUG_VERSION=3.5.1
ARG EDGE_DRIVER_VERSION=146.0.3856.62
ARG MAVEN_VERSION=3.9.12
ARG FLYWAY_VERSION=11.1.1

COPY scripts/ /usr/local/sbin/

ENV TZ="${TZ}" \
    DISPLAY=:99 \
    DEBIAN_FRONTEND=noninteractive \
    PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

# --------------------------------------------------
# Timezone
# --------------------------------------------------
RUN ln -snf "/usr/share/zoneinfo/${TZ}" /etc/localtime \
 && echo "${TZ}" > /etc/timezone

# --------------------------------------------------
# System dependencies + PHP extensions
# --------------------------------------------------
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      ca-certificates \
      curl \
      git \
      gnupg \
      unzip \
      wget \
      nano \
      mariadb-client \
      python3-click \
      python3-colorama \
      python3-git \
      libfreetype6-dev \
      libicu-dev \
      libjpeg62-turbo-dev \
      libpng-dev \
      libzip-dev \
      libonig-dev \
      firefox-esr \
      xvfb \
      x11-utils \
      x11-xserver-utils \
      chromium \
      chromium-driver \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" \
      intl \
      gd \
      pdo \
      pdo_mysql \
      zip \
      calendar \
 && ln -sf /usr/bin/firefox-esr /usr/bin/firefox \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# Xdebug
# --------------------------------------------------
RUN pecl install xdebug-"${XDEBUG_VERSION}" \
 && docker-php-ext-enable xdebug

# --------------------------------------------------
# Microsoft Edge + WebDriver (aligned with .devcontainer/Containerfile)
# --------------------------------------------------
RUN set -euo pipefail; \
    ARCH="$(dpkg --print-architecture)"; \
    if [ "$ARCH" = "amd64" ]; then \
      mkdir -p /etc/apt/keyrings \
   && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
      | gpg --batch --no-tty --dearmor -o /etc/apt/keyrings/microsoft.gpg \
   && echo "deb [arch=amd64 signed-by=/etc/apt/keyrings/microsoft.gpg] https://packages.microsoft.com/repos/edge stable main" \
      > /etc/apt/sources.list.d/microsoft-edge.list \
   && apt-get update \
   && apt-get install -y microsoft-edge-stable \
   && EDGE_VERSION="$(microsoft-edge --version | awk '{print $3}')" \
   && EDGE_VERSION="${EDGE_VERSION%%-*}" \
   && curl -fsSL -o /tmp/msedgedriver.zip \
        "https://msedgedriver.microsoft.com/${EDGE_VERSION}/edgedriver_linux64.zip" \
   && unzip -o /tmp/msedgedriver.zip -d /tmp \
   && mv /tmp/msedgedriver /usr/local/bin/msedgedriver \
   && chmod +x /usr/local/bin/msedgedriver \
   && rm -f /tmp/msedgedriver.zip ; \
      apt-get clean \
   && rm -rf /var/lib/apt/lists/* ; \
    elif [ "$ARCH" = "arm64" ]; then \
      mkdir -p /etc/apt/keyrings \
   && curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
      | gpg --batch --no-tty --dearmor -o /etc/apt/keyrings/microsoft.gpg \
   && echo "deb [arch=arm64 signed-by=/etc/apt/keyrings/microsoft.gpg] https://packages.microsoft.com/repos/edge stable main" \
      > /etc/apt/sources.list.d/microsoft-edge.list \
   && apt-get update || true \
   && apt-get install -y microsoft-edge-stable || true \
   && if command -v microsoft-edge >/dev/null 2>&1; then \
        EDGE_VERSION="$(microsoft-edge --version | awk '{print $3}')"; \
      else \
        EDGE_VERSION="${EDGE_DRIVER_VERSION}"; \
      fi \
   && EDGE_VERSION="${EDGE_VERSION%%-*}" \
   && curl -fsSL -o /tmp/msedgedriver.zip \
        "https://msedgedriver.microsoft.com/${EDGE_VERSION}/edgedriver_arm64.zip" \
   && unzip -o /tmp/msedgedriver.zip -d /tmp \
   && if [ -f /tmp/msedgedriver.exe ]; then \
        echo "Skipping msedgedriver on linux/arm64: edgedriver_arm64.zip is Windows-only (msedgedriver.exe)."; \
      elif [ -f /tmp/msedgedriver ]; then \
        mv /tmp/msedgedriver /usr/local/bin/msedgedriver \
     && chmod +x /usr/local/bin/msedgedriver; \
      else \
        drv="$(find /tmp -maxdepth 3 -type f -name msedgedriver ! -name '*.exe' 2>/dev/null | head -n1)"; \
        if [ -n "${drv:-}" ]; then mv "$drv" /usr/local/bin/msedgedriver && chmod +x /usr/local/bin/msedgedriver; \
        else echo "Skipping msedgedriver on linux/arm64: no Unix binary in archive."; fi; \
      fi \
   && rm -f /tmp/msedgedriver.zip \
   && apt-get clean || true \
   && rm -rf /var/lib/apt/lists/* || true ; \
    else \
      echo "Skipping Microsoft EdgeDriver install for unsupported arch: $ARCH"; \
    fi

# --------------------------------------------------
# Apache modules
# --------------------------------------------------
RUN a2enmod rewrite proxy_fcgi alias headers

# --------------------------------------------------
# Composer
# --------------------------------------------------
COPY --from=composer-image /usr/bin/composer /usr/bin/composer

# --------------------------------------------------
# Node and npm/npx (copy from official node image — reliable on amd64 + arm64)
# Copy the full global node_modules tree. Do not COPY /usr/local/bin/npm from
# upstream: that file is a shim that does require('../lib/cli.js') relative to
# /usr/local/bin, which breaks after copy. Point npm/npx at the real CLIs.
# --------------------------------------------------
COPY --from=node-tools /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node-tools /usr/local/bin/node /usr/local/bin/node
RUN set -eux; \
  for c in npm-cli.js npm-cli.cjs npm-cli.mjs; do \
    if [ -f "/usr/local/lib/node_modules/npm/bin/${c}" ]; then \
      ln -sf "/usr/local/lib/node_modules/npm/bin/${c}" /usr/local/bin/npm; \
      break; \
    fi; \
  done; \
  test -L /usr/local/bin/npm; \
  for c in npx-cli.js npx-cli.cjs npx-cli.mjs; do \
    if [ -f "/usr/local/lib/node_modules/npm/bin/${c}" ]; then \
      ln -sf "/usr/local/lib/node_modules/npm/bin/${c}" /usr/local/bin/npx; \
      break; \
    fi; \
  done; \
  test -L /usr/local/bin/npx; \
  node --version && npm --version && npx --version

# --------------------------------------------------
# Java 21 (Temurin)
# --------------------------------------------------
RUN apt-get update \
 && apt-get install -y --no-install-recommends gnupg \
 && curl -fsSL https://packages.adoptium.net/artifactory/api/gpg/key/public \
    | gpg --batch --no-tty --dearmor -o /usr/share/keyrings/adoptium.gpg \
 && echo "deb [signed-by=/usr/share/keyrings/adoptium.gpg] https://packages.adoptium.net/artifactory/deb $(. /etc/os-release && echo $VERSION_CODENAME) main" \
    > /etc/apt/sources.list.d/adoptium.list \
 && apt-get update \
 && apt-get install -y --no-install-recommends temurin-21-jdk \
 && ln -sf /usr/lib/jvm/temurin-21-jdk-$(dpkg --print-architecture)/bin/java /usr/local/bin/java \
 && ln -sf /usr/lib/jvm/temurin-21-jdk-$(dpkg --print-architecture)/bin/javac /usr/local/bin/javac \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# --------------------------------------------------
# Maven
# --------------------------------------------------
RUN curl -fsSL -o /tmp/maven.tar.gz \
      "https://archive.apache.org/dist/maven/maven-3/${MAVEN_VERSION}/binaries/apache-maven-${MAVEN_VERSION}-bin.tar.gz" \
 && curl -fsSL -o /tmp/maven.tar.gz.sha512 \
      "https://archive.apache.org/dist/maven/maven-3/${MAVEN_VERSION}/binaries/apache-maven-${MAVEN_VERSION}-bin.tar.gz.sha512" \
 && echo "$(cat /tmp/maven.tar.gz.sha512)  /tmp/maven.tar.gz" | sha512sum -c - \
 && tar -xzf /tmp/maven.tar.gz -C /opt \
 && ln -s /opt/apache-maven-${MAVEN_VERSION}/bin/mvn /usr/local/bin/mvn \
 && rm -f /tmp/maven.tar.gz /tmp/maven.tar.gz.sha512

# --------------------------------------------------
# Flyway CLI
# Maven only ships flyway-commandline-*-linux-x64.tar.gz (amd64). For arm64
# builds use the generic .tar.gz (uses the Temurin JDK installed above).
# --------------------------------------------------
RUN set -eux; \
  base="https://repo1.maven.org/maven2/org/flywaydb/flyway-commandline/${FLYWAY_VERSION}"; \
  if [ "${TARGETARCH}" = "amd64" ]; then \
    curl -fsSL -o /tmp/flyway.tar.gz "${base}/flyway-commandline-${FLYWAY_VERSION}-linux-x64.tar.gz"; \
    curl -fsSL -o /tmp/flyway.tar.gz.sha1 "${base}/flyway-commandline-${FLYWAY_VERSION}-linux-x64.tar.gz.sha1"; \
  else \
    curl -fsSL -o /tmp/flyway.tar.gz "${base}/flyway-commandline-${FLYWAY_VERSION}.tar.gz"; \
    curl -fsSL -o /tmp/flyway.tar.gz.sha1 "${base}/flyway-commandline-${FLYWAY_VERSION}.tar.gz.sha1"; \
  fi; \
  echo "$(cat /tmp/flyway.tar.gz.sha1)  /tmp/flyway.tar.gz" | sha1sum -c -; \
  tar -xzf /tmp/flyway.tar.gz -C /opt; \
  ln -sf "/opt/flyway-${FLYWAY_VERSION}/flyway" /usr/local/bin/flyway; \
  rm -f /tmp/flyway.tar.gz /tmp/flyway.tar.gz.sha1


# --------------------------------------------------
# App setup
# --------------------------------------------------
WORKDIR /var/www/html
COPY php83-local/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 80
