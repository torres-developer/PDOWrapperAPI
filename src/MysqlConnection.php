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

declare(strict_types=1);

namespace TorresDeveloper\PdoWrapperAPI;

use TorresDeveloper\PdoWrapperAPI\Core\{
    Connection,
    DataSourceName as DSN,
    QueryBuilder,
};

/**
 * @author João Torres <torres.dev@disroot.org>
 * @link https://www.mysql.com MySQL website
 * @link https://dev.mysql.com/doc/refman/8.0/en/ Reference Manual
 */
class MysqlConnection extends Connection
{
    /**
     * @param \TorresDeveloper\PdoWrapperAPI\Core\DataSourceName $dsn Contains the information required to connect to
     *                                                                the database.
     *                                                                {@link https://www.php.net/manual/en/pdo.construct.php PHP \PDO __construct documentation}
     *
     * @throws \DomainException
     *
     * @return string
     */
    protected function genDSN(DSN $dsn): string
    {
        $info = $dsn->getInfo();

        if (!$this->checkArray($info, "database")) {
            throw new \DomainException("No database name specified.");
        }

        if (
            !$this->checkArray($info, "socket")
            && !$this->checkArray($info, "host")
        ) {
            throw new \DomainException("Neither the MySQL Unix socket nor the "
                . "hostname for the database server defined");
        }

        if (
            $this->checkArray($info, "port")
            && !$this->checkArray($info, "host")
        ) {
            throw new \DomainException("Can't specify database server port "
                . "number without defining an hostname for the database"
                . "server.");
        }

        if (
            $this->checkArray($info, "socket")
            && (
                $this->checkArray($info, "host")
                || $this->checkArray($info, "port")
            )
        ) {
            throw new \DomainException("The MySQL Unix socket shouldn't be "
                . "defined at the same time as an hostname or a port number "
                . "for the server.");
        }

        $dsnStr = "mysql:";

        $dsnStr .= $this->checkArray($info, "socket")
            ? "unix_socket={$this->checkArrayValue($info, "socket")};"
            : ("host={$this->checkArrayValue($info, "host")};"
                . $this->checkArray($info, "port")
                    ? "port={$this->checkArrayValue($info, "port")};"
                    : "");

        $dsnStr .= "dbname={$this->checkArrayValue($info, "database")};";

        $dsnStr .= "charset=utf8mb4";

        return $dsnStr;
    }

    protected function genDriver(): string
    {
        return "mysql";
    }

    public function select(
        string|iterable $columns,
        ?string $table = null
    ): \PDOStatement {
        if (!isset($table) && is_string($columns)) {
            $table = $columns;

            $columns = ["*"];
        } else {
            if (is_string($columns)) {
                $columns = [$columns];
            }
        }

        return $this->getBuilder()->select(...$columns)->from($table)->run();
    }

    public function insert(
        string $table,
        iterable ...$rows
    ): \PDOStatement {
        return $this->getBuilder()
            ->insert($table)
            ->values(QueryBuilder::DEFAULT_ON_NULL, ...$rows)
            ->run();
    }

    public function update(
        string $table,
        iterable $assignments,
        iterable $conditions = []
    ): \PDOStatement {
        $query = $this->getBuilder()
            ->update($table)
            ->set($assignments);

        if (!empty($conditions)) {
            $this->whereAllConditions($query, $conditions);
        }

        return $query->run();
    }

    public function delete(
        string $table,
        iterable $conditions = []
    ): \PDOStatement {
        $query = $this->getBuilder()
            ->delete($table);

        if (!empty($conditions)) {
            $this->whereAllConditions($query, $conditions);
        }

        return $query->run();
    }

    /**
     * @param \TorresDeveloper\PdoWrapperAPI\Core\QueryBuilder &$query     A query to add all the conditions.
     * @param iterable                                         $conditions An iterable column => value.
     *                                                                     The condition is always column=value
     *
     * @return \TorresDeveloper\PdoWrapperAPI\Core\QueryBuilder
     */
    private function whereAllConditions(
        QueryBuilder &$query,
        iterable $conditions
    ): QueryBuilder {
        $didFirst = false;

        foreach ($conditions as $column => $value) {
            if (!$didFirst) {
                $query->where($column, QueryBuilder::EQ, $value);

                $didFirst = true;
            } else {
                $query->and($column, QueryBuilder::EQ, $value);
            }
        }

        return $query;
    }
}
