WORK IN PROGRESS
================

ZF2 Skeleton
============

Yet another Zend Framework 2 Skeleton Application and a little tutorial (see the docs below).

Features:
* Tools used: Composer/Bower/Grunt - [docs](docs/tools.md)
* Doctrine integration - [docs](docs/doctrine.md)
* Memcached integration - [docs](docs/memcached.md)
* Sample console controller - [docs](docs/console.md)
* Translator preconfigured - [docs](docs/translator.md)
* Mail service - [docs](docs/mail.md)
* Custom Error Strategy - [docs](docs/error-strategy.md)
* Session - [docs](docs/session.md)

Installation
------------
1. You can create a repo for your project like this:

  First create an empty repository on github (your-login/my-project) and then:

  ```shell
  > mkdir MyProject
  > cd MyProject
  > git init
  > git remote add skeleton https://github.com/basarevych/zf2-skeleton.git
  > git pull skeleton master
  > git remote add origin https://github.com/your-login/my-project.git
  > git push -u origin master
  ```

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

  Database schema (MySQL only at this time) is in **database** directory. Consult [README](database/README.md) file for specific commands.

5. Setup your webserver

  ```
  <VirtualHost *:80>
    ServerName zf2-skeleton.example.com
    DocumentRoot /path/to/zf2-skeleton/public
    <Directory /path/to/zf2-skeleton/public>
        DirectoryIndex index.php
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>
  </VirtualHost>
  ```

  Or run development server (do not use in production)

  ```shell
  > cd ProjectRoot
  > ./scripts/dev-server
  ```

  This will run PHP web server on port 8000.

6. Run PHP unit tests (requires *development* environment dependencies)

  ```shell
  > cd ProjectRoot
  > ./scripts/test-backend
  ```
