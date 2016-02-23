Server-side: PHP
================

Create an instance of main class (DynamicTable\Table) and one of "data adapters".
Currently there are four adapters available: Array, PDO, Doctrine ORM and Doctrine Mongo.

The first step is to define table columns:

```php
use DynamicTable\Table;

// ...

$table = new Table();
$table->setColumns([
    'id' => [
        'title' => 'ID',
        'sql_id' => 'id',
        'type' => Table::TYPE_INTEGER,
        'filters' => [ Table::FILTER_EQUAL ],
        'sortable' => true,
        'visible' => true,
    ],
    'name' => [
        'title' => 'Name',
        'sql_id' => 'name',
        'type' => Table::TYPE_STRING,
        'filters' => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
        'sortable' => true,
        'visible' => true,
    ],
    'email' => [
        'title' => 'Email',
        'sql_id' => 'email',
        'type' => Table::TYPE_STRING,
        'filters' => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
        'sortable' => true,
        'visible' => true,
    ],
    'created_at' => [
        'title' => 'Created at',
        'sql_id' => 'created_at',
        'type' => Table::TYPE_DATETIME,
        'filters' => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable' => true,
        'visible' => true,
    ],
    'is_admin' => [
        'title' => 'Is admin',
        'sql_id' => 'is_admin',
        'type' => Table::TYPE_BOOLEAN,
        'filters' => [ Table::FILTER_EQUAL, Table::FILTER_NULL ],
        'sortable' => true,
        'visible' => true,
    ],
]);
```

Table::setColumns expects an object defining columns:
* Item key is column ID
* 'title' is the title (goes to &lt;th&gt;)
* 'sql_id' is column name in the SQL table (not needed if array adapter is used)
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

The second step is to create a data mapper:

```php
$table->setMapper(function ($row) {
    $row['email'] = htmlentities($row['email']);                    // escape strings

    if ($row['created_at'] !== null)                                // convert DateTime object to something that could
        $row['created_at'] = $row['created_at']->getTimestamp();    // be sent over the net, i.e. UNIX timestamp

    return $row;
});
```

The data mapper is a function that accepts source data row and returns this row in a form suitable for our jQuery plugin.

**NOTE**: Columns of type TYPE_DATETIME are converted to PHP DateTime objects before being passed to the mapper.

You should at least convert Date objects into UNIX epoch values and optionally escape HTML strings.
The resulting data is then transmitted over the network to the client as JSON object.

Now that we have a table it's time to connect it with the data. You do this by creating a *data adapter*. At the moment there are four available:
* [ArrayAdapter](php-array-adapter.md)
* [PDOAdapter](php-pdo-adapter.md)
* [DoctrineORMAdapter](php-doctrine-orm-adapter.md)
* [DOctrineMongoODMAdapter](php-doctrine-odm-adapter.md)

Back to our table, the last thing to do is to parse jQuery plugin's GET query and return the data.

Two GET requests are made by the jQuery plugin:
* First time the plugin is created it will run '?query=describe' request to the server.
* Each time the table is refreshed it will request '?query=data'.

```php
// ... define table and adapter

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
