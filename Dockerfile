
# Utiliser limage officielle de PHP avec le serveur Apache
FROM php:8.2-apache

# Définir le répertoire de travail dans le conteneur
WORKDIR /var/www/html

# Copier notre fichier de configuration Apache personnalisé pour remplacer celui par défaut
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Copier tout le contenu du projet
COPY . .

# Sassurer que lutilisateur Apache (www-data) peut lire les fichiers
RUN chown -R www-data:www-data /var/www/html

# Exposer le port 80 pour le trafic web
EXPOSE 80

