# Utiliser l'image officielle de PHP avec le serveur Apache
FROM php:8.2-apache

# Définir le répertoire de travail dans le conteneur
WORKDIR /var/www/html

# CORRECTION : Copier le contenu du sous-dossier, pas le dossier parent.
COPY iHar---Luxury-Car-Rental/ .

# S'assurer que l'utilisateur Apache (www-data) peut lire les fichiers.
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80 pour le trafic web
EXPOSE 80
