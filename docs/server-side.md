Server-side
-----------

Server-side classes are framework agnostic but I use them personally with ZF2.

You create an instance of main class (DynamicTable\Table) and one of "data adapters". Currently there are two adapters available: DynamicTable\Adapter\ArrayAdapter and DynamicTable\Adapter\DoctrineAdapter.

```php
$table = new \DynamicTable\Table();

$table->setColumns([
    'id' => [
        'title'     => 'ID',
        'type'      => \DynamicTable\Table::TYPE_INTEGER,
        'filters'   => [ \DynamicTable\Table::FILTER_EQUAL ],
        'sortable'  => false,
        'visible'   => false,
    ],
    // ... other columns here
]);
```

Table::setColumns expects an array of column definitions.
