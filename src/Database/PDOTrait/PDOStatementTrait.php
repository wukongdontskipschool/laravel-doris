<?php

namespace Wukongdontskipschool\LaravelDoris\Database\PDOTrait;

trait PDOStatementTrait
{
    /**
     * @var \mysqli_stmt
     */
    private $stmt;

    /**
     * @var \mysqli
     */
    private $mysqli;

    /**
     * @var string // 待执行sql
     */
    private $sql = '';

    /**
     * @var array // 执行参数
     */
    private $options = [];

    /**
     * @var array // 实际绑定参数 支持? 不支持:name
     */
    private $realBoundParams = [];

    private $fetchMode = \PDO::FETCH_OBJ;

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

    public function setFetchMode($mode, $className = null, ...$params)
    {
        $this->fetchMode = $mode;
        // return parent::setFetchMode($mode, $className, ...$params);
    }

    /**
     * 执行sql
     * @param array|null $params
     * @return bool
     * @throws
     */
    public function execute($params = null): bool
    {
        // parent::execute();
        $sql = $this->buildSql();
        $res = $this->mysqli->query($sql);

        if ($res instanceof \mysqli_result) {
            $this->queryResult = $res;
            return true;
        }

        return $res;
    }

    /**
     * 影响行数
     */
    public function rowCount(): int
    {
        return $this->mysqli->affected_rows;
    }

    /**
     * 绑定值
     * @param int|string $param
     * @param mixed $value
     * @param int $type
     * @throws
     */
    public function bindValue($param, $value, $type = \PDO::PARAM_STR): bool
    {
        // parent::bindValue();
        switch ($type) {
            case \PDO::PARAM_INT:
                $value = (string) $value;
                break;
            case \PDO::PARAM_BOOL:
                $value = $this->escapeBool($value);
                break;
            case \PDO::PARAM_NULL:
                $value = 'NULL';
                break;
            case \PDO::PARAM_STR:
                $value = $this->escapeString($value);
                break;
            case \PDO::PARAM_LOB:
                $value = $this->escapeBinary($value);
                break;
            default:
                throw new \Exception('Unsupported parameter type: ' . $type);
        }

        $this->realBoundParams[$param] = $value;
        return true;
    }

    /**
     * 替换问号
     */
    private function buildSql(): string
    {
        return $this->substituteBindingsIntoRawSql($this->sql, $this->realBoundParams);
    }

    /**
     * Substitute the given bindings into the given raw SQL query.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return string
     */
    private function substituteBindingsIntoRawSql($sql, $bindings)
    {
        $query = '';

        $isStringLiteral = false;

        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $nextChar = $sql[$i + 1] ?? null;

            // Single quotes can be escaped as '' according to the SQL standard while
            // MySQL uses \'. Postgres has operators like ?| that must get encoded
            // in PHP like ??|. We should skip over the escaped characters here.
            if (in_array($char . $nextChar, ["\'", "''", '??'])) {
                $query .= $char . $nextChar;
                $i += 1;
            } elseif ($char === "'") { // Starting / leaving string literal...
                $query .= $char;
                $isStringLiteral = ! $isStringLiteral;
            } elseif ($char === '?' && ! $isStringLiteral) { // Substitutable binding...
                $query .= array_shift($bindings) ?? '?';
            } else { // Normal character...
                $query .= $char;
            }
        }

        return $query;
    }

    /**
     * Escape a boolean value for safe SQL embedding.
     *
     * @param  bool  $value
     * @return string
     */
    private function escapeBool($value)
    {
        return $value ? '1' : '0';
    }

    /**
     * Escape a string value for safe SQL embedding.
     *
     * @param  string  $value
     * @return string
     * @throws
     */
    private function escapeString($value)
    {
        if (!preg_match('//u', $value)) {
            return "'" . $this->bin2Text($value) . "'";
        }

        if (str_contains($value, "\00")) {
            throw new \Exception('Strings with null bytes cannot be escaped. Use the binary escape option.');
        }

        if (preg_match('//u', $value) === false) {
            throw new \Exception('Strings with invalid UTF-8 byte sequences cannot be escaped.');
        }

        return "'" . $this->mysqli->real_escape_string($value) . "'";
    }

    /**
     * Escape a binary value for safe SQL embedding.
     *
     * @param  string  $value
     * @return string 16进制
     */
    private function escapeBinary($value)
    {
        // 转16进制
        $hex = bin2hex($value);

        return "'{$hex}'";
    }

    /**
     * 二进制转字符串
     * @throws
     */
    private function bin2Text($value)
    {
        return $this->hex2Text(bin2hex($value));
    }

    /**
     * 16进制转字符串
     * @throws
     */
    private function hex2Text($hex)
    {
        // 检查是否为有效的十六进制字符串
        if (!ctype_xdigit($hex)) {
            throw new \Exception('Text nust be a hexadecimal, that is a decimal digit or a character from [A-Fa-f].');
        }

        // 如果长度为奇数，补充一个0
        if (strlen($hex) % 2 != 0) {
            throw new \Exception('The length must be an even number.');
        }

        // 转换为二进制，然后解压缩
        $binary = hex2bin($hex);
        $decompressed = @gzuncompress($binary);

        if ($decompressed === false) {
            throw new \Exception('Binary decoding error.');
        }

        return $decompressed;
    }
}