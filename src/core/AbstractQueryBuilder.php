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

declare(strict_types=1);

namespace TorresDeveloper\PdoWrapperAPI\Core;

/**
 * An basic base for all \TorresDeveloper\PdoWrapperAPI\Core\QueryBuilder.
 *
 * @see \TorresDeveloper\PdoWrapperAPI\Core\QueryBuilderData To know what kind of data can be saved
 *
 * @uses \TorresDeveloper\PdoWrapperAPI\Core\QueryBuilder
 * @uses \TorresDeveloper\PdoWrapperAPI\Core\Connection
 * @uses \TorresDeveloper\PdoWrapperAPI\Core\QueryBuilderData To hold some data for the builder
 *
 * @author João Torres <torres.dev@disroot.org>
 *
 * @since 2.0.0
 * @version 1.0.0
 */
abstract class AbstractQueryBuilder implements QueryBuilder
{
    /**
     * @var \TorresDeveloper\PdoWrapperAPI\Core\Connection An proxy for our database.
     */
    protected Connection $dbh;

    /**
     * @var \TorresDeveloper\PdoWrapperAPI\Core\QueryBuilderData Auxiliary object to help on holding some data for
     *                                                           future operations.
     */
    protected QueryBuilderData $data;

    /**
     * @uses \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder::$dbh
     * @uses \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder::$data
     *
     * @throws \RuntimeException In case of no connection with a database.
     */
    public function __construct(Connection $dbh)
    {
        if (!$dbh->hasService()) {
            throw new \RuntimeException("Something went wrong, could not have "
                . "access to the database service.");
        }

        $this->dbh = $dbh;

        $this->data = new QueryBuilderData($this);
    }

    /**
     * @inheritdoc
     *
     * @uses \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder::$data
     */
    public function reset(): static
    {
        $this->data->reset();

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @uses \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder::$dbh
     */
    public function run(): \PDOStatement
    {
        return $this->dbh->fromBuilder($this);
    }
}
