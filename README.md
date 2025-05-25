[![phpunit|phpstan|cs-fixer](https://github.com/danilovl/final-work-system/actions/workflows/phpunit.yml/badge.svg)](https://github.com/danilovl/final-work-system/actions/workflows/phpunit.yml)
[![cypress](https://github.com/danilovl/final-work-system/actions/workflows/cypress.yml/badge.svg)](https://github.com/danilovl/final-work-system/actions/workflows/cypress.yml)

FinalWork web application
========================

Thesis management system based on Symfony 7.0

![Alt text](/git/readme/demo.gif?raw=true "Project example")

Who is it for?
------------

This project is for teachers and students whose interest is to streamline the process of leading the final work.
This is especially true for those teachers who carry out the final theses of several dozen students a year.

Requirements
------------

* PHP 8.3 or higher
* MySQL
* Redis
* RabbitMq
* Elasticsearch
* Composer
* NPM
* Mercure
* Minio
* Kibana
* or you can use docker

Features
------------

* Administration panel
* Management of users(student, opponent, consultant)
* Management of thesis
* Assigning tasks to students
* Scheduling of meetings by the supervisor
* Uploading unfinished/finished versions of final thesis and downloading them
* Chat communication between the supervisor and others
* Sending a bulk message to users
* Email notification of new events in the system
* Multi languages
* An API that covers most of the data
* Unit test
* Cypress test
* and more other features

Project uses extra bundles
------------

* [EasyAdmin](https://github.com/EasyCorp/EasyAdminBundle) - EasyAdmin creates administration backends.
* [KnpMenuBundle](https://github.com/KnpLabs/KnpMenuBundle) - The KnpMenuBundle integrates the KnpMenu PHP library with Symfony.
* [KnpMarkdownBundle](https://github.com/KnpLabs/KnpMarkdownBundle) - Provide markdown conversion (based on Michel Fortin work) to your Symfony projects.
* [RedisBundle](https://github.com/snc/SncRedisBundle) - This bundle integrates Predis and phpredis into your Symfony application.
* [OverblogGraphQLBundle](https://github.com/overblog/GraphQLBundle) - This Symfony bundle provides integration of GraphQL.
* [OverblogGraphiQLBundle](https://github.com/overblog/GraphiQLBundle) - This Symfony bundle provides integration of GraphiQL interface to your Symfony application.
* [Doctrine Behavioral Extensions](https://github.com/Atlantic18/DoctrineExtensions) - This package contains extensions tools to use Doctrine more efficiently.
* [RabbitMqBundle](https://github.com/php-amqplib/RabbitMqBundle) - Incorporates messaging in your application via RabbitMQ using the php-amqplib library.
* [FOSElasticaBundle](https://github.com/FriendsOfSymfony/FOSElasticaBundle) - his bundle provides integration with Elasticsearch and Elastica.
* [RenderServiceTwigExtensionBundle](https://github.com/danilovl/render-service-twig-extension-bundle) - Symfony twig extension bundle provides rendering service method.
* [ParameterBundle](https://github.com/danilovl/parameter-bundle) - Symfony bundle provides comfortable getting parameters from config.
* [PermissionMiddlewareBundle](https://github.com/danilovl/permission-middleware-bundle) - Symfony bundle provides simple mechanism control permission for class or his method.
* [HashidsBundle](https://github.com/danilovl/hashids-bundle) - Integrates hashids/hashids in a Symfony project.
* [AsyncBundle](https://github.com/danilovl/async-bundle) - Symfony bundle provides simple delayed function call.
* [SelectAutocompleterBundle](https://github.com/danilovl/select-autocompleter-bundle) - Symfony bundle provides simple integration with select2.

ERD diagram
------------

Higher resolution: [git/readme/erd_diagram.png](/git/readme/erd_diagram.png)

![Alt text](/git/readme/erd_diagram_small.png?raw=true "ERD diagram")

Test coverage
------------

![Alt text](/git/readme/test_coverage_application.png?raw=true "Test coverage application")

Configuration environment
------------

You can change configuration in the file:

```text
file: .env or .env.local
```
Configure the database connection, SMTP and other connection:

``` env
DATABASE_URL=mysql://root:@mysql:3306/final_work_system
MESSENGER_TRANSPORT_DSN=amqp://guest:guest@rabbitmq:5672
ELASTICSEARCH_URL=http://elasticsearch:9200/
MAILER_DSN=smtp://user:pass@localhost:25
REDIS_HOST=redis
REDIS_PORT=6379
``````

Google api keys:

``` env
GOOGLE_ANALYTICS_CODE=GOOGLE_ANALYTICS_CODE
GOOGLE_MAPS_KEY=GOOGLE_MAPS_KEY
```

Email notifications:

``` env
EMAIL_NOTIFICATION_SENDER=test@test.com
EMAIL_NOTIFICATION_ENABLE_SEND=true
EMAIL_NOTIFICATION_MESSENGER=true
EMAIL_NOTIFICATION_ENABLE_ADD_TO_QUEUE=true
```

Installation dependencies
------------

Install all the necessary npm dependencies:

```bash
$ bin/npm-first-install.sh
```
Install all the necessary php dependencies:

```bash
$ bin/composer-first-install.sh
```

Other available commands
------------

Create user

```bash
$ bin/console app:user-add
```
or you can import test data dump.sql

```bash
$ bin/console app:import-sql docker/mysql/data/dump.sql
```

Docker
------------

Run docker:

```bash
docker-compose -f docker-compose.yml up -d
```

Run command under node image CLI or you can docker-compose:

```bash
docker-compose exec -T node sh bin/npm-first-install.sh
```

Run command under php image CLI or you can docker-compose:

```bash
docker-compose exec -T php sh bin/composer-first-install.sh
```

Now you can access the application via [localhost:9090](localhost:9090).

MIT License
-----------

FinalWork application is completely free and released under the [MIT License](https://github.com/danilovl/finalwork/LICENSE).

Author
-------

Created by [Vladimir Danilov](https://github.com/danilovl).
