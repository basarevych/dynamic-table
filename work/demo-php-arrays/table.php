<?php

namespace DynamicTable;

require '../../php/DynamicTable/Table.php';
require '../../php/DynamicTable/Adapter/AbstractAdapter.php';
require '../../php/DynamicTable/Adapter/ArrayAdapter.php';

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

$adapter = new Adapter\ArrayAdapter();
$adapter->setData($data);

// $adapter->setDbTimezone('UTC');      // Data source could be in different timezone

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
$table->setMapper(function ($row) {
    $row['email'] = htmlentities($row['email']);                    // escape strings

    if ($row['created_at'] !== null)
        $row['created_at'] = $row['created_at']->getTimestamp();    // transmit as Epoch timestamp

    return $row;
});


$table->setAdapter($adapter);

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
