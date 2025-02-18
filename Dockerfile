# Mon docker File

# Utiliser une image de base officielle PHP avec Apache
FROM php:8.2-apache

USER root

RUN docker-php-ext-install pdo_mysql

# Définir le répertoire de travail
WORKDIR /var/www

# Créer le dossier "project"
RUN mkdir project

# Copier le projet dans le conteneur
COPY . project

# Copier la configuration d'Apache (le fichier .conf) dans le répertoire sites-available
COPY vhosts.conf /etc/apache2/sites-enabled

# Activer le module rewrite d'Apache
# RUN a2enmod rewrite

# Donner les bonnes permissions à public
# chown -R www-data:www-data /var/www/project && 
RUN chmod -R 777 /var/www/

# Activer le site Apache
# RUN a2ensite vhosts.conf

# Redémarrer Apache
RUN /etc/init.d/apache2 restart

EXPOSE 80
