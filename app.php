<?php

// 自动加载类文件
spl_autoload_register(function ($class) {
    strpos($class, 'PHPUnit') === 0 || require __DIR__.'/'.str_replace('\\', '/', $class).'.php';
});

// 加载助手函数
require __DIR__.'/helpers.php';
require __DIR__.'/app/helpers.php';

// 绑定Redis类到容器中
\src\Container::bind('Redis', function () {
    $redis = new Redis;
    $config = config('redis');
    $redis->connect($config['host']);
    // $redis->auth($config['pwd']);
    return $redis;
});

// 设置模型的数据库连接
\src\Model::setConnector(function ($model) {
    return mysql($model->getTable())->where('id', $model->id);
});
