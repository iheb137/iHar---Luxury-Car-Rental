# Utiliser l'image officielle de PHP avec le serveur Apache
FROM php:8.2-apache

# Définir le répertoire de travail dans le conteneur
WORKDIR /var/www/html

# Copier tout le contenu du répertoire de travail (qui est maintenant la racine du projet)
COPY . .

# S'assurer que l'utilisateur Apache (www-data) peut lire les fichiers.
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80 pour le trafic web
EXPOSE 80
