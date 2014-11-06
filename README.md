WORK IN PROGRESS
================

ZF2 Skeleton
============

Yet another Zend Framework 2 Skeleton Application.

Features:
* Doctrine integration - [docs](docs/doctrine.md)
* Memcached integration - [docs](docs/memcached.md)
* Sample console controller - [docs](docs/console.md)
* Translator preconfigured - [docs](docs/translator.md)
* Mail service - [docs](docs/mail.md)
* Custom Error Strategy - [docs](docs/error-strategy.md)
* Session - TODO
* DynamicTable - TODO

Installation
------------
1. Fork or clone

2. Install dependencies

  ```shell
  > cd ProjectRoot
  > ./scripts/install-dependecies dev
  ```

  Replace "dev" argument with "prod" for production environment.

3. Create server-local configs

  Read the [README](config/autoload/README.md) if you don't know the difference between local/global config files.

  ```shell
  > cd config/autoload
  > cp local.php.dist local.php
  > cp memcached.local.php.dist memcached.local.php
  ```
  **NOTE**: If you don't use Memcached simply do not create memcached.local.php. No cache will be used.

  Now edit **local.php**, choose your DB driver, credentials, set other parameters. Edit **memcached.local.php** also.

4. Create and populate the database

  Database schema (MySQL only at this time) is in **database** directory. Consult README file for specific commands.

5. Run development server (do not use in production)

  ```shell
  > cd ProjectRoot
  > ./scripts/dev-server
  ```

  This will run PHP web server on port 8000.
