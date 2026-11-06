[![phpunit|phpstan|cs-fixer](https://github.com/danilovl/final-work-system/actions/workflows/phpunit.yml/badge.svg)](https://github.com/danilovl/final-work-system/actions/workflows/phpunit.yml)
[![cypress](https://github.com/danilovl/final-work-system/actions/workflows/cypress.yml/badge.svg)](https://github.com/danilovl/final-work-system/actions/workflows/cypress.yml)
[![playwright](https://github.com/danilovl/final-work-system/actions/workflows/playwright.yml/badge.svg)](https://github.com/danilovl/final-work-system/actions/workflows/playwright.yml)

FinalWork web application
========================

Thesis management system based on Symfony 7.2

Demonstration of the system from the student's perspective.

https://github.com/user-attachments/assets/af8f4974-0edf-427f-8500-b405aef98d8e

Demonstration of the system from the supervisor's perspective.

https://github.com/user-attachments/assets/f351cfd2-2f8e-4583-8d7f-0c942c45b259

Who is it for?
------------

This project is for teachers and students interested in streamlining the process of supervising final theses.
It is especially useful for teachers who supervise the final theses of several dozen students each year.

Requirements
------------

* PHP 8.4 or higher
* MySQL
* Redis
* RabbitMQ
* Elasticsearch
* Composer
* NPM
* Mercure
* Minio
* Kibana
* Or you can use Docker

Features
------------

* Administration panel
* Management of users (students, opponents, consultants)
* Thesis management
* Assigning tasks to students
* Scheduling meetings by the supervisor
* Uploading and downloading draft and final versions of theses
* Chat communication between the supervisor and others
* Sending bulk messages to users
* Email notifications about new events in the system
* Multiple languages
* An API that covers most of the data
* Unit tests
* Cypress tests
* and many other features

Project uses extra bundles
------------
* [API Platform](https://github.com/api-platform/api-platform) - API Platform is a next-generation web framework designed to easily create API-first projects
* [EasyAdmin](https://github.com/EasyCorp/EasyAdminBundle) - EasyAdmin creates administration backends.
* [KnpMenuBundle](https://github.com/KnpLabs/KnpMenuBundle) - The KnpMenuBundle integrates the KnpMenu PHP library with Symfony
* [KnpMarkdownBundle](https://github.com/KnpLabs/KnpMarkdownBundle) - Provides Markdown conversion (based on Michel Fortin's work) to your Symfony projects
* [RedisBundle](https://github.com/snc/SncRedisBundle) - This bundle integrates Predis and phpredis into your Symfony application
* [OverblogGraphQLBundle](https://github.com/overblog/GraphQLBundle) - This Symfony bundle provides integration with GraphQL
* [OverblogGraphiQLBundle](https://github.com/overblog/GraphiQLBundle) - This Symfony bundle provides integration of the GraphiQL interface into your Symfony application
* [Doctrine Behavioral Extensions](https://github.com/Atlantic18/DoctrineExtensions) - This package contains extensions that help use Doctrine more efficiently
* [RabbitMqBundle](https://github.com/php-amqplib/RabbitMqBundle) - Incorporates messaging in your application via RabbitMQ using the php-amqplib library
* [FOSElasticaBundle](https://github.com/FriendsOfSymfony/FOSElasticaBundle) - This bundle provides integration with Elasticsearch and Elastica

Project uses custom bundles
------------
* [AsyncBundle](https://github.com/danilovl/async-bundle) - Symfony bundle that provides simple delayed function calls
* [CacheResponseBundle](https://github.com/danilovl/cache-response-bundle) - Symfony bundle provides caching of controller responses
* [DoctrineEntityDtoBundle](https://github.com/danilovl/doctrine-entity-dto-bundle) - The Symfony bundle provides a simple mechanism to convert Doctrine entities to DTO objects
* [HashidsBundle](https://github.com/danilovl/hashids-bundle) - Integrates hashids/hashids in a Symfony project
* [ObjectToArrayTransformBundle](https://github.com/danilovl/object-to-array-transform-bundle) - Symfony bundle provides conversion of an object to an array based on configured fields
* [ParameterBundle](https://github.com/danilovl/parameter-bundle) - Symfony bundle provides convenient access to parameters from the config
* [PermissionMiddlewareBundle](https://github.com/danilovl/permission-middleware-bundle) - Symfony bundle provides a simple mechanism to control permissions for a class or its methods
* [RenderServiceTwigExtensionBundle](https://github.com/danilovl/render-service-twig-extension-bundle) - Symfony Twig extension bundle provides rendering of service methods
* [SelectAutocompleterBundle](https://github.com/danilovl/select-autocompleter-bundle) - Symfony bundle provides simple integration with Select2
* [Symfony console input validation](https://github.com/danilovl/symfony-console-input-validation) - Provides a simple method for adding input validation to Symfony console commands
* [TranslatorBundle](https://github.com/danilovl/translator-bundle) - Symfony bundle provides simple management of system translations

ERD diagram
------------

Higher resolution: [git/readme/erd_diagram.png](/git/readme/erd_diagram.png)

![Alt text](/git/readme/erd_diagram_small.png?raw=true "ERD diagram")

Test coverage
------------

![Alt text](/git/readme/test_coverage_application.png?raw=true "Test coverage application")
![Alt text](/git/readme/test_coverage_infrastructure.png?raw=true "Test coverage infrastructure")

Kibana
------------

This project uses the OpenTelemetry API SDK, manually configured with hooks.

![Alt text](/git/readme/kibana.png?raw=true "Kibana")

Swagger
------------

Interactive API documentation and the OpenAPI specification are available in this project.

![Alt text](/git/readme/swagger.png?raw=true "Swagger")

Environment configuration
------------

You can change the configuration in the following files:

```text
file: .env or .env.local
```
Configure the database connection, SMTP, and other connections:

``` env
DATABASE_URL=mysql://root:@mysql:3306/final_work_system
MESSENGER_TRANSPORT_DSN=amqp://guest:guest@rabbitmq:5672
ELASTICSEARCH_URL=http://elasticsearch:9200/
MAILER_DSN=smtp://user:pass@localhost:25
REDIS_HOST=redis
REDIS_PORT=6379
```

Google API keys:

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
Install all the necessary PHP dependencies:

```bash
$ bin/composer-first-install.sh
```

Other available commands
------------

Create user

```bash
$ bin/console app:user-add
```
or you can import test data from dump.sql

```bash
$ bin/console app:import-sql docker/mysql/data/dump.sql
```

Docker
------------

Run Docker:

```bash
docker-compose -f docker-compose.yml up -d
```

Run the command inside the Node container:

```bash
docker-compose exec -T node sh bin/npm-first-install.sh
```

Run the command inside the PHP container:

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
