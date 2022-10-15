<?php

/**
 *
 */

namespace TorresDeveloper\PdoWrapperAPI;

use PDOStatement;

interface DataManipulationInterface
{
    public function select(
        string | array $columns,
        string $table
    ): PDOStatement;
    public function insert(
        string $table,
        array $columns,
        array ...$values
    ): PDOStatement;
    public function update(
        string $table,
        array $columnValue,
        array $columns,
        ?array $conditions
    ): PDOStatement;
    public function delete(
        string $table,
        ?array $conditions
    ): PDOStatement;
}

