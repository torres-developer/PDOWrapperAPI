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
 * Singleton 
 *
 * @see https://refactoring.guru/design-patterns/singleton/php/example Singleton
 */

abstract class PDOSingleton implements DataManipulationInterface
{
    use CheckArray, ParamTypeFinder;

    protected \PDO $pdo;

    public string | false $lastID;

    private static $instances = [];

    final protected function __construct(PDODataSourceName $dsn, array $options)
    {
        try {
            $this->pdo = new \PDO(
                $this->genDsn($dsn),
                $dsn->credentials->name ?? null,
                $dsn->credentials->password ?? null,
                array_merge([
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ], $options)
            );
        } catch (\PDOException $e) {
            throw $e;
        }
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
        PDODataSourceName $dsn,
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

    abstract protected function genDsn(PDODataSourceName $dsn): string;

    final protected function query(
        \PDOStatement | string $statement,
        ?array $values = null,
    ): \PDOStatement {
        if (is_string($statement))
            $statement = $this->createPDOStatement($statement);

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

    final public function getBuider(): QueryBuilder
    {
        return new (substr(static::class, 0, -3) . "QueryBuilder")($this);
    }

    final public function fromBuilder(QueryBuilder $query): \PDOStatement
    {
        return $this->query($this->createPDOStatement($query), $query->getValues());
    }

    protected function createPDOStatement(QueryBuilder | string $statement): \PDOStatement
    {
        if ($statement instanceof QueryBuilder) $statement = $statement->getQuery();

        $statement = $this->pdo->prepare($statement);

        if (!$statement) throw new \Error();

        return $statement;
    }

    public function getError(): array
    {
        return $this->pdo->errorInfo();
    }
}
