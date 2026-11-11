# ==============================================================================
# Dockerfile - Sistem Informasi Bank Mini (PHP 8.2 Apache + MySQL InnoDB + Composer)
# Digunakan untuk lingkungan belajar siswa RPL & Pipeline CI/CD Lokal (0 Kuota Cloud)
# ==============================================================================
FROM php:8.2-apache

# Pasang dependensi sistem dan ekstensi PHP wajib (pdo_mysql, bcmath, zip, gd untuk PDF/Excel/Word)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql bcmath zip gd \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Arahkan DocumentRoot Apache langsung ke folder public/ (Single Entry Point)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Salin binary Composer resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html/penyimpanan

EXPOSE 80

