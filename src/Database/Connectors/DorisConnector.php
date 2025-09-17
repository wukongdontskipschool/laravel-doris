<?php

namespace Wukongdontskipschool\LaravelDoris\Database\Connectors;

use Illuminate\Database\Connectors\MySqlConnector;
use PDO;

class DorisConnector extends MySqlConnector
{
    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return \PDO
     */
    public function connect($config)
    {
        $dsn = $this->getDsn($config);
        $options = $this->getOptions($config);
        $cpConfig = $config;
        $cpConfig['dsn'] = $dsn;

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        // 这里mysqli dsn用config
        $connection = $this->createConnection($cpConfig, $config, $options);

        if (!empty($config['database'])) {
            $connection->exec("use `{$config['database']}`;");
        }

        $this->configureConnection($connection, $config);

        return $connection;
    }

    /**
     * Configure the given PDO connection.
     *
     * @param  \PDO  $connection
     * @param  array  $config
     * @return void
     */
    protected function configureConnection(PDO $connection, array $config)
    {
        if (isset($config['isolation_level'])) {
            // doris隔离级别唯一 READ COMMITTED
            // $connection->exec(sprintf('SET SESSION TRANSACTION ISOLATION LEVEL %s;', $config['isolation_level']));
        }

        $statements = [];

        if (isset($config['charset'])) {
            if (isset($config['collation'])) {
                $statements[] = sprintf("NAMES '%s' COLLATE '%s'", $config['charset'], $config['collation']);
            } else {
                $statements[] = sprintf("NAMES '%s'", $config['charset']);
            }
        }

        if (isset($config['timezone'])) {
            $statements[] = sprintf("time_zone='%s'", $config['timezone']);
        }

        $strictMode = $this->getStrictMode($config);
        if ($strictMode !== null) {
            $statements[] = sprintf("enable_insert_strict=%s", $strictMode);
        }

        $sqlMode = $this->getSqlMode($connection, $config);
        if ($sqlMode !== null) {
            $statements[] = sprintf("sql_mode='%s'", $sqlMode);
        }

        if ($statements !== []) {
            $connection->exec(sprintf('SET %s;', implode(', ', $statements)));
        }
    }

    /**
     * Get the sql_mode value.
     *
     * @param  \PDO  $connection
     * @param  array  $config
     * @return string|null
     */
    protected function getSqlMode(PDO $connection, array $config)
    {
        if (isset($config['modes'])) {
            return implode(',', $config['modes']);
        }

        return null;
        // NO_AUTO_CREATE_USER
        // return 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
    }

    protected function getStrictMode(array $config)
    {
        if (! isset($config['strict'])) {
            return null;
        }

        if ($config['strict']) {
            return 'true';
        }

        return 'false';
    }

    /**
     * Create a new PDO connection instance.
     *
     * @param  string  $dsn
     * @param  string  $username
     * @param  string  $password
     * @param  array  $options
     * @return \PDO
     */
    protected function createPdoConnection($dsn, $username, $password, $options)
    {
        // 判断php版本8
        if (strpos(PHP_VERSION, '8') === 0) {
            return new \Wukongdontskipschool\LaravelDoris\Database\PDO\MysqliAsPDO($dsn, $username, $password, $options);
        } else {
            return new \Wukongdontskipschool\LaravelDoris\Database\PDO74\MysqliAsPDO($dsn, $username, $password, $options);
        }
    }
}
