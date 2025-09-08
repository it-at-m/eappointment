FROM php:8.3-apache

# Set timezone
ENV TZ=Europe/Berlin
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# —————————————————————————————————————————
# 1. Build args & user
# —————————————————————————————————————————
ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=1000

RUN groupadd --gid $USER_GID $USERNAME \
 && useradd -m -u $USER_UID -g $USER_GID -s /bin/bash $USERNAME

# —————————————————————————————————————————
# 2. Install system deps & PHP extensions
# —————————————————————————————————————————
RUN apt-get update && apt-get install -y \
      git curl unzip make wget \
      libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
      libicu-dev libonig-dev libzip-dev \
      python3-click python3-git python3-colorama \
      mariadb-client \
      # you'll install Node.js separately below
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl gd pdo pdo_mysql zip \
    && apt-get clean

# —————————————————————————————————————————
# 3. Enable Apache modules you need
# —————————————————————————————————————————
RUN a2enmod rewrite proxy_fcgi alias headers ssl

# —————————————————————————————————————————
# 3b. Generate a self-signed SSL certificate for development
# —————————————————————————————————————————
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/apache-selfsigned.key \
  -out /etc/ssl/certs/apache-selfsigned.crt \
  -subj "/C=DE/ST=Dev/L=Dev/O=Dev/OU=Dev/CN=localhost"

# —————————————————————————————————————————
# 3c. Copy and enable SSL VirtualHost config
# —————————————————————————————————————————
COPY apache-ssl.conf /etc/apache2/sites-available/default-ssl.conf
RUN a2ensite default-ssl

# —————————————————————————————————————————
# 4. Install Node.js 18
# —————————————————————————————————————————
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
 && apt-get install -y nodejs

# —————————————————————————————————————————
# 5. Install Composer
# —————————————————————————————————————————
RUN curl -sS https://getcomposer.org/installer | php \
 && mv composer.phar /usr/local/bin/composer

# —————————————————————————————————————————
# 6. Work in /var/www/html, set permissions
# —————————————————————————————————————————
WORKDIR /var/www/html

# Ensure Apache's user can read/write your app
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html

# —————————————————————————————————————————
# 7. Expose & default is already apache2-foreground
# —————————————————————————————————————————
EXPOSE 80

# (the php:8.0-apache image's default CMD will launch apache2-foreground)
