Create the database
===================

MySQL
-----
```shell
> mysql -u root -p
mysql> create database db_name_here character set utf8;
mysql> create user 'username_here'@'localhost' identified by 'password_here';
mysql> grant all privileges on db_name_here.* to 'username_here'@'localhost';
mysql> flush privileges;
mysql> \q

> php public/index.php dbal:import database/mysql.schema.sql
> php public/index.php populate-db
```

**NOTE**: dbal:import (re)creates the tables. All data will be lost.
