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

class Service implements ServiceInterface
{
    use ParamTypeFinder;

    protected \PDO $pdo;

    protected string | false $lastID;

    protected string $driver;

    private static $instances = [];

    final protected function __construct(DataSourceName $dsn, array $options)
    {
        $credentials = $dsn->getCredentials();

        try {
            $this->pdo = new \PDO(
                $dsn->getDSNString(),
                $credentials->getName() ?? null,
                $credentials->getPassword() ?? null,
                array_merge([
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ], $options)
            );
        } catch (\PDOException $e) {
            throw $e;
        }

        $this->driver = $dsn->getDriver();

        if (!isset($this->driver))
            throw new \Exception();
    }

    final public function __destruct()
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
            $this->pdo->commit();
        }

        unset($this->pdo);
    }


    final protected function __clone()
    {
    }

    final public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    final public static function getInstance(
        DataSourceName $dsn,
        ?array $options = []
    ): static {
        $class = static::class;

        $dsncode = (string) $dsn;

        if (!isset(self::$instances[$class][$dsncode]))
            self::$instances[$class][$dsncode] = new static($dsn, $options);

        return self::$instances[$class][$dsncode];
    }

    final public static function getInstancesListKeys(): array {
        return array_keys(self::$instances[static::class] ?? []);
    }

    /**
     * \PDO interface methods
     */

    public function beginTransaction(): void
    {
        if (!$this->pdo->beginTransaction())
            throw new \Exception("Cannot initiate the transaction");
    }

    public function commit(): void
    {
        if (!$this->pdo->commit())
            throw new \Exception("Cannot commit the transaction");
    }

    public function getError(): array
    {
        return $this->pdo->errorInfo();
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    public function getLastID(): string | false
    {
        return $this->lastID;
    }

    public function rollBack(): void
    {
        if (!$this->pdo->rollBack())
            throw new \Exception("Cannot roll back the transaction");
    }

    final public function query(
        \PDOStatement | string $statement,
        ?array $values = null,
    ): \PDOStatement {
        $valuesAmount = count($values);
        for ($i = 1; $i <= $valuesAmount; ++$i) {
            $value = $values[$i - 1];

            $statement->bindValue($i, $value, $this->findParam($value));
        }

        if (!$statement->execute($values)) {
            $this->pdo->inTransaction() AND $this->pdo->rollBack();

            $error = $statement->errorInfo();

            throw new \Error((string) $error);
        }

        $this->lastID = $this->pdo->lastInsertId();

        return $statement;
    }

    public function &getPDO(ServiceInterface $service): \PDO {
        if ($service instanceof ServiceInterface)
            return $this->pdo;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }
}

