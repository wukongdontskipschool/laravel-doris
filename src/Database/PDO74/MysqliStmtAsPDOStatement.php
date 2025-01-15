<?php

namespace Wukongdontskipschool\LaravelDoris\Database\PDO74;

use \PDOStatement;
use Wukongdontskipschool\LaravelDoris\Database\PDOTrait\PDOStatementTrait;

/**
 * @deprecated
 */
class MysqliStmtAsPDOStatement extends PDOStatement
{
    use PDOStatementTrait;

    /**
     * @param int $mode
     * @param mixed ...$args
     */
    public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL): array
    {
        // return parent::fetchAll($mode, ...$args);
        $result = [];
        if ($this->fetchMode === \PDO::FETCH_OBJ) {
            while ($row = $this->queryResult->fetch_object()) {
                $result[] = $row;
            }
        } else {
            $result = $this->queryResult->fetch_all(MYSQLI_ASSOC);
        }

        return $result;
    }

    /**
     * 获取单行数据
     * @param int $mode
     * @param int $cursorOrientation
     * @param int $cursorOffset
     * @return mixed
     */
    public function fetch($mode = \PDO::FETCH_ASSOC, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0) {
        // return parent::fetch();
        if ($this->fetchMode === \PDO::FETCH_OBJ) {
            return $this->queryResult->fetch_object();
        }

        return $this->queryResult->fetch_assoc();
    }
}
