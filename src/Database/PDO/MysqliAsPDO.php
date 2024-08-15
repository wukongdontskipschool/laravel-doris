<?php

namespace Wukongdontskipschool\LaravelDoris\Database\PDO;

use \PDO;
use Wukongdontskipschool\LaravelDoris\Database\PDO\MysqliStmtAsPDOStatement;

/**
 * @deprecated
 */
class MysqliAsPDO extends PDO
{
    /**
     * @var \mysqli
     */
    protected $mysqli = null;

    /**
     * @param array $dsn // 为config 因为mysqli没有dsn连接方式
     * @param string $username
     * @param string $password
     * @param array $options
     */
    public function __construct($dsn, $username, $password, $options = array())
    {
        $this->mysqli = mysqli_init();

        // 设置
        $this->buildOptions($options);

        //initiate the connection to the server, using both previously specified timeouts
        $this->mysqli->real_connect(
            $dsn['host'],
            $dsn['username'] ?? null,
            $dsn['password'] ?? null,
            $dsn['database'],
            $dsn['port'],
        );

        parent::__construct($dsn['dsn'], $username, $password, $options);
    }

    private function buildOptions(array $options)
    {
        //specify the connection timeout
        $timeout = $options[PDO::ATTR_TIMEOUT] ?? 30;
        $this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);

        if (isset($options[PDO::ATTR_EMULATE_PREPARES])) {
            // 是否转int为字符串 跟配置取反
            $flag = !$options[PDO::ATTR_EMULATE_PREPARES];
            $this->mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, $flag);
        }
    }

    public function exec(string $statement): int|false
    {
        return $this->mysqli->query($statement);
    }

    public function prepare(string $query, array $options = []): MysqliStmtAsPDOStatement|false
    {
        return new MysqliStmtAsPDOStatement($this->mysqli, $query, $options);
    }

    /**
     * doris 数据库没有lastInsertId
     * @return string|false // 0
     */
    public function lastInsertId(string|null $name = null): string|false
    {
        return $this->mysqli->insert_id;
    }
}
