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
 * Proxy for a \TorresDeveloper\PdoWrapperAPI\Core\Service.
 *
 * @see \TorresDeveloper\PdoWrapperAPI\Core\Service See what the real service does
 *
 * @uses \TorresDeveloper\PdoWrapperAPI\Core\Service
 * @uses \TorresDeveloper\PdoWrapperAPI\Core\ServiceInterface
 * @uses \TorresDeveloper\PdoWrapperAPI\Core\DataManipulationInterface
 *
 * @author João Torres <torres.dev@disroot.org>
 *
 * @since 2.0.0
 * @version 1.0.0
 */
abstract class Connection implements ServiceInterface, DataManipulationInterface
{
    use CheckArray;

    /**
     * @var \TorresDeveloper\PdoWrapperAPI\Core\Service The real service.
     */
    private Service $service;

    /**
     * @var string[] Array with PREG regexes of invalid patterns in SQL queries to prevent SQL injection.
     */
    public const INVALID_PATTERNS = [
        "/OR\s+1\s*=\s*1/i",    // OR 1=1
        "/\"\s+OR\s+\"\"=\"/i", // " OR ""="
        "/;/",                  // ;
        "/--/",                 // --
        "/\/\*.*\*\//"          // /* */
    ];

    /**
     * @var \PDOStatement[] Cache for results of SELECT SQL queries.
     */
    protected array $cache = [];

    /**
     * @param \TorresDeveloper\PdoWrapperAPI\Core\DataSourceName $dsn Information that helps creating a \PDO object.
     * @param array|null                                         $opts Some extra opitional options for the \PDO object.
     *
     * @see \TorresDeveloper\PdoWrapperAPI\Core\Service
     *
     * @uses \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder::genDSN()
     * @uses \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder::genDriver()
     *
     * @uses \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder::$service
     *
     * @throws \RuntimeException In case of a bad dsn string for the \PDO __construct.
     */
    public function __construct(DataSourceName $dsn, ?array $opts = [])
    {
        $dsn->setDsn($this->genDSN($dsn));
        $dsn->setDriver($this->genDriver());

        if (!$dsn->hasDsn()) {
            throw new \RuntimeException("Could not generate resources to "
                . "connect to the database.");
        }

        $this->service = Service::getInstance($dsn, $opts);
    }

    /**
     * Returns a dsn string to use on the \PDO __construct using information from the $dsn object.
     *
     * @param \TorresDeveloper\PdoWrapperAPI\Core\DataSourceName $dsn Object with some information to create the dsn
     *                                                                string.
     *
     * @return string
     */
    abstract protected function genDSN(DataSourceName $dsn): string;

    /**
     * Returns the driver for which the \TorresDeveloper\PdoWrapperAPI\Core\Connection is made for. Supported driver
     * names are the ones returned from \PDO::getAvailableDrivers().
     *
     * @return string
     */
    abstract protected function genDriver(): string;

    /**
     * Tests if the service is null
     *
     * @api
     *
     * @return bool
     */
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

    final public function query(
        \PDOStatement|string $statement,
        ?array $values = null
    ): \PDOStatement {
        if (is_string($statement))
            $statement = $this->createPDOStatement($statement);

        $values ??= [];

        if ($injection = self::checkForSQLInjections($values)) {
            throw new \DomainException("Dangerous user input `$injection`.");
        }

        if (str_starts_with($statement->queryString, "SELECT")) {
            $key = $this->genQuery($statement, $values);

            if (!isset($this->cache[$key])) {
                $this->cache[$key] = $this->service->query($statement, $values);
            }

            return $this->cache[$key];
        }

        return $this->service->query($statement, $values);
    }

    /**
     * @return \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder This QueryBuilder is the one for the specific
     *                                                                  driver returned from genDriver().
     */
    final public function getBuilder(): AbstractQueryBuilder
    {
        return new ("TorresDeveloper\\PdoWrapperAPI\\"
            . ucfirst($this->service->getDriver())
            . "QueryBuilder")($this);
    }

    /**
     * @param \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder $query The QueryBuilder you want to execute.
     *
     * @return \PDOStatement
     */
    public function fromBuilder(AbstractQueryBuilder $query): \PDOStatement
    {
        return $this->query(
            $this->createPDOStatement($query),
            $query->getValues()
        );
    }

    /**
     * @param \PDOStatement $statement The query with placeholders.
     * @param array         $values    Values for the placeholders in $statement.
     *
     * @return string The string for the SQL query.
     */
    public function genQuery(\PDOStatement $statement, array $values): string
    {
        $i = 0;
        return preg_replace_callback(
            "/\?/",
            function () use ($values, $i): string {
                $v = $values[$i++] ?? null;

                if (!isset($v)) {
                    return "NULL";
                }

                if (is_bool($v)) {
                    return $v ? "TRUE" : "FALSE";
                }

                if (is_string($v)) {
                    return $this->service->getPDO($this)->quote($v);
                }

                return (string) $v;
            },
            $statement->queryString
        );
    }

    /**
     * @param array $values Values for the placeholders in a \PDOStatement.
     *
     * @return null|string The possible dangerous string.
     */
    public static function checkForSQLInjections(array $values): ?string
    {
        foreach ($values as $v) {
            if (!is_string($v)) {
                continue;
            }

            foreach (static::INVALID_PATTERNS as $regex) {
                if (preg_match($regex, $v)) {
                    return $v;
                }
            }
        }

        return null;
    }

    /**
     * @param \TorresDeveloper\PdoWrapperAPI\Core\AbstractQueryBuilder|string $statement
     *
     * @return \PDOStatement
     */
    protected function createPDOStatement(
        AbstractQueryBuilder | string $statement
    ): \PDOStatement {
        if ($statement instanceof QueryBuilder) {
            $statement = $statement->getQuery();
        }

        // XXX GETPDO needs to change!!!
        $pdo = $this->service->getPDO($this);

        $statement = $pdo->prepare($statement);

        if (!$statement) {
            throw new \RuntimeException($pdo->errorInfo()[2]);
        }

        return $statement;
    }
}

