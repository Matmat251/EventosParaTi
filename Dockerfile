# ================================================================
#  Dockerfile — EventosParaTi
#  Imagen personalizada PHP 8.1 + Apache para la plataforma de
#  venta de tickets de conciertos y eventos en Peru.
#
#  Construccion:
#    docker build -t eventosparati/app:latest .
#
#  Uso con Docker Compose:
#    docker-compose up -d --build
# ================================================================

# ── Etapa 1: Imagen base ─────────────────────────────────────────
FROM php:8.1-apache

# Metadatos del proyecto
LABEL maintainer="Equipo EventosParaTi <contacto@eventosparati.pe>"
LABEL version="1.0"
LABEL description="Plataforma web de venta de tickets EventosParaTi"

# ── Etapa 2: Instalar extensiones PHP necesarias ─────────────────
# mysqli     → Conexion a MySQL (backend PHP)
# pdo        → Abstraccion de base de datos
# pdo_mysql  → Driver PDO para MySQL
# mbstring   → Manejo de strings multibyte (tildes, UTF-8)
# zip        → Compresion (para generacion de tickets ZIP/PDF)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install \
        mysqli \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ── Etapa 3: Habilitar modulos de Apache ─────────────────────────
# rewrite → Permite URLs limpias (mod_rewrite)
# headers → Control de cabeceras HTTP (CORS, seguridad)
RUN a2enmod rewrite headers

# ── Etapa 4: Configuracion personalizada de Apache ───────────────
# Apuntar el DocumentRoot a la carpeta frontend del proyecto
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/frontend|g' \
    /etc/apache2/sites-available/000-default.conf

# Permitir .htaccess en el directorio del proyecto
RUN sed -i 's|AllowOverride None|AllowOverride All|g' \
    /etc/apache2/apache2.conf

# ── Etapa 5: Configuracion de PHP personalizada ───────────────────
# Crear php.ini optimizado para produccion
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/php.ini \
    && echo "post_max_size = 12M" >> /usr/local/etc/php/php.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/php.ini \
    && echo "memory_limit = 128M" >> /usr/local/etc/php/php.ini \
    && echo "display_errors = Off" >> /usr/local/etc/php/php.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/php.ini \
    && echo "error_log = /var/log/apache2/php_errors.log" >> /usr/local/etc/php/php.ini

# ── Etapa 6: Copiar el codigo fuente ─────────────────────────────
WORKDIR /var/www/html
COPY . .

# ── Etapa 7: Permisos correctos ───────────────────────────────────
# www-data es el usuario de Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 644 /var/www/html/frontend/*.html \
    && find /var/www/html/backend -name "*.php" -exec chmod 644 {} \;

# ── Etapa 8: Verificacion de la imagen ───────────────────────────
# Confirmar que las extensiones PHP estan correctamente instaladas
RUN php -m | grep -E "mysqli|pdo|mbstring" \
    && echo "✅ Extensiones PHP verificadas correctamente"

# Puerto expuesto
EXPOSE 80

# Healthcheck: verifica que Apache responde correctamente
HEALTHCHECK --interval=30s --timeout=10s --start-period=15s --retries=3 \
    CMD curl -f http://localhost/ || exit 1
