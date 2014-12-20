Development environment
=======================

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

2. Install dependencies in *development* mode

  ```shell
  > cd MyProject
  > ./scripts/install-dependencies dev
  > ./scripts/build-front
  ```

  In *development* mode the first script will run composer to install *new* versions of backend components and write each component version in **composer.lock**. So in *production* mode it will read that file and install exactly these (tested by you) versions, not the newest ones.

  In *dev* mode the script will also install latest **node** and **bower** components, so you need node.js and npm on the development server.

  The second script will compile all the **bower** modules into **vendor.min.js/css** which you should include in your ZF template.

3. Create local (ignored by git) config files

  Read the [README](../config/autoload/README.md) if you don't know the difference between local/global config files.

  ```shell
  > cd MyProject/config/autoload
  > cp local.php.dist local.php
  > cp memcached.local.php.dist memcached.local.php
  ```
  **NOTE**: If you don't use Memcached simply do not create memcached.local.php. No cache will be used.

  Now edit the newly created files, choose your DB driver, credentials, set other parameters.

4. Create and populate the database

  Database schemas are in **database** directory. Consult [README](../database/README.md) there for specific commands on creating the database.

5. Run development server (do not use in production)

  ```shell
  > cd MyProject
  > ./scripts/dev-server
  ```

  This will run PHP web server on port 8000 of localhost.

Adding new front-end dependency
-------------------------------

1. Add the appropriate line to "require" section of **bower.json**.

2. Run **scripts/install-dependecies** in *dev* mode.

3. Now find .js and .css files of the dependency in **bower_components** and add them to **Gruntfile.js**.

4. Run **scripts/build-front** script to create new **vendor.js** and **vendor.css** files.

5. Keep vendor.js/.css files under git control so you will have them ready to use in production (you need to compile them in development environment only).

Production environment
======================

Installation
------------

1. Create project directory:

  ```shell
  > git clone https://github.com/your-login/my-project.git MyProject
  ```

2. Install dependencies in *production* mode

  ```shell
  > cd MyProject
  > ./scripts/install-dependencies prod
  ```

  You need php only here, no node.js or npm on production server.

3. Create local (ignored by git) config files

  Read the [README](../config/autoload/README.md) if you don't know the difference between local/global config files.

  ```shell
  > cd MyProject/config/autoload
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
    DocumentRoot /path/to/MyProject/public
    <Directory /path/to/MyProject/public>
      DirectoryIndex index.php
      AllowOverride All
      Order allow,deny
      Allow from all
    </Directory>
  </VirtualHost>
  ```

Updating production server
--------------------------

In order to update the production server all you need is:

```shell
> cd MyProject
> git pull
> ./scripts/install-dependencies prod
```
