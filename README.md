FinalWork Web Application
========================

Thesis management system based on Symfony Framework 3.4 

Requirements
------------

  * PHP 7.3.0 or higher
  * MySql
  * Redis server
  * Composer
  * Npm/bower

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
   
Who is it for?
------------

This project is for teachers and students whose interest is to streamline the process of leading the final work. 
This is especially true for those teachers who carry out the final theses of several dozen students a year.
   
Project uses extra bundles
------------

* [SonataCoreBundle](https://github.com/sonata-project/SonataCoreBundle) -  Symfony SonataCoreBundle.
* [SonataAdminBundle](https://github.com/sonata-project/SonataAdminBundle) - AdminBundle - The missing Symfony2 Admin Generator
* [SonataUserBundle](https://github.com/sonata-project/SonataUserBundle) - Symfony SonataUserBundle.
* [IvoryCKEditorBundle](https://github.com/egeloen/IvoryCKEditorBundle) - CKEditor integration in Symfony.
* [HashidsBundle](https://github.com/roukmoute/HashidsBundle) - Integrates hashids/hashids in a Symfony project.
* [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle) - Adds support for a database-backed user system.
* [KnpMenuBundle](https://github.com/KnpLabs/KnpMenuBundle) - The KnpMenuBundle integrates the KnpMenu PHP library with Symfony.
* [KnpMarkdownBundle](https://github.com/KnpLabs/KnpMarkdownBundle) - Provide markdown conversion (based on Michel Fortin work) to your Symfony projects.
* [RedisBundle](https://github.com/snc/SncRedisBundle) - This bundle integrates Predis and phpredis into your Symfony application.
* [OverblogGraphQLBundle](https://github.com/overblog/GraphQLBundle) - This Symfony bundle provides integration of GraphQL.
* [OverblogGraphiQLBundle](https://github.com/overblog/GraphiQLBundle) - This Symfony bundle provides integration of GraphiQL interface to your Symfony application.
* [Doctrine Behavioral Extensions](https://github.com/Atlantic18/DoctrineExtensions) - This package contains extensions tools to use Doctrine more efficiently.

Installation
------------

Configure the database connection and SMTP in the file:

```text
path: app/config/parametrs.yml 
```
``` yaml
parameters:
    database_host:  YOUR DATABASE HOST
    database_port:  YOUR DATABASE PORT
    database_name:  YOUR DATABASE NAME
    database_user:  YOUR DATABASE USER
    database_password:  YOUR DATABASE PASSWORD
    mailer_transport:  YOUR MAILER TRANSPORT
    mailer_host:  YOUR MAILER HOST
    mailer_user:  YOUR MAILER USER
    mailer_password:  YOUR MAILER PASSWORD
    secret: YOUR SECRET RANDOM KEY
    redis_host:  YOUR REDIS HOST
    redis_port:  YOUR REDIS PORT
``````

Configure the google api keys:

``` text
path: app/config/config.yml 
```
``` yaml
parameters:
    google_maps_key:  YOUR GOOGLE MAP KEY
    google_analytics_code: YOUR GOOGLE ANALYTICS CODE
```

Enable\Disable email notifications:

```text
path: app/config/config.yml 
```
``` yaml
parameters:
    email_notification_subscriber:
        sender: YOUR SENDER EMAIL 
        enable: true or false
```

Install all the necessary dependencies by Composer:

```bash
$ composer install
```

Install all the necessary css and js dependencies by Bower:

```bash
$ bower install
```

Set language for tinymce plugin(temporary problems):

```bash
copy folder setup/langs => web/vendor/tinymce
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
$ bin/console doctrine:database:import setup/sql.sql
```

Generating assets:

```bash
$ bin/console assetic:dump
$ bin/console assets:install web
```

Create admin user:

```bash
$ bin/console fos:user:create --super-admin LOGIN EMAIL PASSWORD
```

Clear cache:

```bash
$ bin/console cache:clear
```

MIT License
-----------

FinalWork application is completely free and released under the [MIT License](https://github.com/danilovl/finalwork/LICENSE).

Authors
-------

Created by [Vladimir Danilov](https://github.com/danilovl).