FROM php:8.4-fpm

ARG USER_ID=1000
ARG GROUP_ID=1000

# Modifier l'UID et le GID de www-data pour correspondre à ceux de l'utilisateur hôte
RUN usermod -u ${USER_ID} www-data \
    && groupmod -g ${GROUP_ID} www-data

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Installer les extensions PHP nécessaires à Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers du projet
COPY . .

COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

#Pourquoi `entrypoint` et pas `command` ?
#RUN  → au build, avant les volumes
#ENTRYPOINT      → au démarrage du container, après les volumes