# Stage 1: Build the frontend files
FROM node:latest as frontend

WORKDIR /app

# # Install Node.js and npm
# RUN apt-get update && apt-get install -y nodejs npm

COPY package*.json ./

RUN npm install

COPY . .

RUN npm run build:dev

# Stage 2: Build Laravel backend
FROM php:8.2-fpm as backend

WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP dependencies
# TODO: Check if this is mandatory
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    && docker-php-ext-install zip pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY . .
COPY --from=frontend /app/public ./public

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer update
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist

# EXPOSE 8000

ENTRYPOINT ["./docker_entrypoint.sh"]
