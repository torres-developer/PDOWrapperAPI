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

interface ServiceInterface
{
    /**
     * Sends the $statement to the service to be executed using the $values.
     *
     * The proxy:
     * - Transforms string $statement into \PDOStatement;
     * - Checks for possible SQL injections in $values;
     * - Checks if it's a SELECT query and if it's results are cached already.
     *
     * After that sends the $statement to the service to be executed using the $values.
     *
     * @internal
     *
     * @param \PDOStatement|string $statement The query to execute.
     * @param null|array           $values    Values for the placeholders in $statement.
     *
     * @throws \DomainException In case of possible SQL injection.
     *
     * @return \PDOStatement Results from the query.
     */
    public function query(
        \PDOStatement | string $statement,
        ?array $values = null,
    ): \PDOStatement;

    /*
     * \PDO interface methods
     */

    public function beginTransaction(): void;
    public function commit(): void;
    public function getError(): array;
    public function inTransaction(): bool;
    public function getLastID(): string | false;
    public function rollBack(): void;
}

