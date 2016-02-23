<?php

namespace DynamicTable;

require '../../php/DynamicTable/Table.php';
require '../../php/DynamicTable/Adapter/AbstractAdapter.php';
require '../../php/DynamicTable/Adapter/GenericDBAdapter.php';
require '../../php/DynamicTable/Adapter/PDOAdapter.php';

$dsn = 'mysql:dbname=pdo_example;host=127.0.0.1';
$user = 'pdo_example';
$password = 'pdo_example';

$dbh = new \PDO($dsn, $user, $password);

// run fill-db.php to fill the table with some data

$adapter = new Adapter\PDOAdapter();
$adapter->setPdo($dbh);
$adapter->setSelect('*');               // SELECT * FROM users
$adapter->setFrom('users');
$adapter->setWhere("");
$adapter->setParams([]);

// Or use it like this:
// $adapter->setSelect('*');             // SELECT * FROM users WHERE id > 50
// $adapter->setFrom('users');
// $adapter->setWhere("id > :id");       
// $adapter->setParams([ ':id' => 50 ]);

// $adapter->setDbTimezone('UTC');      // Database could store dates in different timezone

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

    if ($row['is_admin'] !== null)
        $row['is_admin'] = ($row['is_admin'] == 1);                 // convert to boolean

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
