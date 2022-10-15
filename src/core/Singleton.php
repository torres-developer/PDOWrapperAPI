<?php

/**
 * @link https://refactoring.guru/design-patterns/singleton/php/example
 */

namespace TorresDeveloper\PdoWrapperAPI\Core;

abstract class Singleton
{
    private static $instances = [];

    protected function __construct()
    {
    }

    final protected function __clone()
    {
    }

    final public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    final public static function getInstance(): Singleton
    {
        $class = static::class;

        if (!isset(self::$instances[$class]))
            self::$instances[$class] = new static(...func_get_args());

        return self::$instances[$class];
    }
}

