<?php

/**
 *    PDOWrapperAPI - An Wrapper API for the PHP PDO.
 *    Copyright (C) 2022  João Torres
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package TorresDeveloper\\PdoWrapperAPI
 * @author João Torres <torres.dev@disroot.org>
 * @copyright Copyright (C) 2022  João Torres
 * @license https://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License
 * @license https://opensource.org/licenses/AGPL-3.0 GNU Affero General Public License version 3
 *
 * @since 1.0.0
 * @version 1.0.0
 */

namespace TorresDeveloper\PdoWrapperAPI;

use PDOStatement;

class PDO extends Core\Singleton implements DataManipulationInterface
{
    private \PDO $pdo;

    public $lastID;

    protected function __construct(
        string $host,
        string $name,
        string $charset,
        string $username,
        string $password
    ) {
        $dsn = "mysql:"
            . "host=$host;"
            . "dbname=$name;";

        $dsn .= $charset ? "charset=$charset" : "";

        $this->pdo = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ]);
    }

    private function query(
        PDOStatement | string $statement,
        ?array $values = null,
    ): PDOStatement {
        if (is_string($statement))
            $statement = $this->createPDOStatement($statement);


        if (!$statement->execute($values)) {
            $this->pdo->inTransaction() AND $this->pdo->rollBack();

            $error = $statement->errorInfo();

            throw new \Error((string) $error);
        }

        $this->lastID = $this->pdo->lastInsertId();

        return $statement;
    }

    public function select(
        string|array $columns,
        ?string $table = null
    ): PDOStatement {
        $statement = "SELECT";

        if (!isset($table) && is_string($columns)) {
            $statement .= " *";

            $table = $columns;
        } else {
            if (is_string($columns)) {
                $statement .= " $columns";
            } else {
                $columnsNumber = count($columns);
                for ($i = 0; $i < $columnsNumber; ++$i) {
                    if ($i) $statement .= ",";

                    $statement .= " `{$columns[$i]}`";
                }
            }
        }

        $statement .=  " FROM `$table`;";

        return $this->query($statement);
    }

    public function insert(
        string $table,
        array $columns,
        array ...$values
    ): PDOStatement {
        $statement = "INSERT INTO `$table`";

        $columnsAmount = count($columns);
        $columnsKeys = array_keys($columns);

        $placeHolder = str_repeat(
            "?, ",
            $columnsAmount ? ($columnsAmount - 1) : 0
        ) . "?)";

        if ($columnsAmount) {
            $statement .= "(";

            foreach ($columnsKeys as $index => $key)
                $statement .= $index === $columnsAmount -1
                    ? "`$key`)"
                    : "`$key`, ";
        }

        $statement .= " VALUES";

        $valuesAmount = count($values);
        for ($i = 0; $i < $valuesAmount; ++$i) {
            $statement .= " (" . (!empty($columns)
                ? $placeHolder
                : str_repeat("?, ", (count($values[$i]) - 1)) . "?)");

            if (!$i == $valuesAmount) $statement .= ",";
        }

        $statement .= ";";

        $statement = $this->createPDOStatement($statement);

        if ($columnsAmount) {
            for ($i = 0; $i < $columnsAmount; ++$i) {
                $column = $columnsKeys[$i];
                
                for ($j = 0; $j < $valuesAmount; ++$j) {
                    $value = $values[$j];

                    $statement->bindValue(
                        $j * $columnsAmount + $i + 1,
                        $value[$column] ?? 0,
                        $columns[$column]
                    );
                }
            }
        }

        return $this->query($statement);
    }

    public function update(
        string $table,
        array $columnValue,
        array $columns,
        ?array $conditions
    ): PDOStatement {
        $statement = "UPDATE `$table` SET ";

        $columnKeys = array_keys($columnValue);

        $set = [];
        foreach ($columnKeys as $column) $set[] = "`$column` = :$column";
        $statement .= implode(", ", $set);

        if ($conditions) {
            $statement .= " WHERE ";

            $where = [];
            foreach ($conditions as $field => $value)
                $where[] = " `$field` = $value";
            $statement .= implode(" AND", $where);
        }

        $statement .= ";";

        $statement = $this->createPDOStatement($statement);

        foreach ($columnValue as $column => $value) $statement->bindValue(
            ":$column",
            $value ?? \PDO::ATTR_DEFAULT_STR_PARAM,
            $columns[$column] ?? null
        );

        return $this->query($statement);
    }

    public function delete(string $table, ?array $conditions): PDOStatement
    {
        $statement = "DELETE FROM `$table`";

        if ($conditions) {
            $statement .= " WHERE ";

            $where = [];
            $conditionsKeys = array_keys($conditions);
            foreach ($conditionsKeys as $condition)
                $where[] = " `$condition` = ?";
            $statement .= implode(" AND", $where);
        }

        $statement .= ";";

        return $this->query($statement, array_values($conditions));
    }

    private function createPDOStatement(string $statement): PDOStatement
    {
        $statement = $this->pdo->prepare($statement);

        if (!$statement) throw new \Error();

        return $statement;
    }

    public function getError(): array
    {
        return $this->pdo->errorInfo();
    }
}

