# Use an official PHP runtime as a parent image
FROM php:8-apache

# Install PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libidn11-dev \
    libicu-dev && \
    docker-php-ext-install pdo pdo_mysql intl && \
    rm -rf /var/lib/apt/lists/* 

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the application source code
COPY www/css /var/www/html/css
COPY www/robots.txt /var/www/html/
COPY link/index.php /var/www/html/
COPY templates /templates
COPY lmtp.php myfuncs.php marketingtext_echo.php composer.json /var/www/

# Set working directory and permissions
WORKDIR /var/www
RUN composer install --no-scripts 

# Configure Apache
COPY security.conf /etc/apache2/conf-available/security.conf
RUN a2enconf security && a2enmod rewrite

# Expose port 80
EXPOSE 80

# Use the custom Apache runtime configuration
CMD ["apache2-foreground"]

