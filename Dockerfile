# Image officielle PHP + Apache
FROM php:8.2-apache

# Copier le code dans le dossier web Apache
COPY . /var/www/html/

# Appliquer les bons droits
RUN chown -R www-data:www-data /var/www/html

# Exposer le port Apache
EXPOSE 80
