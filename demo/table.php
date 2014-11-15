<?php

require '../php/src/DynamicTable/Table.php';
require '../php/src/DynamicTable/Adapter/AbstractAdapter.php';
require '../php/src/DynamicTable/Adapter/ArrayAdapter.php';

$data = [];
$dt = new \DateTime("2010-05-11 13:00:00");
for ($i = 1; $i <= 100; $i++) {
    $dt->add(new \DateInterval('PT10S'));

    if ($i == 3) {
        $data[] = [
            'id' => $i,
            'string' => null,
            'integer' => null,
            'float' => null,
            'boolean' => null,
            'datetime' => null,
        ];
    } else {
        $data[] = [
            'id' => $i,
            'string' => "string $i",
            'integer' => $i,
            'float' => $i / 100,
            'boolean' => ($i % 2 == 0),
            'datetime' => clone $dt,
        ];
    }
}

$adapter = new DynamicTable\Adapter\ArrayAdapter();
$adapter->setData($data);
$adapter->setMapper(function ($row) {
    $result = $row;

    if ($row['datetime'] !== null)
        $result['datetime'] = $row['datetime']->getTimestamp();

    return $result;
});

$table = new \DynamicTable\Table();

$table->setColumns([
    'id' => [
        'title'     => 'ID',
        'sql_id'    => 's.id',
        'type'      => \DynamicTable\Table::TYPE_INTEGER,
        'filters'   => [ \DynamicTable\Table::FILTER_EQUAL ],
        'sortable'  => true,
        'visible'   => false,
    ],
    'string' => [
        'title'     => 'String',
        'sql_id'    => 's.value_string',
        'type'      => \DynamicTable\Table::TYPE_STRING,
        'filters'   => [ \DynamicTable\Table::FILTER_LIKE, \DynamicTable\Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'integer' => [
        'title'     => 'Integer',
        'sql_id'    => 's.value_integer',
        'type'      => \DynamicTable\Table::TYPE_INTEGER,
        'filters'   => [ \DynamicTable\Table::FILTER_GREATER, \DynamicTable\Table::FILTER_LESS, \DynamicTable\Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'float' => [
        'title'     => 'Float',
        'sql_id'    => 's.value_float',
        'type'      => \DynamicTable\Table::TYPE_FLOAT,
        'filters'   => [ \DynamicTable\Table::FILTER_GREATER, \DynamicTable\Table::FILTER_LESS, \DynamicTable\Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'boolean' => [
        'title'     => 'Boolean',
        'sql_id'    => 's.value_boolean',
        'type'      => \DynamicTable\Table::TYPE_BOOLEAN,
        'filters'   => [ \DynamicTable\Table::FILTER_EQUAL, \DynamicTable\Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
    'datetime' => [
        'title'     => 'DateTime',
        'sql_id'    => 's.value_datetime',
        'type'      => \DynamicTable\Table::TYPE_DATETIME,
        'filters'   => [ \DynamicTable\Table::FILTER_GREATER, \DynamicTable\Table::FILTER_LESS, \DynamicTable\Table::FILTER_NULL ],
        'sortable'  => true,
        'visible'   => true,
    ],
]);

$table->setAdapter($adapter);

$query = @$_GET['query'];
switch ($query) {
case 'describe':
    $data = $table->describe();
    break;
case 'data':
    $table->setFilters(json_decode(@$_GET['filters'], true));
    $table->setSortColumn(json_decode(@$_GET['sort_column'], true));
    $table->setSortDir(json_decode(@$_GET['sort_dir'], true));
    $table->setPageNumber(json_decode(@$_GET['page_number'], true));
    $table->setPageSize(json_decode(@$_GET['page_size'], true));
    $data = $table->fetch();
    break;
default:
    throw new \Exception('Unknown query type: ' . $query);
}

$data['success'] = true;

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
