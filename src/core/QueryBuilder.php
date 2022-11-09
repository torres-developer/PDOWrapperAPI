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
    public const EQ = 0;
    public const GE = 1;
    public const GT = 2;
    public const LE = 3;
    public const LT = 4;
    public const NE = 5;

    public const THROW_ON_NULL = 0;
    public const DEFAULT_ON_NULL = 1;
    public const NULL_ON_NULL = 2;

    protected Service $dbh;

    protected ?\stdClass $query = null;

    public function __construct(Service $dbh)
    {
        $this->dbh = $dbh;

        $this->reset();
    }

    public function reset(): static
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

    abstract public function insert(string $table): static;
    abstract public function colNames(string ...$columns): static;
    abstract public function values(int $type = self::THROW_ON_NULL, array ...$valueList): static;

    //public function innerJoin(string $table): static;

    //public function on(array ...$conditions): static;

    //abstract public function not(string $field, mixed $val): static;

    //public function is(string $field, bool $is = true): static;

    public function run(): \PDOStatement
    {
        return $this->dbh->fromBuilder($this);
    }

    abstract public function getQuery(): string;
    abstract public function getValues(): array;
}

