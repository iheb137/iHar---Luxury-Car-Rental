# Utiliser une image de base officielle qui inclut Apache et PHP 8.2
FROM php:8.2-apache

# Installer les extensions PHP nécessaires pour se connecter à une base de données MySQL.
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copier le code de l'application dans le dossier web par défaut d'Apache
COPY . /var/www/html/

# Appliquer les bons droits pour permettre à Apache d'écrire si nécessaire
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80, qui est le port par défaut pour Apache
EXPOSE 80
