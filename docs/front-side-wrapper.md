Front-side - AngularJS wrapper for the plugin
=============================================

1. Include 'dynamicTable' dependency to your app:

  ```js
    var app = angular.module('app', [ 'dynamicTable' ]);
  ```

2. Configure the service (optional):

  ```js
    app.config(
        [ 'dynamicTableProvider',
        function (dynamicTableProvider) {
            dynamicTableProvider.setTranslationFilter('foobar');
        } ]
    );
  ```

  By default dynamicTable text strings are all English. You can translate them to another language by specifying *translation filter*.

  If you use angular-translate replace 'foobar' with 'translate', and if you use angular-globalize-wrapper set it to 'glMessage'.

  Add the strings to your translation system:

  ```json
    "DT_BANNER_LOADING": "Loading... Please wait",
    "DT_BANNER_EMPTY": "Nothing found",
    "DT_BUTTON_PAGE_SIZE": "Page size",
    "DT_BUTTON_COLUMNS": "Columns",
    "DT_BUTTON_REFRESH": "Refresh",
    "DT_BUTTON_OK": "OK",
    "DT_BUTTON_CLEAR": "Clear",
    "DT_BUTTON_CANCEL": "Cancel",
    "DT_TITLE_FILTER_WINDOW": "Filter",
    "DT_LABEL_CURRENT_PAGE": "Current page",
    "DT_LABEL_ALL_PAGES": "All pages",
    "DT_LABEL_PAGE_OF_1": "Page",
    "DT_LABEL_PAGE_OF_2": "of #",
    "DT_LABEL_FILTER_LIKE": "Strings like",
    "DT_LABEL_FILTER_EQUAL": "Values equal to",
    "DT_LABEL_FILTER_BETWEEN_START": "Values greater than or equal to",
    "DT_LABEL_FILTER_BETWEEN_END": "Values less than or equal to",
    "DT_LABEL_FILTER_NULL": "Include rows with empty value in this column",
    "DT_LABEL_TRUE": "True",
    "DT_LABEL_FALSE": "False",
    "DT_DATE_TIME_FORMAT": "YYYY-MM-DD HH:mm:ss"
  ```

3. Instantiate table controller with the help of the **dynamicTable** service:

  ```js
    app.controller('angularCtrlName',
        ['$scope', 'dynamicTable',
        function($scope, dynamicTable) {
            $scope.tableCtrl = dynamicTable({
                url: '/table.php',
                row_id_column: 'id',
                sort_column: 'id',
                mapper: function (row) {
                    if (row['boolean'] != null) {
                        row['boolean'] = '<i class="glyphicon '
                            + (row['boolean'] ? 'glyphicon-ok text-success' : 'glyphicon-remove text-danger')
                            + '"></i>';
                    }
                    if (row['datetime'] != null) {
                        var m = momenti.unix(row['datetime']).local();      // convert from UTC UNIX timestamp
                        row['datetime'] = m.format('YYYY-MM-DD HH:mm:ss');  // to browser timezone string
                    }

                    return row;
                },
            });
  ```

  **dynamicTable** service returns a function which expects jQuery plugin parameters object as its argument.

4. Use **dynamic-table** directive in your template:

  ```html
    <div id="my-table" dynamic-table="tableCtrl"></div>
  ```

5. Watch for plugin events if you need to:

  ```js
    $scope.$watch('tableCtrl.event', function () {
        switch ($scope.tableCtrl.event) {
            case 'loading':     console.log('Table is loading'); break;
            case 'loaded':      console.log('Table has been loaded'); break;
            case 'selected':    console.log('Row selected'); break;
            case 'deselected':  console.log('Row deselected'); break;
        }
    });
  ```

6. You can get the plugin instance and use it directly:

  ```js
    var thePlugin = $scope.tableCtrl.pugin;
    console.log(thePlugin.getSelected()); // Or any other plugin method
  ```
