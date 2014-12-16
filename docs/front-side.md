Front-side
----------

A sample table:

```php
$table = new Table();

$table->setColumns([
    'id' => [
        'title'     => 'ID',
        'type'      => Table::TYPE_INTEGER,
        'filters'   => [ Table::FILTER_EQUAL ],
        'sortable'  => true,
        'visible'   => false,
    ],
    'string' => [
        'title'     => 'String',
        'type'      => Table::TYPE_STRING,
        'filters'   => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'integer' => [
        'title'     => 'Integer',
        'type'      => Table::TYPE_INTEGER,
        'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'float' => [
        'title'     => 'Float',
        'type'      => Table::TYPE_FLOAT,
        'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'boolean' => [
        'title'     => 'Boolean',
        'type'      => Table::TYPE_BOOLEAN,
        'filters'   => [ Table::FILTER_EQUAL, Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'datetime' => [
        'title'     => 'DateTime',
        'type'      => Table::TYPE_DATETIME,
        'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
]);
```

Front side is a jQuery plugin. 

```html
<div id="table1"></div>

<script>
    var mapper = function (row) {
        if (row['boolean'] != null) {   // Render 'boolean' column as icon
            row['boolean'] = '<span class="glyphicon glyphicon-'
                + (row['boolean'] ? 'ok text-success' : 'remove text-danger')
                + '"></span>';
        }
        if (row['datetime'] != null) {  // DateTime is transmitted as UNIX timestamp
            var m = moment(row['datetime'] * 1000);
            row['datetime'] = m.format('YYYY-MM-DD HH:mm:ss');
        }

        return row;
    };

    var table1 = $('#table1').dynamicTable({
        url: '/path/to/table-backend.php',
        rowIdColumn: 'id',
        mapper: mapper,
    });
</script>
```

Full list of options
--------------------
* **url**: string

  Backend script URL

* **rowIdColumn**: string

  ID column name. If null there will be no row selectors

* **mapper**: function

  A function that will transform input (row received from backend) to resulting row which will be .html()'ed to corresponding table &lt;td&gt;

* **sortColumn**: string

  Initial sorting column. Could be null (table will not be sorted on start)

* **sortDir**: 'asc' | 'desc'

  Initial sorting direction

* **pageNumber**: integer

  Initial page number

* **pageSize**: number

  Initial page size

* **pageSizes**: array of integers

  Page sizes available to choose from. 0 is 'all the rows on single page'

* **tableClass**: string

  CSS classes of the table

* **loaderImage**: string

  Spinner image, initially 'img/loader.gif'
 
* **strings**: object

  Text strings used by the plugin

  ```js
    var strings = {
        BANNER_LOADING: 'Loading... Please wait',
        BANNER_EMPTY: 'Nothing found',
        BUTTON_PAGE_SIZE: 'Page size',
        BUTTON_COLUMNS: 'Columns',
        BUTTON_REFRESH: 'Refresh',
        BUTTON_OK: 'OK',
        BUTTON_CLEAR: 'Clear',
        BUTTON_CANCEL: 'Cancel',
        TITLE_FILTER_WINDOW: 'Filter',
        LABEL_PAGE_OF_1: 'Page',
        LABEL_PAGE_OF_2: 'of {0}',
        LABEL_FILTER_LIKE: 'Strings like',
        LABEL_FILTER_EQUAL: 'Values equal to',
        LABEL_FILTER_BETWEEN_START: 'Values greater than or equal to',
        LABEL_FILTER_BETWEEN_END: 'Values less than or equal to',
        LABEL_FILTER_NULL: 'Include rows with empty value in this column',
        LABEL_TRUE: 'True',
        LABEL_FALSE: 'False',
        DATE_TIME_FORMAT: 'YYYY-MM-DD HH:mm:ss',
    };
  ```
