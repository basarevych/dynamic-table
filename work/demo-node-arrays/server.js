var express = require('express');
var path = require('path');
var moment = require('moment-timezone');
var clone = require('clone');
var validator = require('validator');       // we will use it to escape strings

// the following will be just "require('dynamic-table').table()" in your app
var Table = require('../../node').table();
var ArrayAdapter = require('../../node').arrayAdapter();

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

var data = [];

var dt = moment("2010-05-11 13:00:00");
for (var i = 1; i <= 100; i++) {
    dt.add(10, 'seconds');
    if (i == 3) {
        data.push({
            id: i,
            name: null,
            email: null,
            created_at: null,
            is_admin: null,
        });
    } else {
        data.push({
            id: i,
            name: "User " + i,
            email: "user" + i + "@example.com",
            created_at: dt.format("YYYY-MM-DD HH:mm:ss"),           // as a string
            // created_at: dt.unix(),                               // or as a timestamp
            // created_at: clone(dt.toDate()),                      // or as JS Date object
            // created_at: clone(dt),                               // or as a moment.js object
            is_admin: (i % 2 == 0),
        });
    }
}

app.get('/table', function (req, res) {
    var adapter = new ArrayAdapter();
    adapter.setData(data);
    // adapter.setDbTimezone('UTC');            // Data source could be in different timezone

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

