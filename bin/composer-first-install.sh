#!/usr/bin/env bash

composer install --no-interaction
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:schema:update --force
php bin/console doctrine:migrations:sync-metadata-storage
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console assets:install public
php bin/console cache:clear
php bin/console cache:warmup
php bin/console rabbitmq:setup-fabric
php bin/console fos:elastica:populate

exec "$@"