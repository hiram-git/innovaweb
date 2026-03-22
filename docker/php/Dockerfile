# ─── Base: PHP 8.3 FPM ───────────────────────────────────────────────────────
FROM php:8.3-fpm-alpine AS base

# Dependencias del sistema necesarias para extensiones PHP
RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    unzip \
    zip

# Extensiones PHP necesarias para Laravel + SQL Server (sqlsrv en producción Linux)
# NOTA: sqlsrv requiere Microsoft ODBC Driver 18 instalado en el host/contenedor.
# Para Linux se usa el driver de Microsoft desde packages.microsoft.com
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        bcmath \
        pcntl \
        gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# sqlsrv para Linux (requiere ODBC Driver en el host)
# Descomentar si se despliega con acceso a SQL Server Linux/Azure:
# RUN apk add --no-cache unixodbc-dev \
#     && pecl install sqlsrv pdo_sqlsrv \
#     && docker-php-ext-enable sqlsrv pdo_sqlsrv

# Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Configuración PHP para producción
COPY docker/php/php.ini /usr/local/etc/php/conf.d/innovaweb.ini

# ─── Dependencias del proyecto ───────────────────────────────────────────────
FROM base AS deps

WORKDIR /var/www/html/backend

COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ─── Imagen final ─────────────────────────────────────────────────────────────
FROM base AS production

WORKDIR /var/www/html/backend

# Copiar vendor ya instalado
COPY --from=deps /var/www/html/backend/vendor vendor/

# Copiar código fuente
COPY backend/ .

# Permisos correctos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# PHP-FPM escucha en socket Unix para mayor rendimiento con Nginx
EXPOSE 9000

CMD ["php-fpm"]
