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
 * Trait with function to help checking if a key is set in an array and getting is value if it is.
 *
 * @author João Torres <torres.dev@disroot.org>
 */
trait CheckArray
{
    /**
     * Checks key is set in an array
     *
     * @param array $arr The array
     * @param int|string|float|bool|null $arr The key.
     *
     * @return bool
     */
    protected function checkArray(
        array $arr,
        int | string | float | bool | null $k
    ): bool {
        return isset($arr[$k]);
    }

    /**
     * Returns the value for a key is it is setted.
     *
     * @param array $arr The array
     * @param int|string|float|bool|null $arr The key.
     *
     * @return mixed
     */
    protected function checkArrayValue(
        array $array,
        int | string | float | bool | null $key
    ): mixed {
        return $this->checkArray($array, $key) ? $array[$key] : null;
    }
}
