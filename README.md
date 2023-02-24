# Cbr-converter

Конвертирует валюты с сайта ЦБ РФ

![preview.png](public/preview.png)

## Развёртывание

PHP 8.0, MySQL 5.7

### 1) Установка пакетов

    composer install

### 2) Конфигурация БД и миграции

Прописать параметры БД в `.env` или `.env.local`

    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"

Применить миграции

    php bin/console doctrine:migrations:migrate
