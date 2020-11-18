FinalWork Web Application
========================

Thesis management system based on Symfony Framework 5.1 

![Alt text](/gif/demo.gif?raw=true "Project example")

Requirements
------------

  * PHP 7.4.4 or higher
  * MySQL
  * Redis server
  * Composer
  * NPM

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
   
Who is it for?
------------

This project is for teachers and students whose interest is to streamline the process of leading the final work. 
This is especially true for those teachers who carry out the final theses of several dozen students a year.
   
Project uses extra bundles
------------

* [EasyAdmin](https://github.com/EasyCorp/EasyAdminBundle) - EasyAdmin creates administration backends.
* [HashidsBundle](https://github.com/roukmoute/HashidsBundle) - Integrates hashids/hashids in a Symfony project.
* [KnpMenuBundle](https://github.com/KnpLabs/KnpMenuBundle) - The KnpMenuBundle integrates the KnpMenu PHP library with Symfony.
* [KnpMarkdownBundle](https://github.com/KnpLabs/KnpMarkdownBundle) - Provide markdown conversion (based on Michel Fortin work) to your Symfony projects.
* [RedisBundle](https://github.com/snc/SncRedisBundle) - This bundle integrates Predis and phpredis into your Symfony application.
* [OverblogGraphQLBundle](https://github.com/overblog/GraphQLBundle) - This Symfony bundle provides integration of GraphQL.
* [OverblogGraphiQLBundle](https://github.com/overblog/GraphiQLBundle) - This Symfony bundle provides integration of GraphiQL interface to your Symfony application.
* [Doctrine Behavioral Extensions](https://github.com/Atlantic18/DoctrineExtensions) - This package contains extensions tools to use Doctrine more efficiently.
* [RenderServiceTwigExtensionBundle](https://github.com/danilovl/render-service-twig-extension-bundle) - Symfony twig extension bundle provides rendering service method.
* [ParameterBundle](https://github.com/danilovl/parameter-bundle) - Symfony bundle provides comfortable getting parameters from config.

Installation
------------

Configure the database connection and SMTP in the file:

```text
file: .env 
```
``` env
DATABASE_URL=mysql://username:password@host:port/final_work_system?serverVersion=5.7
MAILER_DSN=smtp://user:pass@localhost:25
REDIS_HOST='127.0.0.1'
REDIS_PORT=6379
``````

Configure the google api keys:

``` text
file: .env 
```
``` env
GOOGLE_ANALYTICS_CODE='GOOGLE_ANALYTICS_CODE'
GOOGLE_MAPS_KEY='GOOGLE_MAPS_KEY'
```

Enable\Disable email notifications:

```text
file: .env 
```
``` env
EMAIL_NOTIFICATION_SENDER='test@test.com'
EMAIL_NOTIFICATION_ENABLE_SEND=true
EMAIL_NOTIFICATION_ENABLE_ADD_TO_QUEUE=true
```

Install all the necessary dependencies by Composer:

```bash
$ composer install
```

Install all the necessary css and js dependencies by NPM:

```bash
$ npm install
```
 
Creating the database:

```bash
$ bin/console doctrine:database:create --if-not-exists
```

Creating the database tables/schema:

```bash
$ bin/console doctrine:schema:update --force
```

Import default data to database:

```bash
$ bin/console doctrine:migrations:sync-metadata-storage
$ bin/console doctrine:migrations:migrate
```

Generating assets:

```bash
$ npm run build
$ bin/console assets:install public
```

Create user:

```bash
$ bin/console app:user-add
```

Clear cache:

```bash
$ bin/console cache:clear
```

Docker
------------

Change Redis server to `redis` in `config\project\services\redis.yaml`:

``` yaml
   ....
   arguments:
    - 'redis'
```

Change DATABASE_URL in `.envl` for docker:

``` env
DATABASE_URL=mysql://final_work_system:password@mariadb:3306/final_work_system
```

Run docker:

```bash
$ docker-compose up -d
```

Run commands under docker CLI:

``` bash
$ composer install
$ npm install
$ bin/console doctrine:database:create --if-not-exists
$ bin/console doctrine:schema:update --force
$ bin/console doctrine:migrations:sync-metadata-storage
$ bin/console doctrine:migrations:migrate
$ npm run build
$ bin/console assets:install public
$ bin/console app:user-add
$ bin/console cache:clear

```

Now you can access the application via [localhost:9090](localhost:9090).

MIT License
-----------

FinalWork application is completely free and released under the [MIT License](https://github.com/danilovl/finalwork/LICENSE).

Author
-------

Created by [Vladimir Danilov](https://github.com/danilovl).