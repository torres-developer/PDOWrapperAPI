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

namespace TorresDeveloper\PdoWrapperAPI\Core;

final class DataSourceName
{
    public array $info = [];
    public ?Credentials $credentials;

    private string $dsn;

    private ?string $driver = null;

    public function __construct(
        array $info,
        ?Credentials $credentials = null
    ) {
        $this->info = $info;
        $this->credentials = $credentials;
    }

    public function getDsn(): string {
        return $this->dsn;
    }

    public function setDsn(string $dsn): void {
        $this->dsn = $dsn;
    }

    public function hasDsn(): bool {
        return (bool) $this->dsn;
    }

    public function setDriver(string $driver): void {
        if (in_array($driver, \PDO::getAvailableDrivers(), true))
            $this->driver = $driver;
    }

    public function getDriver(): string {
        return $this->driver;
    }

    public function __toString(): string
    {
        ksort($this->info);
        return json_encode($this);
    }
}

