DynamicTable demo page (ZF2)
============================

This is just a [DynamicTable](https://github.com/basarevych/dynamic-table) project demo application.

Installation
------------
1. Clone the repo

  ```shell
  > git clone https://github.com/basarevych/dynamic-table-demo
  ```

2. Install dependencies

  ```shell
  > cd dynamic-table-demo
  > ./scripts/install-dependecies prod
  ```

  Replace "prod" (production) argument with "dev" for development environment.

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
    ServerName dynamic-table-demo.example.com
    DocumentRoot /path/to/dynamic-table-demo/public
    <Directory /path/to/dynamic-table-demo/public>
      DirectoryIndex index.php
      AllowOverride All
      Order allow,deny
      Allow from all
    </Directory>
  </VirtualHost>
  ```

  Or run development server (do not use in production)

  ```shell
  > cd dynamic-table-demo
  > ./scripts/dev-server
  ```

  This will run PHP web server on port 8000.

6. Run PHP unit tests (requires *development* environment dependencies)

  ```shell
  > cd dynamic-table-demo
  > ./scripts/test-backend
  ```
