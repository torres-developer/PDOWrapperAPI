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
 * @package TorresDeveloper\\PdoWrapperAPI
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
    protected \PDO $pdo;

    public $lastID;

    private static $instances = [];

    final protected function __construct(PDODataSourceName $dsn, array $options)
    {
        try {
            $this->pdo = new \PDO(
                $this->genDsn($dsn),
                $dsn->credentials->name,
                $dsn->credentials->password,
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
    ): PDOSingleton {
        $class = static::class;

        $dsncode = (string) $dsn;

        if (!isset(self::$instances[$class][$dsncode]))
            self::$instances[$class][$dsncode] = new static($dsn, $options);

        return self::$instances[$class][$dsncode];
    }

    abstract protected function genDsn(PDODataSourceName $dsn): string;

    final protected function query(
        \PDOStatement | string $statement,
        ?array $values = null,
    ): \PDOStatement {
        if (is_string($statement))
            $statement = $this->createPDOStatement($statement);


        if (!$statement->execute($values)) {
            $this->pdo->inTransaction() AND $this->pdo->rollBack();

            $error = $statement->errorInfo();

            throw new \Error((string) $error);
        }

        $this->lastID = $this->pdo->lastInsertId();

        return $statement;
    }

    protected function createPDOStatement(string $statement): \PDOStatement
    {
        $statement = $this->pdo->prepare($statement);

        if (!$statement) throw new \Error();

        return $statement;
    }

    public function getError(): array
    {
        return $this->pdo->errorInfo();
    }
}

