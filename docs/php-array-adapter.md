PHP Array Adapter
-----------------

Let's fill the array:

```php
$data = [];

$dt = new \DateTime("2010-05-11 13:00:00");
for ($i = 1; $i <= 100; $i++) {
    $dt->add(new \DateInterval('PT10S'));

    if ($i == 3) {
        $data[] = [
            'id' => $i,
            'name' => null,
            'email' => null,
            'created_at' => null,
            'is_admin' => null,
        ];
    } else {
        $data[] = [
            'id' => $i,
            'name' => "User $i",
            'email' => "user$i@example.com",
            'created_at' => $dt->format('Y-m-d H:i:s'),             // as a string
            // 'created_at' => $dt->getTimestamp(),                 // or as a timestamp
            // 'created_at' => clone $dt,                           // or as a DateTime
            'is_admin' => ($i % 2 == 0),
        ];
    }
}
```

Table definition for this data source:

```php
$table = new Table();

$table->setColumns([
    'id' => [
        'title' => 'ID',
        'type' => Table::TYPE_INTEGER,
        'filters' => [ Table::FILTER_EQUAL ],
        'sortable' => true,
        'visible' => true,
    ],
    'name' => [
        'title' => 'Name',
        'type' => Table::TYPE_STRING,
        'filters' => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
        'sortable' => true,
        'visible' => true,
    ],
    'email' => [
        'title' => 'Email',
        'type' => Table::TYPE_STRING,
        'filters' => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
        'sortable' => true,
        'visible' => true,
    ],
    'created_at' => [
        'title' => 'Created at',
        'type' => Table::TYPE_DATETIME,
        'filters' => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
        'sortable' => true,
        'visible' => true,
    ],
    'is_admin' => [
        'title' => 'Is admin',
        'type' => Table::TYPE_BOOLEAN,
        'filters' => [ Table::FILTER_EQUAL, Table::FILTER_NULL ],
        'sortable' => true,
        'visible' => true,
    ],
]);
```

Example of data mapper for this table:

```php
$table->setMapper(function ($row) {
    $row['email'] = htmlentities($row['email']);                    // escape strings

    if ($row['created_at'] !== null)
        $row['created_at'] = $row['created_at']->getTimestamp();    // transmit as Epoch timestamp

    return $row;
});
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
