# Use the official PHP image with Apache
FROM php:8.1-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install zip \
    && a2enmod rewrite

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set the working directory to the root of your Apache directory
WORKDIR /var/www/html

# Copy the application source code
COPY . /var/www/html

# Suppress the Apache server name warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN composer install --no-interaction

# Verify that the vendor directory is created
RUN ls -al

# Expose port 80 to access the Apache server
EXPOSE 80

