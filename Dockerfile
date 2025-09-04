# Image officielle PHP + Apache
FROM php:8.2-apache

# Copier ton code dans le dossier web d’Apache
COPY . /var/www/html/

# Donner les bons droits
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80
EXPOSE 80
