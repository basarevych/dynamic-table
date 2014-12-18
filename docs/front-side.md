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
        row_id_column: 'id',
        mapper: mapper,
    });
</script>
```

Full list of options
--------------------
* **url**: string

  Backend script URL

* **row_id_column**: string

  ID column name. If null there will be no row selectors

* **mapper**: function

  A function that will transform input (row received from backend) to resulting row which will be .html()'ed to corresponding table &lt;td&gt;

* **sort_column**: string

  Initial sorting column. Could be null (table will not be sorted on start)

* **sort_dir**: 'asc' | 'desc'

  Initial sorting direction

* **page_number**: integer

  Initial page number

* **page_size**: number

  Initial page size

* **page_sizes**: array of integers

  Page sizes available to choose from. 0 is 'all the rows on single page'

* **table_class**: string

  CSS classes of the table

* **loader_image**: string

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

Methods
-------

* **getSelected()**

  This method will return array of IDs of selected rows.

* **refresh(params)**

  Without parameters **refresh()** will simply refresh the page.

  **params** is an object with any of the following properties:

  * **filters**

    Example:

    ```js
    table.refresh({
        filters: {
            foo: {
                like: 'bar',
                null: true
            },
            bar: {
                between: [3, 5]
            },
            baz: {
                equal: 7
            }
        }
    });
    ```

    The code above will refresh the page while requesting the following filters:

      * Set filter FILTER_LIKE to 'bar' string on column **foo**
      * Enable FILTER_NULL on column **foo**
      * Set filter FILTER_BETWEEN to range from 3 to 5 on column **bar**
      * Set filter FILTER_EQUAL to 7 on column **baz**

  * **sort_column** and **sort_dir**

    Example:

    ```js
    table.refresh({ sort_column: 'foo', sort_dir: 'desc' });
    ```

    The code above will refresh the page with sorting set to **foo** column, descending.

  * **page_number**

    Example:

    ```js
    table.refresh({ page_number: 3 });
    ```

    The code above will load page 3.

  * **page_size**

    Example:

    ```js
    table.refresh({ page_size: 200, page_number: 1 });
    ```

    The code above will set page size to 200 rows and load page 1.

* **enable(flag)**

  Enable (flag == true) or disable (flag == false) the table.

* **setSize(size)**

  Set page size. Will call **refrsh()** with appropriate parameters.

* **setPage(page)**

  Set page number. Will call **refresh()** with appropriate parameters.

* **setFilters(id, filters)**

  Apply **filters** to column **id**. Will call **refresh()** with appropriate parameters.

* **toggleSort(id)**

  Will set sorting to column **id**, ascending. If the sort column was already **id** then sort direction will be set to descending.

* **toggleColumn(id)**

  Toggle visibility of column **id**.

* **toggleSelected(rowId)**

  Toggle *selected* status of row **rowId**.

Events
------

* **dt.loading**

  Triggered when table starts to load the page.

* **dt.loaded**

  Triggered when table has loaded and rendered the page.

* **dt.selected**

  Triggered when user selects a row.

* **dt.deselected**

  Triggered when user deselects a row.

