
### Dependencies

Run:

    npm install
    ./node_modules/.bin/bower install

### MySQL Database

    CREATE TABLE users (
        id int NOT NULL AUTO_INCREMENT,
        name varchar(255) NULL,
        email varchar(255) NULL,
        created_at timestamp NULL,
        is_admin tinyint(1) NULL,
        PRIMARY KEY(id)
    );

Edit fill-db.php (put database credentials) and run:

    php fill-db.php

### Apache

Edit table.php (put database credentials) and point Apache to this directory
