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
 * Username and password for the database user.
 *
 * @author João Torres <torres.dev@disroot.org>
 */
final class Credentials
{
    /**
     * @var null|string $name Username.
     */
    private ?string $name = null;
    /**
     * @var null|string $password Password.
     */
    private ?string $password = null;

    /**
     * @var \TorresDeveloper\PdoWrapperAPI\Core\Credentials[] $cache Cache for the credentials.
     */
    private static array $cache = [];
    
    private function __construct(?string $name, ?string $password) {
        $this->name = $name;
        $this->password = $password;
    }

    public static function getCredentials(
        ?string $name,
        ?string $password
    ): static {
        $args = func_get_args();
        $key = json_encode(array_slice($args, 0, 2));

        if (!isset(self::$cache[$key]))
            self::$cache[$key] = new static(...$args);

        return self::$cache[$key];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}

