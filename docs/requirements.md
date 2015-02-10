Requirements
------------

Your final web page must include latest versions of:
 * jQuery
 * moment.js
 * bootstrap
 * eonasdan datetime picker

Add dependencies to your bower.json:

```
"jquery": "~2.1.3",
"moment": "~2.9.0",
"bootstrap": "~3.3.2",
"eonasdan-bootstrap-datetimepicker": "~4.0.0"
```

DynamicTable itself is not distributed via bower, use composer to install it. Here is "require" section of composer.json:

```
"basarevych/dynamic-table": "dev-master"
```

And the page should include the js and css files of DynamicTable:
 * vendor/basarevych/dynamic-table/dist/jquery.dynamic-table.min.js
 * vendor/basarevych/dynamic-table/dist/jquery.dynamic-table.min.css

Include AngularJS wrapper (if you use Angular) in addition to the above:
 * vendor/basarevych/dynamic-table/dist/angularjs.dynamic-table.min.js
