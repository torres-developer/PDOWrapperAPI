<?php

/**
 *
 */

namespace TorresDeveloper\PdoWrapperAPI;

interface DataManipulationInterface
{
    public function select(string | array $columns, string $table);
    public function insert(string $table, array $columns, array ...$values);
    public function update(
        string $table,
        array $columnValue,
        ?array $conditions
    );
    public function delete(string $table, ?array $conditions);
}

