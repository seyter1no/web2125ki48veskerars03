FROM php:8.0-cli

WORKDIR /usr/src/app

COPY . .

CMD [ "php", "-S", "0.0.0.0:10000", "-t", "." ]