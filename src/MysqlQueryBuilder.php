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

class mysqlQueryBuilder extends Core\AbstractQueryBuilder
{
    public function select(string ...$fields): static
    {
        if (!$this->data) $this->reset();

        $fields = array_filter($fields, fn ($i) => $i);

        if (!$fields) $fields = ["*"];

        $this->data->query = "SELECT " . implode(", ", $fields);
        $this->data->type = "SELECT";

        return $this;
    }

    public function from(string $table): static
    {
        if ($this->data->type !== "SELECT")
            throw new \Exception();

        $this->data->query .= " FROM $table";

        return $this;
    }

    public function where(string $field, int $op, mixed $val): static
    {
        if (!in_array($this->data->type, ["SELECT", "SET", "DELETE"]))
            throw new \Exception();

        $this->data->query .= " WHERE `$field` " . $this->findSignal($op) . " ?";

        $this->data->values[] = $val;

        $this->data->type = "WHERE";

        return $this;
    }

    public function and(string $field, int $op, mixed $val): static
    {
        if (!in_array($this->data->type, ["WHERE", "HAVING"]))
            throw new \Exception();

        $this->data->query .= " && `$field` " . $this->findSignal($op) . " ?";

        $this->data->values[] = $val;

        return $this;
    }

    public function or(string $field, int $op, mixed $val): static
    {
        if (!in_array($this->data->type, ["WHERE", "HAVING"]))
            throw new \Exception();

        $this->data->query .= " || `$field` " . $this->findSignal($op) . " ?";

        $this->data->values[] = $val;

        return $this;
    }

    public function xor(string $field, int $op, mixed $val): static
    {
        if (!in_array($this->data->type, ["WHERE", "HAVING"]))
            throw new \Exception();

        $this->data->query .= " XOR `$field` " . $this->findSignal($op) . " ?";

        $this->data->values[] = $val;

        return $this;
    }

    // TODO: The WHERE can't be from an UPDATE. same for having, orderBy, limit
    public function groupBy(string ...$fields): static
    {
        if (!in_array($this->data->type, ["SELECT", "WHERE"]))
            throw new \Exception();

        $this->data->query .= " GROUP BY " . implode(", ", $fields);

        $this->data->type = "GROUP BY";

        return $this;
    }

    public function having(string $field, int $op, mixed $val): static
    {
        if (!in_array($this->data->type, [
            "SELECT",
            "WHERE",
            "GROUP BY",
            "GROUP BY WITH ROLLUP",
        ]))
            throw new \Exception();

        $this->data->query .= " HAVING `$field` "
            . $this->findSignal($op)
            . " ?";

        $this->data->values[] = $val;

        $this->data->type = "HAVING";

        return $this;
    }

    public function orderBy(string ...$fields): static
    {
        if (!in_array($this->data->type, [
            "SELECT",
            "WHERE",
            "GROUP BY",
            "GROUP BY WITH ROLLUP",
            "HAVING"
        ]))
            throw new \Exception();

        $this->data->query .= " ORDER BY " . implode(", ", $fields);

        $this->data->type = "ORDER BY";

        return $this;
    }

    public function limit(int $rows, ?int $offset = null): static
    {
        if (!in_array($this->data->type, [
            "SELECT",
            "WHERE",
            "GROUP BY",
            "GROUP BY WITH ROLLUP",
            "HAVING",
            "ORDER BY",
            "ORDER BY WITH ROLLUP",
        ]))
            throw new \Exception();

        $this->data->query .= " LIMIT "
            . (isset($offset) ? "$offset, $rows" : $rows);

        $this->data->type = "LIMIT";

        return $this;
    }

    public function withRollup(): static
    {
        if (!in_array($this->data->type, ["GROUP BY", "ORDER BY"]))
            throw new \Exception();

        $this->data->query .= " WITH ROLLUP";

        $this->data->type .= " WITH ROOLUP";

        return $this;
    }

    public function insert(string $table): static
    {
        if (!$this->data) $this->reset();

        $this->data->query = "INSERT INTO `$table`";
        $this->data->type = "INSERT";
        $this->data->extras->columns = null;
        $this->data->extras->table = $table;

        return $this;
    }

    public function colNames(string ...$columns): static
    {
        if ($this->data->type !== "INSERT")
            throw new \Exception();

        if ($columns)
            $this->data->query .= "(`" . implode("`, `", $columns) . "`)";

        $this->data->type .= " INTO";
        $this->data->extras->columns = $columns;

        return $this;
    }

    public function values(int $type = self::THROW_ON_NULL, array ...$valueLists): static
    {
        if (!in_array($this->data->type, ["INSERT", "INSERT INTO", "VALUES"]))
            throw new \Exception();

        if ($type < self::THROW_ON_NULL || $type > self::NULL_ON_NULL)
            throw new \Exception();

        if ($this->data->type !== "VALUES")
            $this->data->query .= " VALUES ";

        if (!$this->data->extras->columns) {
            $this->data->extras->columns = array_map(
                fn ($i) => $i->Field,
                $this->dbh->query(
                    "SHOW COLUMNS FROM {$this->data->extras->table};",
                    []
                )->fetchAll(\PDO::FETCH_OBJ)
            );
        }

        $inserts = [];
        foreach ($valueLists as $list) {
            $placeholders = [];

            foreach ($this->data->extras->columns as $column) {
                if ($type == self::THROW_ON_NULL) {
                    if (!isset($list[$column]))
                        throw new \Error("Cannot find value for column");

                    $this->data->values[] = $list[$column];
                } else if ($type == self::DEFAULT_ON_NULL) {
                    if (!isset($list[$column])) {
                        $placeholders[] = "DEFAULT";
                    } else {
                        $placeholders[] = "?";
                        $this->data->values[] = $list[$column];
                    }
                } else if ($type == self::NULL_ON_NULL) {
                    $placeholders[] = "?";
                    $this->data->values[] = $list[$column] ?? null;
                }
            }

            $inserts[] =  "(" . implode(", ", $placeholders) . ")";
        }
        
        $this->data->query .= implode(", ", $inserts);

        $this->data->type = "VALUES";

        return $this;
    }

    public function update(string $table): static
    {
        if (!$this->data) $this->reset();

        $this->data->query = "UPDATE `$table`";
        $this->data->type = "UPDATE";

        return $this;
    }

    public function set(iterable $assignments): static
    {
        if ($this->data->type !== "UPDATE")
            throw new \Exception();

        $this->data->query .= " SET ";

        $set = [];
        foreach ($assignments as $column => $value) {
            $set[] = "$column=?";
            $this->data->values[] = $value;
        }

        $this->data->query .= implode(", ", $set);

        $this->data->type = "SET";

        return $this;
    }

    public function delete(string $table): static
    {
        if (!$this->data) $this->reset();

        $this->data->query = "DELETE FROM `$table`";
        $this->data->type = "DELETE";

        return $this;
    }

    public function getQuery(): string
    {
        return $this->data->query;
    }

    public function getValues(): iterable
    {
        return $this->data->values;
    }

    protected function findSignal(int $op)
    {
        return match($op) {
            static::EQ => "=",
            static::GE => ">=",
            static::GT => ">",
            static::LE => "<=",
            static::LT => "<",
            static::NE => "!=",
        };
    }
}

