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
 * @since 2.0.0
 * @version 1.0.0
 */

declare(strict_types=1);

namespace TorresDeveloper\PdoWrapperAPI\Core;

/**
 * Holds data that a \TorresDeveloper\PdoWrapperAPI\Core\QueryBuilder may use.
 *
 * @uses \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder
 *
 * @author João Torres <torres.dev@disroot.org>
 *
 * @since 2.0.0
 * @version 1.0.0
 *
 * @internal
 */
class QueryBuilderData
{
    /**
     * @var \TorresDeveloper\PdoWrapperAPI\Core\QueryBuilder The builder which the data is from
     */
    protected QueryBuilder $builder;

    /**
     * @var array Values that will be binded for the '?' placeholders.
     */
    public array $values;

    /**
     * @var string The query string
     */
    public string $query;

    /**
     * @var string Keeps track of what type of query we are working on
     *
     * It can be maybe type SELECT, or type INSERT, or WHERE, and many others types.
     */
    public string $type;

    /**
     * @var \stdClass To use for some extra needs
     */
    public \stdClass $extras;

    /**
     * @uses \TorresDeveloper\PdoWrapperAPI\Core\QueryBuilderData::$builder
     */    
    public function __construct(QueryBuilder $buider)
    {
        $this->builder = $buider;

        $this->reset();
    }

    /**
     * Supposed to be called when the builder resets
     *
     * @internal
     */
    public function reset(): void
    {
        $this->values = [];
        $this->query = "";
        $this->type = "";
        $this->extras = new \stdClass();
    }
}
