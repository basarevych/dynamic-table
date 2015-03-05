WORK IN PROGRESS
================

ZF2 Skeleton
============

Yet another Zend Framework 2 Skeleton Application and a little tutorial (see the docs below).

Requires:
* PHP 5.4+
* PDO extension and doctrine/doctrine-orm-module module
* Mongo extension and doctrine/doctrine-mongo-odm-module

Features:
* Tools used: Composer for backend, Bower/Grunt for frontend, deployment scripts
* Doctrine SQL ORM integration
* Doctrine Mongo ODM integration
* Memcached integration
* Translator preconfigured
* Mail service
* Custom Error/Exception Strategy
* Sessions
* Example of regular and console controllers

Read bundled [Documentation](http://basarevych.github.io/zf2-skeleton).

Installation
============

```shell
> git clone https://github.com/basarevych/zf2-skeleton
> cd zf2-skeleton
> ./scripts/install
```

This is all you need for production installation. Development environment installation will also require this (in addition to the snippet above):

```shell
> ./scripts/update dev
> ./scripts/build-front
```

Web server document root is either **public.prod** (production) or **public.dev** (development).

```
<VirtualHost *:80>
    ServerName my-project.example.com
    DocumentRoot /path/to/MyProject/public.prod
    <Directory /path/to/MyProject/public.prod>
        DirectoryIndex index.php
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```
