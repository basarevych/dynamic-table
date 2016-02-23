var express = require('express');
var path = require('path');
var validator = require('validator');       // we will use it to escape strings
var mysql = require('mysql');

// the following will be just "require('dynamic-table').table()" in your app
var Table = require('../../node').table();
var MysqlAdapter = require('../../node').mysqlAdapter();

var app = express();
app.use(express.static(path.join(__dirname, 'public')));

var table = new Table();
table.setColumns({
    id: {
        title: 'ID',
        sql_id: 'id',
        type: Table.TYPE_INTEGER,
        filters: [ Table.FILTER_EQUAL ],
        sortable: true,
        visible: true,
    },
    name: {
        title: 'Name',
        sql_id: 'name',
        type: Table.TYPE_STRING,
        filters: [ Table.FILTER_LIKE, Table.FILTER_NULL ],
        sortable: true,
        visible: true,
    },
    email: {
        title: 'Email',
        sql_id: 'email',
        type: Table.TYPE_STRING,
        filters: [ Table.FILTER_LIKE, Table.FILTER_NULL ],
        sortable: true,
        visible: true,
    },
    created_at: {
        title: 'Created at',
        sql_id: 'created_at',
        type: Table.TYPE_DATETIME,
        filters: [ Table.FILTER_BETWEEN, Table.FILTER_NULL ],
        sortable: true,
        visible: true,
    },
    is_admin: {
        title: 'Is admin',
        sql_id: 'is_admin',
        type: Table.TYPE_BOOLEAN,
        filters: [ Table.FILTER_EQUAL, Table.FILTER_NULL ],
        sortable: true,
        visible: true,
    },
});

table.setMapper(function (row) {
    if (row['email'])
        row['email'] = validator.escape(row['email']);  // we must escape strings

    // convert moment.js object to UNIX epoch (number of seconds):
    if (row['created_at'])
        row['created_at'] = row['created_at'].unix();

    if (row['is_admin'])
        row['is_admin'] = (row['is_admin'] == 1)        // convert to boolean

    return row;
});

app.get('/table', function (req, res) {
    var connection = mysql.createConnection({
        host: 'localhost',
        user: 'pdo_example',
        password: 'pdo_example',
        database: 'pdo_example',
    });

    // run fill-db.js to fill the table with some data

    var adapter = new MysqlAdapter();
    adapter.setConnection(connection);
    adapter.setSelect('*');             // SELECT * FROM users
    adapter.setFrom('users');
    adapter.setWhere("");
    adapter.setParams([]);

    // Or use it like this:
    // adapter.setSelect('*');             // SELECT * FROM users WHERE id > 50
    // adapter.setFrom('users');
    // adapter.setWhere("id > ?");
    // adapter.setParams([ 50 ]);

    // adapter.setDbTimezone('UTC');        // Data source could be in different timezone

    table.setAdapter(adapter);

    switch (req.query.query) {      // 'query' parameter of a GET request
        case 'describe':
            table.describe(function (err, result) {
                if (err) {
                    console.error(err);
                    throw new Error('DynamicTable describe() failed');
                }

                result['success'] = true;
                res.json(result);
            });
            break;
        case 'data':
            table.setPageParams(req.query)
                .fetch(function (err, result) {
                    if (err) {
                        console.error(err);
                        throw new Error('DynamicTable fetch() failed');
                    }

                    result['success'] = true;
                    res.json(result);
                });
            break;
        default:
            res.json({ success: false });
    }
});

app.listen(3000, function () {
    console.log('Example app listening on port 3000!');
});

