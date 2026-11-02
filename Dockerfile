# =====================================================================
# Dockerfile — Backend PHP 8.2 + Apache
# Dự án: QuanLyCuaHangVai (DO_AN_PTWEB)
# =====================================================================

FROM php:8.2-apache

# --- Cài đặt PHP extensions cần thiết ---
RUN apt-get update && apt-get install -y \
        libzip-dev \
        unzip \
        curl \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# --- Cài đặt Composer ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Cấu hình Apache: bật mod_rewrite ---
RUN a2enmod rewrite

# --- Copy toàn bộ source backend ---
COPY backend/ /var/www/html/

# --- Đặt Document Root vào backend/public/ ---
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/public>|g' \
    /etc/apache2/apache2.conf \
    || true

# --- Thêm cấu hình AllowOverride cho .htaccess (nếu có) ---
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# --- Cài Composer dependencies (nếu composer.json tồn tại) ---
WORKDIR /var/www/html
RUN if [ -f composer.json ]; then \
        composer install --no-dev --optimize-autoloader --no-interaction; \
    fi

# --- Cấp quyền ghi cho thư mục storage ---
RUN mkdir -p /var/www/html/storage/logs \
             /var/www/html/storage/mail_logs \
             /var/www/html/storage/cache \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

EXPOSE 80
