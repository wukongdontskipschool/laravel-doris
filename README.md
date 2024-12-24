# Laravel-doris-cte
让drois和starrocks直接使用查询构造器和ORM还有cte<br/>
This laravel extension adds support for doris and starrocks to the query builder and eloquent and common table expressions (CTE)<br/>

## Require
mysqli<br/>
PDO<br/>
[laravel-cte](https://github.com/staudenmeir/laravel-cte)

## Installation

    // 根据laravel版本自行安装 Install yourself according to the laravel version
    composer require "staudenmeir/laravel-cte":"^1.5"

    composer require "wukongdontskipschool/laravel-doris":"dev-cte-2.0.0.0"

## Use
```
// config/database.php
// connections inside add

'doris' => [
    'driver' => 'doris',
    'url' => env('DORIS_DATABASE_URL'),
    'host' => env('DORIS_DB_HOST', '127.0.0.1'),
    'port' => env('DORIS_DB_PORT', '9030'),
    'database' => env('DORIS_DB_DATABASE', 'forge'),
    'username' => env('DORIS_DB_USERNAME', 'forge'),
    'password' => env('DORIS_DB_PASSWORD', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix' => env('DB_PREFIX', ''),
    'strict' => env('DB_STRICT_MODE', true),
    'options' => [
        // 连接超时时间秒 Connection timeout time Second
        PDO::ATTR_TIMEOUT => 3,
        // 是否数值类型转字符串 Whether numeric type is converted to string
        PDO::ATTR_EMULATE_PREPARES => false
    ]
],


DB::connection('doris')
    ->table('p')
    ->select('p.*')
    ->withExpression('p', DB::connection('doris')->table(tableName))
    ->get();
```

### Lumen需额外注册 Lumen Additional registration required
```
// bootstrap/app.php
// add
$app->register(\Staudenmeir\LaravelCte\DatabaseServiceProvider::class);
$app->register(\Wukongdontskipschool\LaravelDoris\DatabaseServiceProvider::class);
```

## 备注 Remark
```
insert() 是不会返回id的，需要自己获取
         It will not return the id, you need to get your own
delete() 影响的行数始终返回0
         The number of rows affected always returns 0
cursor() 不是真的游标，doris还是会一次性全部返回
         It's not a real cursor. doris will still return all at once
```

## Versions

| Laravel | Package |
|:--------|:--------|
| 11.x    | unknown    |
| 10.x    | 2.0.0.0     |
| 9.x     | unknown     |
| 8.x     | 2.0.0.0   |
| 7.x     | unknown     |
| 6.x     | unknown     |
| 5.8     | unknown     |
| 5.5–5.7 | unknown     |


## 升级事项 Upgrade Notes

### 1.x 升级到 2.x
- DB::connection('doris')->table($tableName)->get();<br>
元素由数组升级为和mysql相对应的对象结构<br>
The element is upgraded from an array to an object structure corresponding to mysql
- 支持事务（支持的sql需要参考不同版本的doris）<br>
Support transaction (The supported sql needs to refer to different versions of doris)
- 插入二进制时转为明文字符串<br>
Converts to plaintext string when inserting binary
