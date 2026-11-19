FROM php:8.3-apache

# Enable apache modules (SSL, headers, rewrite)
RUN a2enmod ssl headers rewrite && a2dissite 000-default

# Copy configurations
COPY docker/php.ini /usr/local/etc/php/conf.d/glc.ini
COPY docker/apache-glc.conf /etc/apache2/sites-enabled/glc.conf
COPY certs/ /etc/apache2/certs/
COPY portal-intranet/ /var/www/html/
COPY scripts/ /opt/glc/scripts/

# Create data directories
RUN mkdir -p /data/db /data/logs /data/sessions /data/docs /data/config \
    && chown -R www-data:www-data /data /var/www/html \
    && chmod -R 775 /data

ENV GLC_DATA=/data

EXPOSE 80 443
