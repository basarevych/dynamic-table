WORK IN PROGRESS
================

Dynamic Table
=============

The idea is to feed an array of data (or some other data source, like Doctrine query) to PHP class and to have instantly a grid with server-side sorting, filtering and pagination.

The demo version is available [here](http://demo.daemon-notes.com/dynamic-table/).

Demo ZF2 project on github: [dynamic-table-demo](https://github.com/basarevych/dynamic-table-demo) (see [IndexController](https://github.com/basarevych/dynamic-table-demo/blob/master/module/Application/src/Application/Controller/IndexController.php#L25) and its [view script](https://github.com/basarevych/dynamic-table-demo/blob/master/module/Application/view/application/index/index.phtml)).

Configuration
-------------

Your project must meet the [requirements](docs/requirements.md).

Dynamic Table consists of two parts:
 * [server-side](docs/server-side.md) - set of PHP classes
 * and [front-side](docs/front-side.md) - jQuery plugin
