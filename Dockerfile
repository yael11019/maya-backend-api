# 🐘 Base image con PHP 8.2 y Apache
FROM php:8.2-apache

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Instala dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Limpia cache de apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia los archivos del proyecto
COPY . /var/www/html

# Ajusta permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configura Apache para usar el directorio public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

# Instala dependencias PHP de Laravel
RUN composer install --no-dev --optimize-autoloader

# Genera APP_KEY si no existe
RUN php -r "file_exists('.env') || copy('.env.example', '.env');" \
    && php artisan key:generate --force || true

# Compila configuraciones en cache (sin romper build si no hay DB aún)
RUN php artisan config:clear || true
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# Expone el puerto 80
EXPOSE 80

# 🔥 Comando de inicio: ejecuta migraciones y arranca Apache
CMD php artisan migrate --force && apache2-foreground
