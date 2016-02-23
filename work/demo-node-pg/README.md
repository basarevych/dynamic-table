
### Dependencies

Run:

    npm install
    ./node_modules/.bin/bower install

### PostgreSQL Database

    CREATE TABLE "users" (
        "id" serial NOT NULL,
        "name" character varying(255) NULL,
        "email" character varying(255) NULL,
        "created_at" timestamp NULL,
        "is_admin" boolean NULL,
        CONSTRAINT "users_pk" PRIMARY KEY ("id")
    );

Edit fill-db.js (put database credentials) and run:

    node fill-db.js

### Web server

Edit server.js (put database credentials) and run:

    node server.js
