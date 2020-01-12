<?php

// 自动加载类文件
spl_autoload_register(function ($class) {
    strpos($class, 'PHPUnit') === 0 || require __DIR__ . '/../' . str_replace('\\', '/', $class) . '.php';
});

// 加载助手函数
require __DIR__ . '/helpers.php';
require __DIR__ . '/../app/helpers.php';

set_error_handler(function ($errno, $errstr) {
    throw new Error($errstr);
});

// 设置模型的数据库连接
\vendor\Model::setConnector(function ($model) {
    return \src\Mysql::table($model->getTable())->where('id', $model->id);
});
