#!/usr/bin/env bash

chmod 777 -R public
chmod 777 -R var

composer install --no-interaction
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:schema:update --force
php bin/console doctrine:migrations:sync-metadata-storage
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console assets:install public
php bin/console cache:clear
php bin/console cache:warmup
php bin/console app:import-sql docker/mysql/data/dump.sql
php bin/console fos:elastica:populate
php bin/console app:s3-create-buckets

exec "$@"
