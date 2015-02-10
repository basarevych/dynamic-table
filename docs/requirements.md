Requirements
------------

Your final web page must include latest versions of:
 * jQuery
 * moment.js
 * bootstrap
 * eonasdan datetime picker

Add dependencies to your bower.json:

```
./node_modules/.bin/bower install jquery moment bootstrap eonasdan-bootstrap-datetimepicker --save
```

DynamicTable itself is not distributed via bower, use composer to install it. Here is "require" section of composer.json:

```
"basarevych/dynamic-table": "dev-master"
```

And the page should include the js and css files of DynamicTable:
 * vendor/basarevych/dynamic-table/dist/jquery.dynamic-table.min.js
 * vendor/basarevych/dynamic-table/dist/jquery.dynamic-table.min.css
