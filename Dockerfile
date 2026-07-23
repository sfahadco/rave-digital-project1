ARG BASE_IMAGE=base

FROM php:8.2-fpm-bookworm AS base

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y --no-install-recommends pdo-pgsql && pecl install redis

WORKDIR /var/www/html

FROM composer:2 AS composer