FROM php:8.3-apache

RUN rm -rf /var/www/html/*
WORKDIR /var/www/html/

# Install PHP modules
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install coveragemap
COPY . .
