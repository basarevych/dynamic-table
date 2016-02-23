NodeJS mysql adapter
--------------------

MySQL table for our DynamicTable:

```sql
CREATE TABLE users (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(255) NULL,
    email varchar(255) NULL,
    created_at timestamp NULL,
    is_admin tinyint(1) NULL,
    PRIMARY KEY(id)
);
```

Create adapter:

```javascript
var mysql = require('mysql');
var MysqlAdapter = require('dynamic-table').mysqlAdapter();

// ...

var connection = mysql.createConnection({
    host: 'localhost',
    user: 'db_user',
    password: 'passwd',
    database: 'db_name',
});

var adapter = new MysqlAdapter();
adapter.setConnection(connection);

// if you do not need to set initial WHERE:
adapter.setSelect('*');             // SELECT * FROM users
adapter.setFrom('users');
adapter.setWhere("");
adapter.setParams([]);

// or a query:
adapter.setSelect('*');             // SELECT * FROM users WHERE id > 50
adapter.setFrom('users');
adapter.setWhere("id > ?");         // mysql syntax for substituted parameters
adapter.setParams([ 50 ]);

// adaper.setDbTimezone('UTC');     // data source could be in different timezone
```

Connect adapter to the table:

```javascript
table.setAdapter(adapter);
```

... continue with the table
