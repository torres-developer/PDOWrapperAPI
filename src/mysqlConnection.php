<?php

/**
 *        PDOWrapperAPI - An Wrapper API for the PHP PDO.
 *        Copyright (C) 2022  João Torres
 *
 *        This program is free software: you can redistribute it and/or modify
 *        it under the terms of the GNU Affero General Public License as
 *        published by the Free Software Foundation, either version 3 of the
 *        License, or (at your option) any later version.
 *
 *        This program is distributed in the hope that it will be useful,
 *        but WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *        GNU Affero General Public License for more details.
 *
 *        You should have received a copy of the GNU Affero General Public License
 *        along with this program.  If not, see <https://www.gnu.org/licenses/>.
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

use TorresDeveloper\PdoWrapperAPI\Core\QueryBuilder;

/**
 * @author João Torres <torres.dev@disroot.org>
 * @link https://www.mysql.com MySQL website
 * @link https://dev.mysql.com/doc/refman/8.0/en/ Reference Manual
 */
class mysqlConnection extends Core\Connection
{
    /**
     * @param \TorresDeveloper\PdoWrapperAPI\Core\DataSourceName $dsn Contains the information required to connect to the database. {@link https://www.php.net/manual/en/pdo.construct.php PHP \PDO __construct documentation}
     *
     * @throws \RuntimeException
     */
    protected function genDsn(Core\DataSourceName $dsn): void
    {
        $info = $dsn->info;

        if (!$this->checkArray($info, "database"))
            throw new \RuntimeException("No database name specified.");

        if (
            !$this->checkArray($info, "socket")
            && !$this->checkArray($info, "host")
        )
            throw new \RuntimeException("Neither the MySQL Unix socket nor the "
            . "hostname for the database server defined");

        if (
            $this->checkArray($info, "port")
            && !$this->checkArray($info, "host")
        )
            throw new \RuntimeException("Can't specify database server port "
            . "number without defining an hostname for the database"
            . "server.");

        if (
            $this->checkArray($info, "socket")
            && ($this->checkArray($info, "host")
                || $this->checkArray($info, "port")
            )
        )
            throw new \RuntimeException("The MySQL Unix socket shouldn't be "
            . "defined at the same time as an hostname or a port number for "
            . "the server.");

        // TODO: should not ser driver here
        $dsn->setDriver("mysql");

        $dsnStr = "mysql:";
        $dsnStr .= $this->checkArray($info, "socket")
            ? ("unix_socket={$this->checkArrayValue($info, "socket")};")
            : (("host={$this->checkArrayValue($info, "host")};")
                . $this->checkArray($info, "port")
                ? "port={$this->checkArrayValue($info, "port")};"
                : "");
        $dsnStr .= "dbname={$this->checkArrayValue($info, "database")};";
        $dsnStr .= "charset=utf8mb4";

        $dsn->setDsn($dsnStr);
    }

    /**
     * @param string|string[] $columns Array of the columns to select from the table $table.
     *                                 In case this is a string:
     *                                 - maybe you want just one column or;
     *                                 - if $table it's null then $columns will be the table and all columns are selected.
     * @param null|string     $table   Name of the table to select.
     *                                 If it's null $columns must be the table name.
     *
     * @return \PDOStatement
     */
    public function select(
        string|array $columns,
        ?string $table = null
    ): \PDOStatement {
        $query = $this->getBuider();

        if (!isset($table) && is_string($columns)) {
            $table = $columns;

            $columns = "*";
        } else {
            if (is_string($columns)) $columns = [$columns];
        }

        return $query->select(...$columns)->from($table)->run();
    }

    /**
     * @param string     $table  Table where you want to insert the rows $values.
     * @param ...mixed[] $values The rows, each one an associative array column => value.
     *
     * @return \PDOStatement
     */
    public function insert(
        string $table,
        array ...$values
    ): \PDOStatement {
        $query = $this->getBuider();

        return $query
            ->insert($table)
            ->values(QueryBuilder::DEFAULT_ON_NULL, ...$values)
            ->run();
    }

    public function update(
        string $table,
        array $columnValue,
        array $columns,
        ?array $conditions
    ): \PDOStatement {
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

    public function delete(string $table, ?array $conditions): \PDOStatement
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
}

