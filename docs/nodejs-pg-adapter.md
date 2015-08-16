NodeJS pg adapter
-----------------

PostgresSQL table for our DynamicTable:

```sql
CREATE TABLE "users" (
    "id" serial NOT NULL,
    "name" character varying(255) NULL,
    "email" character varying(255) NOT NULL,
    "password" character varying(255) NOT NULL,
    "created_at" timestamp NULL,
    "is_admin" boolean NULL,
    CONSTRAINT "users_pk" PRIMARY KEY ("id"),
    CONSTRAINT "users_unique_email" UNIQUE ("email")
);
```

Create adapter:

```javascript
var pg = require('pg');
var PgAdapter = require('dynamic-table').pgAdapter();

// ...

var url = 'postgres://db_user:passwd@localhost/db_name';
var client = new pg.Client(url);

var adapter = new PgAdapter();
adapter.setClient(client);
adapter.setSelect('*');             // SELECT * FROM users WHERE id > 50
adapter.setFrom('users');
adapter.setWhere("id > $1");        // pg syntax for substituted parameters
adapter.setParams([ 50 ]);

// if you do not need to set initial WHERE:
var adapter = new PgAdapter();
adapter.setClient(client);
adapter.setSelect('*');             // SELECT * FROM users
adapter.setFrom('users');
adapter.setWhere("");
adapter.setParams([ ]);
```

Connect adapter to the table:

```javascript
table.setAdapter(adapter);
```

... continue with the table
