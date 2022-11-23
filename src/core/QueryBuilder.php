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
 * @copyright Copyright (C) 2022 João Torres
 * @license https://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License
 * @license https://opensource.org/licenses/AGPL-3.0 GNU Affero General Public License version 3
 *
 * @since 2.0.0
 * @version 1.0.0
 */

namespace TorresDeveloper\PdoWrapperAPI\Core;

/**
 * @todo summary
 *
 * @author João Torres <torres.dev@disroot.org>
 *
 * @since 2.0.0
 * @version 1.0.0
 */
interface QueryBuilder
{
    /**
     * Represents the -eq POSIX shell conditional operator (=).
     *
     * @var int
     */
    public const EQ = 0;
    /**
     * Represents the -ge POSIX shell conditional operator (>=).
     *
     * @var int
     */
    public const GE = 1;
    /**
     * Represents the -gt POSIX shell conditional operator (>).
     *
     * @var int
     */
    public const GT = 2;
    /**
     * Represents the -le POSIX shell conditional operator (<=).
     *
     * @var int
     */
    public const LE = 3;
    /**
     * Represents the -lt POSIX shell conditional operator (<).
     *
     * @var int
     */
    public const LT = 4;
    /**
     * Represents the -ne POSIX shell conditional operator (!= or <>).
     *
     * @var int
     */
    public const NE = 5;

    /**
     * Mode to throw an \Exception in case a value is null.
     *
     * @var int
     */
    public const THROW_ON_NULL = 0;

    /**
     * Mode to use the DEFAULT keyword in case a value is null.
     *
     * Using this mode might cause problems if the table column hasn't a DEFAULT value.
     *
     * @var int
     */
    public const DEFAULT_ON_NULL = 1;

    /**
     * Mode to use the NULL keyword in case a value is null.
     *
     * Using this mode might cause problems if the table column can't be NULL.
     *
     * @var int
     */
    public const NULL_ON_NULL = 2;

    /**
     * @api
     */
    public function select(string ...$fields): static;

    /**
     * @api
     */
    public function from(string $table): static;

    /**
     * @api
     */
    public function where(string $field, int $op, mixed $val): static;
    
    /**
     * @api
     */
    public function groupBy(string ...$fields): static;
    
    /**
     * @api
     */
    public function having(string $field, int $op, mixed $val): static;
    
    /**
     * @api
     */
    public function orderBy(string ...$fields): static;
    
    /**
     * @api
     */
    public function limit(int $rows, ?int $offset = null): static;

    /**
     * Adds an AND SQL statement (`AND field OPERATOR value`) to the query.
     *
     * @uses \TorresDeveloper\PdoWrapper\API\Core\QueryBuilder::EQ for the $op.
     * @uses \TorresDeveloper\PdoWrapper\API\Core\QueryBuilder::GE for the $op.
     * @uses \TorresDeveloper\PdoWrapper\API\Core\QueryBuilder::GT for the $op.
     * @uses \TorresDeveloper\PdoWrapper\API\Core\QueryBuilder::LE for the $op.
     * @uses \TorresDeveloper\PdoWrapper\API\Core\QueryBuilder::LT for the $op.
     * @uses \TorresDeveloper\PdoWrapper\API\Core\QueryBuilder::NE for the $op.
     *
     * @param string $field
     * @param int $op
     * @param mixed $val
     *
     * @return $this For a fluent interface using method chaining.
     *
     * @api
     */
    public function and(string $field, int $op, mixed $val): static;
    
    /**
     * @api
     */
    public function or(string $field, int $op, mixed $val): static;
    
    /**
     * @api
     */
    public function xor(string $field, int $op, mixed $val): static;

    /**
     * @api
     */
    public function withRollup(): static;

    /**
     * @api
     */
    public function insert(string $table): static;
    
    /**
     * @api
     */
    public function colNames(string ...$columns): static;

    /**
     * @uses \TorresDeveloper\PdoWrapper\API\Core\QueryBuilder::THROW_ON_NULL for the $type.
     * @uses \TorresDeveloper\PdoWrapper\API\Core\QueryBuilder::NULL_ON_NULL for the $type.
     * @uses \TorresDeveloper\PdoWrapper\API\Core\QueryBuilder::DEFAULT_ON_NULL for the $type.
     *
     * @api
     */
    public function values(int $type = self::THROW_ON_NULL, array ...$valueList): static;

    /**
     * @api
     */
    public function update(string $table): static;
    
    /**
     * @api
     */
    public function set(iterable $assignments): static;

    /**
     * @api
     */
    public function delete(string $table): static;

    //public function innerJoin(string $table): static;
    //public function on(array ...$conditions): static;
    //public function not(string $field, mixed $val): static;
    //public function is(string $field, bool $is = true): static;
    
    /**
     * @api
     */
    public function reset(): static;
}

