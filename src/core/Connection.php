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

abstract class Connection implements ServiceInterface, DataManipulationInterface
{
    use CheckArray;

    private Service $service;

    public function __construct(DataSourceName $dsn, ?array $options = [])
    {
        $this->genDsn($dsn);

        if (!$dsn->hasDsn()) throw new \Exception();

        $this->service = Service::getInstance($dsn, $options);
    }

    abstract protected function genDsn(DataSourceName $dsn): void;

    public function hasService(): bool
    {
        return (bool) $this->service;
    }

    public function beginTransaction(): void
    {
        $this->service->beginTransaction();
    }

    public function commit(): void
    {
        $this->service->commit();
    }

    public function getError(): array
    {
        return $this->service->getError();
    }

    public function inTransaction(): bool
    {
        return $this->service->inTransaction();
    }

    public function getLastID(): string | false
    {
        return $this->service->getLastID();
    }

    public function rollBack(): void
    {
        $this->service->rollBack();
    }

    final public function getBuider(): QueryBuilder
    {
        return $this->service->getBuider();
    }

    public function fromBuilder(QueryBuilder $query): \PDOStatement
    {
        return $this->service->fromBuilder($query);
    }
}

