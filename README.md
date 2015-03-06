WORK IN PROGRESS
================

Dynamic Table
=============

The idea is to feed an array of data (or some other data source, like Doctrine query) to PHP class and to have instantly a grid with server-side sorting, filtering and pagination.

The demo version is available [here](http://demo.daemon-notes.com/dynamic-table/).

This repository has demo-zf2 branch which is demo project to this project (see [IndexController](https://github.com/basarevych/dynamic-table/blob/demo-zf2/module/Application/src/Application/Controller/IndexController.php#L26) and its [view script](https://github.com/basarevych/dynamic-table/blob/demo-zf2/module/Application/view/application/index/index.phtml)).

Configuration
-------------

Your project must meet the [requirements](docs/requirements.md).

Dynamic Table consists of two parts:
 * [server-side](docs/server-side.md) - set of PHP classes for PHP arrays, Doctrine ORM and Doctrine MongoDB ODM support
 * and [front-side](docs/front-side.md) - jQuery plugin and AngularJS wrapper for the plugin
