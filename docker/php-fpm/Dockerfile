#FROM php:fpm-alpine
#Explicitly using 7 for now to prevent weird bugs until Symfony fixes them.

FROM php:7-fpm-alpine

COPY wait-for-it.sh /usr/bin/wait-for-it

RUN chmod +x /usr/bin/wait-for-it

RUN apk --update --no-cache add git

RUN docker-php-ext-install pdo_mysql

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

CMD composer install ; wait-for-it database:3306 -- bin/console doctrine:migrations:migrate ;  php-fpm 

RUN apk add bash

RUN apk add make

RUN apk add rsync

EXPOSE 9000
