# Gunakan image PHP + Apache
FROM php:8.2-apache

# Salin semua file ke dalam container
COPY . /var/www/html/

# Install ekstensi MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Izinkan Rewrite (jika pakai .htaccess)
RUN a2enmod rewrite

# Expose port default web server
EXPOSE 80
