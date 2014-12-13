Server-side
-----------

Server-side classes are framework agnostic but I use them personally with ZF2.

You create an instance of main class (DynamicTable\Table) and one of "data adapters". Currently there are two adapters available: DynamicTable\Adapter\ArrayAdapter and DynamicTable\Adapter\DoctrineAdapter.

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
* 'title' is the title (goes to <th>)
* 'type' is one of the following:
  * Table::TYPE_STRING
  * Table::TYPE_INTEGER
  * Table::TYPE_FLOAT
  * Table::TYPE_BOOLEAN
  * Table::TYPE_DATETIME
* 'filters' is combination (array) of enabled filters for this column:
  * Table::FILTER_LIKE
  * Table::FILTER_EQUAL
  * Table::FILTER_BETWEEN
  * Table::FILTER_NULL
* 'sortable' - true if table could be sorted by this column or not
* 'visible' - true if column should be visible from the beginning

See [createTable()](https://github.com/basarevych/dynamic-table-demo/blob/master/module/Application/src/Application/Controller/IndexController.php#L94) method of demo page.

Now that we have a table it's time to connect it with the data. You do this by creating a *data adapter*. At the moment there are two available:
* read about [ArrayAdapter](docs/array-adapter.md)
* or [DoctrineAdapter](docs/doctrine-adapter.md)

Back to our table, the last thing to do is to parse jQuery plugin's GET query and return the data.

Two GET requests are made:
* First time the plugin is created it will run '?query=describe' request to the server.
* Each time the table is loaded it will request '?query=data'.

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

See [arrayTableAction()](https://github.com/basarevych/dynamic-table-demo/blob/master/module/Application/src/Application/Controller/IndexController.php#L67) and [doctrineTableAction()](https://github.com/basarevych/dynamic-table-demo/blob/master/module/Application/src/Application/Controller/IndexController.php#L40) methods of demo page for ZF2 examples.
