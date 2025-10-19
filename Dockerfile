# Use a specific version for reproducibility
FROM php:8.3-fpm-alpine AS symfony_php

# Set working directory
WORKDIR /app

# Install system dependencies required by Symfony and common extensions
# Using apk --no-cache reduces image size
RUN apk add --no-cache \
    acl \
    fcgi \
    file \
    gettext \
    git \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    zip

# Install PHP extensions
# Using docker-php-ext-install is the recommended way
RUN docker-php-ext-install -j$(nproc) \
    intl \
    pdo_pgsql \
    zip

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Prevent Composer from prompting for user input
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy essential files to leverage Docker cache
COPY composer.json composer.lock symfony.lock ./

# --- FIX IS HERE ---
# Install ALL dependencies, including dev requirements, for local development.
# We remove the --no-dev flag.
RUN composer install --prefer-dist --no-scripts --no-progress

# Copy the rest of the application code
COPY . .

# --- AND HERE ---
# Run composer scripts (like warming up the cache) without the --no-dev flag
RUN composer dump-autoload --classmap-authoritative
RUN composer run-script post-install-cmd

# Set permissions for var and public directories
# This is crucial for Symfony to be able to write to cache/logs
RUN chown -R www-data:www-data var public
RUN chmod +x bin/console

# The default command for php-fpm is already set in the base image

