<?php

namespace Wukongdontskipschool\LaravelDoris\Database\PDO74;

use \PDO;
use Wukongdontskipschool\LaravelDoris\Database\PDOTrait\PDOTrait;

/**
 * @deprecated
 */
class MysqliAsPDO extends PDO
{
    use PDOTrait;

    /**
     * @var \mysqli
     */
    protected $mysqli = null;

    /**
     * 返回影响行数
     * @param string $statement
     * @return int
     * @throws
     */
    public function exec($statement): int
    {
        $stmt = $this->prepare($statement);
        if ($stmt->execute() === false) {
            throw new \PDOException('SQL execute failed');
        }

        return $stmt->rowCount();
    }

    /**
     * @param string $query
     * @param array $options
     * @return MysqliStmtAsPDOStatement
     */
    public function prepare($query, $options = []): MysqliStmtAsPDOStatement
    {
        // parent::prepare();
        return new MysqliStmtAsPDOStatement($this->mysqli, $query, $options);
    }

    /**
     * doris 数据库没有lastInsertId
     * @param ?string $name
     * @return string // 0
     */
    public function lastInsertId($name = null): string
    {
        return $this->mysqli->insert_id;
    }
}
