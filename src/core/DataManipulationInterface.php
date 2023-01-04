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
 * @package TorresDeveloper\\PdoWrapperAPI\\Core
 * @author João Torres <torres.dev@disroot.org>
 * @copyright Copyright (C) 2022 João Torres
 * @license https://www.gnu.org/licenses/agpl-3.0.txt GNU Affero General Public License
 * @license https://opensource.org/licenses/AGPL-3.0 GNU Affero General Public License version 3
 *
 * @since 1.0.0
 * @version 2.0.0
 */

declare(strict_types=1);

namespace TorresDeveloper\PdoWrapperAPI\Core;

/**
 * Interface to implement DML operations.
 *
 * @author João Torres <torres.dev@disroot.org>
 *
 * @since 1.0.0
 * @version 2.0.0
 */
interface DataManipulationInterface
{
    /**
     * Retrieve multiple rows from a specified table.
     *
     * @api
     *
     * @param string|string[]|\Traversable $columns Columns to select from the table $table.
     *                                              In case this is a string:
     *                                              * maybe you want to select just one column or;
     *                                              * if $table is null then $columns does the $table job and all
     *                                              columns are selected.
     * @param null|string                  $table   Name of the table to select.
     *                                              If null $columns must be the table name.
     *
     * @return \PDOStatement {@see https://secure.php.net/manual/en/class.pdostatement.php}
     */
    public function select(
        string|iterable $columns,
        ?string $table = null
    ): \PDOStatement;

    /**
     * Create new rows in a table.
     *
     * @api
     *
     * @param string             $table Table to create the new rows $rows.
     * @param array|\Traversable $rows  The rows, each one a iterable column => value.
     *
     * @return \PDOStatement {@see https://secure.php.net/manual/en/class.pdostatement.php}
     */
    public function insert(
        string $table,
        iterable ...$rows
    ): \PDOStatement;


    /**
     * Update rows of a table.
     *
     * @api
     *
     * @param string   $table       Table to update the rows.
     * @param iterable $assignments What to update and its new assignment, an iterable column => value.
     * @param iterable $conditions  Some conditions to filter rows to update.
     *                              An iterable column => value.
     *                              For a row to be updated, (column == value) needs to be true for all conditions.
     *
     * @return \PDOStatement {@see https://secure.php.net/manual/en/class.pdostatement.php}
     */
    public function update(
        string $table,
        iterable $assignments,
        iterable $conditions = []
    ): \PDOStatement;

    /**
     * Delete rows of a table
     *
     * @api
     *
     * @param string   $table       Table to delete the rows.
     * @param iterable $conditions  Some conditions to filter rows to delete.
     *                              An iterable column => value.
     *                              For a row to be deleted, (column == value) needs to be true for all conditions.
     *
     * @return \PDOStatement {@see https://secure.php.net/manual/en/class.pdostatement.php}
     */
    public function delete(
        string $table,
        iterable $conditions = []
    ): \PDOStatement;
}
