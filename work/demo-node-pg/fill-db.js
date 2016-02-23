var pg = require('pg');
var moment = require('moment-timezone');

/*
CREATE TABLE "users" (
    "id" serial NOT NULL,
    "name" character varying(255) NULL,
    "email" character varying(255) NULL,
    "created_at" timestamp NULL,
    "is_admin" boolean NULL,
    CONSTRAINT "users_pk" PRIMARY KEY ("id")
);
*/

var url = 'postgres://pdo_example:pdo_example@localhost/pdo_example';

var dt = moment("2010-05-11 13:00:00");
var data = [];
for (var i = 1; i <= 100; i++) {
    dt.add(10, 'seconds');

    if (i == 3) {
        data.push({
            name: null,
            email: null,
            created_at: null,
            is_admin: null,
        });
    } else {
        data.push({
            name: "User " + i,
            email: "user" + i + "@example.com",
            created_at: dt.format("YYYY-MM-DD HH:mm:ss"),
            is_admin: (i % 2 == 0),
        });
    }
}

function insertRow() {
    if (data.length == 0)
        process.exit(0);

    var row = data.shift();
    var sql = "  INSERT"
             +"    INTO users(name, email, created_at, is_admin)"
             +"  VALUES ($1, $2, $3, $4)";

    var client = new pg.Client(url);
    client.connect(function (err) {
        if (err) {
            console.log(err);
            process.exit(1);
        }

        client.query(
            sql,
            [
                row.name,
                row.email,
                row.created_at,
                row.is_admin,
            ],
            function (err, result) {
                if (err) {
                    console.log(err);
                    process.exit(1);
                }

                client.end();

                insertRow();
            }
        );
    });
}
insertRow();
