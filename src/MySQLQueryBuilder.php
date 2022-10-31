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

class MySQLQueryBuilder extends Core\QueryBuilder
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
        if ($this->query->type !== "SELECT")
            throw new \Exception();

        $this->query->base .= " WHERE `$field` " . $this->findSignal($op) . " ?";

        $this->query->values[] = $val;

        $this->query->type = "WHERE";

        return $this;
    }

    public function and(string $field, int $op, mixed $val): static
    {
        if ($this->query->type !== "WHERE")
            throw new \Exception();

        $this->query->base .= " && `$field` " . $this->findSignal($op) . " ?";

        $this->query->values[] = $val;

        return $this;
    }

    public function or(string $field, int $op, mixed $val): static
    {
        if ($this->query->type !== "WHERE")
            throw new \Exception();

        $this->query->base .= " || `$field` " . $this->findSignal($op) . " ?";

        $this->query->values[] = $val;

        return $this;
    }

    public function xor(string $field, int $op, mixed $val): static
    {
        if ($this->query->type !== "WHERE")
            throw new \Exception();

        $this->query->base .= " XOR `$field` " . $this->findSignal($op) . " ?";

        $this->query->values[] = $val;

        return $this;
    }

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
        if (!in_array($this->query->type, ["SELECT", "WHERE", "GROUP BY"]))
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
            "HAVING",
            "ORDER BY"
        ]))
            throw new \Exception();

        $this->query->base .= " LIMIT "
            . (isset($offset) ? "$offset, $rows" : $rows);

        $this->query->type = "LIMIT";

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

