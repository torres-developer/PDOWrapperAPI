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
 * @package TorresDeveloper\\PdoWrapperAPI\\Core
 * @author João Torres <torres.dev@disroot.org>
 * @copyright Copyright (C) 2022  João Torres
 * @license https://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License
 * @license https://opensource.org/licenses/AGPL-3.0 GNU Affero General Public License version 3
 *
 * @since 1.0.0
 * @version 1.0.0
 */

namespace TorresDeveloper\PdoWrapperAPI\Core;

abstract class QueryBuilder
{
    const EQ = 0;
    const GE = 1;
    const GT = 2;
    const LE = 3;
    const LT = 4;
    const NE = 5;

    protected PDOSingleton $dbh;

    protected ?\stdClass $query = null;

    public function __construct(PDOSingleton $dbh)
    {
        $this->dbh = $dbh;

        $this->reset();
    }

    protected function reset(): static
    {
        $this->query = new \stdClass();

        $this->query->values = [];

        return $this;
    }

    abstract public function select(string ...$fields): static;

    abstract public function from(string $table): static;

    abstract public function where(string $field, int $op, mixed $val): static;
    abstract public function groupBy(string ...$fields): static;
    abstract public function having(string $field, int $op, mixed $val): static;
    abstract public function orderBy(string ...$fields): static;
    abstract public function limit(int $rows, ?int $offset = null): static;

    abstract public function and(string $field, int $op, mixed $val): static;
    abstract public function or(string $field, int $op, mixed $val): static;
    abstract public function xor(string $field, int $op, mixed $val): static;

    abstract public function withRollup(): static;

    //public function innerJoin(string $table): static;

    //public function on(array ...$conditions): static;

    //abstract public function not(string $field, mixed $val): static;

    //public function is(string $field, bool $is = true): static;

    //public function createPDOStatement(): \PDOStatement;

    final public function run(): \PDOStatement
    {
        return $this->dbh->fromBuilder($this);
    }

    abstract public function getQuery(): string;
    abstract public function getValues(): array;
}

