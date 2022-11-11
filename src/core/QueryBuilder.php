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

interface QueryBuilder
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

    public function select(string ...$fields): static;

    public function from(string $table): static;

    public function where(string $field, int $op, mixed $val): static;
    public function groupBy(string ...$fields): static;
    public function having(string $field, int $op, mixed $val): static;
    public function orderBy(string ...$fields): static;
    public function limit(int $rows, ?int $offset = null): static;

    public function and(string $field, int $op, mixed $val): static;
    public function or(string $field, int $op, mixed $val): static;
    public function xor(string $field, int $op, mixed $val): static;

    public function withRollup(): static;

    public function insert(string $table): static;
    public function colNames(string ...$columns): static;
    public function values(int $type = self::THROW_ON_NULL, array ...$valueList): static;

    public function update(string $table): static;
    public function set(iterable $assignments): static;

    public function delete(string $table): static;

    //public function innerJoin(string $table): static;

    //public function on(array ...$conditions): static;

    //abstract public function not(string $field, mixed $val): static;

    //public function is(string $field, bool $is = true): static;
}

