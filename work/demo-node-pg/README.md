
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

Edit fill-db.js (put database credentials) and run:

    node fill-db.js

### Web server

Edit server.js (put database credentials) and run:

    node server.js
