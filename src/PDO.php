<?php

/**
 *
 */

namespace TorresDeveloper\PdoWrapperAPI;

class PDO extends Core\Singleton implements DataManipulationInterface
{
    private \PDO $pdo;

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

        return $statement;
    }

    public function select(
        string|array $columns,
        ?string $table = null
    ): \PDOStatement {
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
    ): \PDOStatement {
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

                    echo 
                        $j * $columnsAmount + $i + 1,
                        "\t",
                        $value[$column] ?? \PDO::ATTR_DEFAULT_STR_PARAM,
                        "\t",
                        $columns[$column],
                        PHP_EOL;

                    $statement->bindValue(
                        $j * $columnsAmount + $i + 1,
                        $value[$column] ?? 0,
                        $columns[$column]
                    );
                }
            }
        }

        var_dump($statement);

        return $this->query($statement);
    }

    public function update(
        string $table,
        array $columnValue,
        ?array $conditions
    ) {
        
    }

    public function delete(string $table, ?array $conditions)
    {
        
    }

    private function createPDOStatement(string $statement): \PDOStatement
    {
        $statement = $this->pdo->prepare($statement);

        if (!$statement) throw new \Error();

        return $statement;
    }
}

