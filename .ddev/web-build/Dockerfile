# Custom Dockerfile additions for DDEV web container
# Installs Java 21, Maven, and Flyway for zmsapiautomation tests

# Set timezone at OS level (matches dev container)
ENV TZ=Europe/Berlin
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install Java 21 (Eclipse Temurin LTS)
RUN wget -O - https://packages.adoptium.net/artifactory/api/gpg/key/public | gpg --dearmor | tee /etc/apt/trusted.gpg.d/adoptium.gpg > /dev/null \
 && echo "deb [signed-by=/etc/apt/trusted.gpg.d/adoptium.gpg] https://packages.adoptium.net/artifactory/deb $(awk -F= '/^VERSION_CODENAME/{print $2}' /etc/os-release) main" | tee /etc/apt/sources.list.d/adoptium.list \
 && apt-get update && apt-get install -y temurin-21-jdk \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure JAVA_HOME and PATH for all shells
RUN echo "export JAVA_HOME=/usr/lib/jvm/temurin-21-jdk-$(dpkg --print-architecture)" > /etc/profile.d/java.sh \
 && echo "export PATH=\"\${JAVA_HOME}/bin:\${PATH}\"" >> /etc/profile.d/java.sh \
 && chmod +x /etc/profile.d/java.sh

# Create symlinks for java and javac in /usr/local/bin
RUN JAVA_HOME="/usr/lib/jvm/temurin-21-jdk-$(dpkg --print-architecture)" \
 && update-alternatives --install /usr/bin/java java "${JAVA_HOME}/bin/java" 1 \
 && update-alternatives --install /usr/bin/javac javac "${JAVA_HOME}/bin/javac" 1

# Install Maven 3.9.9 with checksum verification
ARG MAVEN_VERSION=3.9.9
RUN wget -q https://archive.apache.org/dist/maven/maven-3/${MAVEN_VERSION}/binaries/apache-maven-${MAVEN_VERSION}-bin.tar.gz -P /tmp \
 && wget -q https://archive.apache.org/dist/maven/maven-3/${MAVEN_VERSION}/binaries/apache-maven-${MAVEN_VERSION}-bin.tar.gz.sha512 -P /tmp \
 && echo "$(cat /tmp/apache-maven-${MAVEN_VERSION}-bin.tar.gz.sha512)  /tmp/apache-maven-${MAVEN_VERSION}-bin.tar.gz" | sha512sum -c - \
 && tar xf /tmp/apache-maven-${MAVEN_VERSION}-bin.tar.gz -C /opt \
 && mv /opt/apache-maven-${MAVEN_VERSION} /opt/maven \
 && rm /tmp/apache-maven-${MAVEN_VERSION}-bin.tar.gz /tmp/apache-maven-${MAVEN_VERSION}-bin.tar.gz.sha512 \
 && ln -s /opt/maven/bin/mvn /usr/local/bin/mvn

# Install Flyway CLI 9.22.3 with checksum verification
ARG FLYWAY_VERSION=9.22.3
RUN wget -q -O /tmp/flyway.tar.gz https://repo1.maven.org/maven2/org/flywaydb/flyway-commandline/${FLYWAY_VERSION}/flyway-commandline-${FLYWAY_VERSION}-linux-x64.tar.gz \
 && wget -q -O /tmp/flyway.tar.gz.sha1 https://repo1.maven.org/maven2/org/flywaydb/flyway-commandline/${FLYWAY_VERSION}/flyway-commandline-${FLYWAY_VERSION}-linux-x64.tar.gz.sha1 \
 && echo "$(cat /tmp/flyway.tar.gz.sha1)  /tmp/flyway.tar.gz" | sha1sum -c - \
 && tar xzf /tmp/flyway.tar.gz -C /opt \
 && mv /opt/flyway-${FLYWAY_VERSION} /opt/flyway \
 && rm /tmp/flyway.tar.gz /tmp/flyway.tar.gz.sha1 \
 && ln -s /opt/flyway/flyway /usr/local/bin/flyway

