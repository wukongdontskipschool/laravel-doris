<?php

namespace Wukongdontskipschool\LaravelDoris\Database;

use Illuminate\Database\MySqlConnection;

class DorisConnection extends MySqlConnection
{
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {

        $this->pdo = $pdo;

        // First we will setup the default properties. We keep track of the DB
        // name we are connected to since it is needed when some reflective
        // type commands are run such as checking whether a table exists.
        $this->database = $database;

        $this->tablePrefix = $tablePrefix;

        $this->config = $config;

        // We need to initialize a query grammar and the query post processors
        // which are both very important parts of the database abstractions
        // so we initialize these to their default values while starting.
        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
    }

    /**
     * Bind values to their parameters in the given statement.
     * 重写加入null
     * @param  \PDOStatement  $statement
     * @param  array  $bindings
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                is_null($value) ? \PDO::PARAM_NULL : (is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR)
            );
        }
    }
}
