FROM php:7.2-cli

RUN mkdir -m 777 /tmp/.composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /tmp/.composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer global require hirak/prestissimo && chmod 777 -R /tmp/.composer