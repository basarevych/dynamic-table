The database
============

1. Create the database

  * MySQL

    ```shell
    > mysql -u root -p
    mysql> create database db_name_here character set utf8;
    mysql> create user 'username_here'@'localhost' identified by 'password_here';
    mysql> grant all privileges on db_name_here.* to 'username_here'@'localhost';
    mysql> flush privileges;
    mysql> \q

    > mysql -u username_here -p db_name_here < database/mysql.schema.sql
    ```

  * PostgreSQL

    ```shell
    > psql -U pgsql -d template1
    postgres=# create user username_here with password 'password_here';
    postgres=# create database db_name_here;
    postgres=# grant all privileges on database db_name_here to username_here;
    postgres=# \q

    > psql -U username_here -d db_name_here < database/postgresql.schema.sql
    ```

  * MongoDB

    ```js
    > mongo -u root -p --authenticationDatabase admin
    > use db_name_here
    > db.runCommand({ dropAllUsersFromDatabase: 1 })
    > db.runCommand({ dropDatabase: 1 })
    
    > use db_name_here
    > db.createUser(
        {
          user: "username_here",
          pwd: "password_here",
          roles: [
            { role: "userAdmin", db: "db_name_here" },
            { role: "readWrite", db: "db_name_here" },
          ]
        }
      )
    ```

2. Populate the database

  ```shell
  > php public.prod/index.php populate-db
  ```
