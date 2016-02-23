PHP Array Adapter
-----------------

Imagine the following data source (array):

```php
$data = [
    [ 1, "string 1", 111, 10.01, true,  '2010-05-10 13:00:00' ],     // first row
    [ 2, "string 2", 222, 45.45, false, '2015-01-01 17:00:00' ],     // second row
    // ... and so on
];
```

Table definition for this data source:

```php
$table = new Table();

$table->setColumns([
    'id' => [                   // item key is the name of the column
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

Let's create ArrayAdapter for it:

```php
use DynamicTable\Adapter\ArrayAdapter;

// ...

$adapter = new ArrayAdapter();
$adapter->setData($data);           // <-- Feed our array to the adapter
// $adapter->setDbTimezone('UTC');  // Data source could be in different timezone
```

Connect data to the table:

```php
$table->setAdapter($adapter);
```

... continue with the table
