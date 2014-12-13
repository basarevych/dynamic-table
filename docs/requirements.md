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
