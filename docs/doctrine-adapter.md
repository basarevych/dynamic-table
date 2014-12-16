Doctrine Adapter
----------------

An entity we will create DynamicTable for: (Application\Entity\Sample)[https://github.com/basarevych/dynamic-table-demo/blob/master/module/Application/src/Application/Entity/Sample.php]

Here is the table:

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

Let's create DoctrineAdapter for it:

```php
use DynamicTable\Adapter\DoctrineAdapter;

// ...

// $em is Doctrine EntityManager
$qb = $em->createQueryBuilder();
$qb->select('s')
   ->from('Application\Entity\Sample', 's');

$adapter = new DoctrineAdapter();
$adapter->setQueryBuilder($qb);
```

The last step is to create a mapper of source data row to resulting array row. Output format is [ 'column-id' => $cell_value, ... ].

```php
$mapper = function ($row) { // $row in this case is Query result item,
                            // i.e. Sample entity instance
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
