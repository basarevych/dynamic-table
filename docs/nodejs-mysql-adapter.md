NodeJS mysql adapter
--------------------

MySQL table for our DynamicTable:

```sql
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_admin` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
adapter.setSelect('*');             // SELECT * FROM users WHERE id > 50
adapter.setFrom('users');
adapter.setWhere("id > ?");         // mysql syntax for substituted parameters
adapter.setParams([ 50 ]);

// if you do not need to set initial WHERE:
var adapter = new MysqlAdapter();
adapter.setConnection(connection);
adapter.setSelect('*');             // SELECT * FROM users
adapter.setFrom('users');
adapter.setWhere("");
adapter.setParams([]);
```

Connect adapter to the table:

```javascript
table.setAdapter(adapter);
```

... continue with the table
