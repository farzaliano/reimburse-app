FROM php:8.1-apache

# Ganti port Apache ke 8080 biar bisa jalan non-root
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Install dependencies dan ekstensi
RUN apt-get update && apt-get install -y \
    zip unzip libzip-dev \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libldap2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install mysqli zip gd ldap

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Copy file proyek ke container
COPY . /var/www/html/

# Permission agar Apache bisa akses file
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Set working directory
WORKDIR /var/www/html

# Expose port baru
EXPOSE 8080

# Jalankan Apache di foreground
CMD ["apache2-foreground"]