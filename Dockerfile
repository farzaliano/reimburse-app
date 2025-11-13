FROM php:8.1-apache

RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

RUN apt-get update && apt-get install -y \
    zip unzip libzip-dev \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    libldap2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install mysqli zip gd ldap

RUN a2enmod rewrite

# Copy project
COPY . /var/www/html/

# Pastikan folder uploads ada
RUN mkdir -p /var/www/html/uploads

# Set permission folder agar Apache bisa nulis
RUN chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 775 /var/www/html/uploads

WORKDIR /var/www/html
EXPOSE 8080
CMD ["apache2-foreground"]
