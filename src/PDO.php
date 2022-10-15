<?php

/**
 *
 */

namespace TorresDeveloper\PdoWrapperAPI;

use PDOStatement;

class PDO extends Core\Singleton implements DataManipulationInterface
{
    private \PDO $pdo;

    public $lastID;

    protected function __construct(
        string $host,
        string $name,
        string $charset,
        string $username,
        string $password
    ) {
        $dsn = "mysql:"
            . "host=$host;"
            . "dbname=$name;";

        $dsn .= $charset ? "charset=$charset" : "";

        $this->pdo = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ]);
    }

    private function query(
        PDOStatement | string $statement,
        ?array $values = null,
    ): PDOStatement {
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

    public function select(
        string|array $columns,
        ?string $table = null
    ): PDOStatement {
        $statement = "SELECT";

        if (!isset($table) && is_string($columns)) {
            $statement .= " *";

            $table = $columns;
        } else {
            if (is_string($columns)) {
                $statement .= " $columns";
            } else {
                $columnsNumber = count($columns);
                for ($i = 0; $i < $columnsNumber; ++$i) {
                    if ($i) $statement .= ",";

                    $statement .= " `{$columns[$i]}`";
                }
            }
        }

        $statement .=  " FROM `$table`;";

        return $this->query($statement);
    }

    public function insert(
        string $table,
        array $columns,
        array ...$values
    ): PDOStatement {
        $statement = "INSERT INTO `$table`";

        $columnsAmount = count($columns);
        $columnsKeys = array_keys($columns);

        $placeHolder = str_repeat(
            "?, ",
            $columnsAmount ? ($columnsAmount - 1) : 0
        ) . "?)";

        if ($columnsAmount) {
            $statement .= "(";

            foreach ($columnsKeys as $index => $key)
                $statement .= $index === $columnsAmount -1
                    ? "`$key`)"
                    : "`$key`, ";
        }

        $statement .= " VALUES";

        $valuesAmount = count($values);
        for ($i = 0; $i < $valuesAmount; ++$i) {
            $statement .= " (" . (!empty($columns)
                ? $placeHolder
                : str_repeat("?, ", (count($values[$i]) - 1)) . "?)");

            if (!$i == $valuesAmount) $statement .= ",";
        }

        $statement .= ";";

        $statement = $this->createPDOStatement($statement);

        if ($columnsAmount) {
            for ($i = 0; $i < $columnsAmount; ++$i) {
                $column = $columnsKeys[$i];
                
                for ($j = 0; $j < $valuesAmount; ++$j) {
                    $value = $values[$j];

                    $statement->bindValue(
                        $j * $columnsAmount + $i + 1,
                        $value[$column] ?? 0,
                        $columns[$column]
                    );
                }
            }
        }

        return $this->query($statement);
    }

    public function update(
        string $table,
        array $columnValue,
        array $columns,
        ?array $conditions
    ): PDOStatement {
        $statement = "UPDATE `$table` SET ";

        $columnKeys = array_keys($columnValue);

        $set = [];
        foreach ($columnKeys as $column) $set[] = "`$column` = :$column";
        $statement .= implode(", ", $set);

        if ($conditions) {
            $statement .= " WHERE ";

            $where = [];
            foreach ($conditions as $field => $value)
                $where[] = " `$field` = $value";
            $statement .= implode(" AND", $where);
        }

        $statement .= ";";

        $statement = $this->createPDOStatement($statement);

        foreach ($columnValue as $column => $value) $statement->bindValue(
            ":$column",
            $value ?? \PDO::ATTR_DEFAULT_STR_PARAM,
            $columns[$column] ?? null
        );

        return $this->query($statement);
    }

    public function delete(string $table, ?array $conditions): PDOStatement
    {
        $statement = "DELETE FROM `$table`";

        if ($conditions) {
            $statement .= " WHERE ";

            $where = [];
            $conditionsKeys = array_keys($conditions);
            foreach ($conditionsKeys as $condition)
                $where[] = " `$condition` = ?";
            $statement .= implode(" AND", $where);
        }

        $statement .= ";";

        return $this->query($statement, array_values($conditions));
    }

    private function createPDOStatement(string $statement): PDOStatement
    {
        $statement = $this->pdo->prepare($statement);

        if (!$statement) throw new \Error();

        return $statement;
    }
}

