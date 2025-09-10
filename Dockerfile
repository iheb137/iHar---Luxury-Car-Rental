# Utiliser l'image officielle de PHP avec le serveur Apache
FROM php:8.2-apache

# Définir le répertoire de travail dans le conteneur
WORKDIR /var/www/html

# Copier tout le code de votre projet dans le répertoire du serveur web
COPY . /var/www/html/

# Exposer le port 80 pour le trafic web
EXPOSE 80
