DynamicTable demo page (ZF2)
============================

This is DynamicTable project demo application.

Installation
------------

1. Create project directory:

  ```shell
  > git clone https://github.com/basarevych/dynamic-table-demo
  ```

2. Install dependencies in *production* mode

  ```shell
  > cd dynamic-table-demo
  > ./scripts/install-dependencies prod
  ```

  You need php only here, no node.js or npm on production server.

3. Create local (ignored by git) config files

  Read the [README](../config/autoload/README.md) if you don't know the difference between local/global config files.

  ```shell
  > cd dynamic-table-demo/config/autoload
  > cp local.php.dist local.php
  > cp memcached.local.php.dist memcached.local.php
  ```
  **NOTE**: If you don't use Memcached simply do not create memcached.local.php. No cache will be used.

  Now edit the newly created files, choose your DB driver, credentials, set other parameters.

4. Create and populate the database

  Database schemas are in **database** directory. Consult [README](../database/README.md) there for specific commands on creating the database.

5. Create production mode web server configs.

  ```
  <VirtualHost *:80>
    ServerName my-project.example.com
    DocumentRoot /path/to/dynamic-table-demo/public
    <Directory /path/to/dynamic-table-demo/public>
      DirectoryIndex index.php
      AllowOverride All
      Order allow,deny
      Allow from all
    </Directory>
  </VirtualHost>
  ```
