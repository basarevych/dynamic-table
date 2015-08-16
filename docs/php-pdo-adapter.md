PHP PDO adapter
---------------

SQL table for our DynamicTable:

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

```php
use DynamicTable\Adapter\PDOAdapter;

// ...

$dsn = 'pgsql:dbname=db_name_here;host=127.0.0.1';
$user = 'db_user_here';
$password = 'db_password_here';

$dbh = new \PDO($dsn, $user, $password);

$adapter = new PDOAdapter();
$adapter->setPdo($dbh);
$adapter->setSelect('*');             // SELECT * FROM users WHERE id > 50
$adapter->setFrom('users');
$adapter->setWhere("id > :id");       // use named parameters
$adapter->setParams([ ':id' => 50 ]);

// if you do not need to set initial WHERE:
$adapter = new PDOAdapter();
$adapter->setPdo($dbh);
$adapter->setSelect('*');             // SELECT * FROM users
$adapter->setFrom('users');
$adapter->setWhere("");
$adapter->setParams([]);
```

Example of data mapper for PDO row:

```php
$table->setMapper(function ($row) {
    $result = $row;

    $result['email'] = htmlentities($row['email']);

    if ($row['created_at'] !== null) { // convert string to timestamp
        $dt = new \DateTime($row['created_at'], new \DateTimezone('Europe/Kiev')); // db timezone
        $result['created_at'] = $dt->getTimestamp(); // unix timestamp (always in UTC)
    }

    return $result;
});
```

Connect adapter to the table:

```php
$table->setAdapter($adapter);
```

... continue with the table
