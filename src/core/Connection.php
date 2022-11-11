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

    public const invalidPatterns = [
        "/OR\s+1\s*=\s*1/i",    // OR 1=1
        "/\"\s+OR\s+\"\"=\"/i", // " OR ""="
        "/;/",                  // ;
        "/--/",                 // --
        "/\/\*.*\*\//"          // /* */
    ];

    /**
     * @var \PDOStatement[] $cache
     */
    private array $cache = [];

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

    /**
     * \PDO interface methods
     */

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

    final public function query(
        \PDOStatement | string $statement,
        ?array $values = null
    ): \PDOStatement {
        if (is_string($statement))
            $statement = $this->createPDOStatement($statement);

        $values ??= [];

        if (!self::checkForSQLInjections($values))
            throw new \Exception();

        if (str_starts_with($statement->queryString, "SELECT")) {
            $key = $this->genQuery($statement, $values);

            if (!isset($this->cache[$key]))
                $this->cache[$key] = $this->service->query($statement, $values);

            return $this->cache[$key];
        }

        return $this->service->query($statement, $values);
    }

    final public function getBuider(): QueryBuilder
    {
        return new ("TorresDeveloper\\PdoWrapperAPI\\"
            . $this->service->getDriver()
            . "QueryBuilder")($this);
    }

    public function fromBuilder(QueryBuilder $query): \PDOStatement
    {
        return $this->query(
            $this->createPDOStatement($query),
            $query->getValues()
        );
    }

    public function genQuery(\PDOStatement $statement, array $values): string
    {
        $i = 0;
        return preg_replace_callback(
            "/\?/",
            function () use ($values, $i): string {
                $v = $values[$i++] ?? null;

                if (!isset($v))
                    return "NULL";

                if (is_bool($v))
                    return $v ? "TRUE" : "FALSE";

                if (is_string($v))
                    return $this->pdo->quote($v);

                return (string) $v;
            },
            $statement->queryString
        );
    }

    public static function checkForSQLInjections(array $values): bool
    {
        foreach ($values as $v)
            foreach (self::invalidPatterns as $regex)
                if (preg_match($regex, $v))
                    return false;

        return true;
    }

    protected function createPDOStatement(
        QueryBuilder | string $statement
    ): \PDOStatement {
        if ($statement instanceof QueryBuilder)
            $statement = $statement->getQuery();

        $statement = $this->service->getPDO($this)->prepare($statement);

        if (!$statement) throw new \Error();

        return $statement;
    }
}

