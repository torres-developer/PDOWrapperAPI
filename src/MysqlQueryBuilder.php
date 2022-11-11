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
        if (!$this->query) $this->reset();

        $fields = array_filter($fields, fn ($i) => $i);

        if (!$fields) $fields = ["*"];

        $this->query->base = "SELECT " . implode(", ", $fields);
        $this->query->type = "SELECT";

        return $this;
    }

    public function from(string $table): static
    {
        if ($this->query->type !== "SELECT")
            throw new \Exception();

        $this->query->base .= " FROM $table";

        return $this;
    }

    public function where(string $field, int $op, mixed $val): static
    {
        if (!in_array($this->query->type, ["SELECT", "SET", "DELETE"]))
            throw new \Exception();

        $this->query->base .= " WHERE `$field` " . $this->findSignal($op) . " ?";

        $this->query->values[] = $val;

        $this->query->type = "WHERE";

        return $this;
    }

    public function and(string $field, int $op, mixed $val): static
    {
        if (!in_array($this->query->type, ["WHERE", "HAVING"]))
            throw new \Exception();

        $this->query->base .= " && `$field` " . $this->findSignal($op) . " ?";

        $this->query->values[] = $val;

        return $this;
    }

    public function or(string $field, int $op, mixed $val): static
    {
        if (!in_array($this->query->type, ["WHERE", "HAVING"]))
            throw new \Exception();

        $this->query->base .= " || `$field` " . $this->findSignal($op) . " ?";

        $this->query->values[] = $val;

        return $this;
    }

    public function xor(string $field, int $op, mixed $val): static
    {
        if (!in_array($this->query->type, ["WHERE", "HAVING"]))
            throw new \Exception();

        $this->query->base .= " XOR `$field` " . $this->findSignal($op) . " ?";

        $this->query->values[] = $val;

        return $this;
    }

    // TODO: The WHERE can't be from an UPDATE. same for having, orderBy, limit
    public function groupBy(string ...$fields): static
    {
        if (!in_array($this->query->type, ["SELECT", "WHERE"]))
            throw new \Exception();

        $this->query->base .= " GROUP BY " . implode(", ", $fields);

        $this->query->type = "GROUP BY";

        return $this;
    }

    public function having(string $field, int $op, mixed $val): static
    {
        if (!in_array($this->query->type, [
            "SELECT",
            "WHERE",
            "GROUP BY",
            "GROUP BY WITH ROLLUP",
        ]))
            throw new \Exception();

        $this->query->base .= " HAVING `$field` "
            . $this->findSignal($op)
            . " ?";

        $this->query->values[] = $val;

        $this->query->type = "HAVING";

        return $this;
    }

    public function orderBy(string ...$fields): static
    {
        if (!in_array($this->query->type, [
            "SELECT",
            "WHERE",
            "GROUP BY",
            "GROUP BY WITH ROLLUP",
            "HAVING"
        ]))
            throw new \Exception();

        $this->query->base .= " ORDER BY " . implode(", ", $fields);

        $this->query->type = "ORDER BY";

        return $this;
    }

    public function limit(int $rows, ?int $offset = null): static
    {
        if (!in_array($this->query->type, [
            "SELECT",
            "WHERE",
            "GROUP BY",
            "GROUP BY WITH ROLLUP",
            "HAVING",
            "ORDER BY",
            "ORDER BY WITH ROLLUP",
        ]))
            throw new \Exception();

        $this->query->base .= " LIMIT "
            . (isset($offset) ? "$offset, $rows" : $rows);

        $this->query->type = "LIMIT";

        return $this;
    }

    public function withRollup(): static
    {
        if (!in_array($this->query->type, ["GROUP BY", "ORDER BY"]))
            throw new \Exception();

        $this->query->base .= " WITH ROLLUP";

        $this->query->type .= " WITH ROOLUP";

        return $this;
    }

    public function insert(string $table): static
    {
        if (!$this->query) $this->reset();

        $this->query->base = "INSERT INTO `$table`";
        $this->query->type = "INSERT";
        $this->query->columns = null;
        $this->query->table = $table;

        return $this;
    }

    public function colNames(string ...$columns): static
    {
        if ($this->query->type !== "INSERT")
            throw new \Exception();

        if ($columns)
            $this->query->base .= "(`" . implode("`, `", $columns) . "`)";

        $this->query->type .= " INTO";
        $this->query->columns = $columns;

        return $this;
    }

    public function values(int $type = self::THROW_ON_NULL, array ...$valueLists): static
    {
        if (!in_array($this->query->type, ["INSERT", "INSERT INTO", "VALUES"]))
            throw new \Exception();

        if ($type < self::THROW_ON_NULL || $type > self::NULL_ON_NULL)
            throw new \Exception();

        if ($this->query->type !== "VALUES")
            $this->query->base .= " VALUES ";

        if (!$this->query->columns) {
            $this->query->columns = array_map(
                fn ($i) => $i->Field,
                $this->dbh->query(
                    "SHOW COLUMNS FROM {$this->query->table};",
                    []
                )->fetchAll(\PDO::FETCH_OBJ)
            );
        }

        $inserts = [];
        foreach ($valueLists as $list) {
            $placeholders = [];

            foreach ($this->query->columns as $column) {
                if ($type == self::THROW_ON_NULL) {
                    if (!isset($list[$column]))
                        throw new \Error("Cannot find value for column");

                    $this->query->values[] = $list[$column];
                } else if ($type == self::DEFAULT_ON_NULL) {
                    if (!isset($list[$column])) {
                        $placeholders[] = "DEFAULT";
                    } else {
                        $placeholders[] = "?";
                        $this->query->values[] = $list[$column];
                    }
                } else if ($type == self::NULL_ON_NULL) {
                    $placeholders[] = "?";
                    $this->query->values[] = $list[$column] ?? null;
                }
            }

            $inserts[] =  "(" . implode(", ", $placeholders) . ")";
        }
        
        $this->query->base .= implode(", ", $inserts);

        $this->query->type = "VALUES";

        return $this;
    }

    public function update(string $table): static
    {
        if (!$this->query) $this->reset();

        $this->query->base = "UPDATE `$table`";
        $this->query->type = "UPDATE";

        return $this;
    }

    public function set(iterable $assignments): static
    {
        if ($this->query->type !== "UPDATE")
            throw new \Exception();

        $this->query->base .= " SET ";

        $set = [];
        foreach ($assignments as $column => $value) {
            $set[] = "$column=?";
            $this->query->values[] = $value;
        }

        $this->query->base .= implode(", ", $set);

        $this->query->type = "SET";

        return $this;
    }

    public function delete(string $table): static
    {
        if (!$this->query) $this->reset();

        $this->query->base = "DELETE FROM `$table`";
        $this->query->type = "DELETE";

        return $this;
    }

    public function getQuery(): string
    {
        return $this->query->base ?? "";
    }

    public function getValues(): array
    {
        return $this->query->values;
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

