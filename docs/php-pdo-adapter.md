PHP PDO adapter
---------------

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

```php
use DynamicTable\Adapter\PDOAdapter;

// ...

$dsn = 'mysql:dbname=db_name_here;host=127.0.0.1';
$user = 'db_user_here';
$password = 'db_password_here';

$dbh = new \PDO($dsn, $user, $password);

$adapter = new PDOAdapter();
$adapter->setPdo($dbh);

// if you do not need to set initial WHERE:
$adapter->setSelect('*');             // SELECT * FROM users
$adapter->setFrom('users');
$adapter->setWhere("");
$adapter->setParams([]);

// if you need a modified query
$adapter->setSelect('*');             // SELECT * FROM users WHERE id > 50
$adapter->setFrom('users');
$adapter->setWhere("id > :id");       // use named parameters
$adapter->setParams([ ':id' => 50 ]);

// $adapter->setDbTimezone('UTC');      // Data source could be in defferent timezone
```

Example of data mapper for PDO row:

```php
$table->setMapper(function ($row) {
    $row['email'] = htmlentities($row['email']);                    // escape strings

    if ($row['created_at'] !== null)
        $row['created_at'] = $row['created_at']->getTimestamp();    // transmit as Epoch timestamp

    if ($row['is_admin'] !== null)
        $row['is_admin'] = ($row['is_admin'] == 1);                 // convert to boolean

    return $row;
});
```

Connect adapter to the table:

```php
$table->setAdapter($adapter);
```

... continue with the table
