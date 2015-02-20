Server-side
-----------

Server-side classes are framework agnostic but I use them personally with ZF2.

Create an instance of main class (DynamicTable\Table) and one of "data adapters". Currently there are two adapters available: DynamicTable\Adapter\ArrayAdapter and DynamicTable\Adapter\DoctrineAdapter.

First thing to do is to define the table (we use Table::setColumns() to describe table columns):

```php
use DynamicTable\Table;

// ...

$table = new Table();

$table->setColumns([
    'id' => [
        'title'     => 'ID',
        'type'      => Table::TYPE_INTEGER,
        'filters'   => [ Table::FILTER_EQUAL ],
        'sortable'  => false,
        'visible'   => false,
    ],
    // ... other columns here
]);
```

Table::setColumns expects an array of column definitions:
* Array item key is column ID
* 'title' is the title (goes to &lt;th&gt;)
* 'type' is one of the following:
  * Table::TYPE_STRING - cell value is string
  * Table::TYPE_INTEGER - cell value is integer
  * Table::TYPE_FLOAT - cell value is float
  * Table::TYPE_BOOLEAN - cell value is boolean
  * Table::TYPE_DATETIME - cell value is DateTime object
* 'filters' is combination (array) of enabled filters for this column:
  * Table::FILTER_LIKE - similar to SQL LIKE filter
  * Table::FILTER_EQUAL - leave cells with specific values only
  * Table::FILTER_BETWEEN - cell values between 'start' and 'end'
  * Table::FILTER_NULL - include NULLs to filtered dataset
* 'sortable' - true if table could be sorted by this column or not
* 'visible' - true if column should be visible from the beginning

See [createTable()](https://github.com/basarevych/dynamic-table/blob/demo-zf2/module/Application/src/Application/Controller/IndexController.php#L94) method of demo page.

Now that we have a table it's time to connect it with the data. You do this by creating a *data adapter*. At the moment there are two available:
* read about [ArrayAdapter](array-adapter.md)
* or [DoctrineAdapter](doctrine-adapter.md)

Back to our table, the last thing to do is to parse jQuery plugin's GET query and return the data.

Two GET requests are made by the jQuery plugin:
* First time the plugin is created it will run '?query=describe' request to the server.
* Each time the table is refreshed it will request '?query=data'.

Here is framework-agnostic code to handle these requests:

```php
$query = @$_GET['query'];
switch ($query) {
case 'describe':
    $data = $table->describe();
    break;
case 'data':
    $data = $table->setPageParams($_GET)->fetch();
    break;
default:
    throw new \Exception('Unknown query type: ' . $query);
}

$data['success'] = true;

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
```

See [arrayTableAction()](https://github.com/basarevych/dynamic-table/blob/demo-zf2/module/Application/src/Application/Controller/IndexController.php#L67) and [doctrineTableAction()](https://github.com/basarevych/dynamic-table/blob/demo-zf2/module/Application/src/Application/Controller/IndexController.php#L40) methods of demo page for ZF2 examples.
