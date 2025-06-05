FROM php:8.2-apache

# Instala mysqli
RUN docker-php-ext-install mysqli

# Opcional: habilitar mod_rewrite
RUN a2enmod rewrite
