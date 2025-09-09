# Utiliser une image PHP avec Apache
FROM php:8.1-apache

# Installer l'extension MySQL pour PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Copier le projet dans le dossier web d’Apache
COPY . /var/www/html/

# Donner les bons droits
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80
EXPOSE 80
