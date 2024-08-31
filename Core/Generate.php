<?php

namespace Core;

class Generate
{
    private $sql = "";
    private $id_name = '';
    private $selectColumns = '*';
    private $joins = [];
    private $whereClauses = [];
    private $parameters = [];
    private $parentTable; // Property to hold the parent table name

    public function createTable($table_name)
    {
        $this->id_name .= $table_name . '_id';
        $this->sql .= "CREATE TABLE IF NOT EXISTS `$table_name` (\n";
    }

    public function id()
    {
        $this->sql .= "  $this->id_name INT PRIMARY KEY AUTO_INCREMENT,\n";
    }

    public function string($column_name)
    {
        $this->sql .= "  `$column_name` VARCHAR(255) NOT NULL,\n";
    }

    public function bigString($column_name)
    {
        $this->sql .= "  `$column_name` VARCHAR(555) NOT NULL,\n";
    }

    public function int($column_name)
    {
        $this->sql .= "  `$column_name` INT(12) NOT NULL,\n";
    }

    public function bigInt($column_name, $default = null)
    {
        $this->sql .= "  `$column_name` INT(15) NOT NULL";

        if ($default !== null) {
            $this->sql .= " DEFAULT $default";
        }

        $this->sql .= ",\n";
    }

    public function belongsTo($column_name, $references, $constraint_name = null)
    {
        list($table, $column) = explode('::', $references);
        $constraint_name = $constraint_name ?: "fk_$column_name";
        $this->sql .= "  CONSTRAINT `$constraint_name` FOREIGN KEY (`$column_name`) REFERENCES `$table`(`$column`),\n";
    }

    public function setParentTable($tableName)
    {
        $this->parentTable = $tableName;
        return $this;
    }

    public function select($columns = '*')
    {
        $this->selectColumns = $columns;
        return $this;
    }

    public function join($table, $onCondition)
    {
        // Ensure table name and alias are correctly formatted
        // dd($table);
        $tableAndSymb = explode(' ', $table);
        // dd($tableAndSymb);
        $this->joins[] = "INNER JOIN `$tableAndSymb[0]`  $tableAndSymb[1] ON $onCondition";

        return $this;
    }

    public function where($condition, $parameter = null)
    {
        $this->whereClauses[] = $condition;
        if ($parameter !== null) {
            $this->parameters[] = $parameter;
        }
        return $this;
    }

    public function getSql()
    {
        if (!empty($this->sql)) {
            // Creating a table
            $this->sql = rtrim($this->sql, ",\n");
            $this->sql .= "\n);";
            return $this->sql;
        } else {
            // Generating a SELECT query
            if (!$this->parentTable) {
                throw new \Exception("Parent table is not set.");
            }

            $sql = "SELECT " . $this->selectColumns . " FROM ";
            $sql .= "`$this->parentTable` p "; // Specify the parent table and alias
            $sql .= implode(" ", $this->joins); // Adds JOIN clauses
            if (!empty($this->whereClauses)) {
                $sql .= " WHERE " . implode(" AND ", $this->whereClauses); // Adds WHERE clauses
            }
            return $sql;
        }
    }
}
