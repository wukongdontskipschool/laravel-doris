<?php

namespace Wukongdontskipschool\LaravelDoris\Database\PDO;

use Illuminate\Database\QueryException;
use \PDOStatement;

use function PHPUnit\Framework\isNull;

/**
 * @deprecated
 */
class MysqliStmtAsPDOStatement extends PDOStatement
{
    /**
     * @var \mysqli_stmt
     */
    protected $stmt;

    /**
     * @var \mysqli
     */
    protected $mysqli;

    /**
     * @var string // 待执行sql
     */
    protected $sql = '';

    /**
     * @var array // 执行参数
     */
    protected $options = [];

    /**
     * @var array // 实际绑定参数 支持? 不支持:name
     */
    private $realBoundParams = [];

    private $mysqliBindings = [];

    /**
     * @var \mysqli_result
     */
    private $queryResult = null;

    public function __construct(\mysqli $mysqli, $sql, $options = [])
    {
        $this->stmt = new \mysqli_stmt($mysqli, null);
        $this->sql = $sql;
        $this->options = $options;
        $this->mysqli = $mysqli;
    }

    public function execute(array|null $params = null): bool
    {
        $sql = $this->buildSql();

        // echo json_encode(['execute', $sql]) . '<br/>' . PHP_EOL;
        try {
            $res = $this->mysqli->query($sql);
        } catch (\Throwable $e) {
            throw $e;
        }

        if ($res instanceof \mysqli_result) {
            $this->queryResult = $res;
            return true;
        }

        return $res;
    }

    public function setFetchMode($mode, $className = null, ...$params)
    {
        // return parent::setFetchMode($mode, $className, ...$params);
    }

    public function bindValue(int|string $param, mixed $value, int $type = \PDO::PARAM_STR): bool
    {
        switch ($type) {
            case \PDO::PARAM_INT:
                $value = intval($value);
                break;
            case \PDO::PARAM_STR:
                $value = "'" . $this->mysqli->real_escape_string($value) . "'";
                break;
            case \PDO::PARAM_BOOL:
                $value = boolval($value);
                break;
            case \PDO::PARAM_NULL:
                $value = null;
                break;
            case \PDO::PARAM_LOB:
                $value = null;
                break;
        }
        $this->realBoundParams[$param] = $value;
        return true;
    }

    /**
     * $args 有异议
     */
    public function fetchAll(int $mode = \PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        // return parent::fetchAll($mode, ...$args);
        return $this->queryResult->fetch_all(MYSQLI_ASSOC);
    }

    public function fetch(int $mode = \PDO::FETCH_DEFAULT, mixed ...$args): mixed
    {
        // return parent::fetch($mode, ...$args);
        return $this->queryResult->fetch_assoc();
    }

    /**
     * 影响行数
     */
    public function rowCount(): int
    {
        return $this->mysqli->affected_rows;
    }

    /**
     * 替换问号
     */
    public function buildSql(): string
    {
        ksort($this->realBoundParams);
        $values = $this->realBoundParams;
        $result = preg_replace_callback(
            '/\?/',
            static function () use (&$values) {
                $val = array_shift($values);
                // null 用NULL字符串代替 sql中时没有引号的
                return is_null($val) ? 'NULL' : $val;
            },
            $this->sql
        );

        return $result;
    }
}
