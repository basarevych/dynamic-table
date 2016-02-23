<?php

namespace DynamicTable;

/*
CREATE TABLE users (
    id int NOT NULL AUTO_INCREMENT,
    name varchar(255) NULL,
    email varchar(255) NULL,
    created_at timestamp NULL,
    is_admin tinyint(1) NULL,
    PRIMARY KEY(id)
);
*/

$dsn = 'mysql:dbname=pdo_example;host=127.0.0.1';
$user = 'pdo_example';
$password = 'pdo_example';

$dbh = new \PDO($dsn, $user, $password);

$dt = new \DateTime("2010-05-11 13:00:00");
for ($i = 1; $i <= 100; $i++) {
    $dt->add(new \DateInterval('PT10S'));

    $sql = "  INSERT"
          ."    INTO users(name, email, created_at, is_admin)"
          ."  VALUES (:name, :email, :created_at, :is_admin)";

    $query = $dbh->prepare($sql);

    if ($i == 3) {
        $query->execute([
            ':name' => null,
            ':email' => null,
            ':created_at' => null,
            ':is_admin' => null,
        ]);
    } else {
        $query->execute([
            ':name' => "User $i",
            ':email' => "user$i@example.com",
            ':created_at' => $dt->format("Y-m-d H:i:s"),
            ':is_admin' => ($i % 2 == 0),
        ]);
    }
}
