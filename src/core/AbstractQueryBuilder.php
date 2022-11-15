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

/**
 * An basic base for all \TorresDeveloper\PdoWrapperAPI\Core\QueryBuilder
 *
 * @author João Torres <torres.dev@disroot.org>
 */
abstract class AbstractQueryBuilder implements QueryBuilder
{
    /**
     * @var \TorresDeveloper\PdoWrapperAPI\Core\Connection $dbh An proxy for our database
     */
    protected Connection $dbh;

    /**
     * @var \stdClass $query Auxiliary key => value object to help in some operations
     *
     * TODO: Maybe create a class for this.
     */
    protected ?\stdClass $query = null;

    /**
     * The __construct of an \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder
     *
     * @param \TorresDeveloper\PdoWrapperAPI\Core\Connection $dbh
     *
     * @throws \RuntimeException In case of no connection with a database
     *
     * @return \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder
     */
    public function __construct(Connection $dbh)
    {
        if (!$dbh->hasService()) {
            throw new \RuntimeException("Something went wrong, could not have "
                . "access to the database service.");
        }

        $this->dbh = $dbh;

        $this->reset();
    }

    /**
     * Resets the \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder so you can create an query from the start.
     *
     * @return \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder
     */
    public function reset(): static
    {
        $this->query = new \stdClass();

        $this->query->values = [];

        return $this;
    }

    /**
     * Executes the final query and returns an \PDOStatement so you can check the results.
     *
     * @return \PDOStatement
     */
    public function run(): \PDOStatement
    {
        return $this->dbh->fromBuilder($this);
    }

    /**
     * Returns the current SQL query
     *
     * @return string
     */
    abstract public function getQuery(): string;

    /**
     * Returns the values that will be binded for the placeholders '?'
     * TODO: link to '?' docs
     *
     * @return array
     */
    abstract public function getValues(): array;
}
