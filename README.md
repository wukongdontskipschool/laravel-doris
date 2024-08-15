# Laravel-doris
让drois和starrocks直接使用查询构造器和ORM<br/>
This laravel extension adds support for drois and starrocks to the query builder and eloquent.<br/>
不建议在生产环境使用<br/>
Not recommended for use in production environments<br/>

## Require
mysqli<br/>
PDO<br/>

## Installation

    composer require "wukongdontskipschool/laravel-doris" "^1.0.0"

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
```

### lumen需额外注册 Lumen Additional registration required
```
// bootstrap/app.php
// add
$app->register(\Wukongdontskipschool\LaravelDoris\DatabaseServiceProvider::class);
```

## 备注 Remark
#### Example
```
DB::connection('doris')->select('show tables');

XXModel::where('value', '=', 1)->get();
```

#### DB查询结果集 DB Query result
```
// DB开头的查询返回结果集的类型和其他驱动的不同
// The type of result set returned by a query that starts with DB is different from that of other drivers
$dorisres = DB::connection('doris')->table(tableName)->all();
$mysqlres = DB::connection('mysql')->table(tableName)->all();
dd($dorisres, $mysqlres);

// doris items里的元素类型是array
// The element type in doris items is array
^ Illuminate\Support\Collection^ {#375
  #items: array:1 [
    0 => array:2 [
      "id" => 1
      "name" => "name"
    ]
  ]
  #escapeWhenCastingToString: false
}

// mysql items里的元素类型是object
// The element type in mysql items is object
Illuminate\Support\Collection^ {#368
  #items: array:1 [
    0 => {#370id
      +"id": 1
      +"name": "name"
    }
  ]
  #escapeWhenCastingToString: false
}

```


#### 其他 Other
```
insert() 是不会返回id的，需要自己获取
         It will not return the id, you need to get your own
cursor() 不是真的游标，doris还是会一次性全部返回
         It's not a real cursor. doris will still return all at once
```

## Versions

| Laravel | Package |
|:--------|:--------|
| 11.x    | unknown    |
| 10.x    | unknown     |
| 9.x     | unknown     |
| 8.x     | 1.0.0   |
| 7.x     | unknown     |
| 6.x     | unknown     |
| 5.8     | unknown     |
| 5.5–5.7 | unknown     |
