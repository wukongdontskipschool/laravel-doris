<?php

namespace Wukongdontskipschool\LaravelDoris\Database\PDO;

use \PDO;
use Wukongdontskipschool\LaravelDoris\Database\PDOTrait\PDOTrait;

/**
 * @deprecated
 */
class MysqliAsPDO extends PDO
{
    use PDOTrait;

    /**
     * 执行语句 返回影响行数
     */
    public function exec(string $statement): int|false
    {
        // parent::exec();
        $stmt = $this->prepare($statement);
        if ($stmt->execute() === false) {
            return false;
        }

        return $stmt->rowCount();
    }

    /**
     * 获取预处理对象
     */
    public function prepare(string $query, array $options = []): MysqliStmtAsPDOStatement|false
    {
        $stmt = new MysqliStmtAsPDOStatement($this->mysqli, $query, $options);
        $stmt->setDBConfig($this->config);
        return $stmt;
    }

    /**
     * doris 数据库没有lastInsertId
     * @return string|false // 0
     */
    public function lastInsertId(?string $name = null): string|false
    {
        // parent::lastInsertId();
        return $this->mysqli->insert_id;
    }
}
