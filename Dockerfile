FROM php:8.1-apache

# Installer seulement ce qui n’est pas déjà activé
RUN docker-php-ext-install mysqli

# Copier ton code
COPY . /var/www/html/

# Mettre les bons droits
RUN chown -R www-data:www-data /var/www/html

# Exposer le port
EXPOSE 80
