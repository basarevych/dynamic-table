WORK IN PROGRESS
================

Dynamic Table
=============

The idea is to feed an array of data (or some other data source, like Doctrine query) to PHP class and to have instantly a grid with sorting, filtering and pagination.

The demo version is available [here](http://demo.daemon-notes.com/dynamic-table/).

Configuration
-------------

Dynamic Table consists of two parts:
 * [front-side](docs/front-side.md) - jQuery plugin
 * and [server-side](docs/server-side.md) - set of PHP classes

Requirements
------------

Your final web page must include latest versions of:
 * jQuery
 * moment.js
 * bootstrap
 * eonasdan datetime picker

Here is the "dependencies" section of bower.json:

```
"jquery": "~2.1.1",
"moment": "~2.8.3",
"bootstrap": "~3.1.1",
"eonasdan-bootstrap-datetimepicker": "~3.1.3"
```

DynamicTable itself is not distributed via bower, use composer to install it. Here is "require" section of composer.json:

```
"basarevych/dynamic-table": "dev-master"
```

And the page should include the js and css files of DynamicTable:
 * vendor/basarevych/dynamic-table/dist/jquery.dynamic-table.min.js
 * vendor/basarevych/dynamic-table/dist/jquery.dynamic-table.min.css
