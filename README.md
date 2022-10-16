# PDOWrapperAPI
An Wrapper API for the PHP PDO.

# Basic Usage:

```php
<?php

require __DIR__ ."/../vendor/autoload.php";

$dbh = \TorresDeveloper\PdoWrapperAPI\PDO::getInstance(
    "192.168.1.40",
    "exampleDatabase",
    "utf8",
    "user",
    "passwd"
);

// get all from all users
print_r($dbh->select("user")->fetchAll(PDO::FETCH_OBJ));
// get all users `id`
print_r($dbh->select("id", "user")->fetchAll(PDO::FETCH_OBJ));
// get all users `name` and `age`
print_r($dbh->select(["name", "age"], "user")->fetchAll(PDO::FETCH_OBJ));

// insert new user
$dbh->insert("user", [
    "name" => PDO::PARAM_STR,
    "age" => PDO::PARAM_INT
], ...[
    ["age" => 25, "name" => "Darius"],
    ["age" => 32, "name" => "Swain"]
]);

// update users with `name` "Swain"
$dbh->update("user", [
    "name" => "Garen",
    "email" => "garen@demacia.gov"
], [
    "name" => PDO::PARAM_STR,
    "email" => PDO::PARAM_STR
], [
    "name" => "\"Swain\"",
]);

// delete user with `id` 69
$dbh->delete("user", ["id" => 69]);

```

