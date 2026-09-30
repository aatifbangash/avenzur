# Use the official PHP image with Apache
FROM php:7.4.3-apache

# Stage 1: Use the official Composer image to get the Composer executable
FROM composer:2 AS composer-stage

# Stage 2: Build the web application image
FROM php:7.4.3-apache

# Copy the Composer binary from the first stage into the final image
COPY --from=composer-stage /usr/bin/composer /usr/bin/composer

# Point to Debian archive for Buster repositories
RUN sed -i 's|http://deb.debian.org/debian|http://archive.debian.org/debian|g' /etc/apt/sources.list \
    && sed -i 's|http://security.debian.org/debian-security|http://archive.debian.org/debian-security|g' /etc/apt/sources.list \
    && sed -i '/stretch-updates/d' /etc/apt/sources.list

# Disable the Release file expiration check
RUN echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/99no-check-valid-until

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo_mysql mysqli

# Enable Apache mod_rewrite and headers
RUN a2enmod rewrite headers

# Set the working directory
WORKDIR /var/www/html/avenzur

# Set recommended permissions
RUN chown -R www-data:www-data /var/www/html

# Copy your php.ini file
COPY ./php.ini /usr/local/etc/php/

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Expose port 80
EXPOSE 80