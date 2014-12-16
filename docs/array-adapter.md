Array Adapter
-------------

Imagine this is our data:

```php
$data = [
    [ 1, "string 1", 111, 10.01, true,  new \DateTime('2010-05-10 13:00:00') ],
    [ 2, "string 2", 222, 45.45, false, new \DateTime('2015-01-01 17:00:00') ],
    // ... and so on
];
```

We created a table for it:

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

Let's create ArrayAdapter for it:

```php
use DynamicTable\Adapter\ArrayAdapter;

// ...

$adapter = new ArrayAdapter();
$adapter->setData($data);       // <-- Feed our array to the adapter
```

The last step is to create a mapper of source data row to resulting array row. Output format is [ 'column-id' => $cell_value, ... ].

```php
$mapper = function ($row) {
    return [
        'id'        => $row[0],
        'string'    => htmlentities($row[1]),       // We should escape strings!
        'integer'   => $row[2],
        'float'     => $row[3],
        'boolean'   => $row[4],
        'datetime'  => $row[5]->getTimestamp(),     // Transmit DateTime cell as UNIX timestamp
    ];
};
```

For example, for the first row of our data, we will get this out of our mapper:

```php
[
    'id'        => 1,
    'string'    => "string 1",
    'integer'   => 111,
    'float'     => 10.01
    'boolean'   => true,
    'datetime'  => 1273496400,
]
```

Connect data to the table:

```php
$table->setAdapter($adapter);
$table->setMapper($mapper);
```
