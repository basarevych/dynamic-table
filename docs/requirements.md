Requirements
------------

**NOTE**: DynamicTable contains both PHP files and JS/CSS files which should match each other.
          Because of this DynamicTable is distributed via composer only (and not via bower).

This project does not add any depency, but your must include this:

1. Server-side dependencies

```
"basarevych/dynamic-table": "0.1.*"
```

If you use Doctrine ORM or Doctrine MongoDB ODM add to your composer.json the appropriate
dependencies. Follow your PHP framework guides for specific composer modules.

For example, for Zend Framework 2 add:

```
"doctrine/doctrine-orm-module": "0.8.*",
"doctrine/doctrine-mongo-odm-module": "0.8.*",
```

2. Front-side dependencies:

  * jQuery
  * moment.js
  * bootstrap
  * eonasdan datetime picker

Add to your bower.json:

```
"jquery": "~2.1.3",
"moment": "~2.10.2",
"bootstrap": "~3.3.4",
"eonasdan-bootstrap-datetimepicker": "~4.7.14"
```

And the page should include the js and css files of DynamicTable:
* vendor/basarevych/dynamic-table/dist/jquery.dynamic-table.min.js
* vendor/basarevych/dynamic-table/dist/jquery.dynamic-table.min.css

Include AngularJS wrapper (if you use Angular) in addition to the above:
* vendor/basarevych/dynamic-table/dist/angularjs.dynamic-table.min.js
