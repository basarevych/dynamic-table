PHP Doctrine Adapter
--------------------

The entity we will create DynamicTable for: [Application\Entity\Sample](https://github.com/basarevych/dynamic-table/blob/demo-zf2/module/Application/src/Application/Entity/Sample.php)

Here is the table:

```php
$table = new Table();

$table->setColumns([
    'id' => [
        'sql_id'    => 's.id',
        'title'     => 'ID',
        'type'      => Table::TYPE_INTEGER,
        'filters'   => [ Table::FILTER_EQUAL ],
        'sortable'  => true,
        'visible'   => false,
    ],
    'string' => [
        'sql_id'    => 's.value_string',
        'title'     => 'String',
        'type'      => Table::TYPE_STRING,
        'filters'   => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'integer' => [
        'sql_id'    => 's.value_integer',
        'title'     => 'Integer',
        'type'      => Table::TYPE_INTEGER,
        'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'float' => [
        'sql_id'    => 's.value_float',
        'title'     => 'Float',
        'type'      => Table::TYPE_FLOAT,
        'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'boolean' => [
        'sql_id'    => 's.value_boolean',
        'title'     => 'Boolean',
        'type'      => Table::TYPE_BOOLEAN,
        'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'datetime' => [
        'sql_id'    => 's.value_datetime',
        'title'     => 'DateTime',
        'type'      => Table::TYPE_DATETIME,
        'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
]);
```

Let's create DoctrineORMAdapter.

**NOTE** DoctrineORMAdapter requires additional property on the column - **sql_id**. This is how Doctrine refers to an entity property. For example, we **select()**ed entity **s** in QueryBuilder, then its properties are named like "s.property_name", i.e. "s.value_string" is property "value_string" of entity "s".

```php
use DynamicTable\Adapter\DoctrineORMAdapter;

// ...

// $em is Doctrine EntityManager
$qb = $em->createQueryBuilder();
$qb->select('s')
   ->from('Application\Entity\Sample', 's');

$adapter = new DoctrineORMAdapter();
$adapter->setQueryBuilder($qb);
```

Connect data to the table:

```php
$table->setAdapter($adapter);
```

... continue with the table
