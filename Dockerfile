FROM php:8.2-apache

# Cài mysqli + pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql