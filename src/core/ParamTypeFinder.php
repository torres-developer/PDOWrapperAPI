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

declare(strict_types=1);

namespace TorresDeveloper\PdoWrapperAPI\Core;

/**
 * This trait has a function that returns the best \PDO::PARAM_* for the given value to help with the \PDOStatement
 * bindValue() and bindParam().
 *
 * @author João Torres <torres.dev@disroot.org>
 */
trait ParamTypeFinder
{
    /**
     * Finds best suitable \PDO::PARAM_* for a given value.
     *
     * @param mixed $value
     *
     * @return null|int An \PDO::PARAM_*.
     */
    protected function findParam(mixed $x): ?int
    {
        if (is_null($x)) {
            return \PDO::PARAM_NULL;
        }

        if (is_bool($x)) {
            return \PDO::PARAM_BOOL;
        }

        if (is_int($x)) {
            return \PDO::PARAM_INT;
        }

        if (is_string($x)) {
            return \PDO::PARAM_STR;
        }

        return null;
    }
}

