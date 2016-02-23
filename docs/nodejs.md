Server-side NodeJS
------------------

Create an instance of main class (Table) and one of "data adapters".
Currently there are three adapters available: Array, mysql and pg.

The first step is to define table columns:

```javascript
var Table = require('dynamic-table').table();

// ...

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
```

Table.setColumns expects an object defining columns:
* Item key is column ID
* 'title' is the title (goes to &lt;th&gt;)
* 'sql_id' is column name in the SQL table (not needed if array adapter is used)
* 'type' is one of the following:
  * Table.TYPE_STRING - cell value is string
  * Table.TYPE_INTEGER - cell value is integer
  * Table.TYPE_FLOAT - cell value is float
  * Table.TYPE_BOOLEAN - cell value is boolean
  * Table.TYPE_DATETIME - cell value is DateTime object
* 'filters' is combination (array) of enabled filters for this column:
  * Table.FILTER_LIKE - similar to SQL LIKE filter
  * Table.FILTER_EQUAL - leave cells with specific values only
  * Table.FILTER_BETWEEN - cell values between 'start' and 'end'
  * Table.FILTER_NULL - include NULLs to filtered dataset
* 'sortable' - true if table could be sorted by this column or not
* 'visible' - true if column should be visible from the beginning

The second step is to create data mapper:

```javascript
var validator = require('validator');       // we will use 'validator' npm package to escape HTML strings

// ...

table.setMapper(function (row) {
    if (row['email'])
        row['email'] = validator.escape(row['email']);  // we must escape strings

    if (row['created_at'])                              // convert moment.js object to something that could
        row['created_at'] = row['created_at'].unix();   // be sent over the net, i.e. UNIX timestamp

    return row;
});
```

The data mapper is a function that accepts source data row and returns this row in a form suitable for our jQuery plugin.
You should at least convert moment.js Date objects into UNIX epoch values and optionally escape HTML strings.
The resulting data is then transmitted over the network to the client as JSON object.

Now that we have a table it's time to connect it with the data. You do this by creating a *data adapter*. At the moment there are three available:
* [ArrayAdapter](nodejs-array-adapter.md)
* [MysqlAdapter](nodejs-mysql-adapter.md)
* [PgAdapter](nodejs-pg-adapter.md)

Back to our table, the last thing to do is to parse jQuery plugin's GET query and return the data.

Two GET requests are made by the jQuery plugin:
* First time the plugin is created it will run '?query=describe' request to the server.
* Each time the table is refreshed it will request '?query=data'.

```javascript
// 'app' is an ExpressJS application

app.get('/table', function(req, res){
    // ... define table and adapter

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
```
