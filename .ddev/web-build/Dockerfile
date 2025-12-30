# Custom Dockerfile additions for DDEV web container
# Installs Java 17, Maven, and Flyway for zmsapiautomation tests

# Install Java 17 (Eclipse Temurin)
RUN wget -O - https://packages.adoptium.net/artifactory/api/gpg/key/public | gpg --dearmor | tee /etc/apt/trusted.gpg.d/adoptium.gpg > /dev/null \
 && echo "deb [signed-by=/etc/apt/trusted.gpg.d/adoptium.gpg] https://packages.adoptium.net/artifactory/deb $(awk -F= '/^VERSION_CODENAME/{print $2}' /etc/os-release) main" | tee /etc/apt/sources.list.d/adoptium.list \
 && apt-get update && apt-get install -y temurin-17-jdk \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure JAVA_HOME and PATH for all shells
RUN echo "export JAVA_HOME=/usr/lib/jvm/temurin-17-jdk-$(dpkg --print-architecture)" > /etc/profile.d/java.sh \
 && echo "export PATH=\"\${JAVA_HOME}/bin:\${PATH}\"" >> /etc/profile.d/java.sh \
 && chmod +x /etc/profile.d/java.sh

# Create symlinks for java and javac in /usr/local/bin
RUN JAVA_HOME="/usr/lib/jvm/temurin-17-jdk-$(dpkg --print-architecture)" \
 && update-alternatives --install /usr/bin/java java "${JAVA_HOME}/bin/java" 1 \
 && update-alternatives --install /usr/bin/javac javac "${JAVA_HOME}/bin/javac" 1

# Install Maven 3.9.6
RUN wget https://dlcdn.apache.org/maven/maven-3/3.9.6/binaries/apache-maven-3.9.6-bin.tar.gz -P /tmp \
 && tar xf /tmp/apache-maven-3.9.6-bin.tar.gz -C /opt \
 && mv /opt/apache-maven-3.9.6 /opt/maven \
 && rm /tmp/apache-maven-3.9.6-bin.tar.gz \
 && ln -s /opt/maven/bin/mvn /usr/local/bin/mvn

# Install Flyway CLI 9.22.3
RUN wget -qO- https://repo1.maven.org/maven2/org/flywaydb/flyway-commandline/9.22.3/flyway-commandline-9.22.3-linux-x64.tar.gz | tar xvz -C /opt \
 && mv /opt/flyway-9.22.3 /opt/flyway \
 && ln -s /opt/flyway/flyway /usr/local/bin/flyway

