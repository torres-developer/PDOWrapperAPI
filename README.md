# PDOWrapperAPI
An Wrapper API for the PHP PDO.

# Basic Usage:

```php
<?php

require __DIR__ ."/../vendor/autoload.php";

$dbh = new \TorresDeveloper\PdoWrapperAPI\mysqlConnection(
    new Core\PDODataSourceName([
        "host" => "192.168.1.40",
        "database" => "exampleDatabase",
    ], Core\Credentials::getCredentials(
        "user",
        "passwd"
    ))
);

/*
 * Use the CRUD interface for easy operations:
 * - select;
 * - insert;
 * - update;
 * - delete.
 */

// get all from all users
$dbh->select("user")->fetchAll(PDO::FETCH_OBJ);
// get all users `id`
$dbh->select("id", "user")->fetchAll(PDO::FETCH_OBJ);
// get all users `name` and `age`
$dbh->select(["name", "age"], "user")->fetchAll(PDO::FETCH_OBJ);

// insert new users
$users = [
    ["id" => 123, "name" => "asdfs"],
    ["age" => 2],
    ["id" => null, "text" => "y7tgebh"]
];
$dbh->insert("user", ...$users);

/*
 * You can use a query builder for more complex operations:
 */
$dbh->getBuider()->select()
    ->from("user")
    ->limit(5)
    ->run()
    ->fetchAll(\PDO::FETCH_OBJ)
```

