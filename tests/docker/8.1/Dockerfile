FROM php:8.1

RUN apt-get update && apt-get install -y zip unzip

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

WORKDIR /var/www/html
ADD ./composer-test.json ./composer.json
RUN composer config -g repositories.aliyun composer https://mirrors.aliyun.com/composer/
RUN composer update
