FROM php:8.1-cli-buster
COPY "./php.ini-development" "${PHP_INI_DIR}/php.ini"

VOLUME /app
WORKDIR /app
CMD ["php", "./now.php"]