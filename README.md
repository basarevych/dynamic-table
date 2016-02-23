Dynamic HTML grid for PHP and NodeJS
====================================

The idea is to feed an array of data or a database query to PHP/NodeJS class and to have instantly a grid with server-side sorting, filtering and pagination.

Online demo is available [here](http://demo.daemon-notes.com/dynamic-table/).

Look at [work/](work/) subdirectory for demo projects


Configuration
-------------

Your project must meet the [requirements](docs/requirements.md).

Dynamic Table consists of two parts - backend and frontend:
 * Backend
   * [server-side PHP](docs/php.md) - set of PHP classes for PHP arrays, PDO, Doctrine ORM and Doctrine MongoDB ODM support
   * or [server-side NodeJS](docs/nodejs.md) - set of JavaScript classes for arrays, MySQL and PostgreSQL databases
 * Frontend
   * [jQuery plugin](docs/front-side-plugin.md) - the main plugin
   * and, if you need it, [AngularJS wrapper](docs/front-side-wrapper.md) for the plugin

Changelog
---------

**1.0.0**
 * Backend data mapper now receives values of TYPE_DATETIME type columns as DateTime (PHP) or moment.js (NodeJS) objects.
 * Adapters now have optional setDbTimezone() method, which specifies the time zone of data source
