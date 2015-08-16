Requirements
------------

**NOTE**: DynamicTable contains both backend and frontend files which should match each other.
          Because of this DynamicTable is distributed via server-side tools only (Composer or NPM) and not via bower.

This project does not add any depency, but your project must include this:

1. PHP: Server-side dependencies (composer)

  ```
    "require": {
        "basarevych/dynamic-table": "0.2.*"
    }
  ```

  If you use Doctrine ORM or Doctrine MongoDB ODM add to your composer.json the appropriate
  dependencies. Follow your PHP framework guides for specific composer modules.

  For example, if you are going to use Zend Framework 2 with Doctrine ORM add:

  ```
    "require": {
        "doctrine/doctrine-orm-module": "0.9.*"
    }
  ```

  For Zend Framework 2 and Doctrine Mongo ODM add:

  ```
    "require": {
        "doctrine/doctrine-mongo-odm-module": "0.9.*"
    }
  ```

2. NodeJS: Server-side dependencies (npm)

  If you need MySQL support add to your package.json:

  ```
    "dependencies": {
      "mysql": "~2.8.0",
    }
  ```

  If you need PostgreSQL support add:

  ```
    "dependencies": {
      "pg": "~4.4.1",
    }
  ```

3. Front-side dependencies (bower):

  You will need in your project:

  * jQuery
  * moment.js
  * bootstrap
  * eonasdan datetime picker

  Add to your bower.json:

  ```
    "devDependencies": {
        "jquery": "~2.1.4",
        "moment": "~2.10.6",
        "bootstrap": "~3.3.5",
        "eonasdan-bootstrap-datetimepicker": "~4.15.35"
    }
  ```

  And your HTML page should include the JS and CSS files of DynamicTable:

  * If your backend is PHP include these files:

    * vendor/basarevych/dynamic-table/dist/jquery.dynamic-table.min.js
    * vendor/basarevych/dynamic-table/dist/jquery.dynamic-table.min.css

  * Or NodeJS, include the following:

    * node_modules/dynamic-table/dist/jquery.dynamic-table.min.js
    * node_modules/dynamic-table/dist/jquery.dynamic-table.min.css

  You should also include AngularJS wrapper (if you use Angular) in addition to the above:

  * vendor/basarevych/dynamic-table/dist/angularjs.dynamic-table.min.js

  Or:

  * node_modules/dynamic-table/dist/angularjs.dynamic-table.min.js

