Dynamic HTML grid for PHP and NodeJS
====================================

The idea is to feed an array of data (or some other data source, like PDO or Doctrine query) to PHP/NodeJS class and to have instantly a grid with server-side sorting, filtering and pagination.

The demo version is available [here](http://demo.daemon-notes.com/dynamic-table/).

Configuration
-------------

Your project must meet the [requirements](docs/requirements.md).

Dynamic Table consists of two parts - backend and frontend:
 * Backend
   * [server-side PHP](docs/php.md) - set of PHP classes for PHP arrays, PDO, Doctrine ORM and Doctrine MongoDB ODM support
   * or [server-side NodeJS](docs/nodejs.md) - set of JavaScript classes for arrays, MySQL and PostgreSQL databases
 * and [front-side](docs/front-side.md) - jQuery plugin and AngularJS wrapper for the plugin
