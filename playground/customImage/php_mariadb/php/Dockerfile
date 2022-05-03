FROM php:8.1-apache-buster

RUN docker-php-ext-install pdo_mysql

# ユーザ設定
RUN adduser php && chown -R php /var/www/html
USER php

VOLUME /var/www/html
EXPOSE 8080
