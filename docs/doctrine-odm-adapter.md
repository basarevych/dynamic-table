Doctrine Adapter
----------------

The document we will create DynamicTable for: [Application\Document\Sample](https://github.com/basarevych/dynamic-table/blob/demo-zf2/module/Application/src/Application/Document/Sample.php)

Here is the table:

```php
$table = new Table();

$table->setColumns([
    'id' => [
        'field_name'    => 'id',
        'title'         => 'ID',
        'type'          => Table::TYPE_STRING,
        'filters'       => [ Table::FILTER_EQUAL ],
        'sortable'      => true,
        'visible'       => false,
    ],
    'string' => [
        'field_name'    => 'value_string',
        'title'         => 'String',
        'type'          => Table::TYPE_STRING,
        'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
        'sortable'      => true,
        'visible'       => true,
    ],
    'integer' => [
        'field_name'    => 'value_integer',
        'title'         => 'Integer',
        'type'          => Table::TYPE_INTEGER,
        'filters'       => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable'      => true,
        'visible'       => true,
    ],
    'float' => [
        'field_name'    => 'value_float',
        'title'         => 'Float',
        'type'          => Table::TYPE_FLOAT,
        'filters'       => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable'      => true,
        'visible'       => true,
    ],
    'boolean' => [
        'field_name'    => 'value_boolean',
        'title'         => 'Boolean',
        'type'          => Table::TYPE_BOOLEAN,
        'filters'       => [ Table::FILTER_EQUAL, Table::FILTER_NULL ],
        'sortable'      => true,
        'visible'       => true,
    ],
    'datetime' => [
        'field_name'    => 'value_datetime',
        'title'         => 'DateTime',
        'type'          => Table::TYPE_DATETIME,
        'filters'       => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable'      => true,
        'visible'       => true,
    ],
]);
```

Let's create DoctrineMongoODMAdapter.

**NOTE** DoctrineMongoODMAdapter requires additional property on the column - **field_name**. This is field name of our Document.

```php
use DynamicTable\Adapter\DoctrineMongoODMAdapter;

// ...

// $dm is Doctrine DocumentManager
$qb = $dm->createQueryBuilder();
$qb->find('Application\Document\Sample');

$adapter = new DoctrineMongoODMAdapter();
$adapter->setQueryBuilder($qb);
```

The last step is to create a mapper of source data row to resulting array row. Output format is [ 'column-id' => $cell_value, ... ].

```php
$mapper = function ($row) { // $row in this case is Query result item,
                            // i.e. Sample document instance
    $datetime = $row->getValueDatetime();
    if ($datetime !== null)
        $datetime = $datetime->getTimestamp();

    return [
        'id'        => $row->getId(),
        'string'    => htmlentities($row->getValueString()), // We must escape strings!
        'integer'   => $row->getValueInteger(),
        'float'     => $row->getValueFloat(),
        'boolean'   => $row->getValueBoolean(),
        'datetime'  => $datetime,   // Transmit DateTime object as UNIX timestamp
    ];
};
```

Connect data to the table:

```php
$table->setAdapter($adapter);
$table->setMapper($mapper);
```
