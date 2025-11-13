FROM php:8.1-apache

# Ganti port Apache ke 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Install ekstensi yang dibutuhkan
RUN apt-get update && apt-get install -y \
    zip unzip libzip-dev \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libldap2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install mysqli zip gd ldap

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Copy semua file proyek
COPY . /var/www/html/

# Set permission untuk OpenShift random UID
RUN mkdir -p /var/www/html/upload \
    && chgrp -R 0 /var/www/html \
    && chmod -R g+rwX /var/www/html

WORKDIR /var/www/html

EXPOSE 8080
CMD ["apache2-foreground"]
